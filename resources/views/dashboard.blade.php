@extends('layouts.app')

{{-- Content body: main page content --}}
@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1></h1>
    </div>

</div>
@endsection
@section('content_body')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-4 col-6">
            <div class="small-box border-left-primary shadow-sm rounded">
                <div class="inner p-3">
                    <span class="text-uppercase text-muted font-weight-bold text-xs">Total Trips</span>
                    <h2 class="font-weight-bold my-1 text-primary">{{ number_format($totalTrips) }}</h2>
                    <p class="mb-0 text-xs text-primary">
                        <i class="fas fa-arrow-up mr-1"></i>All Trips
                    </p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-day text-primary opacity-25"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box border-left-info shadow-sm rounded">
                <div class="inner p-3">
                    <span class="text-uppercase text-muted font-weight-bold text-xs">Pending / In Progress</span>
                    <h2 class="font-weight-bold my-1 text-info">{{ number_format($pendingTrips) }}</h2>
                    <p class="mb-0 text-xs text-muted">Awaiting user finalization</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock text-info opacity-25"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box border-left-danger shadow-sm rounded">
                <div class="inner p-3">
                    <span class="text-uppercase text-muted font-weight-bold text-xs">Cancelled</span>
                    <h2 class="font-weight-bold my-1 text-danger">{{ $cancelledFormsCount ?? 0 }}</h2>
                    <p class="mb-0 text-xs text-muted">Rejected or cancelled</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle text-danger opacity-25"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-6">
            <div class="small-box border-left-success shadow-sm rounded">
                <div class="inner p-3">
                    <span class="text-uppercase text-muted font-weight-bold text-xs">Finalized Trips</span>
                    <h2 class="font-weight-bold my-1 text-success">{{ number_format($finalizedTrips) }}</h2>
                    <p class="mb-0 text-xs text-success">
                        <i class="fas fa-check-circle mr-1"></i>Finalized
                    </p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-double text-success opacity-25"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-6">
            <div class="small-box border-left-navy shadow-sm rounded">
                <div class="inner p-3">
                    <span class="text-uppercase text-muted font-weight-bold text-xs">Active Trucks Used</span>
                    <h2 class="font-weight-bold my-1 text-navy">{{ $uniqueTrucksUsedCount }} <span class="fs-6 text-muted fw-normal">/ {{ $totalTrucksInFleet }}</span></h2>
                    <p class="mb-0 text-xs text-navy">
                        <i class="fas fa-truck mr-1"></i>Trucks
                    </p>
                </div>
                <div class="icon">
                    <i class="fas fa-truck text-navy opacity-25"></i>
                </div>
            </div>
        </div>

    </div>

    <div class="row mb-4">
        <!-- TRUCK UTILIZATION RANKING -->
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 text-bold">Truck Utilization Breakdown</h6>
                    <span class="badge text-muted border">By Total Dispatches</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Truck</th>
                                    <th>Capacity (Weight / Vol)</th>
                                    <th>Price / Ton</th>
                                    <th class="text-center">Times Assigned</th>
                                    <th class="pe-3" style="width: 25%;">Usage Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($trucks as $truck)
                                    @php
                                        $percent = $totalTrips > 0 ? round(($truck->trips_count / $totalTrips) * 100) : 0;
                                    @endphp
                                    <tr>
                                        <td class="text-center">
                                            <div class="d-flex align-items-center gap-2">
                                                @if (!empty($truck->picture))
                                                    <img src="{{ asset($truck->picture) }}" 
                                                         alt="{{ $truck->name }}" 
                                                         class="rounded-circle object-cover" 
                                                         style="width: 98px; height: 64px;">
                                                @else
                                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-muted" 
                                                         style="width: 98px; height: 64px; font-size: 14px;">
                                                        🚛
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="text-bold text-dark">{{ $truck->truck_name }}</div>

                                        </td>
                                        <td class="small text-muted">
                                            {{ number_format($truck->weight_cap, 2) }} T / {{ number_format($truck->volume_cap, 2) }} CBM
                                        </td>
                                        <td class="small">
                                            ₱{{ number_format($truck->price, 2) }}
                                        </td>
                                        <td class="text-center text-bold text-dark">
                                            {{ $truck->trips_count }}
                                        </td>
                                        <td class="pe-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar {{ $percent > 50 ? 'bg-success' : 'bg-primary' }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $percent }}%;" 
                                                         aria-valuenow="{{ $percent }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <span class="small text-muted text-nowrap" style="min-width: 38px;">{{ $percent }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No trucks registered.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- RECENT TRIP OPTIMIZATIONS -->
        <div class="col-12 col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 text-bold">Recent Trip Optimizations</h6>
                    <span class="badge bg-light text-muted border">Latest 10</span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse ($recentTrips as $trip)
                            @php
                                $tripTruckIds = is_array($trip->truck_ids) ? $trip->truck_ids : json_decode($trip->truck_ids ?? '[]', true);
                                $assignedTrucks = $trucks->whereIn('id', (array) $tripTruckIds);
                            @endphp
                            <li class="list-group-item py-3 px-3">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div class="text-bold text-primary">{{ $trip->control_number }}</div>
                                    <span class="badge {{ $trip->status === 'finalized' ? 'bg-success bg-opacity-10 text-success border border-success' : 'bg-warning bg-opacity-10 text-warning' }}">
                                        {{ ucfirst($trip->status) }}
                                    </span>
                                </div>
                                <div class="small text-muted mb-2">
                                    Trucks ({{ count($assignedTrucks) }}): 
                                    <span class="text-dark fw-semibold">
                                        {{ $assignedTrucks->pluck('truck_name')->implode(', ') ?: 'None selected' }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center small">
                                    <span class="text-muted">Est. Cost: <strong class="text-success">₱{{ number_format($trip->total_price ?? 0, 2) }}</strong></span>
                                    <span class="text-muted">{{ $trip->created_at?->diffForHumans() }}</span>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center py-4 text-muted">
                                No trip optimizations generated yet.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- MONTHLY COST BAR CHART -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="float-right">
                        <span class="badge bg-success text-success border border-success">
                            Total: ₱{{ number_format(array_sum($monthlyCosts), 2) }}
                        </span>
                    </h3>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Monthly Logistics Cost</h6>
                        <small class="text-muted">Total estimated trip spend per month</small>
                    </div>
                </div>
                <div class="card-body">
                    <div style="height: 300px; position: relative;">
                        <canvas id="monthlyCostChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@stop

{{-- Push extra CSS --}}

@push('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}

<style>
    .border-left-primary { border-left: 4px solid #007bff !important; }
    .border-left-warning { border-left: 4px solid #ffc107 !important; }
    .border-left-success { border-left: 4px solid #28a745 !important; }
    .border-left-danger  { border-left: 4px solid #dc3545 !important; }
    .border-left-navy  { border-left: 4px solid #12024c !important; }
    .border-left-info  { border-left: 4px solid #00c3fe !important; }

    .small-box {
        position: relative;
        display: block;
        border-radius: 8px;
        transition: transform 0.2s ease-in-out;
    }
    .small-box:hover {
        transform: translateY(-3px);
    }
    .small-box .icon {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 2.2rem;
    }
    .opacity-25 { opacity: 0.25; }
    .text-xs { font-size: 0.75rem; }
</style>
@endpush

{{-- Push extra scripts --}}

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('monthlyCostChart');

        if (!ctx) return;

        const monthlyLabels = @json($monthlyLabels);
        const monthlyCosts  = @json($monthlyCosts);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Total Trip Cost (₱)',
                    data: monthlyCosts,
                    backgroundColor: 'rgba(25, 135, 84, 0.25)', // Bootstrap success tint
                    borderColor: 'rgba(25, 135, 84, 1)',         // Bootstrap success solid
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 45
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let val = context.parsed.y || 0;
                                return ' Cost: ₱' + val.toLocaleString('en-PH', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + Number(value).toLocaleString();
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
