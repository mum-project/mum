<?php

namespace App\Http\Controllers;

use App\AliasRequest;
use App\Domain;
use App\Http\Filters\AliasRequestFilter;
use App\Http\Resources\AliasRequestResource;
use App\Http\Resources\MailboxResource;
use App\Mailbox;
use App\Notifications\AliasRequestCreatedNotification;
use App\Notifications\AliasRequestStatusNotification;
use App\Rules\AliasesAvailable;
use App\Rules\UniqueEmailAddress;
use App\Rules\ValidLocalPart;
use function array_except;
use function array_key_exists;
use function array_merge;
use function array_push;
use function compact;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class AliasRequestController extends Controller
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
     * @param Request            $request
     * @param AliasRequestFilter $queryFilter
     * @param ControllerHelper   $controllerHelper
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function index(Request $request, AliasRequestFilter $queryFilter, ControllerHelper $controllerHelper)
    {
        if (!$queryFilter->hasFilter('status')) {
            $queryFilter->addFilter('status', 'open');
        }

        $aliasRequests = AliasRequest::whereAuthorized()
            ->filter($queryFilter)
            ->paginate();

        if ($request->wantsJson()) {
            return AliasRequestResource::collection($aliasRequests);
        }

        $searchHiddenInputValues = $controllerHelper->generateSearchHiddenInputValues($queryFilter);

        return response()->view('alias-requests.index', compact('aliasRequests', 'searchHiddenInputValues'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', AliasRequest::class);

        $availableDomainOptions = $this->getAvailableDomainOptions();

        return response()->view('alias-requests.create', compact('availableDomainOptions'));
    }

    /**
     * Store a newly created resource in storage and notify the admins.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        $this->authorize('create', AliasRequest::class);

        $validated = $request->validate(array_merge([
            'local_part'  => [
                'required',
                'string',
                new ValidLocalPart,
                new UniqueEmailAddress($request->get('domain_id'))
            ],
            'domain_id'   => [
                'required',
                'exists:domains,id',
                new AliasesAvailable
            ],
            'description' => 'nullable|string',
        ], $this->getSendersAndRecipientsValidationRules()));

        $validated['mailbox_id'] = Auth::id();

        /** @var AliasRequest $aliasRequest */
        $aliasRequest = AliasRequest::create(Arr::except($validated, [
            'sender_mailboxes',
            'recipient_mailboxes',
            'external_recipients',
        ]));

        $this->setSendersAndRecipients($validated, $aliasRequest);

        Notification::send(Mailbox::query()
            ->isSuperAdmin()
            ->get(), new AliasRequestCreatedNotification($aliasRequest));

        flash('success', 'Request for ' . $aliasRequest->address() . ' was created successfully.');
        return redirect()->route('alias-requests.show', compact('aliasRequest'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AliasRequest $aliasRequest
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(AliasRequest $aliasRequest)
    {
        $this->authorize('view', $aliasRequest);

        /** @var Collection $senderMailboxes */
        $senderMailboxes = $aliasRequest->senderMailboxes;
        $recipientMailboxes = $aliasRequest->recipientMailboxes;
        $senderAndRecipientMailboxes = $senderMailboxes->intersect($recipientMailboxes);
        $senderMailboxes = $senderMailboxes->diff($senderAndRecipientMailboxes);
        $recipientMailboxes = $recipientMailboxes->diff($senderAndRecipientMailboxes);
        $externalRecipients = $aliasRequest->externalRecipients();

        return response()->view(
            'alias-requests.show',
            compact(
                'aliasRequest',
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
     * @param  \App\AliasRequest $aliasRequest
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(AliasRequest $aliasRequest)
    {
        $this->authorize('update', $aliasRequest);

        $availableDomainOptions = $this->getAvailableDomainOptions();
        $senderMailboxes = MailboxResource::collection($aliasRequest->senderMailboxes);
        $recipientMailboxes = MailboxResource::collection($aliasRequest->recipientMailboxes);
        $externalRecipients = $aliasRequest->getExternalRecipientResource();

        return response()->view(
            'alias-requests.edit',
            compact(
                'aliasRequest',
                'availableDomainOptions',
                'senderMailboxes',
                'recipientMailboxes',
                'externalRecipients'
            )
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\AliasRequest        $aliasRequest
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, AliasRequest $aliasRequest)
    {
        $this->authorize('update', $aliasRequest);

        $validationRules = array_merge([
            'local_part'  => [
                'string',
                new UniqueEmailAddress($request->get('domain_id')),
                new ValidLocalPart
            ],
            'domain_id'   => [
                'exists:domains,id',
                'integer',
            ],
            'description' => 'nullable|string',
        ], $this->getSendersAndRecipientsValidationRules());

        if ($request->get('domain_id') !== $aliasRequest->domain_id) {
            array_push($validationRules, new AliasesAvailable);
        }

        if (isUserSuperAdmin()) {
            $validationRules['status'] = 'in:open,approved,dismissed';
        }

        $validated = $request->validate($validationRules);

        $aliasRequest->update(Arr::except($validated, [
            'sender_mailboxes',
            'recipient_mailboxes',
            'external_recipients',
        ]));

        $this->setSendersAndRecipients($validated, $aliasRequest);

        flash('success', 'Request for ' . $aliasRequest->address() . ' was updated successfully.');
        return redirect()->route('alias-requests.show', compact('aliasRequest'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\AliasRequest        $aliasRequest
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateStatus(Request $request, AliasRequest $aliasRequest)
    {
        $this->authorize('updateStatus', $aliasRequest);

        $validated = $request->validate(['status' => 'required|in:open,approved,dismissed']);

        $aliasRequest->update($validated);

        if ($validated['status'] === 'approved') {
            $alias = $aliasRequest->generateAlias();
            Notification::send($aliasRequest->mailbox, new AliasRequestStatusNotification($aliasRequest));
            flash('success', 'Request for ' . $aliasRequest->address() . ' was approved successfully.');
            return redirect()->route('aliases.show', compact('alias'));
        }

        if ($validated['status'] === 'dismissed') {
            Notification::send($aliasRequest->mailbox, new AliasRequestStatusNotification($aliasRequest));
        }

        flash('success', 'Request for ' . $aliasRequest->address() . ' was updated successfully.');
        return redirect()->route('alias-requests.show', compact('aliasRequest'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AliasRequest $aliasRequest
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(AliasRequest $aliasRequest)
    {
        $this->authorize('delete', $aliasRequest);

        $aliasRequest->delete();

        flash('success', 'Request for ' . $aliasRequest->address() . ' was deleted successfully.');
        return redirect()->route('alias-requests.index');
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
            ->orWhere('id', Auth::user()->domain_id)
            ->get()
            ->map(function (Domain $domain) {
                return [
                    'label' => $domain->domain,
                    'value' => $domain->id
                ];
            });
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
     * included in the validated request parameters to the alias request.
     *
     * @param array        $validated
     * @param AliasRequest $aliasRequest
     */
    private function setSendersAndRecipients(array $validated, AliasRequest $aliasRequest)
    {
        $aliasRequest->senderMailboxes()
            ->detach();
        if (array_key_exists('sender_mailboxes', $validated)) {
            foreach ($validated['sender_mailboxes'] as $mailboxInput) {
                if ($aliasRequest->senderMailboxes()
                    ->where('mailbox_id', $mailboxInput['id'])
                    ->doesntExist()) {
                    $aliasRequest->senderMailboxes()
                        ->save(Mailbox::findOrFail($mailboxInput['id']));
                }
            }
        }

        $aliasRequest->removeAllRecipientMailboxes();
        if (array_key_exists('recipient_mailboxes', $validated)) {
            foreach ($validated['recipient_mailboxes'] as $mailboxInput) {
                if ($aliasRequest->recipientMailboxes()
                    ->where('mailbox_id', $mailboxInput['id'])
                    ->doesntExist()) {
                    $aliasRequest->addRecipientMailbox(Mailbox::findOrFail($mailboxInput['id']));
                }
            }
        }

        $aliasRequest->removeAllExternalRecipients();
        if (array_key_exists('external_recipients', $validated)) {
            foreach ($validated['external_recipients'] as $recipientInput) {
                $aliasRequest->addExternalRecipient($recipientInput['address']);
            }
        }
    }
}
