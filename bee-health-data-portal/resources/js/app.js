/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

Vue.use(CoreuiVue)

window.addEventListener('load', (event) => {
    window.countable.default();
    window.animateCounter.default();
    window.aos.init();
})

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

// var alertList = document.querySelectorAll('.alert')
// alertList.forEach(function (alert) {
//     new coreui.Alert(alert)
// })

Date.prototype.toShortFormat = function () {

    let monthNames = ["Jan", "Feb", "Mar", "Apr",
        "May", "Jun", "Jul", "Aug",
        "Sep", "Oct", "Nov", "Dec"];

    let day = this.getDate();

    let monthIndex = this.getMonth();
    let monthName = monthNames[monthIndex];

    let year = this.getFullYear();

    return `${day}-${monthName}-${year}`;
}

Vue.component('keywords-component', require('./components/KeywordsComponent.vue').default);
Vue.component('authors-component', require('./components/AuthorListComponent/index.vue').default);
Vue.component('authorization-organisations-component', require('./components/SpecificOrganisationsComponent/index.vue').default);
Vue.component('files-component', require('./components/FileList/index.vue').default);
Vue.component('facet-search', require('./components/FacetSearchComponent/index.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const appKeywords = new Vue({
    el: '#app-keywords',
});

const appAuthors = new Vue({
    el: '#app-authors',
});

const appAuthorizationOrganisations = new Vue({
    el: '#app-authorization-organisations',
});

const appFiles = new Vue({
    el: '#app-files',
});

const appFacetSearch = new Vue({
    el: '#app-facet-search',
})
