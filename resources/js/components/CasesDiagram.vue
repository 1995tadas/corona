<template>
    <canvas :id="canvasId" width="400" height="400"></canvas>
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
            type: Object
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
    mounted() {
        this.addDatasets(this.cases)
        this.createDiagram(this.canvasId);
        this.renderTitle(this.canvasId);
    },
    data() {
        return {
            type: this.chartType,
            data: {
                labels: this.cases.dates,
                datasets: []
            }
        }
    },
    methods: {
        renderTitle(canvasId) {
            let title = document.createElement("h1");
            title.innerHTML = this.title;
            let diagram = document.getElementById(canvasId);
            title.classList.add("diagram-title");
            diagram.parentNode.insertBefore(title, diagram.previousSibling)
        },
        createDiagram(canvasId) {
            let ctx = document.getElementById(canvasId).getContext('2d');
            new Chart(ctx, {
                type: this.type,
                data: this.data,
                options: this.options
            });
        },
        addDatasets(cases) {
            let datasets = [];
            let caseTypes = ['confirmed', 'deaths', 'active']
            for (let i = 0; i < caseTypes.length; i++) {
                let blueprint = {};
                if (cases[caseTypes[i]].length && (this.filter === caseTypes[i] || this.filter === 'all')) {
                    blueprint = {
                        label: [],
                        backgroundColor: '',
                        borderColor: '',
                        pointRadius: 1,
                        pointHoverRadius: 2,
                        borderWidth: 0.5,
                        fill: false,
                        data: []
                    }
                    blueprint.label = this.translation[caseTypes[i]];
                    blueprint.backgroundColor = this.colors[caseTypes[i]];
                    blueprint.borderColor = this.colors[caseTypes[i]];
                    blueprint.data = this.cases[caseTypes[i]];
                    datasets.push(blueprint)
                }
            }

            this.data.datasets = datasets;
        }
    }
}
</script>
