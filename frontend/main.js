import Vue from 'vue';
import VueCookie from 'vue-cookie';
import router from './router';
import store from './store';

Vue.use(VueCookie);

import './plugins/index';

import App from './App.vue';

new Vue({
    el: '#sits',
    render: h => h(App),
    router,
    store,
    components: {
        App
    }
});