<template>
    <div class="sync">

        <div class="heading">
            <div class="heading__title">
                <h2 class="h3">Sync Users</h2>
                <div>Update user names, activity status and user games.</div>
            </div>

            <div class="heading__right">
                <div class="heading__load">
                    <loading-indicator
                        v-if="isUpdatingAll"
                        :show-content="false"
                    />
                </div>
                <a
                    v-if="userCount"
                    @click.prevent="startUpdatingAll"
                    class="link-control"
                >
                    <i class="fas fa-fw fa-mr fa-sync-alt"></i>Update all {{userCount}} users
                </a>
                <div class="heading__status">
                    <template v-if="isUpdatingAll">
                        <i class="fas fa-fw fa-mr fa-hourglass-end"></i>Updating {{updatedUsersCount}} of {{updatingUsersCount}}...
                        <a
                            @click.prevent="stopUpdatingAll"
                            class="link-control link-control--warning"
                        >Stop</a>
                    </template>

                    <div
                        v-if="updatingError"
                        class="heading__error"
                    >{{updatingError}}</div>
                </div>
            </div>

        </div>

        <loading-indicator v-if="isLoading" :add-margin="true" />

        <template v-else>
            <div class="filters section">
                <div class="filters__line">
                    <div class="filters__item">
                        <label for="f_users_name" class="filters__label">Filter by user name:</label>
                        <input
                            v-model="userNameFilter"
                            type="text"
                            id="f_users_name"
                            class="simpleinput filters__input"
                            placeholder="User name..."
                        />
                    </div>

                    <div class="filters__item">
                        <label for="f_users_group" class="filters__label">Filter by group:</label>
                        <select
                            v-model="groupFilter"
                            id="f_users_group"
                            class="simpleinput filters__input"
                        >
                            <option selected value="0">All</option>
                            <option
                                v-for="(group, key) in sortedGroups"
                                :value="group.id"
                            >{{group.name}}</option>
                            <option value="no">No groups</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="sync__items">

                <template v-if="shownUsers.length > 0">
                    <sync-user
                        v-for="(user, key) in shownUsers"
                        :key="'user_'+user.id"
                        :user-id="user.id"
                        :groups="getGroups(user.groups)"
                        :is-updating="curUpdatingUser === user.id"
                        :is-updated="isUserUpdated(user.id)"
                        :name-changed="!!nameChanged[user.id]"
                        :actions-blocked="actionsBlocked"
                        @update-user-activity="changeUserActivity"
                        @remove-user="removeUser"
                        @update-user="updateUser"
                        @change-custom-name="changeUserCustomName"
                    />
                </template>

                <div v-else class="not-found">No users were found</div>

            </div>

        </template>

    </div>
</template>

<script>
    import {mapActions, mapGetters} from 'vuex';
    import LoadingIndicator from "./LoadingIndicator";
    import UserBox from "./UserBox";
    import SyncUser from "./SyncUser";

    export default {
        name: "SyncUsers",
        components: {SyncUser, UserBox, LoadingIndicator},
        props: {},
        data() {
            return {
                isLoading: false,
                curUpdatingUser: -1,
                userNameFilter: '',
                groupFilter: '0',
                nameChanged: {},

                isUpdatingAll: false,
                idsToUpdate: [],
                updatingUsersCount: 0,

                updatedUsers: [],
                updatedUsersCount: 0,
                updatingError: '',
                actionsBlocked: false
            };
        },
        computed: {
            ...mapGetters({
                users: 'users/getUserList',
                getGroups: 'groups/getGroups',
                getUser: 'users/getUser',
                sortedGroups: 'groups/getAllSortedGroups'
            }),

            userCount: function() {
                return this.shownUsers.length;
            },

            shownUsers: function () {
                let shownUsers = this.users;

                if (this.groupFilter && (this.groupFilter !== '0') && (this.groupFilter !== 'no'))
                {
                    shownUsers = shownUsers.filter(user => {
                        return user.groups.indexOf( this.groupFilter ) !== -1;
                    });
                }

                if (this.groupFilter === 'no')
                {
                    shownUsers = shownUsers.filter(user => {
                        return user.groups.length === 0;
                    });
                }

                if (this.userNameFilter)
                {
                    shownUsers = shownUsers.filter(user => {
                        return user.profileName.toLowerCase().indexOf( this.userNameFilter.toLowerCase() ) !== -1;
                    });
                }

                return shownUsers;
            }
        },
        watch: {
            groupFilter: function () {
                let currentQuery = this.$route.query.group || '0';

                // not using === because of different types (string/number)
                // noinspection EqualityComparisonWithCoercionJS
                if (currentQuery != this.groupFilter)
                {
                    let queryData = {
                        name: 'sync_users'
                    };

                    if (this.groupFilter !== '0')
                    {
                        queryData.query = {};
                        queryData.query.group = this.groupFilter;
                    }

                    this.$router.push(queryData);
                }
            }
        },
        methods: {
            ...mapActions({
                loadUsers: 'users/loadUsers',
                setUserActivity: 'users/setUserActivity',
                setUserCustomName: 'users/setUserCustomName',
                removeUserFromList: 'users/removeUser',
                updateUserGames: 'users/updateUserGames'
            }),

            changeUserActivity({userId, active}) {
                this.actionsBlocked = true;
                this.curUpdatingUser = userId;

                this.setUserActivity({userId, active})
                    .catch(e => {
                        let error = e.response.data.error || e.message;
                        console.log(error);
                    })
                    .finally(() => {
                        this.curUpdatingUser = -1;
                        this.actionsBlocked = false;
                    });
            },

            removeUser(userId) {
                this.actionsBlocked = true;
                this.curUpdatingUser = userId;

                this.removeUserFromList(userId)
                    .catch(e => {
                        let error = e.response.data.error || e.message;
                        console.log(error);
                    })
                    .finally(() => this.actionsBlocked = false);
            },

            updateUser(userId) {
                this.actionsBlocked = true;
                this.curUpdatingUser = userId;

                if ( this.isUpdatingAll && (this.updatedUsers.indexOf(userId) !== -1) )
                {
                    console.log(this.getUser(userId).profileName + ': already updated');

                    this.updatedUsersCount++;

                    let nextUserId = this.idsToUpdate.shift();
                    this.updateUser( nextUserId );

                    return false;
                }

                this.updateUserGames(userId)
                    .then(() => {
                        this.updatedUsers.push(userId);

                        if (this.isUpdatingAll)
                        {
                            this.updatedUsersCount++;

                            if (!this.idsToUpdate.length)
                            {
                                this.stopUpdatingAll();
                                return;
                            }

                            let nextUserId = this.idsToUpdate.shift();
                            this.updateUser( nextUserId );
                        }
                    })
                    .catch(e => {
                        let error = e.response.data.error || e.message;
                        let userName = this.getUser(userId).profileName;
                        this.updatingError = userName + ': ' + error;

                        if (this.isUpdatingAll)
                            this.stopUpdatingAll();
                    })
                    .finally(() => {
                        if (!this.isUpdatingAll)
                        {
                            this.curUpdatingUser = -1;
                            this.actionsBlocked = false;
                        }
                    })
            },

            startUpdatingAll() {
                if (this.isUpdatingAll || this.actionsBlocked)
                    return;

                this.isUpdatingAll = true;
                this.actionsBlocked = true;
                this.updatedUsersCount = 0;

                let userIds = [];

                this.shownUsers.forEach(user => {
                    userIds.push(user.id);
                });

                this.idsToUpdate = userIds;
                this.updatingUsersCount = this.idsToUpdate.length;

                let firstId = this.idsToUpdate.shift();
                this.updateUser( firstId );
            },

            stopUpdatingAll() {
                this.isUpdatingAll = false;
                this.idsToUpdate = [];
                this.actionsBlocked = false;
                this.updatingUsersCount = 0;
            },

            isUserUpdated(userId) {
                return this.updatedUsers.indexOf(userId) !== -1;
            },

            changeUserCustomName({userId, customName}) {
                if (this.actionsBlocked)
                    return;

                this.actionsBlocked = true;
                this.curUpdatingUser = userId;

                this.setUserCustomName({userId, customName})
                    .then(() => {
                        this.$set(this.nameChanged, userId, true);
                    })
                    .catch(e => {
                        let error = e.response.data.error || e.message;
                        console.log(error);
                    })
                    .finally(() => {
                        this.curUpdatingUser = -1;
                        this.actionsBlocked = false;
                    });
            }
        },
        created() {
            this.isLoading = true;

            this.groupFilter = ((this.$route.query.group > 0) ? +this.$route.query.group : this.$route.query.group) || '0';

            this.loadUsers()
                .finally(() => this.isLoading = false);
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";
    @import "../assets/blocks/user-list";
    @import "../assets/blocks/filters";
    @import "../assets/blocks/not-found";

</style>