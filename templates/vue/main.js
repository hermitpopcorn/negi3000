import Vue from 'vue'
import VueRouter from 'vue-router'
import VueResource from 'vue-resource'
import VueFilters from './filters'
import VueSweetAlert from 'vue-sweetalert'
import VueScrollTo from 'vue-scroll-to'
Vue.use(VueRouter)
Vue.use(VueResource)
Vue.use(VueSweetAlert)
Vue.use(VueScrollTo)
Vue.filter('currency', VueFilters.currency)
Vue.filter('date', VueFilters.date)

import App from './App.vue'
import router from './router'

Vue.http.headers.common['Auth'] = window.token;
Vue.http.get('api/user/details').then(response => {
    // start app if logged in
    window.user = response.body
    /* eslint-disable no-new */
    new Vue({
        el: '#app',
        router,
        template: '<App/>',
        components: { App }
    })
}, response => {
    // redirect to login page if not logged in
    return window.location.href = 'auth/login'
});
