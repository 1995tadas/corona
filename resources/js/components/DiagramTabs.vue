<template>
    <div class="tabs">
        <ul class="tab-select" v-if="tabs.length > 1">
            <li class="tab-name" v-for="(tab, index) in tabs" :class="{'tab-current':index === currentTab}">
                <a class="tab-link" @click.prevent="displayTab(index)" href="">
                    {{ tab.tabName }}
                </a>
            </li>
        </ul>
        <template v-for="(tab, index) in tabs">
            <div v-show="index === currentTab">
                <cases-diagram-component :cases="tab.cases"
                                         :title="tab.title"
                                         :names="tab.names"
                                         :labels="tab.labels"
                                         :canvas-id="tab.canvasId"
                                         :chartType="tab.type"
                                         :colors="tab.colors"
                                         :translation="translation">
                </cases-diagram-component>
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
        tabs: {
            type: Array,
            required: true
        }
    },
    data() {
        return {
            currentTab: 0,
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
