<template>
    <div class="table-wrapper">
        <table class="table">
            <tr>
                <th>#</th>
                <th v-for="field in requiredFields">
                    {{ capitalize(translation[field]) }}
                    <a class="sort-link" v-if="field !== 'country'" href=""
                       @click.prevent="sortBy(field, typeof order[field] === 'boolean'?order[field] = !order[field]:order[field] = false)">
                        <i class="sort-icon fas" :class="arrowDirection(field)"></i>
                    </a>
                </th>
            </tr>
            <tr v-for="(data, index) in sortedSummary">
                <td>{{ index + 1 }}</td>
                <td v-for="field in requiredFields" v-if="field !== 'country'">
                    {{ emptyFilter(field, data[field]) }}
                </td>
                <td v-else>
                    <a class="country-cases-link" :href="casesByCountryRoute + '/' + data.country.slug">
                        <template v-if="countriesTranslation">
                            {{ countriesTranslation[data.country.iso2] }}
                        </template>
                        <template v-else>
                            {{ data.country.country }}
                        </template>
                    </a>
                </td>
            </tr>
        </table>
    </div>
</template>
<script>
export default {
    props: {
        summary: {
            type: Array,
            required: true
        },
        translation: {
            type: Object,
            required: true
        },
        countriesTranslation: {
            type: Object,
        },
        casesByCountryRoute: {
            type: String,
            required: true
        },
    },
    data() {
        return {
            sortedSummary: [],
            requiredFields: [
                'country',
                'total_confirmed',
                'new_confirmed',
                'total_deaths',
                'new_deaths',
                'total_recovered',
                'new_recovered'
            ],
            order: []
        }
    },
    mounted() {
        this.sortBy('total_confirmed', 'desc');
    },
    computed: {
        filteredSummary() {
            return this.summary.filter((data) => {
                return data.country_id !== null && data.country_id !== undefined
            });
        },
    },
    methods: {
        sortBy(property, desc) {
            this.sortedSummary = this.filteredSummary.sort((a, b) => {
                if (desc) {
                    return b[property] - a[property];
                }

                return a[property] - b[property]
            });
        },
        capitalize(string) {
            if (typeof (string) !== 'string') {
                return ''
            }

            return string.trim().charAt(0).toUpperCase() + string.slice(1);
        },
        arrowDirection(field) {
            return {
                'fa-sort-down': (this.order[field] === true || this.order[field] === undefined),
                'fa-sort-up': this.order[field] === false
            }
        },
        emptyFilter(name, field) {
            return name.startsWith('new_')
                ? (field !== 0 ? '+' + new Intl.NumberFormat().format(field) : '')
                : new Intl.NumberFormat().format(field);
        }
    }
}
</script>
