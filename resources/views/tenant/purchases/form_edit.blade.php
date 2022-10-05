@extends('tenant.layouts.app')

@section('content')
 
    <tenant-purchases-edit :resource-id="{{json_encode($resourceId)}}"
    :type-user="{{json_encode(Auth::user()->type)}}"></tenant-purchases-edit>

@endsection