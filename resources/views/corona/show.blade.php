@extends('layouts.app')
@section('content')
    <div class="country-presentation">
        <h1 class="country-title">{{$country->country}}
            <img class="country-flag" src="https://flagcdn.com/80x60/{{ $country->iso2 }}.png" alt="{{$country->slug}}">
        </h1>
    </div>
    <cases-data-component :provinces="{{ $provinces }}"
                          cases-route="{{ route('corona.cases', ['slug' => $country->slug]) }}"
                          :translation="{{ json_encode(trans('cases')) }}">
    </cases-data-component>
@endsection
