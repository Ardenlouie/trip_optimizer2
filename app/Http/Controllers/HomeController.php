<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notifications\TestNotification;
use App\Models\TripOptimizer;
use App\Models\Truck;
use Carbon\Carbon;

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
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $totalTrips = TripOptimizer::count();
        $finalizedTrips = TripOptimizer::where('status', 'finalized')->count();
        $pendingTrips = TripOptimizer::where('status', 'finalizing')->count();
        $cancelledFormsCount = TripOptimizer::where('status', 'cancelled')->count();

        $monthsToDisplay = 6;
        $monthlyLabels = [];
        $monthlyCosts = [];

        for ($i = $monthsToDisplay - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthLabel = $month->format('M Y'); // e.g. "Jun 2026"
            $monthlyLabels[] = $monthLabel;

            // Sum total_price for that month
            $cost = TripOptimizer::whereYear('date_submitted', $month->year)
                ->whereMonth('date_submitted', $month->month)
                ->sum('total_price');

            $monthlyCosts[] = round((float) $cost, 2);
        }

        $trips = TripOptimizer::all(['id', 'truck_ids', 'total_price', 'status']);

        // Collect and normalize all truck_ids into a flat list
        $allTruckIds = $trips->flatMap(function ($trip) {
            $ids = $trip->truck_ids;

            // Handle string array, comma-separated string, or native array cast
            if (is_string($ids)) {
                $decoded = json_decode($ids, true);
                $ids = is_array($decoded) ? $decoded : explode(',', $ids);
            }

            return collect($ids)->map(fn($id) => (int) trim($id))->filter();
        });

        // Frequency count: [truck_id => count]
        $truckUsageCounts = $allTruckIds->countBy();

        // Unique trucks deployed at least once
        $uniqueTrucksUsedCount = $truckUsageCounts->keys()->count();
        $totalTrucksInFleet = Truck::count();

        // 3. Attach usage counts and metrics to Truck models
        $trucks = Truck::all()->map(function ($truck) use ($truckUsageCounts) {
            $truck->trips_count = $truckUsageCounts->get($truck->id, 0);
            return $truck;
        })->sortByDesc('trips_count')->values();

        // 4. Recent trips table
        $recentTrips = TripOptimizer::latest()->take(10)->get();

        return view('dashboard', compact(
            'totalTrips',
            'cancelledFormsCount',
            'finalizedTrips',
            'pendingTrips',
            'totalTrucksInFleet',
            'uniqueTrucksUsedCount',
            'trucks',
            'recentTrips',
            'monthlyLabels',
            'monthlyCosts'
        ));
    }

}
