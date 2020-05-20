import Vue from 'vue';
import apiSync from '../../api/sync';

export default {
    namespaced: true,
    state: {
        games: {},
        gameIdList: [],
    },
    getters: {
        getGames: (state) => state.gameIdList.map(gameId => state.games[gameId]),

        getGame: (state) => (gameId) => state.games[gameId]
    },
    mutations: {
        setGames: (state, games) => state.games = games,
        setGamesIdList: (state, gameIdList) => state.gameIdList = gameIdList,

        addGame: (state, game) => {
            Vue.set(state.games, game.id, game);
        },

        addGameId: (state, gameId) => {
            if (state.gameIdList.indexOf(gameId) === -1)
                state.gameIdList.push(gameId)
        },

        addGames: (state, games) => {
            state.games = {...state.games, ...games};
        },

        updateGame: (state, game) => {
            Vue.set(state.games, game.id, {...state.games[game.id], ...game});
        }
    },
    actions: {
        addGame({state, commit}, game) {
            commit('addGame', game);
        },

        updateGameStats({state, commit}, gameId) {
            return new Promise((resolve, reject) => {
                apiSync.syncSingleAppDetails(gameId)
                    .then(({data: response}) => {
                        commit('updateGame', response.game);
                        resolve();
                    })
                    .catch(e => reject(e));
            });
        }
    }
}