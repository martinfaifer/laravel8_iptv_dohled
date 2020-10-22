import Vue from "vue";

import Vuex from "vuex";

Vue.use(Vuex);

export const store = new Vuex.Store({
    state: {
        loggedUser: [],
        iptvDokuConnectionStatus: []
    },
    actions: {},
    mutations: {
        update(state, user) {
            state.loggedUser = user;
        },

        updateIptvStatus(state, status) {
            state.iptvDokuConnectionStatus = status;
        }
    },
    getters: {}
});
