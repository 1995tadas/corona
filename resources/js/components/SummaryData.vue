<template>
    <table class="summary-table">
        <tr>
            <th>#</th>
            <th v-for="field in requiredFields">
                {{ translation[field] }}
                <a class="sort-link" v-if="field !== 'country'" href=""
                   @click.prevent="sortBy(field, typeof order[field] === 'boolean'?order[field] = !order[field]:order[field] = false)">
                    <i class="sort-icon fas"
                       :class="{'fa-sort-down':(order[field] === true || order[field] === undefined),'fa-sort-up':order[field] ===false }"></i>
                </a>
            </th>
        </tr>
        <tr v-for="(data, index) in sortedSummary">
            <td>{{ index + 1 }}</td>
            <td v-for="field in requiredFields" v-if="field !== 'country'">
                {{ data[field] }}
            </td>
            <td v-else>
                <a :href="casesByCountryRoute+'/'+data.country.slug">
                    <template v-if="data.country.lt_country">
                        {{ data.country.lt_country }}
                    </template>
                    <template v-else>
                        {{ data.country.country }}
                    </template>
                </a>
            </td>
        </tr>
    </table>
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
        casesByCountryRoute: {
            type: String,
            required: true
        },
    },
    data() {
        return {
            filteredSummary: [],
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
        this.filterSummary();
        this.sortBy('total_confirmed', 'desc');
    },
    methods: {
        filterSummary() {
            this.filteredSummary = this.summary.filter((data) => {
                return data.country_id !== null && data.country_id !== undefined
            });
        },
        sortBy(property, desc) {
            this.sortedSummary = this.filteredSummary.sort((a, b) => {
                if (desc) {
                    return b[property] - a[property];
                }

                return a[property] - b[property]
            });
        }
    }
}
</script>
