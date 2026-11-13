<?php

use Livewire\Component;

use App\Models\TripOptimizer;
use App\Models\TripOptimizerItem;
use App\Models\Truck;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

new class extends Component
{
    public $control_number, $company_id, $forms, $user, $form_id=1, $department, $ship_date, $data, $items;
    public $total_quantity, $total_weight, $total_volume, $total_pallet, $selectedTruckOption, $activeAddress, 
    $warehouse, $selectedRows=[], $firstWarehouse, $selectedTotalPrice=0.0, $selectedTruckIds=[];
    public $details = [];

    protected $listeners = ['loadTripSummary' => 'loadData'];

    public function loadData($selectedRows=[], $company, $shipDate)
    {
        $this->reset('total_quantity','total_weight','total_volume','total_pallet');

        $this->user = Auth::user();
        $this->company_id = $company;
        $this->ship_date = $shipDate;
        $total_weight = 0;

        $this->selectedRows = $selectedRows;
        
        if(!empty($selectedRows)){
            $response = Http::withToken('UaHxtws9LHZ47QG21lBXjQgka3Fe93H5xV1Y6HBQDN4=')->get(env('API_URL').'trip-optimizer/sormdet', [
                'SalesOrder' => $selectedRows
            ]);

            $sales_order_response = Http::withToken('UaHxtws9LHZ47QG21lBXjQgka3Fe93H5xV1Y6HBQDN4=')->get(env('API_URL').'trip-optimizer/sormview', [
                'SalesOrder' => $selectedRows
            ]);

            $sales_orders = collect($sales_order_response->json())->groupBy('SalesOrder');

            $addresses = collect($sales_orders)
                ->flatten(1)
                ->map(function ($item) {
                    return collect([
                        $item['ShipAddress1'] ?? null,
                        $item['ShipAddress2'] ?? null,
                        $item['ShipAddress3'] ?? null,
                    ])
                    ->map(fn($part) => trim((string) $part))
                    ->filter()
                    ->implode(', ');
                })
                ->filter()
                ->unique()
                ->values();

            $destinationRoute = $addresses
                ->map(fn($addr) => urlencode($addr))
                ->implode('+to:');

            $this->activeAddress = $destinationRoute;

            $this->details = collect($response->json())->groupBy('SalesOrder');
            $so_details = $response->json(); 

            $warehouse_collect = collect($response->json())->groupBy('MWarehouse'); 
            $this->firstWarehouse = collect($warehouse_collect)->flatten(1)->first();
            switch($this->firstWarehouse['MWarehouse']) {
                case 'CAL':
                    $warehouse_loc = '14.20925573884995, 121.1352084850637';
                    break;
                case 'CEB1':
                    $warehouse_loc = '10.32917225394279, 123.95322298659869';
                    break;
                case 'CDO':
                    $warehouse_loc = '8.47202500524859, 124.69597830282912';
                    break;
                case 'DVO':
                    $warehouse_loc = '7.155885593121402, 125.65900593290212';
                    break;
                case 'VIR':
                    $warehouse_loc = '14.07038763361044, 121.33834574370277';
                    break;
                default:
                    $warehouse_loc = '14.07038763361044, 121.33834574370277';
                    break;
            }

            $this->warehouse = $warehouse_loc;

            if(!empty($data['control_number'])){
                $this->control_number;

            } else {
                $this->control_number = $this->generateControlNumber();
            }

            foreach($so_details as $sordet){
                $sordet_quantity = $sordet['ConvFactAltUom']*$sordet['MOrderQty'];

                $sordet_weight_g = $sordet['ConvFactAltUom']*$sordet['MStockUnitMass'];
                $sordet_weight_cs = $sordet_weight_g*$sordet['MShipQty'];

                $sordet_volume_cmb = $sordet['ConvFactAltUom']*$sordet['MStockUnitVol'];
                $sordet_volume_cs = $sordet_volume_cmb*$sordet['MShipQty'];
            
                $total_weight += $sordet_weight_cs;
                $this->total_volume += $sordet_volume_cs;
                $this->total_quantity += $sordet_quantity;
            }
            $total_kg = $total_weight*0.001;
            $total_tons = $total_kg*0.00110231; 

            $this->total_weight = $total_tons;

            $this->data = [
                'sales_orders' => $selectedRows,
                'company_id' => $company,
                'ship_date' => $shipDate,
                'total_weight' => $this->total_weight,
                'total_quantity' => $this->total_quantity,
                'total_volume' => $this->total_volume,
                'total_pallet' => $this->total_pallet,
                'ship_address' => $this->activeAddress,
                'warehouse' => $this->firstWarehouse['MWarehouse'],
                
            ];

            $this->items = $so_details;
        } else {
            $this->details = [];
            $this->data = [];
            $this->items = [];
            $this->activeAddress = null;
            $this->total_weight = 0;
            $this->total_volume = 0;
        }

        
        


    }

    private function generateControlNumber() {
        $date_code = date('Y');
        do {
            $control_number = 'TPO-0'.$this->company_id.'-'.$date_code.'-001';

            $tpo = TripOptimizer::withTrashed()->orderBy('control_number', 'DESC')->where('company_id', $this->company_id)
                ->first();
            if(!empty($tpo->control_number)) {
                $latest_control_number = $tpo->control_number;
                list(,$prev_company_id, $prev_year, $last_number) = explode('-', $latest_control_number);

                $number = ('0'.$this->company_id == $prev_company_id && $prev_year == $date_code) ? ((int)$last_number + 1) : 1;

                $formatted_number = str_pad($number, 3, '0', STR_PAD_LEFT);

                $control_number = "TPO-0$this->company_id-$date_code-$formatted_number";
            }

        } while(TripOptimizer::withTrashed()->where('control_number', $control_number)->where('company_id', $this->company_id)->exists());

        return $control_number;
    }

    public function updatedSelectedTruckOption($value)
    {
        $chosenOption = collect($this->truckSuggestions)->firstWhere('id', $value);

        if ($chosenOption) {
            $this->selectedTotalPrice = (float) ($chosenOption['total_price'] ?? 0.0);
            $select_id = $chosenOption['id'] ?? '';
            $this->selectedTruckIds = $chosenOption['trucks']->pluck('id')->toArray();
        } else {
            $this->selectedTotalPrice = 0.0;
            $this->selectedTruckIds = [];
            $select_id = '';
        }

        Session::put('trip_data', [
            'control_number' => $this->control_number,
            'data' => $this->data,
            'items' => $this->items,
            'truck_ids' => $this->selectedTruckIds,
            'total_price' => $this->selectedTotalPrice,
            'select_id' => $select_id,
        ]);

    }

    public function getTruckSuggestionsProperty()
    {
        return $this->suggestTruckCombination($this->total_weight, $this->total_volume, limit: 3);
    }

    protected function suggestTruckCombination($totalWeight, $totalVolume, int $limit = 3)
    {
        if ($totalWeight <= 0 && $totalVolume <= 0) {
            return collect();
        }

        $trucks = Truck::all();
        $candidates = collect();
        $truckCount = $trucks->count();

        // -------------------------------------------------------------
        // 1-TRUCK EVALUATION
        // -------------------------------------------------------------
        foreach ($trucks as $truck) {
            if ($truck->weight_cap >= $totalWeight && $truck->volume_cap >= $totalVolume) {
                $weightSlack = $truck->weight_cap - $totalWeight;
                $volumeSlack = $truck->volume_cap - $totalVolume;
                $truckPrice = $truck->price * $totalWeight + $truck->price;

                $singleId = collect([$truck->id])->sort()->join(', ');

                $candidates->push([
                    'id' => $singleId,
                    'type' => 'Single',
                    'truck_count' => 1,
                    'trucks' => collect([$truck]),
                    'truck_names' => $truck->truck_name,
                    'pictures' => $truck->picture,
                    'total_weight_cap' => (float) $truck->weight_cap,
                    'total_volume_cap' => (float) $truck->volume_cap,
                    'total_price' => $truckPrice,
                    'weight_slack' => $weightSlack,
                    'volume_slack' => $volumeSlack,
                    'total_slack' => $weightSlack + $volumeSlack,
                ]);
            }
        }

        // -------------------------------------------------------------
        // 2-TRUCK EVALUATION
        // -------------------------------------------------------------
        for ($i = 0; $i < $truckCount; $i++) {
            for ($j = $i; $j < $truckCount; $j++) {
                $truckA = $trucks[$i];
                $truckB = $trucks[$j];

                $combinedWeightCap = $truckA->weight_cap + $truckB->weight_cap;
                $combinedVolumeCap = $truckA->volume_cap + $truckB->volume_cap;
                $priceA = $truckA->price * $totalWeight + $truckA->price;
                $priceB = $truckB->price * $totalWeight + $truckB->price;

                if ($combinedWeightCap >= $totalWeight && $combinedVolumeCap >= $totalVolume) {
                    $weightSlack = $combinedWeightCap - $totalWeight;
                    $volumeSlack = $combinedVolumeCap - $totalVolume;
                    $truckPrice = $priceA + $priceB;

                    $pairIds = collect([$truckA->id, $truckB->id])->sort()->join(', ');

                    $candidates->push([
                        'id' => $pairIds,
                        'type' => 'Pair',
                        'truck_count' => 2,
                        'trucks' => collect([$truckA, $truckB]),
                        'truck_names' => $truckA->truck_name . ' + ' . $truckB->truck_name,
                        'pictures' => collect([$truckA->picture, $truckB->picture]),
                        'total_weight_cap' => (float) $combinedWeightCap,
                        'total_volume_cap' => (float) $combinedVolumeCap,
                        'total_price' => $truckPrice,
                        'weight_slack' => $weightSlack,
                        'volume_slack' => $volumeSlack,
                        'total_slack' => $weightSlack + $volumeSlack,
                    ]);
                }
            }
        }

        // -------------------------------------------------------------
        // 3-TRUCK EVALUATION
        // -------------------------------------------------------------
        for ($i = 0; $i < $truckCount; $i++) {
            for ($j = $i; $j < $truckCount; $j++) {
                for ($k = $j; $k < $truckCount; $k++) {
                    $truckA = $trucks[$i];
                    $truckB = $trucks[$j];
                    $truckC = $trucks[$k];

                    $combinedWeightCap = $truckA->weight_cap + $truckB->weight_cap + $truckC->weight_cap;
                    $combinedVolumeCap = $truckA->volume_cap + $truckB->volume_cap + $truckC->volume_cap;
                    $priceA = $truckA->price * $totalWeight + $truckA->price;
                    $priceB = $truckB->price * $totalWeight + $truckB->price;
                    $priceC = $truckC->price * $totalWeight + $truckC->price;

                    if ($combinedWeightCap >= $totalWeight && $combinedVolumeCap >= $totalVolume) {
                        $weightSlack = $combinedWeightCap - $totalWeight;
                        $volumeSlack = $combinedVolumeCap - $totalVolume;
                        $truckPrice = $priceA + $priceB + $priceC;

                        $trioIds = collect([$truckA->id, $truckB->id, $truckC->id])->sort()->join(', ');

                        $candidates->push([
                            'id' => $trioIds,
                            'type' => 'Trio',
                            'truck_count' => 3,
                            'trucks' => collect([$truckA, $truckB, $truckC]),
                            'truck_names' => $truckA->truck_name . ' + ' . $truckB->truck_name . ' + ' . $truckC->truck_name,
                            'pictures' => collect([$truckA->picture, $truckB->picture, $truckC->picture]),
                            'total_weight_cap' => (float) $combinedWeightCap,
                            'total_volume_cap' => (float) $combinedVolumeCap,
                            'total_price' => $truckPrice,
                            'weight_slack' => $weightSlack,
                            'volume_slack' => $volumeSlack,
                            'total_slack' => $weightSlack + $volumeSlack,
                        ]);
                    }
                }
            }
        }

        return $candidates
            ->unique('id')
            ->sortBy([
                ['truck_count', 'asc'],
                ['total_slack', 'asc'],
            ])
            ->take($limit)
            ->values();
    }
};
?>

<div>
    <div class="modal-content">
        <div class="modal-header bg-primary">
            <h4 class="modal-title text-uppercase" >TRIP SUMMARY
                <i class="fa fa-spinner fa-spin" wire:loading></i>
            </h4>
        </div>
        <div class="modal-body">
            <div class="row mb-3 text-left">
                <div class="col-lg-6 ">
                    @if($company_id== 1)
                    <img src="{{asset('/images/bevilogonobg.png')}}" alt="product photo" class="product-img mb-3" height="50" width="250">
                    @elseif($company_id == 2)
                    <img src="{{asset('/images/bevanobg.png')}}" alt="product photo" class="product-img mb-3" height="80" width="120">
                    @elseif($company_id == 3)
                    <img src="{{asset('/images/biginobg.png')}}" alt="product photo" class="product-img mb-3" height="100" width="200">
                    @elseif($company_id == 4)
                    <img src="{{asset('/images/bevminobg.png')}}" alt="product photo" class="product-img mb-3" height="80" width="220">
                    @elseif($company_id == 5)
                    <img src="{{asset('/images/osp.png')}}" alt="product photo" class="product-img mb-3" height="80" width="250">
                    @elseif($company_id == 6)
                    <img src="{{asset('/images/pbb.png')}}" alt="product photo" class="product-img mb-3" height="80" width="150">
                    @endif
                    <h5 class="mb-0">Selected Sales Order/s: <span class="text-bold">{{ count($selectedRows) }} (
                        @foreach ($selectedRows as $id)
                            #{{ $id }},  
                        @endforeach
                        )</span>
                    </h5>
                </div>
                <div class="col-lg-6">
                    <h4 class="mb-3">Ref. No.: <b>{{ $control_number }}</b></h4>
                    <h5>Ship Date: <b>{{ date('F d, Y', strtotime($ship_date ?? '')) }}</b></h5>
                    <h5>Date Submitted: <b>{{ date('F d, Y') }}</b></h5>

                </div>
                <div class="col-lg-6">
                    
                         
                </div>
            </div>
            <div class="row text-center">
                <div class="col-lg-12">
                    <div class="card border rounded-lg p-3">
                        <div class="row">
                            <div class="col-sm-3 col-6">
                                <div class="border-right">
                                <h3 class="text-bold text-success">{{ number_format($total_quantity, 0) ?? '' }}</h3>
                                <h6 class="text-bold">TOTAL QUANTITY (PCS)</h6>
                                </div>
                            </div>
                            <div class="col-sm-3 col-6">
                                <div class="border-right">
                                <h3 class="text-bold text-success">{{ number_format($total_weight, 2) ?? '' }}</h3>
                                <h6 class="text-bold">TOTAL WEIGHT (TONS)</h6>
                                </div>
                            </div>
                            <div class="col-sm-3 col-6">
                                <div class="border-right">
                                <h3 class="text-bold text-success">{{ number_format($total_volume, 2) ?? '' }}</h3>
                                <h6 class="text-bold">TOTAL VOLUME (CBM)</h6>
                                </div>
                            </div>
                            <div class="col-sm-3 col-6">
                                <div>
                                <h3 class="text-bold text-success">{{ number_format($total_pallet, 0) ?? '' }}</h3>
                                <h6 class="text-bold">TOTAL PALLET (PCS)</h6>
                                </div>
                            </div>
                           
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="border rounded-lg p-3">
                            <h4 class="text-bold text-navy">
                                Distance from Warehouse to Ship Address <br>(Google Maps)
                            </h4>
                            @if($activeAddress)
                                <div class="p-2 bg-gray justify-between items-center">
                                    <span class="truncate">🚚 <b class="ml-1">{{ $firstWarehouse['MWarehouse'] }} to </b></span>
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
                                    height="800"
                                    width="100%"
                                    loading="lazy"
                                    allowfullscreen
                                    referrerpolicy="no-referrer-when-downgrade"
                                    src="https://maps.google.com/maps?saddr={{ urlencode($warehouse) }}&daddr={{ $activeAddress }}&output=embed"
                                ></iframe>
                            @else
                                <div class="items-center justify-center text-gray text-center">
                                    <span>🗺️ Select a row's address to view it on the map</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card ">
                        @if ($this->truckSuggestions->isNotEmpty())
                        <div class="border rounded-lg p-3">
                            <h4 class="text-bold text-navy">
                                Select Truck Allocation Option <br>(System Suggestions)
                            </h4>
                            <div class="text-left">
                                @foreach ($this->truckSuggestions as $index => $option)
                                    <label 
                                        style="cursor: pointer;" 
                                        class="border rounded-md p-3 {{ $selectedTruckOption === $option['id'] ? 'border-success bg-yellow' : 'border-gray' }}"
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
                                                        value="{{ $option['id'] }}" 
                                                        wire:model.live="selectedTruckOption"
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
            <h3 class="text-uppercase" 
                style="cursor: pointer;" 
                data-toggle="collapse" 
                data-target="#salesOrderTable" 
                aria-expanded="false" 
                aria-controls="salesOrderTable">
                <span class="badge bg-success">
                    <b>VIEW ORDERS</b> 
                    <i class="fas fa-chevron-down ml-2"></i>
                </span>
            </h3>
           
            @forelse ($details as $salesOrder => $items)
            <div class="border rounded-lg p-4 mb-3" wire:key="so-group-{{ $salesOrder }}">
                <div class="row mb-3 text-left">
                    
                    <div class="col-lg-6">
                        <h3 class="text-uppercase" >
                            <span class="badge bg-navy">
                                <b>{{ $salesOrder }}</b> 
                            </span>
                        </h3>
                        <span class="text-xs font-semibold">
                            {{ $items->count() }} SKU(s)
                        </span>
                    </div>
                    <div class="col-lg-6">
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="collapse" id="salesOrderTable">
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
                                    @foreach ($items as $item)
                                    @php
                                        $sku_weight = $item['ConvFactAltUom'] * $item['MStockUnitMass'];
                                        $sku_weight_cs = $sku_weight * $item['MShipQty'];

                                        $sku_volume = $item['ConvFactAltUom'] * $item['MStockUnitVol'];
                                        $sku_volume_cs = $sku_volume * $item['MShipQty'];

                                        $sku_pallet = $item['ConvFactAltUom'] * $item['PalletConfig'];
                                        $sku_pallet_cs = $sku_pallet * $item['MShipQty'];
                                    @endphp
                                    <tr>
                                        <td>{{ $item['MStockCode'] ?? '' }}</td>
                                        <td>{{ $item['MStockDes'] ?? '' }}</td>
                                        <td>{{ $item['MOrderUom'] ?? '' }}</td>
                                        <td>{{ number_format($item['MShipQty'], 0, '.', '') ?? '' }}</td>
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
            </div>
        @empty
            <div class="p-4 text-center text-gray-500 border rounded-lg mb-3">
                No Sales Order found.
            </div>
        @endforelse 
            
            <div class="row text-center">
                <div class="col-lg-6">
                    <h4>Prepared By: <br><b>{{ ($user->name ?? '' )}}</b></h4>
                </div>
                
            </div>
        </div>
        

        <div class="modal-footer">

            <a class="btn-draft btn btn-secondary">Draft</a>

            <a class="btn-confirm btn btn-info">Save</a>

            <a class="btn-finalizing btn btn-success">Finalize</a>
    
            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        </div>
    </div>

</div>