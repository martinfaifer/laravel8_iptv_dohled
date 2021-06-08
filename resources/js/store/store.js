import Vue from "vue";

import Vuex from "vuex";

Vue.use(Vuex);

export const store = new Vuex.Store({
    state: {
        alerts: null,
        loggedUser: [],
        iptvDokuConnectionStatus: [],
        streamAlerts: []
    },
    actions: {},
    mutations: {
        updateAlert(state, alert) {
            state.alerts.push(alert);
        },
        update(state, user) {
            state.loggedUser = user;
        },

        updateIptvStatus(state, status) {
            state.iptvDokuConnectionStatus = status;
        },

        updateAlert(state, alert) {
            state.streamAlerts = alert;
        }
    },
    getters: {}
});
