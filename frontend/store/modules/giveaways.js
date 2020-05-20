import Vue from 'vue';
import apiGiveaways from '../../api/giveaways.js';

export default {
    namespaced: true,
    state: {
        giveaways: {},
        giveawayIdsOrder: []
    },
    getters: {
        getGiveaway: (state) => (giveawayId) => state.giveaways[giveawayId],

        getGiveawayCollections: (state, getters, rootState, rootGetters) => {
            let collections = [];
            state.giveawayIdsOrder.forEach(giveawayId => {
                let giveaway = state.giveaways[giveawayId];
                let gift = rootGetters['gifts/getGift'](giveaway.gifts[0]);

                collections.push({
                    gameId: gift.game,
                    giveaways: [giveaway.id],
                });
            });

            return collections;
        }
    },
    mutations: {
        setGiveaways: (state, giveaways) => state.giveaways = giveaways,
        addGiveaways: (state, giveaways) => state.giveaways = {...state.giveaways, ...giveaways},

        setGiveawayIdsOrder: (state, ids) => state.giveawayIdsOrder = ids,

        addGiveawayIdsOrder: (state, ids) => {
            ids.forEach(id => {
                if (!state.giveawayIdsOrder.includes(id))
                    state.giveawayIdsOrder.push(id);
            })
        },

        removeGiveaway: (state, giveawayId) => {
            if (state.giveawayIdsOrder.includes(giveawayId))
                state.giveawayIdsOrder = state.giveawayIdsOrder.filter(item => item !== giveawayId);

            Vue.delete(state.giveaways, giveawayId)
        }

    },
    actions: {
        addGiveawaysToState({commit}, giveawaysResponse) {
            let {map: giveawayMap, ids: giveawayIds} = this._vm.$normalizeData( giveawaysResponse.giveaways );
            let {map: giftMap} = this._vm.$normalizeData( giveawaysResponse.gifts );
            let {map: gamesMap} = this._vm.$normalizeData( giveawaysResponse.games );
            let {map: usersMap} = this._vm.$normalizeData( giveawaysResponse.users );

            commit('games/addGames', gamesMap, {root: true});
            commit('users/addUsers', usersMap, {root: true});
            commit('gifts/addGifts', giftMap, {root: true});
            commit('addGiveaways', giveawayMap);
            commit('addGiveawayIdsOrder', giveawayIds);
        },

        setGiveawaysToState({commit}, giveawaysResponse) {
            let {map: giveawayMap, ids: giveawayIds} = this._vm.$normalizeData( giveawaysResponse.giveaways );
            let {map: giftMap} = this._vm.$normalizeData( giveawaysResponse.gifts );
            let {map: gamesMap} = this._vm.$normalizeData( giveawaysResponse.games );
            let {map: usersMap} = this._vm.$normalizeData( giveawaysResponse.users );

            commit('games/setGames', gamesMap, {root: true});
            commit('users/setUsers', usersMap, {root: true});
            commit('gifts/setGifts', giftMap, {root: true});
            commit('setGiveaways', giveawayMap);
            commit('setGiveawayIdsOrder', giveawayIds);
        },

        clearGiveawaysState({commit}) {
            commit('setGiveaways', {});
            commit('setGiveawayIdsOrder', []);
        },

        loadGiveaways({commit, state, dispatch}, {filter, pageNumber, sortParam, sortDir}) {
            return new Promise((resolve, reject) => {
                apiGiveaways.getList({filter, pageNumber, sortParam, sortDir})
                    .then(({data: giveawaysResponse}) => {
                        dispatch('setGiveawaysToState', giveawaysResponse)
                            .then(() => resolve({
                                maxPageNumber: giveawaysResponse.maxPageNumber,
                                curPageNumber: giveawaysResponse.curPageNumber,
                                totalItemsCount: giveawaysResponse.totalItemsCount
                            }));
                    })
                    .catch(e => reject(e));
            });
        },

        saveGiveaways({dispatch}, giveaways) {
            return new Promise((resolve, reject) => {
                apiGiveaways.save(giveaways)
                    .then(({data: giveawaysResponse}) => {
                        dispatch('addGiveawaysToState', giveawaysResponse)
                            .then(() => resolve( {errors: giveawaysResponse.errors} ));
                    })
                    .catch(e => reject(e))
            })
        },

        markGiveawayFinished({dispatch}, giveawayId) {
            return new Promise((resolve, reject) => {
                apiGiveaways.finish(giveawayId)
                    .then(({data: giveawaysResponse}) => {
                        dispatch('addGiveawaysToState', giveawaysResponse)
                            .then(() => resolve());
                    })
                    .catch(e => reject(e))
            })
        },

        removeGiveaway({commit, dispatch}, giveawayId) {
            return new Promise((resolve, reject) => {
                apiGiveaways.remove(giveawayId)
                    // it returns freed gifts
                    .then(({data: giftsResponse}) => {
                        commit('removeGiveaway', giveawayId);
                        dispatch('gifts/addGiftsToState', giftsResponse, {root: true})
                            .then(() => resolve());
                    })
                    .catch(e => reject(e))
            })
        },
    }
}