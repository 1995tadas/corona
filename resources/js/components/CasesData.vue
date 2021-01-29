<template>
    <div v-if="loading" class="cases-loading">
        <div>{{ translation.loading }}</div>
        <div class="loader"></div>
    </div>
    <div class="no-data" v-else-if="error">
        {{ translation.error_while_loading }}
    </div>
    <div v-else-if="checkIfDataIsNotEmpty(rawCases)">
        <div class="cases-diagrams">
            <diagram-tabs-component :translation="translation"
                                    :tabs="[{
                                            tabName: translation.all,
                                            title: translation.all + ' ' + translation.cases,
                                            names: ['confirmed','active','deaths','recovered'],
                                            cases: formattedCases,
                                            type: 'line',
                                            canvasId: 'casesChart',
                                            colors: {
                                                 confirmed: colors.confirmed,
                                                 active: colors.active,
                                                 deaths: colors.deaths,
                                                 recovered: colors.recovered,
                                            }
                                        },{
                                            tabName: translation.new,
                                            title: translation.new + ' ' + translation.cases + ' ' + translation.per_day,
                                            names: ['new_confirmed','new_active','new_deaths','new_recovered'],
                                            labels: ['confirmed','active','deaths','recovered'],
                                            cases: formattedCases,
                                            type: 'line',
                                            canvasId: 'newCasesChart',
                                            colors: {
                                                 new_confirmed: colors.confirmed,
                                                 new_active: colors.active,
                                                 new_deaths: colors.deaths,
                                                 new_recovered: colors.recovered,
                                            }
                                        },{
                                            tabName: translation.confirmed,
                                            title: translation.confirmed + ' ' + translation.cases,
                                            names: ['confirmed'],
                                            cases: formattedCases,
                                            type: 'bar',
                                            canvasId: 'casesConfirmed',
                                            colors: {
                                                 confirmed: colors.confirmed
                                            }
                                        },{
                                            tabName: translation.deaths,
                                            title: translation.death_plural + ' ' + translation.cases,
                                            names: ['deaths'],
                                            cases: formattedCases,
                                            type: 'bar',
                                            canvasId: 'casesDeaths',
                                            colors: {
                                                 deaths: colors.deaths
                                            }
                                        },{
                                            tabName: translation.active,
                                            title: translation.active,
                                            names: ['active'],
                                            cases: formattedCases,
                                            type: 'bar',
                                            canvasId: 'casesActive',
                                            colors: {
                                                 active: colors.active,
                                            }
                                        },{
                                            tabName: translation.recovered,
                                            title: translation.recovered,
                                            names: ['recovered'],
                                            cases: formattedCases,
                                            type: 'bar',
                                            canvasId: 'casesRecovered',
                                            colors: {
                                                 recovered: colors.recovered,
                                            }
                                        }
                                    ]">
            </diagram-tabs-component>
        </div>
        <cases-table-component :cases="rawCases" :translation="translation">
        </cases-table-component>
    </div>
    <div v-else class="no-data">{{ translation.no_data }}</div>
</template>
<script>
export default {
    props: {
        casesRoute: {
            type: String,
            required: true
        },
        translation: {
            type: Object,
            required: true
        }
    },
    created() {
        this.fetchCases(this.casesRoute);
    },
    data() {
        return {
            formattedCases: {},
            rawCases: [],
            loading: false,
            error: false,
            colors: {
                confirmed: '#EE0A0A',
                active: '#055EF6',
                deaths: '#000',
                recovered: '#0d9820',
            }
        }
    },
    methods: {
        checkIfDataIsNotEmpty(element) {
            return Object.keys(element).length || element.length
        },
        fetchCases(casesRoute) {
            this.loading = true;
            axios.get(casesRoute)
                .then((response) => {
                    this.formattedCases = response.data.formatted;
                    this.rawCases = response.data.raw;
                })
                .catch(() => {
                    this.error = true;
                }).finally(() => {
                this.loading = false;
            })
        }
    }
}
</script>
