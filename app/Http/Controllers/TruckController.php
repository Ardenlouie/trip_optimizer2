<?php

namespace App\Http\Controllers;

use App\Models\Truck;
use App\Models\User;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

use App\Http\Requests\TruckStoreRequest;
use App\Http\Requests\TruckUpdateRequest;

use App\Http\Traits\SettingTrait;
use App\Helpers\FileSavingHelper;

class TruckController extends Controller
{
    use SettingTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim($request->get('search'));

        $trucks = Truck::orderBy('weight_cap', 'ASC')
            ->when(! empty($search), function ($query) use ($search) {
                $query->where('truck_name', 'like', '%'.$search.'%');
            })
            ->paginate($this->getDataPerPage())
            ->appends(request()->query());

        return view('pages.trucks.index')->with([
            'search' => $search,
            'trucks' => $trucks,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
      
        return view('pages.trucks.create')->with([
            
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TruckStoreRequest $request)
    {
        $truck = new Truck([
            'truck_name' => $request->truck_name,
            'weight_cap' => $request->weight_cap,
            'volume_cap' => $request->volume_cap,
            'pallet_cap' => $request->pallet_cap,
            'price' => $request->price,
            'status' => $request->status,
            'gasoline' => $request->gasoline,
        ]);
        $truck->save();

        // logs
        activity('created')
            ->performedOn($truck)
            ->log(':causer.name has created truck :subject.name');

        return redirect()->route('truck.index')->with([
            'message_success' => __('Truck '.$truck->truck_name.' was created')
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Truck $truck)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $truck = Truck::findorFail(decrypt($id));

        return view('pages.trucks.edit')->with([
            'truck' => $truck,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, TruckUpdateRequest $request)
    {
        $truck = Truck::findorFail(decrypt($id));

        $changes_arr['old'] = $truck->getOriginal();

        $truck->update([
            'truck_name' => $request->truck_name,
            'weight_cap' => $request->weight_cap,
            'volume_cap' => $request->volume_cap,
            'pallet_cap' => $request->pallet_cap,
            'price' => $request->price,
            'gasoline' => $request->gasoline,
            'status' => $request->status,
        ]);


        $path = NULL;
        if(!empty($request->picture)) {
            $path = FileSavingHelper::saveFile($request->picture, $truck->id, 'Truck');

            $truck->update([
                'picture' => $path
            ]);
        }

        $changes_arr['changes'] = $truck->getChanges();


        activity('updated')
            ->performedOn($truck)
            ->withProperties($changes_arr)
            ->log(':causer.name has updated truck :subject.name');

        return back()->with([
            'message_success' => __('Truck '.$truck->truck_name.' was updated')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Truck $truck)
    {
        //
    }
}
