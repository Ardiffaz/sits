import Vue from 'vue';
import apiUsers from '../../api/users';

export default {
    namespaced: true,
    state: {
        users: {},
        userIdList: [],
        gifters: {}
    },
    getters: {
        getUserList: (state) => state.userIdList.map(userId => state.users[userId]),

        getUser: (state) => (userId) => state.users[userId],
        getUsers: (state) => (userIds) => userIds.map(userId => state.users[userId]),

        getLastUpdated: (state) => userIds => {
            if (!userIds)
                return '';

            let lastUpdated = Date.now();

            userIds.map(userId => {
                let user = state.users[userId];

                if (user)
                {
                    if (!user.lastUpdated)
                        lastUpdated = 0;
                    else
                    {
                        let userLastUpdated = Date.parse(user.lastUpdated);

                        if (userLastUpdated < lastUpdated)
                            lastUpdated = userLastUpdated;
                    }
                }
            });

            return lastUpdated ? (+lastUpdated/1000).toString() : null;
        }
    },
    mutations: {
        setUsers: (state, users) => state.users = users,
        addUsers: (state, users) => state.users = {...state.users, ...users},

        setUserIdList: (state, userIdList) => state.userIdList = userIdList,

        setUser: (state, user) => {
            let updatedUser = state.users.hasOwnProperty(user.id) ? state.users[user.id] : {};
            updatedUser = {...updatedUser, ...user};

            Vue.set(state.users, user.id, {...updatedUser, ...user})
        },

        removeUser: (state, removedId) => {
            let index = state.userIdList.indexOf(removedId);

            if (index !== -1)
                state.userIdList.splice( index, 1 );

            Vue.delete(state.users, removedId);
        },

        setGifters: (state, users) => state.gifters = users
    },
    actions: {
        loadUsers({commit}) {
            return new Promise((resolve, reject) => {
                apiUsers.getList()
                    .then(({data: usersResponse}) => {

                        let {ids: userIds, map: userMap} = this._vm.$normalizeData( usersResponse.users );
                        let groupMap = {};

                        for (let userId in userMap)
                        {
                            if (!userMap.hasOwnProperty(userId))
                                continue;

                            let user = userMap[userId];
                            let {ids: groupIds, map: userGroupsMap} = this._vm.$normalizeData( user.groups );

                            userMap[userId].groups = groupIds;
                            groupMap = Object.assign(groupMap, userGroupsMap);
                        }

                        commit('groups/setGroups', groupMap, {root: true});
                        commit('setUsers', userMap);
                        commit('setUserIdList', userIds);

                        resolve();
                    })
                    .catch(e => reject(e));

            });
        },

        loadGifters({commit}) {

            return new Promise((resolve, reject) => {

                apiUsers.getGifterList()
                    .then(({data: usersResponse}) => {
                        let {ids: userIds, map: userMap} = this._vm.$normalizeData( usersResponse.users );
                        commit('setGifters', userMap);

                        resolve();
                    })
                    .catch(e => reject(e));
            })
        },

        loadUsersWithRoles({commit}) {
            return new Promise((resolve, reject) => {
                apiUsers.getUsersWithRolesList()
                    .then(({data: usersResponse}) => {
                        let {ids: userIds, map: userMap} = this._vm.$normalizeData( usersResponse.users );
                        commit('setUsers', userMap);
                        commit('setUserIdList', userIds);

                        resolve();
                    })
                    .catch(e => reject(e));
            })
        },

        setUserActivity({commit, state}, {userId, active}) {
            return new Promise((resolve, reject) => {

                apiUsers.changeActivity(userId, active)
                    .then(({data: userData}) => {

                        commit('setUser', userData.user);
                        resolve();
                    })
                    .catch(e => reject(e));
            });
        },

        setUserCustomName({commit, state}, {userId, customName}) {
            return new Promise((resolve, reject) => {

                apiUsers.changeCustomName(userId, customName)
                    .then(({data: userData}) => {

                        commit('setUser', userData.user);
                        resolve();
                    })
                    .catch(e => reject(e));
            });
        },

        updateUserGames({commit, state}, userId) {
            return new Promise((resolve, reject) => {
                apiUsers.updateGames(userId)
                    .then(({data: data}) => {

                        let log = this._vm.$getCurrentTime() + ' ';
                        log += state.users[userId].profileName + ': ';
                        log += JSON.stringify(data);
                        console.log(log);

                        let userCounts = {
                            ownedCount: data.counters.owned.total,
                            wishlistedCount: data.counters.wishlisted.total
                        };

                        commit('setUser', {...state.users[userId], ...userCounts});

                        resolve(data);
                    })
                    .catch(e => reject(e));
            })
        },

        removeUser({commit, state}, userId) {
            return new Promise((resolve, reject) => {
                apiUsers.removeUser(userId)
                    .then(() => {
                        commit('removeUser', userId);
                        resolve();
                    })
                    .catch(e => reject(e));
            })
        },

        grantUserRole({commit, state}, {userId, role}) {
            return new Promise((resolve, reject) => {
                apiUsers.grantRole(userId, role)
                    .then(({data: userData}) => {

                        commit('setUser', userData.user);
                        resolve();
                    })
                    .catch(e => reject(e));
            })
        },

        revokeUserRole({commit, state}, {userId, role}) {
            return new Promise((resolve, reject) => {
                apiUsers.revokeRole(userId, role)
                    .then(({data: userData}) => {

                        commit('setUser', userData.user);
                        resolve();
                    })
                    .catch(e => reject(e));
            })
        }
    }
}