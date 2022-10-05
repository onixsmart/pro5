@extends('tenant.layouts.app')

@section('content')
 
    <tenant-purchases-form :order_id="{{ json_encode($order_id) }}"
    :type-user="{{json_encode(Auth::user()->type)}}"></tenant-purchases-form>

@endsection