@extends('layouts.app')
@section('content')
    <h1 class="summary-title">{{__('summary.title')}}</h1>
    @empty($globalSummary)
        <div class="summary-empty">{{__('summary.empty')}}</div>
    @else
        <span class="global-last-updated">{{__('summary.updated').': '.$lastUpdated}}</span>
        <section class="global-summary">
            @foreach($globalSummary as $summaryIndex => $summaryValue)
                @if($summaryIndex === 'total_confirmed' || $summaryIndex === 'new_confirmed'||
                $summaryIndex === 'total_deaths' || $summaryIndex === 'new_deaths'||
                $summaryIndex === 'total_recovered' || $summaryIndex === 'new_recovered')
                    <div class="summary-global-data-pair">
                        <h2 class="summary-global-{{substr($summaryIndex, 0,  3) === 'new'?'new-':null}}title">
                            {{__('summary.'.$summaryIndex)}}
                        </h2>
                        <span class="summary-global-cases">{{number_format($summaryValue)}}</span>
                    </div>
                @endif
            @endforeach
        </section>
        <summary-data-component :summary="{{ json_encode($countriesSummary) }}"
                                :translation="{{ json_encode(trans('summary')) }}"
                                cases-by-country-route="{{route('corona.show',['slug'=>'/'])}}"
                                @if(App::isLocale('lt') && !array_key_exists('country_id', $countriesSummary[0]))
                                :countries-translation="{{json_encode(trans('countries')) }}"
                                @endif
        ></summary-data-component>
    @endempty
@endsection
