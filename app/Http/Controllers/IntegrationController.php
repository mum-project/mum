<?php

namespace App\Http\Controllers;

use App\Alias;
use App\Domain;
use App\Exceptions\NotImplementedException;
use App\Http\Middleware\AreIntegrationsEnabled;
use App\Http\Resources\IntegrationParameterResource;
use App\Integration;
use App\Mailbox;
use App\ShellCommandIntegration;
use App\WebHookIntegration;
use function array_push;
use function config;
use function flash;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use function sprintf;

class IntegrationController extends Controller
{
    protected $availableModelOptions = [
        [
            'label' => 'Domain',
            'value' => Domain::class
        ],
        [
            'label' => 'Mailbox',
            'value' => Mailbox::class
        ],
        [
            'label' => 'Alias',
            'value' => Alias::class
        ]
    ];

    protected $eventTypeOptions = [
        'created',
        'updated',
        'deleted'
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(AreIntegrationsEnabled::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', Integration::class);

        $integrations = Integration::paginate();
        return view('integrations.index', compact('integrations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', Integration::class);
        return view('integrations.create', [
            'availableModelOptions'           => $this->availableModelOptions,
            'eventTypeOptions'                => $this->eventTypeOptions,
            'shellCommandOptions'             => $this->getAvailableShellCommandOptions(),
            'availableIntegrationTypeOptions' => $this->getAvailableIntegrationTypeOptions(),
            'shellParametersEnabled'          => config('integrations.options.shell_commands.allow_parameters')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->authorize('create', Integration::class);

        $validationRules = [
            'model_class' => 'required|string|in:' . Domain::class . ',' . Mailbox::class . ',' . Alias::class,
            'event_type'  => 'required|in:created,updated,deleted',
            'name'        => 'string|nullable',
            'active'      => 'boolean',
        ];

        switch ($request->get('type')) {
            case 'shell_command':
                $integrationClass = ShellCommandIntegration::class;
                $validationRules['value'] = 'required|string|in:01,02,03,04,05,06,07,08,09,10';
                $validationRules = array_merge($validationRules, $this->getParametersValidationRules());
                break;
            case 'web_hook':
                $integrationClass = WebHookIntegration::class;
                $validationRules['value'] = 'required|url';
                break;
            case null:
                throw ValidationException::withMessages([
                    'type' => [trans('validation.required', ['attribute' => 'type'])]
                ]);
            default:
                throw ValidationException::withMessages([
                    'type' => [trans('validation.in', ['attribute' => 'type'])]
                ]);
        }

        $validated = $request->validate($validationRules);

        $integration = $integrationClass::create(array_except($validated, ['parameters']));

        $this->setIntegrationParameters($validated, $integration);

        flash('success', $integration->name . ' was created successfully.');
        return redirect()->route('integrations.show', compact('integration'));
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Integration $integration
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Integration $integration)
    {
        $this->authorize('view', $integration);

        return view('integrations.show', compact('integration'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Integration $integration
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Integration $integration)
    {
        $this->authorize('update', $integration);

        $integrationParameters = IntegrationParameterResource::collection($integration->parameters);

        return view('integrations.edit', [
            'integration'            => $integration,
            'availableModelOptions'  => $this->availableModelOptions,
            'eventTypeOptions'       => $this->eventTypeOptions,
            'shellCommandOptions'    => $this->getAvailableShellCommandOptions(),
            'integrationParameters'  => $integrationParameters,
            'shellParametersEnabled' => config('integrations.options.shell_commands.allow_parameters')
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Integration         $integration
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Integration $integration)
    {
        $this->authorize('update', $integration);

        $validationRules = [
            'model_class' => 'string|in:' . Domain::class . ',' . Mailbox::class . ',' . Alias::class,
            'event_type'  => 'in:created,updated,deleted',
            'name'        => 'string|nullable',
            'active'      => 'boolean'
        ];

        switch ($integration->type) {
            case ShellCommandIntegration::class:
                $validationRules['value'] = 'string|in:01,02,03,04,05,06,07,08,09,10';
                $validationRules = array_merge($validationRules, $this->getParametersValidationRules());
                break;
            case WebHookIntegration::class:
                $validationRules['value'] = 'url';
                break;
            default:
                throw new NotImplementedException('Integration type is not implemented yet.');
        }

        $validated = $request->validate($validationRules);

        $integration->update(array_except($validated, ['parameters']));

        $this->setIntegrationParameters($validated, $integration);

        flash('success', $integration->name . ' was updated successfully.');
        return redirect()->route('integrations.show', compact('integration'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Integration $integration
     * @return \Illuminate\Http\Response
     * @throws \Exception
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Integration $integration)
    {
        $this->authorize('delete', $integration);

        $integration->delete();

        flash('success', 'The integration was deleted successfully.');
        return redirect()->route('integrations.index');
    }

    private function getAvailableShellCommandOptions()
    {
        $options = [];

        for ($i = 1; $i <= 10; $i++) {
            if (config('integrations.shell_commands.' . sprintf('%02d', $i))) {
                array_push($options, [
                    'label' => config('integrations.shell_commands.' . sprintf('%02d', $i)),
                    'value' => sprintf('%02d', $i)
                ]);
            }
        }

        return $options;
    }

    private function getAvailableIntegrationTypeOptions()
    {
        $options = [];

        if (!config('integrations.enabled.generally')) {
            return $options;
        }

        if (config('integrations.enabled.shell_commands')) {
            array_push($options, [
                'label' => 'Shell Command',
                'value' => 'shell_command'
            ]);
        }

        if (config('integrations.enabled.web_hooks')) {
            array_push($options, [
                'label' => 'Webhook',
                'value' => 'web_hook'
            ]);
        }

        return $options;
    }

    private function getParametersValidationRules()
    {
        if (!config('integrations.options.shell_commands.allow_parameters')) {
            return [];
        }

        return [
            'parameters'                  => 'nullable|array',
            'parameters.*.option'         => 'nullable|string',
            'parameters.*.value'          => 'required|string',
            'parameters.*.use_equal_sign' => 'required|boolean'
        ];
    }

    private function setIntegrationParameters(array $validated, Integration $integration)
    {
        $integration->parameters()
            ->delete();

        if (array_key_exists('parameters', $validated)) {
            foreach ($validated['parameters'] as $parameter) {
                $integration->parameters()
                    ->create([
                        'option'         => $parameter['option'],
                        'value'          => $parameter['value'],
                        'use_equal_sign' => $parameter['use_equal_sign'],
                    ]);
            }
        }
    }
}
