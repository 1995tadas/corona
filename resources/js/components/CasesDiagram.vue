<template>
    <div>
        <h1 class="diagram-title">{{ title }}</h1>
        <canvas :id="canvasId" width="400" height="400">
        </canvas>
        <div class="interval-filter">
            <div>
                <label for="from-date">{{ translation['from'] }}</label>
                <input id="from-date" type="date" v-model="selectedDates[0]"
                       :min="defaultDateIntervals[0]" :max="changeWeek(selectedDates[1],'-')">
            </div>
            <div>
                <label for="to-date">{{ translation['to'] }}</label>
                <input id="to-date" type="date" v-model="selectedDates[1]"
                       :min="changeWeek(selectedDates[0], '+')" :max="defaultDateIntervals[1]">
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        canvasId: {
            type: String,
            required: true
        },
        chartType: {
            type: String,
            default: 'bar',
        },
        filter: {
            type: String,
            default: 'all'
        },
        title: {
            type: String,
            required: true
        },
        colors: {
            type: Object,
            required: true
        },
        cases: {
            type: Object,
            required: true
        },
        translation: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            type: this.chartType,
            data: {
                labels: [],
                datasets: [],
            },
            defaultDateIntervals: [],
            selectedDates: [],
            chart: {},
            options: {}
        }
    },
    created() {
        this.defaultDateIntervals = this.setDefaultDates(this.cases.dates);
        this.selectedDates = this.defaultDateIntervals.slice(0);
    },
    watch: {
        selectedDates() {
            this.filterArray(this.cases, this.cases.dates, this.selectedDates[0], this.selectedDates[1]);
        }
    },
    methods: {
        newDiagram(cases) {
            this.addDatasets(cases)
            this.createDiagram(this.canvasId, this.chart);
        },
        setDefaultDates(dates) {
            if (!Array.isArray(dates)) {
                return [];
            }

            let length = dates.length;
            if (!length) {
                return [];
            }

            let first = dates[0];
            let last = dates[length - 1]
            return [first, last];
        },
        filterArray(array, datesArray, firstCut, secondCut) {
            let first = datesArray.indexOf(firstCut);
            if (first === -1) {
                first = 0;
            }

            let last = datesArray.indexOf(secondCut);
            if (last === -1) {
                last = datesArray - 1;
            } else {
                last++;
            }

            let filteredArray = [];
            Object.keys(array).map((element) => {
                filteredArray[element] = array[element].slice(first, last)
            });
            this.newDiagram(filteredArray);
        },
        changeWeek(date, operand) {
            let result = new Date(date);
            let newDate = null;
            let daysToChange = 7;
            if (operand === '-') {
                newDate = result.getDate() - daysToChange;
            } else if (operand === '+') {
                newDate = result.getDate() + daysToChange;
            } else {
                return '';
            }

            result.setDate(newDate);
            return result.toLocaleDateString("lt-LT")
        },
        createDiagram(canvasId, chart) {
            let ctx = document.getElementById(canvasId).getContext('2d');
            if (!(chart instanceof Chart)) {
                this.chart = new Chart(ctx, {
                    type: this.type,
                    data: this.data,
                    options: this.options
                });
            } else {
                chart.update();
            }
        },
        addDatasets(cases) {
            let datasets = [];
            let names = ['confirmed', 'deaths', 'active'];
            let namesCount = names.length;
            let pointRadius = [];
            if (this.chartType === 'line') {
                let casesCount = cases.dates.length;
                pointRadius = this.setPointRadius(casesCount);
            }

            for (let i = 0; i < namesCount; i++) {
                if (cases[names[i]].length && (this.filter === names[i] || this.filter === 'all')) {
                    let blueprint = {}
                    blueprint.label = this.translation[names[i]];
                    blueprint.backgroundColor = this.colors[names[i]];
                    if (this.chartType === 'line') {
                        blueprint.fill = false;
                        blueprint.pointRadius = pointRadius;
                        blueprint.pointHoverRadius = pointRadius;
                    }

                    blueprint.borderColor = this.colors[names[i]];
                    blueprint.data = cases[names[i]];
                    datasets.push(blueprint)
                }
            }

            this.data.datasets = datasets;
            this.data.labels = cases.dates
        },

        setPointRadius(count) {
            if (count > 200) {
                return 1;
            } else if (count <= 50) {
                return 3;
            } else if (count <= 100) {
                return 2.5;
            } else if (count <= 150) {
                return 2;
            } else if (count <= 200) {
                return 1.5;
            }
        }
    }
}
</script>
