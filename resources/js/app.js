/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

import Chart from 'chart.js'


/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('cases-data-component', require('./components/CasesData.vue').default);
Vue.component('cases-diagram-component', require('./components/CasesDiagram.vue').default);
Vue.component('cases-table-component', require('./components/CasesTable.vue').default);
Vue.component('summary-data-component', require('./components/SummaryData.vue').default);
Vue.component('diagram-tabs-component', require('./components/DiagramTabs.vue').default);
Vue.component('slider-component', require('./components/Slider.vue').default);

/**W
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});
