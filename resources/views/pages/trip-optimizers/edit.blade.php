@extends('layouts.app')

{{-- Customize layout sections --}}

{{-- Content body: main page content --}}
@section('content_header')
<div class="row">
    <div class="col-md-6">
        <a href="{{route('trip-optimizer.index')}}" class="btn bg-red">
            <i class="fa fa-caret-left"></i>
            Back
        </a>
    </div>
</div>
@endsection
@section('content_body')
<form action="{{ route('trip-optimize.update',encrypt($trip_optimizer->id)) }}" method="POST" id="update_trip">
    @csrf 
    <div class="card">
        <div class="card-header">
            <h3 class="text-right text-uppercase">
                @if($trip_optimizer->status == 'draft')
                    <span class="badge badge-secondary"><b>DRAFT</b></span>
                @elseif($trip_optimizer->status == 'finalizing')
                    <span class="badge badge-info"><b>Finalizing</b></span>
                @elseif($trip_optimizer->status == 'finalized')
                    <span class="badge badge-success"><b>Finalized</b></span>
                @else
                    <span class="badge bg-dark"><b>Pending</b></span>
                @endif
            </h3>
            <div class="row">
                <div class="col-12">
                    @if($trip_optimizer->company->id == 1)
                    <img src="{{asset('/images/bevilogonobg.png')}}" alt="product photo" class="product-img" height="50" width="250">
                    @elseif($trip_optimizer->company->id == 2)
                    <img src="{{asset('/images/bevanobg.png')}}" alt="product photo" class="product-img" height="80" width="120">
                    @elseif($trip_optimizer->company->id == 3)
                    <img src="{{asset('/images/biginobg.png')}}" alt="product photo" class="product-img" height="80" width="150">
                    @endif
                    <h4 class="float-right">Ref. No.: <b>{{ $trip_optimizer->control_number ?? ''}}</b></h4>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <h4>Sales Order/s: 
                        <b>
                        {{ 
                            collect(is_array($trip_optimizer->sales_orders) ? $trip_optimizer->sales_orders : explode(',', $trip_optimizer->sales_orders))
                                ->map(fn($so) => ltrim(trim($so), '0'))
                                ->implode(', ') 
                        }}
                        </b>
                    </h4>


                </div>
                <div class="col-6 text-right">
                    <h4>Ship Date: <b>{{ date('F d, Y', strtotime($trip_optimizer->ship_date ?? '')) }}</b></h4>
                    @if(!empty($trip_optimizer->date_submitted))
                    <h4>Date Submitted: <b>{{ date('F d, Y', strtotime($trip_optimizer->date_submitted ?? '')) }}</b></h4>
                    @endif
                    
                </div>
            </div>
            <div class="row text-center mb-3">
                <div class="col-lg-12">
                    <div class="card border rounded-lg p-3">
                        <div class="row">
                            <div class="col-sm-3 col-6">
                                <div class="border-right">
                                <h3 class="text-bold text-success">{{ number_format($trip_optimizer->total_quantity, 0) ?? '' }}</h3>
                                <h6 class="text-bold">TOTAL QUANTITY (PCS)</h6>
                                </div>
                            </div>
                            <div class="col-sm-3 col-6">
                                <div class="border-right">
                                <h3 class="text-bold text-success">{{ number_format($trip_optimizer->total_weight, 2) ?? '' }}</h3>
                                <h6 class="text-bold">TOTAL WEIGHT (TONS)</h6>
                                </div>
                            </div>
                            <div class="col-sm-3 col-6">
                                <div class="border-right">
                                <h3 class="text-bold text-success">{{ number_format($trip_optimizer->total_volume, 2) ?? '' }}</h3>
                                <h6 class="text-bold">TOTAL VOLUME (CBM)</h6>
                                </div>
                            </div>
                            <div class="col-sm-3 col-6">
                                <div>
                                <h3 class="text-bold text-success">{{ number_format($trip_optimizer->total_pallet, 0) ?? '' }}</h3>
                                <h6 class="text-bold">TOTAL PALLET (PCS)</h6>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="border rounded-lg p-3">
                            <h4>
                                Distance from Warehouse to Ship Address <br><b>(Google Maps)</b>
                            </h4>
                            @if($activeAddress)
                                <div class="p-2 bg-gray justify-between items-center">
                                    <span class="truncate">🚚 <b class="ml-1">{{ $trip_optimizer->warehouse }} to </b></span>
                                    <span class="truncate">📍 <b class="ml-1">{{ $activeAddress }}</b></span>
                                    <a 
                                        href="https://www.google.com/maps/search/?api=1&query={{ urlencode($activeAddress) }}" 
                                        target="_blank" 
                                        class="text-primary text-bold"
                                    >
                                        Open in Maps ↗
                                    </a>
                                </div>

                                <iframe 
                                    class="border-0"
                                    height="500"
                                    width="100%"
                                    loading="lazy"
                                    allowfullscreen
                                    referrerpolicy="no-referrer-when-downgrade"
                                    src="https://maps.google.com/maps?saddr={{ urlencode($warehouse) }}&daddr={{ $activeAddress }}&output=embed"
                                ></iframe>
                            @else
                                <div class="items-center justify-center text-gray text-center">
                                    <span>🗺️ No Active Address</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card ">
                        @if ($truck_suggestions->isNotEmpty())
                        <div class="border rounded-lg p-3">
                            <h4 class="text-bold text-navy">
                                Select Truck Allocation Option <br>(System Suggestions)
                            </h4>
                            <div class="text-left" id="truck-options-container">
                                @foreach ($truck_suggestions as $index => $option)
                                    <label 
                                        style="cursor: pointer;" 
                                        class="truck-option-card border rounded-md p-3 d-block mb-2 {{ $loop->first ? 'border-success selected-card' : 'border-secondary' }}"
                                    >
                                        @if ($loop->first)
                                            <span class="badge bg-success">
                                                Best Match
                                            </span>
                                        @endif
                                        <div>
                                            <div class="items-center">
                                                <h4 class="text-bold text-navy">
                                                    <input 
                                                        type="radio" 
                                                        name="truck_radio_choice" 
                                                        value="{{ $option['id'] }}" 
                                                        class="truck-radio me-2"
                                                        data-truck-ids="{{ $option['id'] }}"
                                                        data-total-price="{{ $option['total_price'] }}"
                                                        {{ $loop->first ? 'checked' : '' }}
                                                    >
                                                    {{ $option['truck_names'] }}  
                                                </h4>
                                                @foreach ($option['trucks'] as $truck)
                                                    @if ($truck->picture)
                                                        <img 
                                                            src="{{ asset($truck->picture) }}" 
                                                            alt="{{ $truck->name }}" 
                                                            width="220"
                                                            height="170"
                                                        >
                                                    @else
                                                        🚚
                                                    @endif
                                                @endforeach
                                            </div>
                                            <div class="text-md">
                                                <h6>[ Type: <b class="text-uppercase">{{ $option['type'] }} Truck </b>]
                                                    [ Weight Cap: <b>{{ number_format($option['total_weight_cap'], 2) }} Tons </b>]
                                                    [ Volume Cap: <b>{{ number_format($option['total_volume_cap'], 2) }} CBM </b>]
                                                </h6>
                                            </div>
                                        </div>
                                        <h3 class="mt-2 pt-2 pl-5">
                                            Estimated Cost: <b class="text-olive">₱{{ number_format($option['total_price'], 2) }} PHP</b>
                                        </h3>
                                        <div class="mt-2 pt-2 pl-5 text-right">
                                            Unused Capacity (Tons+CBM): <b class="text-danger">{{ number_format($option['total_slack'], 2) }} units</b>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @elseif ($total_weight > 0 || $total_volume > 0)
                            <div class="p-3 bg-red-50 text-red-700 text-xs border border-red rounded-md">
                                ⚠️ No 1 or 2 truck combination can accommodate {{ number_format($total_weight, 2) }} Tons / {{ number_format($total_volume, 2) }} CBM.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @foreach($sales_order as $key => $sales_order_part)
            <div class="border rounded-lg p-4 mb-3">
                <div class="row mb-3 text-left">
                    
                    <div class="col-lg-6">
                        <h3 class="text-uppercase" >
                            <span class="badge bg-navy">
                                <b>{{ $sales_order_part->sales_order }}</b> 
                            </span>
                        </h3>
                        <span class="text-xs font-semibold">
                            {{ $trip_optimizer->trip_optimizer_item()->where('sales_order', $sales_order_part->sales_order)->get()->count() }} SKU(s)
                        </span>
                    </div>
                    <div class="col-lg-6">
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="table-responsive mb-3">
                        <table class="table table-striped text-center">
                            <thead>
                                <tr>
                                    <th>Stock Code</th>
                                    <th>Description</th>
                                    <th>UOM</th>
                                    <th>Ship Qty</th>
                                    <th>Weight (G)</th>
                                    <th>Volume (CBM)</th>
                                    <th>Pallet (PCS)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($trip_optimizer->trip_optimizer_item()->where('sales_order', $sales_order_part->sales_order)->get() as $item)
                                @php
                                    $sku_weight = $item['convert_factor'] * $item['weight'];
                                    $sku_weight_cs = $sku_weight * $item['quantity'];

                                    $sku_volume = $item['convert_factor'] * $item['volume'];
                                    $sku_volume_cs = $sku_volume * $item['quantity'];

                                    $sku_pallet = $item['convert_factor'] * $item['pallet'];
                                    $sku_pallet_cs = $sku_pallet * $item['quantity'];
                                @endphp
                                <tr>
                                    <td>{{ $item['stock_code'] ?? '' }}</td>
                                    <td>{{ $item['item_description'] ?? '' }}</td>
                                    <td>{{ $item['uom'] ?? '' }}</td>
                                    <td>{{ number_format($item['quantity'], 0, '.', '') ?? '' }}</td>
                                    <td>{{ number_format($sku_weight_cs, 2) ?? '' }}</td>
                                    <td>{{ number_format($sku_volume_cs, 2, '.', '') ?? '' }}</td>
                                    <td>''</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach
            <div class="row">
                <div class="col-6">
                    <a type="button" href="" target="_blank" class="btn bg-gradient-navy" style="margin-right: 5px;">
                        <i class="fas fa-download"></i> Generate PDF
                    </a>
                </div>
                <div class="col-6 text-right">
                    <div class="form-group">
                        @if($trip_optimizer->status == 'finalizing' && $trip_optimizer->user_id == $user->id)
                        <a class="btn-finalize btn btn-success btn-lg">Finalize</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <input type="hidden" id="status" name="status" form="update_trip" value="finalizing">
            <input type="hidden" id="truck_ids" name="truck_ids[]" form="update_trip" value="">
            <input type="hidden" id="total_price" name="total_price" form="update_trip" value="">
        </div>
    </div>
</form>

@stop

{{-- Push extra CSS --}}

@push('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}

    <style>
        .truck-option-card.selected-card {
            background-color: #ffe100 !important; /* Soft yellow */
            border-color: #198754 !important;     /* Green/Success border */
        }
    </style>
@endpush

{{-- Push extra scripts --}}

@push('js')
<script>
    $(function() {
        $('#truck-options-container').on('change', 'input[name="truck_radio_choice"]', function() {
            // Remove active class from all cards
            $('.truck-option-card').removeClass('selected-card border-success').addClass('border-secondary');

            // Add active styling to the parent label of the selected radio
            $(this).closest('.truck-option-card').addClass('selected-card border-success').removeClass('border-secondary');
        });
    });
</script>

@endpush
