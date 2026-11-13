@extends('layouts.app')

{{-- Customize layout sections --}}

{{-- Content body: main page content --}}
@section('content_header')
<div class="row">
    <div class="col-md-6">
        <h1></h1>
    </div>

</div>
@endsection
@section('content_body')
   
<div class="card">
    <div class="card-header bg-navy">
        <div class="row">
            <div class="col-lg-1">
            </div>
            <div class="col-lg-10 text-center">
                <strong class="text-lg card-title float-none text-bold">TRIP OPTIMIZER</strong>
            </div>
            <div class="col-lg-1 text-right">
                <a href="{{route('home')}}" class="btn bg-red btn-sm">
                    <i class="fa fa-caret-left"></i>
                    Home
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="card-tools text-right mb-3">
            <a href="{{ route('trip-optimizer.create') }}" class="btn bg-blue btn-xl">
                <i class="fa fa-plus"></i>
                {{__('Add Trip Optimize')}}
            </a>
        </div>
        <div class="row mb-3">
            <div class="col-lg-4">
                <div class="form-group">
                    <input type="text" id="search_forms" class="form-control form-control-xl" placeholder="Search">
                </div>
            </div>
            <div class="col-lg-4">
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-filter"></i>FILTER</span>
                    </div>
                    <select id="status_filter" class="form-control text-uppercase">
                        <option value="">All</option>
                        
                    </select>
                </div>
            </div>
          
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div id="forms_table_container" class="table-responsive p-0">
                    @include('pages.trip-optimizers.partials' ) 
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="modal fade" id="modal-view">
            <div class="modal-dialog modal-xl">
                <livewire:trip-optimizer.view-trip />
            </div>
        </div>
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
        $('body').on('click', '.btn-view', function(e) {
            e.preventDefault();
            let data = {
                id: $(this).data('id'),
            };
            Livewire.dispatch('viewTrip', {data});
            $('#modal-view').modal('show');
        });
    });
</script>

@endpush
