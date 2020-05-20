import Vue from 'vue';
import apiGroups from '../../api/groups';
import apiGameChecker from '../../api/gameChecker';

export default {
    namespaced: true,
    state: {
        groups: {},
        groupIdList: [],
        maxUsersLimit: 500,
    },
    getters: {
        getGroupList: (state) => state.groupIdList.map(groupId => state.groups[groupId]),
        getGroupSteamIds: (state) => Object.values(state.groups).map(group => group.steamId),

        getGroup: (state) => (groupId) => state.groups[groupId],
        getGroups: (state) => (groupIds) => groupIds.map(groupId => state.groups[groupId]),

        getAllSortedGroups: (state) => {
            let sortedGroups = Object.values(state.groups);

            sortedGroups.sort((a, b) => {
                return a.name.toLowerCase().localeCompare(b.name.toLowerCase());
            });

            return sortedGroups;
        }
    },
    mutations: {
        setGroups: (state, groups) => state.groups = groups,
        setGroupIdList: (state, groupIdList) => state.groupIdList = groupIdList,

        setGroup: (state, group) => {
            Vue.set(state.groups, group.id, group);
        },
        addGroupId: (state, groupId) => state.groupIdList.push(groupId),

        removeGroup: (state, removedId) => {
            let index = state.groupIdList.indexOf(removedId);

            if (index !== -1)
                state.groupIdList.splice( index, 1 );

            Vue.delete(state.groups, removedId);
        }
    },
    actions: {
        loadGroups({commit, state}) {
            return new Promise((resolve, reject) => {
                apiGroups.getList()
                    .then(({data: groupsReponse}) => {
                        let {ids: groupIds, map: groupMap} = this._vm.$normalizeData( groupsReponse.groups );

                        commit('setGroups', groupMap);
                        commit('setGroupIdList', groupIds);

                        resolve();
                    })
                    .catch(e => reject(e))

            });
        },

        loadGroupsWithMembers({commit, state}) {
            return new Promise((resolve, reject) => {
                apiGroups.getList(true)
                    .then(({data: groupsReponse}) => {

                        let {ids: groupIds, map: groupMap} = this._vm.$normalizeData( groupsReponse.groups );

                        let userMap = {};

                        for (let groupId in groupMap)
                        {
                            if (!groupMap.hasOwnProperty(groupId))
                                continue;

                            let group = groupMap[groupId];

                            let {ids: memberIds, map: memberMap} = this._vm.$normalizeData( group.members );
                            groupMap[groupId].members = memberIds;
                            userMap = Object.assign(userMap, memberMap);
                        }

                        commit('users/setUsers', userMap, {root: true});
                        commit('setGroups', groupMap);
                        commit('setGroupIdList', groupIds);

                        resolve();

                    })
                    .catch(e => reject(e));
            });
        },

        loadGroup({commit, state}, groupId) {
            return new Promise((resolve, reject) => {
                apiGroups.getGroup(groupId)
                    .then(({data: groupData}) => {
                        let group = groupData.group;
                        let members = groupData.members;

                        let {ids: memberIds, map: memberMap} = this._vm.$normalizeData( members );
                        group.members = memberIds;

                        commit('users/setUsers', memberMap, {root: true});
                        commit('setGroup', group);

                        resolve();
                    })
                    .catch(e => reject(e));

            });
        },

        addGroup({commit, state}, group) {
            commit('setGroup', group);
            commit('addGroupId', group.id);
        },

        updateGroup({commit, state}, groupId) {
            return new Promise((resolve, reject) => {
                apiGroups.updateGroup(groupId)
                    .then(({data: data}) => {
                        let group = data.group;
                        let members = group.members;

                        if (members)
                        {
                            let {ids: memberIds, map: memberMap} = this._vm.$normalizeData( members );
                            group.members = memberIds;

                            commit('users/addUsers', memberMap, {root: true});
                        }

                        commit('setGroup', group);

                        resolve();
                    })
                    .catch(e => reject(e));
            });
        },

        updageGroupSgLink({commit, state}, {groupId, sgLink}) {
            return new Promise((resolve, reject) => {
                apiGroups.changeSgLink(groupId, sgLink)
                    .then(({data: data}) => {
                        let group = data.group;
                        commit('setGroup', group);
                        resolve();
                    })
                    .catch(e => reject(e));
            });
        },

        removeGroup({commit, state}, groupId) {
            return new Promise((resolve, reject) => {
                apiGroups.removeGroup(groupId)
                    .then(() => {
                        commit('removeGroup', groupId);
                        resolve();
                    })
                    .catch(e => reject(e));
            });
        },

        loadGameChecker({commit, state}, {groupId, searchParam, pageNumber}) {
            return new Promise((resolve, reject) => {
                apiGameChecker.getGroupGames({groupId, searchParam, pageNumber})
                    .then(({data: checkerData}) => {
                        let group = checkerData.group;
                        let members = checkerData.members;


                        let {ids: memberIds, map: memberMap} = this._vm.$normalizeData( members );
                        group.members = memberIds;

                        commit('users/setUsers', memberMap, {root: true});
                        commit('setGroup', group);

                        let {ids: gameIds, map: gamesMap} = this._vm.$normalizeData( checkerData.games );

                        commit('games/setGames', gamesMap, {root: true});
                        commit('games/setGamesIdList', gameIds, {root: true});

                        resolve({maxPageNumber: checkerData.maxPageNumber, curPageNumber: checkerData.curPageNumber});
                    })
                    .catch(e => reject(e));
            })
        }
    }
}