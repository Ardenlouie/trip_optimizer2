<table class="table table-striped table-hover mb-0 rounded">
    <thead class="text-center bg-navy">
        <tr class="text-center">
            <th>Control No.</th>
            <th>Sales Orders</th>
            <th>Ship Date</th>
            <th>Created By</th>
            <th>Date Submitted</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($trip_optimizers as $trip_optimizer)
            <tr>
                <td class="align-middle text-center">
                    @if($trip_optimizer->company_id == 1)
                    <img src="{{asset('/images/bevinobg.png')}}" alt="company-logo" class="product-img" height="50" width="100">
                    @elseif($trip_optimizer->company_id == 2)
                    <img src="{{asset('/images/bevanobg.png')}}" alt="company-logo" class="product-img" height="50" width="80">
                    @elseif($trip_optimizer->company_id == 3)
                    <img src="{{asset('/images/biginobg.png')}}" alt="company-logo" class="product-img" height="50" width="100">
                    @endif
                    <br>
                    <b>{{$trip_optimizer->control_number ?? ''}}</b>
                </td>
                <td class="align-middle text-center">
                    {{ 
                        collect(is_array($trip_optimizer->sales_orders) ? $trip_optimizer->sales_orders : explode(',', $trip_optimizer->sales_orders))
                            ->map(fn($so) => ltrim(trim($so), '0'))
                            ->implode(', ') 
                    }}
                </td>
                <td class="align-middle text-center">
                    {{ date('M-d-Y', strtotime($trip_optimizer->ship_date))  ?? ''}}
                </td>
                <td class="align-middle text-center">
                    {{$trip_optimizer->user->name ?? ''}} 
                </td>
                <td class="align-middle text-center">
                    @if(!empty($trip_optimizer->date_submitted))
                    {{ date('M-d-Y', strtotime($trip_optimizer->date_submitted )) }}
                    @endif
                </td>
                <td class="align-middle text-center">
                    @if($trip_optimizer->status == 'draft')
                        <span class="badge badge-secondary"><b>DRAFT</b></span>
                    @elseif($trip_optimizer->status == 'finalizing')
                        <span class="badge badge-info"><b>Finalizing</b></span>
                    @elseif($trip_optimizer->status == 'finalized')
                        <span class="badge badge-success"><b>Finalized</b></span>
                    @elseif($trip_optimizer->status == 'cancelled')
                        <span class="badge badge-danger"><b>Cancelled</b></span>
                    @else
                        <span class="badge bg-dark"><b>Pending</b></span>
                    @endif
                </td>
                <td class="align-middle text-right">
                    <a href="#" title="view" data-id="{{$trip_optimizer->id}}" class="btn-view btn bg-dark btn-xs mb-0 ml-0">
                        <i class="fa fa-eye"></i> View
                    </a>
                    <a href="{{ route('trip-optimizer.show', encrypt($trip_optimizer->id)) }}" title="show" class="btn bg-orange btn-xs mb-0 ml-0">
                        <i class="fa fa-file-contract"></i> Show
                    </a>
                    @if($trip_optimizer->status == 'finalizing' && $trip_optimizer->user_id == $user->id)
                    <a href="{{ route('trip-optimizer.edit', encrypt($trip_optimizer->id)) }}" title="finalize" class="btn bg-success btn-xs mb-0 ml-0">
                        <i class="fa fa-file-signature"></i> Finalize
                    </a>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
{{ $trip_optimizers->appends(request()->query())->links() }}

