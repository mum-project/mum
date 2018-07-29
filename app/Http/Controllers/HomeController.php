<?php

namespace App\Http\Controllers;

use App\Domain;
use App\ServiceHealthCheck;
use App\SizeMeasurement;
use App\SystemService;
use function array_combine;
use function compact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use function response;

class HomeController extends Controller
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
     * Show the application dashboard.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $rootFolderSizeMeasurements = SizeMeasurement::ofRootFolder()
            ->get()
            ->sortBy('created_at', null, false);

        $systemServices = SystemService::all();
        $latestServiceHealthCheck = ServiceHealthCheck::query()->latest()->first();

        return response()->view('home', compact('rootFolderSizeMeasurements', 'systemServices', 'latestServiceHealthCheck'));
    }
}
