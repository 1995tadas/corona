<template>
    <div v-if="loading" class="cases-loading">
        <div>{{ translation.loading }}</div>
        <div class="loader"></div>
    </div>
    <div class="no-data" v-else-if="error">{{ translation.error_while_loading }}</div>
    <div v-else-if="checkIfDataIsNotEmpty(formattedCases)">
        <div class="cases-diagrams">
            <content-tabs-component :translation="translation" :tabsNumber="4"
                                    :tabsNames="[
                                        translation.all,
                                        translation.confirmed,
                                        translation.deaths,
                                        translation.active
                                    ]">
                <cases-diagram-component slot="tab-1" :cases="formattedCases" :translation="translation"
                                         canvas-id="casesChart" chartType="line"
                                         :title="translation.all+' '+translation.cases">
                </cases-diagram-component>
                <cases-diagram-component slot="tab-2" :cases="formattedCases" :translation="translation"
                                         canvas-id="casesPerDayConfirmed" chartType="bar" filter="confirmed"
                                         :title="translation.confirmed+' '+translation.cases+' '+translation.per_day">
                </cases-diagram-component>
                <cases-diagram-component slot="tab-3" :cases="formattedCases" :translation="translation"
                                         canvas-id="casesPerDayDeaths" chartType="bar" filter="deaths"
                                         :title="translation.death_plural+' '+translation.cases+' '+translation.per_day"
                                         :colors="colorsForCases">
                </cases-diagram-component>
                <cases-diagram-component slot="tab-4" :cases="formattedCases" :translation="translation"
                                         canvas-id="casesPerDayActive" chartType="bar" filter="active"
                                         :title="translation.active+' '+translation.per_day">
                </cases-diagram-component>
            </content-tabs-component>
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
            colorsForCases: {
                confirmed: '#EE0A0A',
                active: '#055EF6',
                deaths: '#000'
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
