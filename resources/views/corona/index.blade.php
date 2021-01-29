@extends('layouts.app')
@section('content')
    <h1 class="summary-title">{{__('summary.title')}}</h1>
    @empty($globalSummary)
        <div class="summary-empty">{{__('summary.empty')}}</div>
    @else
        <span class="global-last-updated">{{__('summary.updated').': '.$lastUpdated}}</span>
        <slider-component :number-of-slots="{{ 2 }}">
            <section class="summary-global" slot="1">
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
            <div class="cases-diagrams" slot="2">
                <diagram-tabs-component :translation="{{ json_encode((trans('cases')))}}"
                                        :tabs="{{json_encode([(object)[
                                        'tabName' => __('cases.all'),
                                        'title' => __('cases.all') .' '. __('cases.cases'),
                                        'names' => ['confirmed', 'deaths', 'recovered'],
                                        'cases' => $allCases,
                                        'type' => 'line',
                                        'canvasId' => 'summaryChart',
                                        'colors' => (object)[
                                            'confirmed' => '#EE0A0A',
                                            'deaths' => '#000',
                                            'recovered' => '#0d9820',
                                        ]
                                    ],(object)[
                                        'tabName' => __('cases.new'),
                                        'title' => __('cases.new').' '. __('cases.cases'),
                                        'names' => ['new_confirmed', 'new_deaths', 'new_recovered'],
                                        'labels'=> ['confirmed', 'deaths', 'recovered'],
                                        'cases' => $allCases,
                                        'type' => 'line',
                                        'canvasId' => 'newSummaryChart',
                                        'colors' => (object)[
                                            'new_confirmed' => '#EE0A0A',
                                            'new_deaths' => '#000',
                                            'new_recovered' => '#0d9820',
                                        ]
                                    ]])}}">
                </diagram-tabs-component>
            </div>
        </slider-component>
        <summary-data-component
            :summary="{{ json_encode($countriesSummary) }}"
            :regions="{{ json_encode($regions) }}"
            :translation="{{ json_encode(trans('summary')) }}"
            cases-by-country-route="{{route('corona.show',['slug'=>'/'])}}"
            @if(app()->getLocale() !=='en')
            :places-translation="{{json_encode(trans('places'))}}"
            @endif>
        </summary-data-component>
    @endempty
@endsection
