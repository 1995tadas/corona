<template>
    <div class="tabs">
        <ul class="tab-select">
            <li class="tab-name" v-for="number in tabsNumber" :class="{'tab-current':number === currentTab}">
                <a class="tab-link" @click.prevent="displayTab(number)" href="">
                    {{ tabsNames[number - 1] }}
                </a>
            </li>
        </ul>
        <template v-for="number in tabsNumber">
            <div v-show="number === currentTab">
                <slot :name="'tab-' + number"></slot>
            </div>
        </template>
    </div>
</template>
<script>
export default {
    props: {
        translation: {
            type: Object,
            required: true
        },
        tabsNumber: {
            type: Number,
            required: true
        },
        tabsNames: {
            type: Array,
            required: true
        }
    },
    data() {
        return {
            currentTab: 1,
        }
    },
    methods: {
        displayTab(numberOfTab) {
            if (this.currentTab !== numberOfTab) {
                this.currentTab = numberOfTab;
            }
        }
    }
}
</script>
