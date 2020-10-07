require('./bootstrap');

window.Vue = require('vue');

import Chartkick from 'vue-chartkick'
import Chart from 'chart.js'
import Vuetify from 'vuetify';
import VueRouter from 'vue-router';
import { store } from './store/store';

Vue.use(Vuetify);
Vue.use(VueRouter);
Vue.use(Chartkick.use(Chart));


import LoginComponent from './components/LoginComponent'
import NavigationComponent from './components/NavigationComponent'
import MozaikaComponent from './components/Main/MozaikaComponent'

let routes = [
    {
        path: "/",
        component: NavigationComponent,
        children: [
            {
                path: '',
                component: MozaikaComponent
            }
        ]
    },
    {
        path: '/login',
        component: LoginComponent
    },
    // {
    //     path: '*',
    //     component: PageNotFoundComponent
    // },
];

// definice konstant
const router = new VueRouter({
    routes
})
const opts = {}

//module.export
const app = new Vue({
    el: '#app',
    store,
    router,
    vuetify: new Vuetify({
        theme: {
            dark: true,
        },
        opts
    })
});
