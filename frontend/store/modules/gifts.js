import Vue from 'vue';
import apiGifts from "../../api/gifts";

export default {
    namespaced: true,
    state: {
        gifts: {},
        giftIdsOrder: []
    },
    getters: {
        getGift: (state) => (giftId) => state.gifts[giftId],
        getGiftCollections: (state) => {
            let giftsByGame = {};
            state.giftIdsOrder.forEach(giftId => {
                let gift = state.gifts[giftId];
                let key = 'g_' + gift.game.toString();

                if (!giftsByGame[key])
                    giftsByGame[key] = {
                        gameId: gift.game,
                        gifts: [],
                        giveaways: []
                    };

                if (gift.giveaway)
                {
                    if (!giftsByGame[key].giveaways.includes(gift.giveaway))
                        giftsByGame[key].giveaways.push(gift.giveaway);
                }

                else
                    giftsByGame[key].gifts.push(gift.id);
            });

            return giftsByGame;
        }
    },
    mutations: {
        setGifts: (state, gifts) => state.gifts = gifts,

        addGifts: (state, gifts) => state.gifts = {...state.gifts, ...gifts},

        setGiftIdsOrder: (state, ids) => state.giftIdsOrder = ids,

        addGiftIdsOrder: (state, ids) => {
            ids.forEach(id => {
                if (!state.giftIdsOrder.includes(id))
                    state.giftIdsOrder.push(id);
            })
        },

        updateGift: (state, gift) => {
            Vue.set(state.gifts, gift.id, {...state.gifts[ gift.id ], ...gift});

            if (!state.giftIdsOrder.includes(gift.id))
                state.giftIdsOrder.push(gift.id);
        },

        removeGift: (state, giftId) => {

            if (state.giftIdsOrder.includes(giftId))
                state.giftIdsOrder = state.giftIdsOrder.filter(item => item !== giftId);

            Vue.delete(state.gifts, giftId);
        }
    },
    actions: {
        setGiftsToState({state, commit}, giftsResponse) {
            let {map: giftMap, ids: giftIds} = this._vm.$normalizeData( giftsResponse.gifts );
            let {map: gamesMap} = this._vm.$normalizeData( giftsResponse.games );
            let {map: usersMap} = this._vm.$normalizeData( giftsResponse.users );
            let {map: giveawayMap} = this._vm.$normalizeData( giftsResponse.giveaways );

            commit('games/setGames', gamesMap, {root: true});
            commit('users/setUsers', usersMap, {root: true});
            commit('giveaways/setGiveaways', giveawayMap, {root: true});
            commit('setGifts', giftMap);
            commit('setGiftIdsOrder', giftIds);
        },

        addGiftsToState({state, commit}, giftsResponse) {
            let {map: giftMap, ids: giftIds} = this._vm.$normalizeData( giftsResponse.gifts );
            let {map: gamesMap} = this._vm.$normalizeData( giftsResponse.games );
            let {map: usersMap} = this._vm.$normalizeData( giftsResponse.users );
            let {map: giveawayMap} = this._vm.$normalizeData( giftsResponse.giveaways );

            commit('games/addGames', gamesMap, {root: true});
            commit('users/addUsers', usersMap, {root: true});
            commit('giveaways/addGiveaways', giveawayMap, {root: true});
            commit('addGifts', giftMap);
            commit('addGiftIdsOrder', giftIds);
        },

        clearGiftsState({commit}) {
            commit('setGifts', {});
            commit('setGiftIdsOrder', []);
        },

        loadGifts({commit, dispatch}, {filter, pageNumber, sortParam, sortDir}) {
            return new Promise((resolve, reject) => {

                apiGifts.getList({filter, pageNumber, sortParam, sortDir})
                    .then(({data: giftsResponse}) => {
                        dispatch('setGiftsToState', giftsResponse)
                            .then(() => resolve({maxPageNumber: giftsResponse.maxPageNumber, curPageNumber: giftsResponse.curPageNumber}));
                    })
                    .catch(e => reject(e));

            });
        },

        saveGifts({commit, state, dispatch}, updatedGifts) {
            return new Promise((resolve, reject) => {
                apiGifts.save(updatedGifts)
                    .then(({data: giftsResponse}) => {
                        dispatch('addGiftsToState', giftsResponse)
                            .then(() => resolve({errors: giftsResponse.errors}));
                    })
                    .catch(e => reject(e))
            });
        },

        reserveGifts({commit, state, dispatch, rootState}, {giftIds, isReserving}) {
            let userId = rootState.auth.loggedUserId;

            return new Promise((resolve, reject) => {
                apiGifts.reserve(giftIds, userId, isReserving)
                    .then(({data: giftResponse}) => {
                        dispatch('addGiftsToState', giftResponse)
                            .then(() => resolve({errors: giftResponse.errors}));
                    })
                    .catch(e => reject(e));
            });
        },

        removeGifts({commit, state}, giftIds) {
            return new Promise((resolve, reject) => {
                apiGifts.remove(giftIds)
                    .then(({data: giftResponse}) => {
                        giftResponse.removed.forEach(giftId => {
                            commit('removeGift', giftId);
                        });
                        resolve({errors: giftResponse.errors});
                    })
                    .catch(e => reject(e));
            });
        }
    }
}