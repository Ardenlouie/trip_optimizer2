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
<form action="{{ route('trip-optimize.store') }}" method="POST" id="add_trip">
    @csrf
    <livewire:inputs.create-trip />
    <input type="hidden" id="status" name="status" form="add_trip" value="pending">
</form>

@stop

{{-- Push extra CSS --}}

@push('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@endpush

{{-- Push extra scripts --}}

@push('js')



@endpush
