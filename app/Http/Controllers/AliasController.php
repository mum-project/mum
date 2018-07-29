<?php

namespace App\Http\Controllers;

use App\Alias;
use App\Domain;
use App\Http\Filters\AliasFilter;
use App\Http\Resources\AliasResource;
use App\Http\Resources\MailboxResource;
use App\Mailbox;
use App\Rules\AliasesAvailable;
use App\Rules\UniqueEmailAddress;
use function array_key_exists;
use function array_push;
use function compact;
use function flash;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use function redirect;
use function response;
use Carbon\Carbon;

class AliasController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param AliasFilter $queryFilter
     * @param ControllerHelper $controllerHelper
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function index(Request $request, AliasFilter $queryFilter, ControllerHelper $controllerHelper)
    {
        $aliases = Alias::whereAuthorized()
            ->filter($queryFilter)
            ->paginate()
            ->appends($queryFilter->filters());

        if ($request->wantsJson()) {
            return AliasResource::collection($aliases);
        }

        $searchHiddenInputValues = $controllerHelper->generateSearchHiddenInputValues($queryFilter);

        return response()->view('aliases.index', compact('aliases', 'searchHiddenInputValues'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', Alias::class);

        $availableDomainOptions = $this->getAvailableDomainOptions();

        return response()->view('aliases.create', compact('availableDomainOptions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        $this->authorize('create', Alias::class);

        $validated = $request->validate(array_merge([
            'local_part'            => [
                'string',
                'required',
                new UniqueEmailAddress($request->get('domain_id'))
            ],
            'description'           => 'string|nullable',
            'domain_id'             => [
                'exists:domains,id',
                'required',
                new AliasesAvailable
            ],
            'deactivate_at_days'    => 'integer|nullable',
            'deactivate_at_hours'   => 'integer|nullable',
            'deactivate_at_minutes' => 'integer|nullable'
        ], $this->getSendersAndRecipientsValidationRules()));

        $validated['deactivate_at'] = $this->getDeactivateAtValue($validated);

        $alias = Alias::create(array_except($validated, [
            'sender_mailboxes',
            'recipient_mailboxes',
            'external_recipients',
            'deactivate_at_days',
            'deactivate_at_hours',
            'deactivate_at_minutes'
        ]));

        $this->setSendersAndRecipients($validated, $alias);

        flash('success', $alias->address() . ' was created successfully.');
        return redirect()->route('aliases.show', compact('alias'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Alias $alias
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Alias $alias)
    {
        $this->authorize('view', $alias);

        /** @var Collection $senderMailboxes */
        $senderMailboxes = $alias->senderMailboxes;
        $recipientMailboxes = $alias->recipientMailboxes;
        $senderAndRecipientMailboxes = $senderMailboxes->intersect($recipientMailboxes);
        $senderMailboxes = $senderMailboxes->diff($senderAndRecipientMailboxes);
        $recipientMailboxes = $recipientMailboxes->diff($senderAndRecipientMailboxes);
        $externalRecipients = $alias->externalRecipients();

        return response()->view(
            'aliases.show',
            compact(
                'alias',
                'senderAndRecipientMailboxes',
                'senderMailboxes',
                'recipientMailboxes',
                'externalRecipients'
            )
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Alias $alias
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Alias $alias)
    {
        $this->authorize('update', $alias);

        $availableDomainOptions = $this->getAvailableDomainOptions();
        $senderMailboxes = MailboxResource::collection($alias->senderMailboxes);
        $recipientMailboxes = MailboxResource::collection($alias->recipientMailboxes);
        $externalRecipients = $alias->getExternalRecipientResource();

        return response()->view(
            'aliases.edit',
            compact('alias', 'availableDomainOptions', 'senderMailboxes', 'recipientMailboxes', 'externalRecipients')
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Alias $alias
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Alias $alias)
    {
        $this->authorize('update', $alias);

        $localPartValidationRules = [];


        if ($request->get('local_part') !== $alias->local_part && $request->get('local_part') !== null) {
            $localPartValidationRules = array_merge($localPartValidationRules, [
                'local_part' => [
                    'string',
                    new UniqueEmailAddress($request->get('domain_id'))
                ]
            ]);
        }

        $validationRules = array_merge([
            'domain_id'             => [
                'exists:domains,id',
                'integer'
            ],
            'description'           => 'string|nullable',
            'active'                => 'boolean',
            'deactivate_at_days'    => 'integer|nullable',
            'deactivate_at_hours'   => 'integer|nullable',
            'deactivate_at_minutes' => 'integer|nullable'
        ], $this->getSendersAndRecipientsValidationRules(), $localPartValidationRules);

        if ($request->get('domain_id') !== $alias->domain_id) {
            array_push($validationRules['domain_id'], new AliasesAvailable);
        }

        $validated = $request->validate($validationRules);

        $validated['deactivate_at'] = $this->getDeactivateAtValue($validated);

        $alias->update(array_except($validated, [
            'sender_mailboxes',
            'recipient_mailboxes',
            'external_recipients',
            'deactivate_at_days',
            'deactivate_at_hours',
            'deactivate_at_minutes'
        ]));

        $this->setSendersAndRecipients($validated, $alias);

        flash('success', $alias->address() . ' was updated successfully.');
        return redirect()->route('aliases.show', compact('alias'));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param Alias $alias
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(Alias $alias)
    {
        $this->authorize('delete', $alias);
        $alias->delete();
        flash('success', $alias->address() . ' was deleted successfully.');
        return redirect()->route('aliases.index');
    }

    /**
     * Gets an array to use in a blade component select box
     * with all domains that the authenticated mailbox user
     * is authorized for.
     *
     * @return array
     */
    private function getAvailableDomainOptions()
    {
        return Domain::whereAuthorized()
            ->get()
            ->map(function (Domain $domain) {
                return [
                    'label' => $domain->domain,
                    'value' => $domain->id
                ];
            });
    }

    /**
     * Calculate the timestamp when the alias should be deactivated.
     * Returns null if no deactivate at inputs are present.
     *
     * @param array $validated
     * @return Carbon|null
     */
    private function getDeactivateAtValue(array $validated)
    {
        if (!$this->doesDeactivateAtInputExist($validated)) {
            return null;
        }
        if (array_key_exists('active', $validated) && $validated['active'] === true) {
            return null;
        }
        return Carbon::now()
            ->addDays($this->getDeactivateAtInputWithFallback('deactivate_at_days', $validated))
            ->addHours($this->getDeactivateAtInputWithFallback('deactivate_at_hours', $validated))
            ->addMinutes($this->getDeactivateAtInputWithFallback('deactivate_at_minutes', $validated));
    }

    /**
     * Assert whether any deactivate at inputs were validated.
     *
     * @param array $validated
     * @return bool
     */
    private function doesDeactivateAtInputExist(array $validated)
    {
        return array_key_exists('deactivate_at_days', $validated) ||
            array_key_exists('deactivate_at_hours', $validated) ||
            array_key_exists('deactivate_at_minutes', $validated);
    }

    /**
     * Gets the deactivate at input value or a fallback if it is null.
     * The fallback is 0 by default, you may supply something else.
     *
     * @param string $name
     * @param array $validated
     * @param int $fallback
     * @return int|mixed
     */
    private function getDeactivateAtInputWithFallback(string $name, array $validated, $fallback = 0)
    {
        return array_key_exists($name, $validated) ? $validated[$name] : $fallback;
    }

    /**
     * Get the validation rules needed to validate sender_mailboxes,
     * recipient_mailboxes and external_recipients.
     *
     * @return array
     */
    private function getSendersAndRecipientsValidationRules()
    {
        return [
            'sender_mailboxes'              => 'required_without_all:recipient_mailboxes,external_recipients|array',
            'recipient_mailboxes'           => 'required_without_all:sender_mailboxes,external_recipients|array',
            'external_recipients'           => 'required_without_all:sender_mailboxes,recipient_mailboxes|array',
            'sender_mailboxes.*.id'         => 'required_with:sender_mailboxes|exists:mailboxes,id',
            'recipient_mailboxes.*.id'      => 'required_with:recipient_mailboxes|exists:mailboxes,id',
            'external_recipients.*.address' => 'required_with:external_recipients|email',
        ];
    }

    /**
     * Add all senders and recipients (mailboxes and external email addresses)
     * included in the validated request parameters to the alias.
     *
     * @param array $validated
     * @param Alias $alias
     */
    private function setSendersAndRecipients(array $validated, Alias $alias)
    {
        $alias->senderMailboxes()
            ->detach();
        if (array_key_exists('sender_mailboxes', $validated)) {
            foreach ($validated['sender_mailboxes'] as $mailboxInput) {
                if ($alias->senderMailboxes()
                    ->where('mailbox_id', $mailboxInput['id'])
                    ->doesntExist()) {
                    $alias->senderMailboxes()
                        ->save(Mailbox::findOrFail($mailboxInput['id']));
                }
            }
        }

        $alias->removeAllRecipientMailboxes();
        if (array_key_exists('recipient_mailboxes', $validated)) {
            foreach ($validated['recipient_mailboxes'] as $mailboxInput) {
                if ($alias->recipientMailboxes()
                    ->where('mailbox_id', $mailboxInput['id'])
                    ->doesntExist()) {
                    $alias->addRecipientMailbox(Mailbox::findOrFail($mailboxInput['id']));
                }
            }
        }

        $alias->removeAllExternalRecipients();
        if (array_key_exists('external_recipients', $validated)) {
            foreach ($validated['external_recipients'] as $recipientInput) {
                $alias->addExternalRecipient($recipientInput['address']);
            }
        }
    }
}
