<?php

namespace App\Http\Controllers;

use App\Domain;
use App\Http\Filters\MailboxFilter;
use App\Http\Resources\MailboxResource;
use App\Mailbox;
use App\Rules\MailboxesAvailable;
use App\Rules\UniqueEmailAddress;
use App\Rules\ValidLocalPart;
use Illuminate\Support\Arr;
use function array_except;
use function array_key_exists;
use function compact;
use function config;
use function getHomedirForMailbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use function isUserSuperAdmin;

class MailboxController extends Controller
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
     * @param Request          $request
     * @param MailboxFilter    $queryFilter
     * @param ControllerHelper $controllerHelper
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function index(Request $request, MailboxFilter $queryFilter, ControllerHelper $controllerHelper)
    {
        $mailboxes = Mailbox::filter($queryFilter)
            ->whereAuthorized()
            ->paginate()
            ->appends($queryFilter->filters());

        if ($request->wantsJson()) {
            return MailboxResource::collection($mailboxes);
        }

        $searchHiddenInputValues = $controllerHelper->generateSearchHiddenInputValues($queryFilter);

        return view('mailboxes.index', compact('mailboxes', 'searchHiddenInputValues'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', Mailbox::class);

        $availableDomainOptions = $this->getAvailableDomainOptions();

        return view('mailboxes.create', compact('availableDomainOptions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        $this->authorize('create', Mailbox::class);

        $validationRules = [
            'local_part'        => [
                'required',
                'string',
                new ValidLocalPart,
                new UniqueEmailAddress($request->get('domain_id'))
            ],
            'password'          => 'required|string|confirmed|min:' . config('auth.password_min_length'),
            'domain_id'         => [
                'required',
                'exists:domains,id',
                new MailboxesAvailable
            ],
            'name'              => 'string|nullable',
            'alternative_email' => 'email|nullable',
            'quota'             => 'integer|nullable',
            'send_only'         => 'boolean',
            'active'            => 'boolean'
        ];
        if (isUserSuperAdmin()) {
            $validationRules['is_super_admin'] = 'boolean';
        }

        $validated = $request->validate($validationRules);
        $mailbox = new Mailbox($validated);
        $mailbox->password = Hash::make($validated['password']);
        $mailbox->save();

        flash('success', $mailbox->address() . ' was created successfully.');
        return redirect()->route('mailboxes.show', compact('mailbox'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Mailbox $mailbox
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Mailbox $mailbox)
    {
        $this->authorize('view', $mailbox);

        return view('mailboxes.show', compact('mailbox'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Mailbox $mailbox
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Mailbox $mailbox)
    {
        $this->authorize('update', $mailbox);

        $availableDomainOptions = $this->getAvailableDomainOptions();

        return view('mailboxes.edit', compact('mailbox', 'availableDomainOptions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Mailbox             $mailbox
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Mailbox $mailbox)
    {
        $this->authorize('update', $mailbox);

        $validationRules = [
            'password'          => 'nullable|string|confirmed|min:' . config('auth.password_min_length'),
            'name'              => 'string|nullable',
            'alternative_email' => 'email|nullable',
        ];

        if (Auth::user()
            ->isSuperAdmin()) {
            $validationRules['is_super_admin'] = 'boolean';
        }

        if (Auth::user()
                ->isSuperAdmin() || Auth::user()->administratedMailboxes->contains($mailbox) ||
            Auth::user()->administratedDomains->contains($mailbox->domain)) {
            $validationRules['quota'] = 'integer|nullable';
            $validationRules['send_only'] = 'boolean';
            $validationRules['active'] = 'boolean';
        }

        $validated = $request->validate($validationRules);
        $mailbox->update(Arr::except($validated, 'password'));

        if (array_key_exists('password', $validated) && $validated['password']) {
            $mailbox->password = Hash::make($validated['password']);
            $mailbox->save();
        }

        flash('success', $mailbox->local_part . ' was updated successfully.');
        return redirect()->route('mailboxes.show', compact('mailbox'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Mailbox $mailbox
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Mailbox $mailbox)
    {
        $this->authorize('delete', $mailbox);

        $mailbox->delete();
        return redirect()->route('mailboxes.index');
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
}
