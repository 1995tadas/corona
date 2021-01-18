@extends('errors::minimal')

@section('title', __('errors.many_requests'))
@section('code', '429')
@section('message', __('errors.many_requests'))
