import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

import authModule from './modules/auth';
import usersModule from './modules/users';
import groupsModule from './modules/groups';
import gamesModule from './modules/games';
import giftsModule from './modules/gifts';
import giveawaysModule from './modules/giveaways';

const store = new Vuex.Store({
    modules: {
        auth: authModule,
        users: usersModule,
        groups: groupsModule,
        games: gamesModule,
        gifts: giftsModule,
        giveaways: giveawaysModule
    },
    state: {
        lastUniqueId: 0
    },
    getters: {
        getUniqueId: (state) => {
            return 'new' + state.lastUniqueId;
        }
    },
    mutations: {
        incrementLastUniqueId: (state) => state.lastUniqueId++
    },
    actions: {
        updateUniqueId({commit}) {
            commit('incrementLastUniqueId');
        }
    }
});

export default store;