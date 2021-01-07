<template>
    <section class="summary-data">
        <div class="region-filter" @mouseleave="hideSubRegions">
            <ul>
                <li>
                    <a :class="{'selected-region':selectedRegion.region === ''}"
                       @click.prevent="filterByRegion()" href="#">
                        {{ translation['all'] }}
                    </a>
                </li>
                <li v-for="(content, continent) in preparedRegions" :key="continent">
                    <a href="#" :class="{'selected-region':selectedRegion.region === continent}"
                       @click.prevent="filterByRegion(content, continent)"
                       @mouseenter="showSubRegions(content.subRegions)">
                        <template v-if="regionsTranslation">
                            {{ regionsTranslation[continent] }}
                        </template>
                        <template v-else>
                            {{ continent }}
                        </template>
                    </a>
                </li>
            </ul>
            <ul v-show="subRegions.length">
                <li v-for="region in subRegions">
                    <a @click.prevent="filterByRegion(region, region.name, true)"
                       :class="{'selected-region':selectedRegion.region === region.name}" href="">
                        <template v-if="regionsTranslation">
                            {{ regionsTranslation[region.name] }}
                        </template>
                        <template v-else>
                            {{ region.name }}
                        </template>
                    </a>
                </li>
            </ul>
        </div>
        <div v-if="sortedSummary.length" class="table-wrapper">
            <table class="table">
                <tr>
                    <th>#</th>
                    <th v-for="field in requiredFields">
                        {{ capitalize(translation[field]) }}
                        <a class="sort-link" v-if="field !== 'country'" href="" v-show="sortingShowStatus"
                           @click.prevent="sortBy(field,
                           typeof order[field] === 'boolean'? order[field] = !order[field]: order[field] = false)">
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
        <h1 class="no-countries" v-else>
            {{ translation['no-countries'] }}
        </h1>
    </section>
</template>
<script>
export default {
    props: {
        summary: {
            type: Array,
            required: true
        },
        regions: {
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
        regionsTranslation: {
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
            filteredSummary: [],
            countriesSummary: [],
            selectedRegion: {
                region: '',
                sub: false
            },
            subRegions: [],
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
        this.countriesSummary = this.filterCountriesSummary;
        this.showSummary();
    },
    computed: {
        /*
            Filters out global summary data
        */
        filterCountriesSummary() {
            let summary = this.summary.filter((data) => {
                if (data.country_id !== null && data.country_id !== undefined) {
                    return true;
                }
            });
            this.filteredSummary = summary;
            return summary;
        },
        /*
            Processes, forms and prepares region data for further use
        */
        preparedRegions() {
            let regions = {};
            let regionsLength = this.regions.length;
            for (let i = 0; i < regionsLength; i++) {
                let continent = this.regions[i].continent.name;
                let continentId = this.regions[i].id;
                if (regions[continent] === undefined) {
                    regions[continent] = {
                        id: [],
                        subRegions: []
                    };
                }

                regions[continent].id.push(continentId);
                if (this.regions[i].sub_region !== null) {
                    let subRegion = this.regions[i].sub_region.name;
                    let subRegionId = this.regions[i].sub_region.id;
                    regions[continent].subRegions.push({
                        id: [subRegionId],
                        name: subRegion
                    });
                }
            }

            return regions;
        },
        /*
            Checks if summary have more than one item
        */
        sortingShowStatus(){
            return this.sortedSummary.length > 1
        }
    },
    methods: {
        /*
            Filters summary by region and sets selected region
        */
        filterByRegion(region, name = '', sub = false) {
            if (this.selectedRegion.region !== name) {
                this.selectedRegion.region = name;
                this.selectedRegion.sub = sub;
                if (region) {
                    this.filteredSummary = this.countriesSummary.filter((data) => {
                        return region.id.includes(data.country.region_id);
                    });
                } else {
                    this.filteredSummary = this.countriesSummary;
                }

                this.showSummary()
            }
        },
        /*
            Sets selected sub-regions array and later sub-regions list will be shown in template
        */
        showSubRegions(subRegions) {
            if (subRegions.length) {
                this.subRegions = subRegions;
            }
        },
        /*
            Removes sub-regions from selected array
        */
        hideSubRegions() {
            if (!this.selectedRegion.sub) {
                this.subRegions = [];
            }
        },
        /*
            Sets sorting arrows to default positions and shows default summary data
        */
        showSummary() {
            this.order = [];
            this.sortBy('total_confirmed', 'desc');
        },
        /*
            Sorts summary by selected column and direction
        */
        sortBy(property, desc) {
            this.sortedSummary = this.filteredSummary.sort((a, b) => {
                if (desc) {
                    return b[property] - a[property];
                }

                return a[property] - b[property]
            });
        },
        /*
            Capitalizes string
        */
        capitalize(string) {
            if (typeof (string) !== 'string') {
                return ''
            }

            return string.trim().charAt(0).toUpperCase() + string.slice(1);
        },
        /*
           Toggles sorting arrows logo directions
        */
        arrowDirection(field) {
            return {
                'fa-sort-down': (this.order[field] === true || this.order[field] === undefined),
                'fa-sort-up': this.order[field] === false
            }
        },
        /*
           Summary data filter.
           For new cases adds + to the start and hides empty data.
           Formats numbers and conjunction marks between digits
        */
        emptyFilter(name, field) {
            return name.startsWith('new_')
                ? (field !== 0 ? '+' + new Intl.NumberFormat().format(field) : '')
                : new Intl.NumberFormat().format(field);
        }
    }
}
</script>
