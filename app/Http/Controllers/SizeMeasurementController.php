<?php

namespace App\Http\Controllers;

use function abort_if;
use App\Domain;
use App\Http\Resources\SizeMeasurementResource;
use App\Mailbox;
use function array_combine;
use function compact;
use function flash;
use Illuminate\Http\Request;
use function isUserSuperAdmin;
use function response;

class SizeMeasurementController extends Controller
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
     * @param Domain  $domain
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function indexForDomain(Request $request, Domain $domain)
    {
        $this->authorize('view', $domain);

        $sizeMeasurements = $domain->sizeMeasurements()
            ->get()
            ->sortBy('created_at', null, false);

        if ($request->wantsJson()) {
            return SizeMeasurementResource::collection($sizeMeasurements);
        }

        return response()->view('domains.sizes', compact('domain', 'sizeMeasurements'));
    }


    /**
     * Remove the resources from storage.
     *
     * @param Request $request
     * @param Domain  $domain
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyForDomain(Request $request, Domain $domain)
    {
        abort_if(!isUserSuperAdmin(), 403);

        $domain->sizeMeasurements()
            ->delete();

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        flash('success', 'Size measurements for ' . $domain->domain . ' were deleted successfully.');
        return redirect()->route('domains.sizes', compact('domain'));
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param Mailbox $mailbox
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function indexForMailbox(Request $request, Mailbox $mailbox)
    {
        $this->authorize('view', $mailbox);

        $sizeMeasurements = $mailbox->sizeMeasurements()
            ->get()
            ->sortBy('created_at', null, false);

        if ($request->wantsJson()) {
            return SizeMeasurementResource::collection($sizeMeasurements);
        }

        return response()->view('mailboxes.sizes', compact('mailbox', 'sizeMeasurements'));
    }

    /**
     * Remove the resources from storage.
     *
     * @param Request $request
     * @param Mailbox $mailbox
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyForMailbox(Request $request, Mailbox $mailbox)
    {
        abort_if(!isUserSuperAdmin(), 403);

        $mailbox->sizeMeasurements()
            ->delete();

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        flash('success', 'Size measurements for ' . $mailbox->address() . ' were deleted successfully.');
        return redirect()->route('mailboxes.sizes', compact('mailbox'));
    }
}
