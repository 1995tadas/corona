<template>
    <section class="summary-data">
        <div class="summary-panel">
            <div class="region-filter" @mouseleave="hideSubRegions">
                <ul>
                    <li>
                        <a :class="{'selected-region':selectedRegion === ''}"
                           @click.prevent="filterByRegion()" href="#">
                            {{ translation['all'] }}
                        </a>
                    </li>
                    <li v-for="(content, continent) in preparedRegions" :key="continent">
                        <a href="#" :class="{'selected-region':selectedRegion.name === continent}"
                           @click.prevent="filterByRegion(content.id, continent)"
                           @mouseenter="showSubRegions(content.subRegions)">
                            <template v-if="placesTranslation[continent]">
                                {{ placesTranslation[continent] }}
                            </template>
                            <template v-else>
                                {{ continent }}
                            </template>
                        </a>
                    </li>
                </ul>
                <ul v-show="subRegions.length">
                    <li v-for="region in subRegions">
                        <a @click.prevent="filterByRegion(region.id, region.name, true)"
                           :class="{'selected-region':selectedRegion.name === region.name}" href="">
                            <template v-if="placesTranslation[region.name]">
                                {{ placesTranslation[region.name] }}
                            </template>
                            <template v-else>
                                {{ region.name }}
                            </template>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="summary-search">
                <input type="text" :placeholder="translation.search" @input="filterBySearch" v-model="searchQuery">
            </div>
        </div>
        <div v-if="sortedSummary.length" class="table-wrapper">
            <table class="table">
                <tr>
                    <th>#</th>
                    <th v-for="field in requiredFields">
                        <a class="field" :class="{'sort-link':canBeSorted(field)}" ref=""
                           @click.prevent="sortableFields[field] ? sortBy(field, sortingOrder(field)) : ''">
                            {{ capitalize(translation[field]) }}
                            <template v-if="field === 'area'">
                                (km<sup>2</sup>)
                            </template>
                            <i class="sort-icon fas" :class="arrowDirection(field)"
                               v-show="sortableFields[field]"></i>
                        </a>
                    </th>
                </tr>
                <tr v-for="(data, index) in sortedSummary">
                    <td>{{ index + 1 }}</td>
                    <template v-for="field in requiredFields">
                        <td v-if="field === 'country'">
                            <a class="country-cases-link" :href="casesByCountryRoute + '/' + data.country.slug">
                                <template v-if="placesTranslation[data.country.iso2]">
                                    {{ placesTranslation[data.country.iso2] }}
                                </template>
                                <template v-else>
                                    {{ data.country.country }}
                                </template>
                            </a>
                        </td>
                        <td v-else-if="field === 'capital'">
                            <template v-if="placesTranslation[data.country.capital]">
                                {{ checkIfFieldEmpty(placesTranslation[data.country.capital]) }}
                            </template>
                            <template v-else>
                                {{ checkIfFieldEmpty(data.country.capital) }}
                            </template>
                        </td>
                        <td v-else>
                            {{
                            formatSummaryNumbers(field, (data[field] !== undefined ? data[field] : data.country[field]))
                            }}
                        </td>
                    </template>
                </tr>
            </table>
        </div>
        <h1 class="no-data" v-else>
            {{ translation['no_data'] }}
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
        placesTranslation: {
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
            selectedRegion: {},
            subRegions: [],
            searchQuery: '',
            requiredFields: [
                'country',
                'total_confirmed',
                'total_confirmed_per_capita',
                'new_confirmed',
                'new_confirmed_per_capita',
                'total_deaths',
                'total_deaths_per_capita',
                'new_deaths',
                'new_deaths_per_capita',
                'total_recovered',
                'new_recovered',
                'population',
                'area',
                'capital'
            ],
            directions: [],
            sortableFields: []
        }
    },
    created() {
        this.countriesSummary = this.filterCountriesSummary;
        this.selectedRegion = this.getRegionCookie();
        if (this.selectedRegion) {
            this.showPreviouslySelectedSummary();
        } else {
            this.showSummary();
        }
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
    },
    methods: {
        /*
           Checks if summary column have more than one item
        */
        canBeSorted(field) {
            if (this.sortedSummary.length <= 1) {
                return false;
            }

            for (let summaryItem of this.sortedSummary) {
                if (summaryItem[field] !== null && summaryItem[field] !== '' && summaryItem[field] !== 0) {
                    this.sortableFields[field] = true;
                    return true;
                }
            }

            return false;
        },
        /*
            Filters summary by region and sets selected region
        */
        filterByRegion(regionsIds, name = '', sub = false) {
            if (this.selectedRegion === '' || this.selectedRegion.name !== name) {
                this.updateSelectedRegion(regionsIds, name, sub);
            }

            if (regionsIds) {
                this.filteredSummary = this.countriesSummary.filter((data) => {
                    return regionsIds.includes(data.country.region_id);
                });
            } else {
                this.filteredSummary = this.countriesSummary;
            }

            this.showSummary()
        },
        /*
          Filters summary out and shows records than match with search query
        */
        filterBySearch() {
            this.filteredSummary = this.countriesSummary.filter(
                summary => (this.placesTranslation[summary.country.iso2].toLowerCase()).includes(this.searchQuery.toLowerCase())
            );
            if (this.selectedRegion) {
                this.updateSelectedRegion()
            }

            this.showSummary()
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
            this.directions = [];
            this.sortBy('total_confirmed', 'desc');
        },
        /*
            Sorts summary by selected column and direction
        */
        sortBy(property, desc) {
            this.sortableFields = [];
            this.sortedSummary = this.filteredSummary.sort((a, b) => {
                let elements = []
                if (a.country[property] !== undefined) {
                    if (property === 'country' && this.placesTranslation) {
                        property = 'iso2';
                    }

                    elements.first = a.country[property];
                    elements.second = b.country[property];
                } else if (a[property] !== undefined) {
                    elements.first = a[property];
                    elements.second = b[property];
                } else {
                    return []
                }
                /* Sorting strings */
                if (typeof elements.first === 'string' && typeof elements.second === 'string'
                    && elements.first !== "" && elements.second !== "") {
                    if (this.placesTranslation[elements.first] && this.placesTranslation[elements.second]) {
                        elements.first = this.placesTranslation[elements.first];
                        elements.second = this.placesTranslation[elements.second];
                    }

                    if (desc) {
                        return elements.first.localeCompare(elements.second);
                    }

                    return elements.second.localeCompare(elements.first)
                }
                /* Sorting numbers */
                if (typeof elements.first === 'number' && typeof elements.second === 'number') {
                    if (desc) {
                        return elements.second - elements.first;
                    }

                    return elements.first - elements.second
                }
                /* Sorting empty values */
                if (desc) {
                    if (elements.first === "" || elements.first === null) {
                        return 1;
                    }

                    if (elements.second === "" || elements.second === null) {
                        return -1;
                    }
                } else {
                    if (elements.first === "" || elements.first === null) {
                        return -1;
                    }

                    if (elements.second === "" || elements.second === null) {
                        return 1;
                    }
                }
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
                'fa-sort-down': (this.directions[field] === true || this.directions[field] === undefined),
                'fa-sort-up': this.directions[field] === false
            }
        },
        /*
            Sets boolean variables for fields direction tracking
        */
        sortingOrder(field) {
            return typeof this.directions[field] === 'boolean' ?
                this.directions[field] = !this.directions[field] :
                this.directions[field] = false;
        },
        /*
           Summary data filter.
           For new cases adds + to the start and hides empty data.
           Formats numbers and conjunction marks between digits
        */
        formatSummaryNumbers(name, field) {
            if (Number.isInteger(field)) {
                let formattedNumber = new Intl.NumberFormat().format(field);
                if (name.startsWith('new_')) {
                    if (field === 0) {
                        formattedNumber = null;
                    } else {
                        formattedNumber = '+' + formattedNumber;
                    }
                }

                return formattedNumber
            }

            return this.checkIfFieldEmpty(field);
        },
        /*
            When passed data field is empty returns warning string N/A
        */
        checkIfFieldEmpty(field) {
            if (field === '' || field === null || field === []) {
                return this.translation['not_available'];
            }

            return field;
        },
        /*
           Creates cookie for regions
        */
        setRegionCookie(regionsIds = '', region = '', sub = false) {
            let regionsString = '';
            if (typeof regionsIds === 'object') {
                regionsString = JSON.stringify({'name': region, 'sub': sub, regionsIds});
            }

            document.cookie = 'region=' + regionsString + ';';
        },
        /*
           Parses cookie with has a region name
        */
        getRegionCookie() {
            let cookiesString = document.cookie
            let cookiesArray = cookiesString.split(";")
            for (let cookie of cookiesArray) {
                if (cookie.startsWith('region')) {
                    let start = cookie.indexOf('=');
                    if (start) {
                        let regionString = cookie.slice(start + 1)
                        if (regionString.length !== 0) {
                            return JSON.parse(regionString);
                        }

                        break;
                    }
                }
            }

            return '';
        },
        /*
          Sets region cookie and updates selected regions
        */
        updateSelectedRegion(regionsIds = '', region = '', sub = 'false') {
            this.setRegionCookie(regionsIds, region, sub);
            this.selectedRegion = this.getRegionCookie();
        },
        /*
            Filters summary out by selectedRegion data
        */
        showPreviouslySelectedSummary() {
            let selectedRegion = this.selectedRegion;
            this.filterByRegion(selectedRegion.regionsIds, selectedRegion.name, selectedRegion.sub);
            if (selectedRegion.regionsIds.length === 1) {
                for (const property in this.preparedRegions) {
                    if (this.preparedRegions[property].id.includes(selectedRegion.regionsIds[0])) {
                        this.showSubRegions(this.preparedRegions[property].subRegions)
                        break;
                    }
                }
            }
        }
    }
}
</script>
