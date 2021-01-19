<template>
    <div class="table-wrapper">
        <table class="table">
            <tr>
                <th>{{ translation.date }}</th>
                <th>{{ translation.confirmed + ' ' + translation.cases }}</th>
                <th>{{ translation.confirmed + ' ' + translation.cases + ' ' + translation.per_day }}</th>
                <th>{{ translation.death_plural + ' ' + translation.cases }}</th>
                <th>{{ translation.death_plural + ' ' + translation.cases + ' ' + translation.per_day }}</th>
                <th>{{ translation.active }}</th>

            </tr>
            <tr v-for="value in formattedCases">
                <td>{{ value.date }}</td>
                <td>{{ value.confirmed }}</td>
                <td>{{ value.confirmedPerDay }}</td>
                <td>{{ value.deaths }}</td>
                <td>{{ value.deathsPerDay }}</td>
                <td>{{ value.active }}</td>
            </tr>
        </table>
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
        },
    },
    computed: {
        formattedCases() {
            let preparedCases = [];
            let formatted = {};
            for (let cases of this.cases) {
                formatted = {};
                for (const [key, value] of Object.entries(cases)) {
                    if (key !== 'id' && key !== 'country_id' && key !== 'date') {
                        formatted[key] = new Intl.NumberFormat().format(value);
                    } else {
                        formatted[key] = value;
                    }
                }

                preparedCases.push(formatted);
            }

            return preparedCases.reverse();
        }
    }
}
</script>
