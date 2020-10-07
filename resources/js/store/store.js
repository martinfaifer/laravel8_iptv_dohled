import Vue from 'vue';

import Vuex from 'vuex';

Vue.use(Vuex);

export const store = new Vuex.Store({
    state: {
        userData: [],
        alerts: [],
    },
    actions: {

    },
    mutations: {
      update (state, user) {
        state.userData = user;
      },

      update (state, alert) {
        state.alerts = alert;
      },

    },
    getters: {

    }
});
