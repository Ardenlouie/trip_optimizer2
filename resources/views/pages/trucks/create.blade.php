@extends('layouts.app')

{{-- Customize layout sections --}}
@section('subtitle', __('New Truck'))
@section('content_header_title', __('Trucks'))
@section('content_header_subtitle', __('New Truck'))

{{-- Content body: main page content --}}
@section('content_body')
    {{ html()->form('POST', route('truck.store'))->open() }}

        <div class="card">
            <div class="card-header py-2">
                <div class="row">
                    <div class="col-lg-6 align-middle">
                        <strong class="text-lg">New Truck</strong>
                    </div>
                    <div class="col-lg-6 text-right">
                        <a href="{{route('truck.index')}}" class="btn btn-secondary btn-xs">
                            <i class="fa fa-caret-left"></i>
                            {{__('adminlte::utilities.back')}}
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">

                <div class="row">

                    <div class="col-lg-3">
                        <div class="form-group">
                            {{ html()->label(__('Truck Name'), 'truck_name')->class(['mb-0']) }} <small class="text-danger font-italic text-bold">(required)</small>
                            {{ 
                                html()->text('truck_name', '')
                                ->class(['form-control', 'form-control', 'is-invalid' => $errors->has('truck_name')])
                                ->placeholder(__('Truck Name'))
                            }}
                            <small class="text-danger">{{$errors->first('truck_name')}}</small>
                        </div>
                    </div>
                    
                    <div class="col-lg-3">
                        <div class="form-group">
                            {{ html()->label(__('Weight Capacity (TONS)'), 'weight_cap')->class(['mb-0']) }} <small class="text-danger font-italic text-bold">(required)</small>
                            <input type="number" 
                                class="form-control" 
                                name="weight_cap" 
                                step="0.01" 
                                min="0" 
                                placeholder="0.00">
                            <small class="text-danger">{{$errors->first('weight_cap')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            {{ html()->label(__('Volume Capacity (CBM)'), 'volume_cap')->class(['mb-0']) }} <small class="text-danger font-italic text-bold">(required)</small>
                            <input type="number" 
                                class="form-control" 
                                name="volume_cap" 
                                step="0.01" 
                                min="0" 
                                placeholder="0.00">
                            <small class="text-danger">{{$errors->first('volume_cap')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            {{ html()->label(__('Pallet Capacity (PCS)'), 'pallet_cap')->class(['mb-0']) }} <small class="text-danger font-italic text-bold">(required)</small>
                            <input type="number" 
                                class="form-control" 
                                name="pallet_cap" 
                                min="0" 
                                placeholder="0">
                            <small class="text-danger">{{$errors->first('pallet_cap')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            {{ html()->label(__('Price per KM (PHP)'), 'price')->class(['mb-0']) }} <small class="text-danger font-italic text-bold">(required)</small>
                            <input type="number" 
                                class="form-control" 
                                name="price" 
                                step="0.01" 
                                min="0" 
                                placeholder="0.00">
                            <small class="text-danger">{{$errors->first('price')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            {{ html()->label(__('Gasoline'), 'gasoline')->class(['mb-0']) }} <small class=" font-italic text-bold">(optional)</small>
                            {{ 
                                html()->text('gasoline', '')
                                ->class(['form-control', 'form-control', 'is-invalid' => $errors->has('gasoline')])
                                ->placeholder(__('Gasoline'))
                            }}
                            <small class="text-danger">{{$errors->first('gasoline')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="mb-0">Status <small class="text-danger font-italic text-bold">(required)</small></label>
                                <select class="form-control" name="status">
                                    <option value="">-- Select Status --</option>
                                    <option value="Available">Available</option>
                                    <option value="For Repair">For Repair</option>
                                </select>
                            <small class="text-danger">{{$errors->first('status')}}</small>
                        </div>
                    </div>

                </div>

            </div>
            <div class="card-footer text-right">
                {{ html()->submit('<i class="fa fa-save"></i> '.__('Save Truck'))->class(['btn', 'btn-primary']) }}
            </div>
        </div>

    {{ html()->form()->close() }}
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
        });
    </script>
@endpush