<?php

use Livewire\Component;
use App\Models\Company;
use App\Models\SalesOrder;
use App\Models\TripOptimizer;
use App\Models\TripOptimizerItem;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Http\Traits\SettingTrait;
use Livewire\WithPagination;

new class extends Component
{
    use SettingTrait;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $sales_orders, $trucks, $so_id, $companies, $companies_arr, $company_id, $ship_date, $company=1, $date='', 
    $trip_sales_collect, $item_per_page, $selectedCustomer='', $selectedRows = [], $selectAll = false;

    public function mount() 
    {
        $this->item_per_page = '10';

        $this->companies = Company::all();
        $this->companies_arr = [];
        foreach($this->companies as $company) {
            $this->companies_arr[$company->id] = $company->name;
        }

    }
    

    public function updatedCompany($company)
    {
        $this->company = $company;

        if(!empty($this->date)){
            $this->updatedDate($this->date);
        }
        $this->resetSelectedRows();
        $this->reset('selectedCustomer');


    }

    public function updatedDate()
    {
        $this->resetPage();
        $this->resetSelectedRows();
    }

    public function updatedSelectedCustomer()
    {
        $this->resetPage();
        $this->updateSelectAllState();
    }

    public function updatedSelectedRows()
    {
        $this->selectedRows = array_map('strval', $this->selectedRows);
        $this->updateSelectAllState();
    }

    protected function updateSelectAllState()
    {
        $currentPageIds = collect($this->tripSales->items())
            ->pluck('SalesOrder')
            ->map(fn($id) => (string) $id)
            ->filter();

        if ($currentPageIds->isEmpty()) {
            $this->selectAll = false;
            return;
        }

        $this->selectAll = $currentPageIds->every(fn($id) => in_array($id, $this->selectedRows, true));
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = collect($this->tripSales->items())
            ->pluck('SalesOrder')
            ->map(fn($id) => (string) $id)
            ->toArray();

        if ($value) {
            $this->selectedRows = array_values(array_unique(array_merge($this->selectedRows, $currentPageIds)));
        } else {
            $this->selectedRows = array_values(array_diff($this->selectedRows, $currentPageIds));
        }
    }

    public function resetSelectedRows()
    {
        $this->selectedRows = [];
        $this->selectAll = false;
    }

    protected function getApiData()
    {
        if (empty($this->date) || empty($this->company)) {
            return collect();
        }

        $response = Http::withToken('UaHxtws9LHZ47QG21lBXjQgka3Fe93H5xV1Y6HBQDN4=')->get(env('API_URL').'trip_sales_order/'.$this->company.'/'.$this->date);

        return collect($response->json());
    }

    public function getCustomerNamesProperty()
    {
        return $this->getApiData()
            ->pluck('CustomerName')
            ->filter()      // Remove null or empty values
            ->unique()      // Ensure one per customer selection
            ->sort()
            ->values();
    }

    public function getTripSalesProperty()
    {
        $collection = $this->getApiData();

        // Filter by selected CustomerName if one is chosen
        if (!empty($this->selectedCustomer)) {
            $collection = $collection->where('CustomerName', $this->selectedCustomer);
        }

        // Paginate the filtered collection
        $perPage = 10;
        $currentPage = $this->getPage();
        $currentItems = $collection->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $currentItems,
            $collection->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }

    public function openPreview()
    {
        // Dispatch event to livewire:summary.trip-optimize
        $this->dispatch(
            'loadTripSummary', 
            selectedRows: $this->selectedRows,
            company: $this->company,
            shipDate: $this->date
        );
    }

};  
?>

<div>
    <div class="card">
        <div class="card-header bg-navy">
            <div class="row">
                <div class="col-lg-1">
                </div>
                <div class="col-lg-10 text-center">
                <strong class="text-lg card-title float-none text-bold">CREATE TRIP OPTIMIZE
                    <i class="fa fa-spinner fa-spin" wire:loading></i>
                </strong>

                </div>
                <div class="col-lg-1 text-right">
                    <a href="{{route('trip-optimizer.index')}}" class="btn bg-red btn-sm">
                        <i class="fa fa-caret-left"></i>
                        Back
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label class="mb-0">Company <small class="text-danger font-italic text-bold">(required)</small></label>
                        <select 
                            wire:model.live="company" 
                            id="company_id" 
                            name="company_id" 
                            style="cursor: pointer;"
                            class="form-control @error('company_id') is-invalid @enderror"
                        >
                            @foreach($companies_arr as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>

                        @error('company_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label class="mb-0">Ship Date <small class="text-danger font-italic text-bold">(required)</small></label>
                        <input type="date" class="form-control @error('ship_date') is-invalid @enderror" name="ship_date" wire:model.live="date" id="date" > 

                        @error('ship_date')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                
                @if(!empty($date))
                <div class="col-lg-4">
                    <div class="form-group">
                        <label class="mb-0">Accounts </label>
                        <select wire:model.live="selectedCustomer" class="form-control" style="cursor: pointer;">
                            <option value="">-- All Customers --</option>
                            @foreach ($this->customerNames as $customer)
                                <option value="{{ $customer }}">{{ $customer }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif
            </div> 
            <div class="table-responsive p-0">
      
                <div class="text-bold text-primary">
                    <a wire:click="resetSelectedRows" class="btn-sm btn btn-success">
                        <i class="fas fa-reply-all"></i> Clear
                    </a>
                </div>
                
                <table class="table table-striped table-hover mb-0 rounded" x-data="{ selectAll: false }">
                    <thead class="text-center bg-navy">
                        <tr class="text-center">
                            <th class="text-center">
                                <input type="checkbox" wire:model.live="selectAll" style="cursor: pointer;">
                            </th>
                            <th>Company</th>
                            <th>Sales Order</th>
                            <th>Customer</th>
                            <th>Address 1</th>
                            <th>Address 2</th>
                            <th>Address 3</th>
                            <th>Region</th>
                            <th>Warehouse</th>
                            <th>Ship Date</th>
                            <th>PO Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->tripSales as $trip_sales_order)
                        @php 
                            $soKey = (string) $trip_sales_order['SalesOrder']; 
                        @endphp
                        <tr 
                            style="cursor: pointer;"
                            wire:key="row-customer-{{ $soKey }}" 
                            x-data
                            @click="$wire.selectedRows.includes('{{ $soKey }}') 
                                ? $wire.selectedRows = $wire.selectedRows.filter(id => id !== '{{ $soKey }}') 
                                : $wire.selectedRows.push('{{ $soKey }}')"
                            class="cursor-pointer select-none hover:bg-gray-100 transition-colors {{ in_array($soKey, $selectedRows, true) ? 'bg-blue-50' : '' }}"
                        >
                            <td class="align-middle text-center" @click.stop>
                                <input 
                                    type="checkbox" 
                                    value="{{ $soKey }}" 
                                    wire:model.live="selectedRows"
                                    style="cursor: pointer;"
                                >
                            </td>
                            <td class="align-middle text-center">
                                @if($trip_sales_order['Company'] == 1)
                                    BEVI
                                @elseif($trip_sales_order['Company'] == 2)
                                    BEVA
                                @elseif($trip_sales_order['Company'] == 3)
                                    BIGI
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                {{$trip_sales_order['SalesOrder'] ?? ''}}
                            </td>
                            <td class="align-middle text-center">
                                {{$trip_sales_order['CustomerName'] ?? ''}}
                            </td>
                            <td class="align-middle text-center">
                                {{$trip_sales_order['ShipAddress1'] ?? ''}}
                            </td>
                            <td class="align-middle text-center">
                                {{$trip_sales_order['ShipAddress2'] ?? ''}}
                            </td>
                            <td class="align-middle text-center">
                                {{$trip_sales_order['ShipAddress3'] ?? ''}}
                            </td>
                            <td class="align-middle text-center">
                                {{$trip_sales_order['Region'] ?? ''}}
                            </td>
                            <td class="align-middle text-center">
                                {{$trip_sales_order['MWarehouse'] ?? ''}}
                            </td>
                            <td class="align-middle text-center">
                                {{ date('F d, Y', strtotime($trip_sales_order['ReqShipDate'] ?? '')) }}
                            </td>
                            <td class="align-middle text-center">
                                {{$trip_sales_order['CustomerPoNumber'] ?? ''}}
                            </td>
                        </tr>       
                        @empty
                              
                        @endforelse
                    </tbody>    
                </table>
            </div>
        </div>
        <div class="card-footer text-right">
            <div class="mt-4">
                {{ $this->tripSales->links() }}
            </div>
            <a href="#" title="preview" class="btn-preview btn btn-primary">Preview</a>

            <div class="modal fade" id="modal-preview">
                <div class="modal-dialog modal-xl">
                    <livewire:summary.trip-optimize />
                </div>
            </div>
        </div>

    </div>
    <script>
        $(function() {
            $('body').on('click', '.btn-preview', function(e) {
                e.preventDefault();

                let componentId = $(this).closest('[wire\\:id]').attr('wire:id');
                let component = Livewire.find(componentId);

                if (!component) {
                    console.error('Livewire component instance not found.');
                    return;
                }

                let selectedRows = component.get('selectedRows') || [];
                let company = component.get('company') || '';
                let shipDate = component.get('date') || '';

                Livewire.dispatch('loadTripSummary', {
                    selectedRows: selectedRows,
                    company: company,
                    shipDate: shipDate
                });

                $('#modal-preview').modal('show');
            });
        });
    </script>
</div>
