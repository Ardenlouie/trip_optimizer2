@extends('layouts.app')

{{-- Customize layout sections --}}
@section('subtitle', __('Trucks'))
@section('content_header_title', __('Trucks'))
@section('content_header_subtitle', __('Truck List'))

{{-- Content body: main page content --}}
@section('content_body')
    <div class="card">
        <div class="card-header py-2">
            <div class="row">
                <div class="col-lg-6 align-middle">
                    <strong class="text-lg">Truck List</strong>
                </div>
                <div class="col-lg-6 text-right">
                    @can('truck create')
                        <a href="{{route('truck.create')}}" class="btn btn-primary btn-xs">
                            <i class="fa fa-file"></i>
                            New Truck
                        </a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body">

            {{ html()->form('GET', route('truck.index'))->open() }}
                <div class="row mb-1">
                    <div class="col-lg-4">
                        <div class="form-group">
                            {{ html()->label(__('adminlte::utilities.search'), 'search')->class('mb-0') }}
                            {{ html()->input('text', 'search', $search)->placeholder(__('adminlte::utilities.name'))->class(['form-control', 'form-control-sm'])}}
                        </div>
                    </div>
                </div>
            {{ html()->form()->close() }}
            
            <div class="row">
                <div class="col-12 table-responsive">
                    <table class="table table-sm table-striped table-hover mb-0 rounded">
                        <thead class="tex-center bg-dark">
                            <tr class="text-center">
                                <th>Truck Name</th>
                                <th>Weight Capacity (TONS)</th>
                                <th>Volume Capacity (CBM)</th>
                                <th>Pallet Capacity (PCS)</th>
                                <th>Price (PHP)</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trucks as $truck)
                                <tr>
                                    <td class="align-middle text-center">
                                        {{$truck->truck_name ?? ''}}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{$truck->weight_cap ?? ''}}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{$truck->volume_cap ?? ''}}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{$truck->pallet_cap ?? ''}}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{$truck->price ?? ''}}
                                    </td>
                                    
                                    <td class="align-middle text-right p-0 pr-1">
                                        <a href="{{route('truck.show', encrypt($truck->id))}}" class="btn btn-info btn-xs mb-0 ml-0">
                                            <i class="fa fa-list"></i>
                                            {{__('adminlte::utilities.view')}}
                                        </a>
                                        @can('company edit')
                                            <a href="{{route('truck.edit', encrypt($truck->id))}}" class="btn btn-success btn-xs mb-0 ml-0">
                                                <i class="fa fa-pen-alt"></i>
                                                {{__('adminlte::utilities.edit')}}
                                            </a>
                                        @endcan
                                        @can('company delete')
                                            <a href="" class="btn btn-danger btn-xs mb-0 ml-0 btn-delete" data-id="{{encrypt($truck->id)}}">
                                                <i class="fa fa-trash-alt"></i>
                                                {{__('adminlte::utilities.delete')}}
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="card-footer">
            {{$trucks->links()}}
        </div>
    </div>
@stop

{{-- Push extra CSS --}}
@push('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@endpush

{{-- Push extra scripts --}}
@push('js')
    <script>
        $(function() {
            $('body').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                Livewire.dispatch('setDeleteModel', {type: 'Truck', model_id: id});
                $('#modal-delete').modal('show');
            });
        });
    </script>
@endpush