@extends('tenant.layouts.app')

@section('content')
 
    <tenant-purchases-form :purchase_order_id="{{ json_encode($purchase_order_id) }}"
    :type-user="{{json_encode(Auth::user()->type)}}"></tenant-purchases-form>

@endsection