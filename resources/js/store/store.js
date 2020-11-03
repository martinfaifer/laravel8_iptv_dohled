import Vue from "vue";

import Vuex from "vuex";

Vue.use(Vuex);

export const store = new Vuex.Store({
    state: {
        loggedUser: [],
        iptvDokuConnectionStatus: [],
        streamAlerts: []
    },
    actions: {},
    mutations: {
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
