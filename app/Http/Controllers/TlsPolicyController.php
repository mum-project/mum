<?php

namespace App\Http\Controllers;

use App\Rules\Domain as DomainRule;
use App\TlsPolicy;
use function compact;
use function flash;
use Illuminate\Http\Request;
use function redirect;
use function response;

class TlsPolicyController extends Controller
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
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', TlsPolicy::class);

        $tlsPolicies = TlsPolicy::orderBy('domain', 'asc')
            ->paginate();
        return response()->view('tls-policies.index', compact('tlsPolicies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', TlsPolicy::class);

        return response()->view('tls-policies.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        $this->authorize('create', TlsPolicy::class);

        $validated = $request->validate([
            'domain'      => [
                'required',
                'unique:tls_policies,domain',
                new DomainRule
            ],
            'policy'      => 'required|in:none,may,encrypt,dane,dane-only,fingerprint,verify,secure',
            'params'      => 'string|nullable',
            'description' => 'string|nullable'
        ]);
        $tlsPolicy = TlsPolicy::create($validated);

        flash('success', 'Your TLS policy was created successfully.');
        return redirect()->route('tls-policies.show', compact('tlsPolicy'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\TlsPolicy $tlsPolicy
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(TlsPolicy $tlsPolicy)
    {
        $this->authorize('view', $tlsPolicy);

        return response()->view('tls-policies.show', compact('tlsPolicy'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TlsPolicy $tlsPolicy
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(TlsPolicy $tlsPolicy)
    {
        $this->authorize('update', $tlsPolicy);

        return response()->view('tls-policies.edit', compact('tlsPolicy'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\TlsPolicy           $tlsPolicy
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, TlsPolicy $tlsPolicy)
    {
        $this->authorize('update', $tlsPolicy);

        $validated = $request->validate([
            'domain'      => [
                'unique:tls_policies,domain,' . $tlsPolicy->id,
                new DomainRule
            ],
            'policy'      => 'in:none,may,encrypt,dane,dane-only,fingerprint,verify,secure',
            'params'      => 'string|nullable',
            'description' => 'string|nullable',
            'active'      => 'boolean'
        ]);
        $tlsPolicy->update($validated);

        flash('success', 'Your TLS policy was updated successfully.');
        return redirect()->route('tls-policies.show', compact('tlsPolicy'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\TlsPolicy $tlsPolicy
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(TlsPolicy $tlsPolicy)
    {
        $this->authorize('delete', $tlsPolicy);

        $tlsPolicy->delete();

        flash('success', 'The TLS Policy for ' . $tlsPolicy->domain . ' was deleted successfully.');
        return redirect()->route('tls-policies.index');
    }
}
