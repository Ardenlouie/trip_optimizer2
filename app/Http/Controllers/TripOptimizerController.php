<?php

namespace App\Http\Controllers;

use App\Models\TripOptimizer;
use App\Models\TripOptimizerItem;
use App\Models\User;
use App\Models\Truck;
use App\Models\Position;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;

use App\Http\Requests\TripOptimizerStoreRequest;
use App\Http\Requests\TripOptimizerUpdateRequest;

use App\Notifications\FinalizeNotification;
use App\Notifications\ApproveNotification;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\FileSavingHelper;
use App\Http\Traits\SettingTrait;
use Auth;

class TripOptimizerController extends Controller
{
    use SettingTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim($request->get('search'));
        $user = Auth::user();

        $trip_optimizers = TripOptimizer::orderBy('created_at', 'DESC')
            ->when(! empty($search), function ($query) use ($search) {
                $query->where('control_number', 'like', '%'.$search.'%')
                    ->where('sales_orders', 'like', '%'.$search.'%');
            })
            ->paginate($this->getDataPerPage())
            ->appends(request()->query());

        return view('pages.trip-optimizers.index')->with([
            'search' => $search,
            'user' => $user,
            'trip_optimizers' => $trip_optimizers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.trip-optimizers.create')->with([

        ]);
    }

    private function generateControlNumber($company_id) {
        $date_code = date('Y');
        do {
            $control_number = 'TPO-0'.$company_id.'-'.$date_code.'-001';

            $tpo = TripOptimizer::withTrashed()->orderBy('control_number', 'DESC')->where('company_id', $company_id)
                ->first();
            if(!empty($tpo->control_number)) {
                $latest_control_number = $tpo->control_number;
                list(,$prev_company_id, $prev_year, $last_number) = explode('-', $latest_control_number);

                $number = ('0'.$company_id == $prev_company_id && $prev_year == $date_code) ? ((int)$last_number + 1) : 1;

                $formatted_number = str_pad($number, 3, '0', STR_PAD_LEFT);

                $control_number = "TPO-0$company_id-$date_code-$formatted_number";
            }

        } while(TripOptimizer::withTrashed()->where('control_number', $control_number)->where('company_id', $company_id)->exists());

        return $control_number;
    }

    public function sales_order_api(Request $request)
    {
        $search    = $request->query('search');
        $company_id   = $request->query('company_id');

        $wh_api = Http::withToken('UaHxtws9LHZ47QG21lBXjQgka3Fe93H5xV1Y6HBQDN4=')
            ->get(env('API_URL').'sormview/'.$company_id);

        $wh_collect = collect($wh_api->json()['data'] ?? $wh_api->json());

        $warehouses = $wh_collect
        ->when($search, function ($collection) use ($search) {
            return $collection->filter(function ($item) use ($search) {
                $warehouseValue = data_get($item, 'Warehouse');
                return str_contains(strtolower($warehouseValue), strtolower($search));
            });
        })
        ->take(10) 

        ->map(function ($item) {
            $warehouseValue = data_get($item, 'Warehouse');
            return [
                'id'          => $warehouseValue,
                'text'        => $warehouseValue,
                'description' => $warehouseValue, 
            ];
        })
        ->values();
            
        return response()->json(['results' => $warehouses]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TripOptimizerStoreRequest $request)
    {
        $user = Auth::user();
        $date_submitted = date('Y-m-d');
        $date_code = date('Y');

        $accounting_manager = Position::where('position', 'Accounting Manager')->first();
        $am_user = User::where('position_id', $accounting_manager->id)->get();
        $approver = $am_user->pluck('id')->toArray();

        $trip_data = Session::get('trip_data');

        if(!empty($trip_data)){
            $data = $trip_data['data'];

            $trip_optimizer = new TripOptimizer([
                'company_id' => $data['company_id'],
                'user_id' => $user->id,
                'sales_orders' => $data['sales_orders'],
                'truck_ids' => $trip_data['truck_ids'],
                'total_weight' => $data['total_weight'],
                'total_volume' => $data['total_volume'],
                'total_pallet' => $data['total_pallet'] ?? '',
                'total_quantity' => $data['total_quantity'],
                'total_price' => $trip_data['total_price'],
                'ship_date' => $data['ship_date'],
                'ship_address' => $data['ship_address'],
                'warehouse' => $data['warehouse'],
                'approver' => $approver,
                'status' => $request->status,
            ]);
            $trip_optimizer->save();
            
            foreach ($trip_data['items'] as $key => $items){
                $trip_optimizer_items = new TripOptimizerItem([
                    'trip_optimizer_id' => $trip_optimizer->id,
                    'stock_code' => $items['MStockCode'],
                    'sales_order' => $items['SalesOrder'],
                    'item_description' => $items['MStockDes'],
                    'uom' => $items['MOrderUom'],
                    'quantity' => $items['MShipQty'],
                    'weight' => $items['MStockUnitMass'],
                    'volume' => $items['MStockUnitVol'],
                    'pallets' => $items['PalletConfig'],
                    'convert_factor' => $items['ConvFactAltUom'],
                ]);
                $trip_optimizer_items->save();
            }
        }
       


        if($trip_optimizer->status == 'finalized') {
            $trip_optimizer->update([
                'control_number' => $trip_data['control_number'],
                'date_submitted' => $date_submitted,
            ]);

            $approvers = User::whereIn('id', $trip_optimizer->approver ?? [])->get();

            if ($approvers->isNotEmpty()) {
                Notification::send($approvers, new FinalizeNotification($trip_optimizer));
            }
        }

        $control_number = $trip_optimizer->control_number;
        
        activity('created')
            ->performedOn($trip_optimizer)
            ->log(':causer.name has created trip optimizer :subject.name');

        return redirect()->route('trip-optimizer.index')->with([
            'message_success' => __('Trip Optimizer '.$control_number.' was created')
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id) {
        $trip_optimizer = TripOptimizer::findOrFail(decrypt($id));
        $user = Auth::user();

        $sales_order = TripOptimizerItem::select('sales_order')->distinct()->where('trip_optimizer_id', $trip_optimizer->id)->get('sales_order');
        $activeAddress = $trip_optimizer->ship_address;

        switch($trip_optimizer->warehouse) {
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

        $warehouse = $warehouse_loc;

        $trucks = Truck::whereIn('id', $trip_optimizer->truck_ids ?? [])->get();

        
        // dd($trucks);
        return view('pages.trip-optimizers.show')->with([
            'trip_optimizer' => $trip_optimizer,
            'sales_order' => $sales_order,
            'activeAddress' => $activeAddress,
            'warehouse' => $warehouse,
            'trucks' => $trucks,
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = Auth::user();
        $trip_optimizer = TripOptimizer::findOrFail(decrypt($id));
        $sales_order = TripOptimizerItem::select('sales_order')->distinct()->where('trip_optimizer_id', $trip_optimizer->id)->get('sales_order');
        $activeAddress = $trip_optimizer->ship_address;
        $selectedTruckOption = '';

        switch($trip_optimizer->warehouse) {
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

        $warehouse = $warehouse_loc;

        $trucks = Truck::whereIn('id', $trip_optimizer->truck_ids ?? [])->get();

        $truck_suggestions = $this->suggestTruckCombination($trip_optimizer->total_weight, $trip_optimizer->total_volume, limit: 3);


        
        // dd($truck_suggestions);
        return view('pages.trip-optimizers.edit')->with([
            'user' => $user,
            'trip_optimizer' => $trip_optimizer,
            'sales_order' => $sales_order,
            'activeAddress' => $activeAddress,
            'warehouse' => $warehouse,
            'trucks' => $trucks,
            'truck_suggestions' => $truck_suggestions,
            'selectedTruckOption' => $selectedTruckOption,
        ]);
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

    /**
     * Update the specified resource in storage.
     */
    public function update($id, TripOptimizerUpdateRequest $request)
    {
        $trip_optimizer = TripOptimizer::findOrFail(decrypt($id));
        $date_submitted = date('Y-m-d');
        $date_code = date('Y');
        $control_number = $this->generateControlNumber($trip_optimizer->company_id);

        $trip_optimizer->update([
            'truck_ids' => $request->truck_ids,
            'total_price' => $request->total_price,
            'status' => $request->status,
        ]);


        if($trip_optimizer->status == 'finalized') {
            $trip_optimizer->update([
                'control_number' => $control_number,
                'date_submitted' => $date_submitted,
            ]);

            $approvers = User::whereIn('id', $trip_optimizer->approver ?? [])->get();

            if ($approvers->isNotEmpty()) {
                Notification::send($approvers, new FinalizeNotification($trip_optimizer));
            }

        }

        $control_number = $trip_optimizer->control_number;
        
        activity('updated')
            ->performedOn($trip_optimizer)
            ->log(':causer.name has updated trip optimizer :subject.name');

        return redirect()->route('trip-optimizer.index')->with([
            'message_success' => __('Trip Optimizer '.$control_number.' was updated')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TripOptimizer $tripOptimizer)
    {
        //
    }

    public function printPDF($id) 
    {
        $trip_optimizer = TripOptimizer::findOrFail(decrypt($id)); 

        ini_set('memory_limit', '512M'); 
        set_time_limit(300);

        $pdf = PDF::loadView('pages.trip-optimizers.pdf', [
            'trip_optimizer' => $trip_optimizer,
        ]);

        return $pdf->stream();
    }

    public function cancel($id, TripOptimizerUpdateRequest $request) 
    {
        $trip_optimizer = TripOptimizer::findOrFail(decrypt($id)); 
        $control_number = $trip_optimizer->control_number;
        $user = Auth::user();

        $trip_optimizer->update([
            'status' => 'cancelled',
            'remarks' => $request->remarks,
        ]);

        activity('cancelled')
            ->performedOn($trip_optimizer)
            ->log('Approver has cancelled ['.$control_number.']');

        return redirect()->route('trip-optimizer.index')->with([
            'message_success' => 'Trip Optimizer ['.$control_number.'] was cancelled!'
        ]);
    }
}
