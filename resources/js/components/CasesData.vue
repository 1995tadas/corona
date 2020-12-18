<template>
    <div>
        <div class="cases-diagrams">
            <template></template>
            <cases-diagram-component :cases="allCases" :translation="translation"
                                     canvas-id="casesChart" chartType="line"
                                     :title="translation.all+' '+translation.cases" :colors="colorsForCases">
            </cases-diagram-component>
            <cases-diagram-component :cases="casesPerDay" :translation="translation"
                                     canvas-id="casesPerDayConfirmed" chartType="bar" filter="confirmed"
                                     :title="translation.confirmed+' '+translation.cases+' '+translation.per_day"
                                     :colors="colorsForCases">
            </cases-diagram-component>
            <cases-diagram-component :cases="casesPerDay" :translation="translation"
                                     canvas-id="casesPerDayDeaths" chartType="bar" filter="deaths"
                                     :title="translation.death_plural+' '+translation.cases+' '+translation.per_day"
                                     :colors="colorsForCases">
            </cases-diagram-component>
            <cases-diagram-component :cases="casesPerDay" :translation="translation"
                                     canvas-id="casesPerDayActive" chartType="bar" filter="active"
                                     :title="translation.active+' '+translation.cases+' '+translation.per_day"
                                     :colors="colorsForCases">
            </cases-diagram-component>
        </div>
        <!--        <cases-table-component :cases="cases" :translation="translation">-->
        <!--        </cases-table-component>-->
    </div>
</template>

<script>
export default {
    props: {
        cases: {
            type: Array,
            required: true
        },
        translation: {
            type: Object,
            required: true
        }
    },
    created() {
        this.countCasesPerDay(this.cases);
        this.extractDataForDiagram(this.cases.reverse());
    },
    data() {
        return {
            allCases: {},
            casesPerDay: {},
            colorsForCases: {
                confirmed: '#EE0A0A',
                active: '#055EF6',
                deaths: '#000'
            }
        }
    },
    methods: {
        extractDataForDiagram(cases) {
            let allCases = {
                confirmed: [],
                deaths: [],
                active: [],
                dates: []
            }

            let casesPerDay = {
                confirmed: [],
                deaths: [],
                active: [],
                dates: []
            }

            for (let i = 0; i < cases.length; i++) {
                allCases.dates.push([cases[i].date]);
                casesPerDay.dates.push(cases[i].date);
                allCases.confirmed.push(cases[i].confirmed);
                casesPerDay.confirmed.push(cases[i].confirmedPerDay);
                allCases.deaths.push(cases[i].deaths);
                casesPerDay.deaths.push(cases[i].deathsPerDay);
                allCases.active.push(cases[i].active);
                casesPerDay.active.push(cases[i].activePerDay);
            }

            this.allCases = allCases;
            this.casesPerDay = casesPerDay;
        },

        countCasesPerDay(cases) {
            let previousDay = [];
            previousDay['confirmed'] = 0;
            previousDay['deaths'] = 0;
            previousDay['active'] = 0;
            for (let i = cases.length - 1; i >= 0; i--) {
                cases[i].confirmedPerDay = cases[i].confirmed - previousDay['confirmed'];
                previousDay['confirmed'] = cases[i].confirmed;
                cases[i].deathsPerDay = cases[i].deaths - previousDay['deaths'];
                previousDay['deaths'] = cases[i].deaths;
                cases[i].activePerDay = cases[i].active - previousDay['active'];
                previousDay['active'] = cases[i].active;
            }
        }
    }
}
</script>
