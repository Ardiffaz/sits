<template>
    <div class="add-group">
        <h3 class="h3">Add new group</h3>
        <div class="add-group__choices-head">
            <span
                @click="setModeGroupId"
                :class="{'link-control': !isGroupIdMode}"
            >Enter group ID</span>
            or
            <span
                @click="setModeUserGroups"
                :class="{'link-control': !isUserGroupsMode}"
            >select one of user's groups</span>
        </div>

        <error-box v-if="error">{{error}}</error-box>

        <div
            class="add-group__choice"
            v-if="isGroupIdMode"
        >
            <input
                v-model="searchParam"
                type="text"
                name="group_id"
                class="simpleinput simpleinput--big add-group__input"
                placeholder="Group Id..."
            />

            <button
                @click="fetchSingleGroup"
                type="button"
                class="button"
            >
                <i class="fas fa-fw fa-mr fa-search"></i>Fetch
            </button>
        </div>

        <div
            class="add-group__choice"
            v-if="isUserGroupsMode"
        >
            <div class="add-group__user-block">
                User:
                <span class="add-group__username">{{user.profileName}}</span>
                <span
                    v-if="user.steamId64"
                    class="add-group__user-id steam-id"
                >{{user.steamId64}}</span>
                (<a @click="setSelectingUser" class="link-control">change</a>)
            </div>
            <button
                v-if="user.steamId64"
                @click="fetchUserGroups"
                type="button"
                class="button"
            >
                <i class="fas fa-fw fa-mr fa-stream"></i>Fetch Group List
            </button>

            <div
                v-if="groups.length > 3"
                class="add-group__group-filter"
            >
                <span class="add-group__filter-label">Filter groups:</span>
                <input
                    v-model="groupsFilter"
                    type="text"
                    class="simpleinput simpleinput--big  add-group__input"
                    placeholder="Group name..."
                />
            </div>

        </div>

        <loading-indicator
            v-if="isLoading"
            :add-margin="true"
        />

        <user-select-list
            v-if="isUserGroupsMode && isSelectingUser"
            @cancel-selecting-user="cancelSelectingUser"
            @change-user="changeUser"
        />

        <div class="add-group__fetched-groups">
            <fetched-group
                v-for="(group, key) in shownGroups"
                :key="'fetch_group_'+key"
                :group="group"
                :is-added="isGroupInList(group.groupId)"
                :max-users-limit="maxUsersLimit"
                @add-group="addGroup"
            />

        </div>

    </div>
</template>

<script>
    import {mapGetters, mapActions, mapState} from 'vuex';
    import apiFetchSteam from '../api/fetchSteam';
    import apiGroups from '../api/groups';
    import ErrorBox from "./ErrorBox.vue";
    import FetchedGroup from "./FetchedGroup.vue"
    import LoadingIndicator from "./LoadingIndicator.vue";
    import UserSelectList from "./UserSelectList";

    export default {
        name: "AddGroupForm",
        components: {
            ErrorBox,
            FetchedGroup,
            LoadingIndicator,
            UserSelectList
        },
        props: {},
        data () {
            return {
                isLoading: false,
                error: '',

                searchParam: '',
                selectedMode: '',

                user: null,
                isSelectingUser: false,

                groups: [],
                groupsFilter: ''
            };
        },
        computed: {
            ...mapState({
                loggedUser: state => state.auth.loggedUser,
                maxUsersLimit: state => state.grouos.maxUsersLimit
            }),

            ...mapGetters({
                groupSteamIds: 'groups/getGroupSteamIds'
            }),

            isGroupIdMode() {
                return this.selectedMode === 'groupId';
            },

            isUserGroupsMode() {
                return this.selectedMode === 'userGroups';
            },

            shownGroups() {
                if (!this.groupsFilter)
                    return this.groups;

                return this.groups.filter(group => {
                    return group.groupName.toLowerCase().indexOf( this.groupsFilter.toLowerCase() ) !== -1;
                });
            }

        },
        methods: {
            ...mapActions({
                addGroupToList: 'groups/addGroup'
            }),

            setModeGroupId() {
                this.selectedMode = 'groupId';
            },

            setModeUserGroups() {
                this.selectedMode = 'userGroups';

                if (this.loggedUser)
                    this.user = {...this.loggedUser};
            },

            setSelectingUser() {
                this.isSelectingUser = true;
            },

            cancelSelectingUser() {
                this.isSelectingUser = false;
            },

            changeUser(newUser) {
                this.user = newUser;
                this.isSelectingUser = false;
                this.groupsFilter = '';
                this.groups = [];
            },

            fetchFromSteam(methodName, param) {
                this.isSelectingUser = false;
                this.isLoading = true;
                this.error = '';
                this.groups = [];

                apiFetchSteam[methodName](param)
                    .then( ({data: groupData}) => {

                        if (groupData.group)
                            this.groups = [groupData.group];
                        else if (groupData.groups)
                            this.groups = groupData.groups;

                    })
                    .catch(e => {
                        this.error = e.message;

                        if (e.response.data.error)
                            this.error += ': ' + e.response.data.error;
                    })
                    .finally( () => {
                        this.isLoading = false;
                    });
            },

            fetchSingleGroup() {
                if (!this.searchParam)
                    return false;

                this.fetchFromSteam('group', this.searchParam);
            },

            fetchUserGroups() {
                if (!this.user.steamId64)
                    return false;

                this.fetchFromSteam('userGroups', this.user.steamId64);
            },

            isGroupInList (groupSteamId) {
                return this.groupSteamIds.indexOf(groupSteamId) !== -1;
            },

            getGroup(groupId) {
                let group = this.groups.filter(group => {
                    return (group.groupId === groupId)
                });
                return group.shift();
            },

            addGroup(groupId) {
                this.error = '';

                let curGroup = this.getGroup(groupId);
                this.$set(curGroup, 'isLoading', true);

                apiGroups.addGroup(groupId)
                    .then( ({data: data}) => {
                        this.addGroupToList(data.group);
                    })
                    .catch(e => {
                        this.error = e.response.data.error;
                        this.$set(curGroup, 'error', e.response.data.error);
                    })
                    .finally(() => {
                        this.$set(curGroup, 'isLoading', false);
                    });
            }

        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .add-group{

        &__choices-head{
            margin-bottom: 14px;
        }

        &__choice{
            display: grid;
            grid-auto-flow: column;
            grid-gap: 10px;
            grid-template-columns: repeat(5, min-content);
            justify-items: start;
            align-items: center;
            margin-bottom: 20px;
        }

        &__input{
            min-width: 250px;
        }

        &__user-block{
            white-space: nowrap;
        }
        
        &__username{
            color: @color-blue;
        }

        &__user-id{
            font-size: 12px;
            color: @color-gray;
        }

        &__fetched-groups{
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            grid-gap: 20px;
        }

        &__group-filter{
            margin-left: 40px;
            display: flex;
            align-items: center;
        }

        &__filter-label{
            white-space: nowrap;
            margin-right: 10px;
        }
    }

</style>