@extends('layouts.app')

{{-- Customize layout sections --}}
@section('subtitle', __('Update Truck'))
@section('content_header_title', __('Trucks'))
@section('content_header_subtitle', __('Update Truck'))

{{-- Content body: main page content --}}
@section('content_body')
    <form action="{{ route('truck.update', encrypt($truck->id)) }}" method="POST" id="update_truck" enctype="multipart/form-data">
        <div class="card">
        @csrf          
            <div class="card-header py-2">
                <div class="row">
                    <div class="col-lg-6 align-middle">
                        <strong class="text-lg">Update Truck</strong>
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
                            <label class="mb-0">Truck Name <small class="text-danger font-italic text-bold">(required)</small></label>
                            <input type="text" class="form-control" name="truck_name" value="{{ $truck->truck_name }}"> 
                            <small class="text-danger">{{$errors->first('truck_name')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="mb-0">Weight Capacity (TONS) <small class="text-danger font-italic text-bold">(required)</small></label>
                            <input type="number" 
                                class="form-control" 
                                name="weight_cap" 
                                value="{{ $truck->weight_cap }}" 
                                step="0.01" 
                                min="0" 
                                placeholder="0.00">
                            <small class="text-danger">{{$errors->first('weight_cap')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="mb-0">Volume Capacity (CBM) <small class="text-danger font-italic text-bold">(required)</small></label>
                            <input type="number" 
                                class="form-control" 
                                name="volume_cap" 
                                value="{{ $truck->volume_cap }}" 
                                step="0.01" 
                                min="0" 
                                placeholder="0.00">
                            <small class="text-danger">{{$errors->first('volume_cap')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="mb-0">Pallet Capacity (PCS) <small class="text-danger font-italic text-bold">(required)</small></label>
                            <input type="number" 
                                class="form-control" 
                                name="pallet_cap" 
                                value="{{ $truck->pallet_cap }}" 
                                step="0.01" 
                                min="0" 
                                placeholder="0.00">
                            <small class="text-danger">{{$errors->first('pallet_cap')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="mb-0">Price per KM (PHP) <small class="text-danger font-italic text-bold">(required)</small></label>
                            <input type="number" 
                                class="form-control" 
                                name="price" 
                                value="{{ $truck->price }}" 
                                step="0.01" 
                                min="0" 
                                placeholder="0.00">
                            <small class="text-danger">{{$errors->first('pallet_cap')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="mb-0">Gasoline <small class=" font-italic text-bold">(optional)</small></label>
                            <input type="text" class="form-control" name="gasoline" value="{{ $truck->gasoline }}"> 
                            <small class="text-danger">{{$errors->first('gasoline')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="mb-0">Status <small class="text-danger font-italic text-bold">(required)</small></label>
                                <select class="form-control" name="status">
                                    <option value="Available" {{ ($truck->status ?? $truck->status ?? '') == 'Available' ? 'selected' : '' }}>Available</option>
                                    <option value="For Repair" {{ ($truck->status ?? $truck->status ?? '') == 'For Repair' ? 'selected' : '' }}>For Repair</option>
                                </select>
                            <small class="text-danger">{{$errors->first('status')}}</small>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="mb-0">Picture <small class=" font-italic text-bold">(optional)</small></label>
                            <input type="file" class="form-control" value="{{ $truck->picture }}" name="picture" id="picture" accept="image/*, application/pdf">
                            @if(!empty($truck->picture))
                            <img class="img-fluid profile-img"
                                src="{{ asset($truck->picture) }}"
                                alt="Truck profile picture">
                            @endif
                            <small class="text-danger">{{$errors->first('status')}}</small>
                        </div>
                    </div>
                    <div class="col-lg-6">
                            <label class="mb-0">Preview </label>
                        <iframe id="picturePreview" style="width: 100%; height: 400px; border: 1px solid #ccc;"></iframe>
                    </div>
                </div>

            </div>
            <div class="card-footer text-right">
                {{ html()->submit('<i class="fa fa-save"></i> '.__('Update Truck'))->class(['btn', 'btn-primary', 'btn']) }}
            </div>
        </div>
    </form>
@stop

{{-- Push extra CSS --}}
@push('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@endpush

{{-- Push extra scripts --}}
@push('js')
    <script>
        $('.select2').select2({
            allowClear: true,
            theme: "classic",

        });
    </script>

    <script>
        document.getElementById('picture').addEventListener('change', function () {
            const file = this.files[0];
            const iframe = document.getElementById('picturePreview');

            // Fix MIME types: 'image/png', 'image/jpeg', or 'application/pdf'
            if (file && (file.type === 'image/png' || file.type === 'image/jpeg' || file.type === 'application/pdf')) {
                iframe.src = URL.createObjectURL(file);
            } else {
                iframe.src = '';
            }
        });
    </script>
@endpush