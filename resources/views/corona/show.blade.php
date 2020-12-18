@extends('layouts.app')
@section('content')
    @empty($cases)
        <div class="no-data">{{__('cases.no_data')}}</div>
    @else
        <cases-data-component :cases="{{ $cases }}" :translation="{{ json_encode(trans('cases')) }}">
        </cases-data-component>
    @endempty
@endsection
