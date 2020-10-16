import Vue from "vue";

import Vuex from "vuex";

Vue.use(Vuex);

export const store = new Vuex.Store({
    state: {
        loggedUser: []
    },
    actions: {},
    mutations: {
        update(state, user) {
            state.loggedUser = user;
        }
    },
    getters: {}
});
