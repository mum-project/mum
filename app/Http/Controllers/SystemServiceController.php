<?php

namespace App\Http\Controllers;

use App\Http\Filters\SystemServiceFilter;
use App\Http\Resources\SystemServiceResource;
use App\SystemService;
use function compact;
use Illuminate\Http\Request;
use function redirect;
use function response;

class SystemServiceController extends Controller
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
     * @param Request             $request
     * @param SystemServiceFilter $queryFilter
     * @param ControllerHelper    $controllerHelper
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, SystemServiceFilter $queryFilter, ControllerHelper $controllerHelper)
    {
        $this->authorize('index', SystemService::class);

        $systemServices = SystemService::query()
            ->filter($queryFilter)
            ->paginate()
            ->appends($queryFilter->filters());

        if ($request->wantsJson()) {
            return SystemServiceResource::collection($systemServices);
        }

        $searchHiddenInputValues = $controllerHelper->generateSearchHiddenInputValues($queryFilter);

        return response()->view('system-services.index', compact('systemServices', 'searchHiddenInputValues'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', SystemService::class);

        return response()->view('system-services.create');
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
        $this->authorize('create', SystemService::class);

        $validated = $request->validate([
            'service' => 'required|string',
            'name'    => 'nullable|string'
        ]);

        $systemService = SystemService::create($validated);

        flash('success', 'The service ' . $systemService->name() . ' was created successfully.');
        return redirect()->route('system-services.show', compact('systemService'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SystemService $systemService
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(SystemService $systemService)
    {
        $this->authorize('view', $systemService);

        $incidentHistory = $systemService->serviceHealthChecks()
            ->whereNotRunning()
            ->latest()
            ->get();

        return response()->view('system-services.show', compact('systemService', 'incidentHistory'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SystemService $systemService
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(SystemService $systemService)
    {
        $this->authorize('update', $systemService);

        return response()->view('system-services.edit', compact('systemService'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\SystemService       $systemService
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, SystemService $systemService)
    {
        $this->authorize('update', $systemService);

        $validated = $request->validate([
            'service' => 'string',
            'name'    => 'nullable|string'
        ]);

        $systemService->update($validated);

        flash('success', 'The service ' . $systemService->name() . ' was updated successfully.');
        return redirect()->route('system-services.show', compact('systemService'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SystemService $systemService
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(SystemService $systemService)
    {
        $this->authorize('delete', $systemService);

        $systemService->delete();

        flash('success', 'The service ' . $systemService->name() . ' was deleted successfully.');
        return redirect()->route('system-services.index');
    }
}
