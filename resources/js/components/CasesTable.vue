<template>
    <table class="case-table">
        <tr>
            <th>{{ translation.date }}</th>
            <th>{{ translation.confirmed + ' ' + translation.cases }}</th>
            <th>{{ translation.confirmedPerDay + ' ' + translation.cases }}</th>
            <th>{{ translation.active + ' ' + translation.cases }}</th>
            <th>{{ translation.deaths + ' ' + translation.cases }}</th>
        </tr>
        <tr v-for="value in cases">
            <td>{{ value.date }}</td>
            <td>{{ value.confirmed }}</td>
            <td>{{ value.confirmedPerDay }}</td>
            <td>{{ value.active }}</td>
            <td>{{ value.deaths }}</td>
        </tr>
    </table>
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
    },
    data() {
        return {}
    },
    methods: {}
}
</script>
