@extends('layouts.app')
@section('content')
    <h1 class="summary-title">{{__('summary.title')}}</h1>
    @empty($globalSummary)
        <div class="summary-empty">{{__('summary.empty')}}</div>
    @else
        <span class="global-last-updated">{{__('summary.updated').': '.$lastUpdated}}</span>
        <section class="summary-global">
            @foreach($globalSummary as $summaryIndex => $summaryValue)
                @if($summaryIndex === 'total_confirmed' || $summaryIndex === 'new_confirmed'||
                $summaryIndex === 'total_deaths' || $summaryIndex === 'new_deaths'||
                $summaryIndex === 'total_recovered' || $summaryIndex === 'new_recovered')
                    <div class="summary-category">
                        <h2 class="summary-category-title">
                            {{__('summary.'.$summaryIndex)}}
                        </h2>
                        <span class="summary-global-cases">
                            {{(strtok($summaryIndex,'_') === 'new'?'+':null) . number_format($summaryValue)}}
                        </span>
                    </div>
                @endif
            @endforeach
        </section>
        <summary-data-component
            :summary="{{ json_encode($countriesSummary) }}"
            :regions="{{ json_encode($regions) }}"
            :translation="{{ json_encode(trans('summary')) }}"
            cases-by-country-route="{{route('corona.show',['slug'=>'/'])}}"
            @if(app()->getLocale() !=='en')
            :countries-translation="{{json_encode(trans('countries'))}}"
            :regions-translation="{{json_encode(trans('regions'))}}"
            @endif>
        </summary-data-component>
    @endempty
@endsection
