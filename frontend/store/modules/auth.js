import apiAuth from '../../api/auth';

export default {
    namespaced: true,
    state: {
        isAuthenticated: false,
        isAdmin: false,
        isGifter: false,
        loggedUserId: null,
        loggedUser: {}
    },
    getters: {},
    mutations: {
        setLoggedUserId: (state, userId) => state.loggedUserId = userId,

        setLoggedUser: (state, user) => state.loggedUser = user,

        setAuthenticated: (state, value) => state.isAuthenticated = value,

        setAdmin: (state, isAdmin) => state.isAdmin = isAdmin,

        setGifter: (state, isGifter) => state.isGifter = isGifter
    },
    actions: {
        loadProfile ({commit}) {
            return new Promise((resolve) => {
                apiAuth.getProfile()
                    .then(({data: data}) => {
                        let user = data.user;

                        if (!user)
                            return;

                        commit('setLoggedUserId', user.id);
                        commit('setLoggedUser', user);
                        commit('setAuthenticated', true);
                        commit('setAdmin', !!user.admin);
                        commit('setGifter', !!user.gifter);

                        resolve();
                    })
            })
        }
    }
}
