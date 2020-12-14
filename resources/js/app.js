require("./bootstrap");

window.Vue = require("vue");

import Chartkick from "vue-chartkick";
import VueApexCharts from 'vue-apexcharts'
import Chart from "chart.js";
import Vuetify from "vuetify";
import VueRouter from "vue-router";
import { store } from "./store/store";
import Vue from "vue";
import TrendChart from "vue-trend-chart";

Vue.use(TrendChart);
Vue.use(Vuetify);
Vue.use(VueRouter);
Vue.use(Chartkick.use(Chart));
Vue.use(VueApexCharts)

Vue.component('apexchart', VueApexCharts)

import LoginComponent from "./components/LoginComponent";
import NavigationComponent from "./components/NavigationComponent";
import MozaikaComponent from "./components/Main/MozaikaComponent";
import StreamInfoComponent from "./components/Main/StreamInfo/StreamInfoComponent";
import UserComponent from "./components/UserSettings/UserComponent";

import SettingsComponent from "./components/Settings/SettingsComponent";
import SettingsPrehledComponent from "./components/Settings/SettingsPrehledComponent";
// import SettingsSystemPrehledComponent from "./components/Settings/SettingsSystemPrehledComponent";
import StreamsComponent from "./components/Settings/StreamsComponent";
import UsersComponent from "./components/Settings/UsersComponent";
import FirewallComponent from './components/Settings/FirewallComponent';
import AlertComponent from './components/Settings/AlertComponent'
import PageNotFoundComponent from './components/PageNotFoundComponent'

let routes = [
    {
        path: "/",
        component: NavigationComponent,
        children: [
            {
                path: "",
                component: MozaikaComponent
            },
            {
                path: "stream/:id",
                component: StreamInfoComponent
            },
            {
                path: "user/:id",
                component: UserComponent
            },
            {
                path: "/settings/prehled",
                component: SettingsComponent,
                children: [
                    {
                        path: "",
                        component: SettingsPrehledComponent
                    },
                    {
                        path: "/settings/streams",
                        component: StreamsComponent
                    },
                    {
                        path: "/settings/users",
                        component: UsersComponent
                    },
                    {
                        path: "/settings/firewall",
                        component: FirewallComponent
                    },
                    {
                        path: "/settings/alerts",
                        component: AlertComponent
                    }
                ]
            }
        ]
    },
    {
        path: "/login",
        component: LoginComponent
    },
    {
        path: '*',
        component: PageNotFoundComponent
    },
];

// definice konstant
const router = new VueRouter({
    routes
});
const opts = {};

//module.export
const app = new Vue({
    el: "#app",
    store,
    router,
    vuetify: new Vuetify({
        theme: {
            dark: true
        },
        opts
    })
});
