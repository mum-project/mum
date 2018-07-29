<?php

namespace App\Http\Controllers;

use App\Domain;
use App\Http\Filters\DomainFilter;
use App\Http\Resources\DomainResource;
use App\Rules\Domain as DomainRule;
use Illuminate\Http\Request;

class DomainController extends Controller
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
     * @param DomainFilter     $queryFilter
     * @param ControllerHelper $controllerHelper
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function index(Request $request, DomainFilter $queryFilter, ControllerHelper $controllerHelper)
    {
        $domains = Domain::filter($queryFilter)
            ->whereAuthorized()
            ->paginate()
            ->appends($queryFilter->filters());

        if ($request->wantsJson()) {
            return DomainResource::collection($domains);
        }

        $searchHiddenInputValues = $controllerHelper->generateSearchHiddenInputValues($queryFilter);

        return view('domains.index', compact('domains', 'searchHiddenInputValues'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', Domain::class);
        return view('domains.create');
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
        $this->authorize('create', Domain::class);
        $validated = $request->validate([
            'domain'        => [
                'string',
                'required',
                'unique:domains,domain',
                new DomainRule
            ],
            'description'   => 'string|nullable',
            'quota'         => 'integer|nullable',
            'max_quota'     => 'integer|nullable',
            'max_aliases'   => 'integer|nullable',
            'max_mailboxes' => 'integer|nullable',
        ]);

        $domain = Domain::create($validated);

        flash('success', $domain->domain . ' was created successfully.');
        return redirect()->route('domains.show', compact('domain'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Domain $domain
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Domain $domain)
    {
        $this->authorize('view', $domain);
        return view('domains.show', compact('domain'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Domain $domain
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Domain $domain)
    {
        $this->authorize('update', $domain);
        return view('domains.edit', compact('domain'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Domain              $domain
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Domain $domain)
    {
        $this->authorize('update', $domain);
        $validated = $request->validate([
            'domain'        => [
                'string',
                'unique:domains,id,' . $domain->id,
                new DomainRule
            ],
            'description'   => 'string|nullable',
            'quota'         => 'integer|nullable',
            'max_quota'     => 'integer|nullable',
            'max_aliases'   => 'integer|nullable',
            'max_mailboxes' => 'integer|nullable',
            'active'        => 'boolean'
        ]);
        $domain->update($validated);

        flash('success', $domain->domain . ' was updated successfully.');
        return redirect()->route('domains.show', compact('domain'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Domain $domain
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(Domain $domain)
    {
        $this->authorize('delete', $domain);
        $domain->delete();

        flash('success', $domain->domain . ' was deleted successfully.');
        return redirect()->route('domains.index');
    }
}
