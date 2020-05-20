<template>
    <div class="sync">

        <div class="heading">
            <div class="heading__title">
                <h2 class="h3">Sync Steam Groups</h2>
                <div class="sync__options section">
                    <div class="sync__option">
                        <input
                            v-model="needUpdatingUsers"
                            :value="false"
                            type="radio"
                            class="radiobox"
                            id="ug_no_users"
                        />
                        <label for="ug_no_users">Update group summary and member list only.</label>
                    </div>
                    <div class="sync__option">
                        <input
                            v-model="needUpdatingUsers"
                            :value="true"
                            type="radio"
                            class="radiobox"
                            id="ug_with_users"
                        />
                        <label for="ug_with_users">Fully update group and its members, their owned games and wishlists.</label>
                    </div>
                </div>
            </div>

            <div class="heading__right">
                <div class="heading__load">
                    <loading-indicator
                        v-if="isUpdatingAll"
                        :show-content="false"
                    />
                </div>
                <div>
                    <a
                        v-if="groupCount"
                        @click.prevent="startUpdatingAllGroups"
                        class="link-control"
                    >
                        <i class="fas fa-fw fa-mr fa-sync-alt"></i>Update all {{groupCount}} groups
                    </a>
                </div>

                <div class="heading__status">
                    <template v-if="isUpdatingAll">
                        <i class="fas fa-fw fa-mr fa-hourglass-end"></i>Updating {{curUpdatingGroupIndex + 1}} of {{groupCount}}...
                        <a
                            @click.prevent="stopUpdatingAllGroups"
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

        <div class="sync__items">

            <sync-group
                v-for="group in groups"
                :key="'sync_group_'+group.id"
                :group-id="group.id"
                :is-updating="curUpdatingGroup === group.id"
                :is-updated="isGroupUpdated(group.id)"
                :actions-blocked="actionsBlocked"
                :updating-user-index="curUpdatingUserIndex"
                :last-updated="getLastUpdatedByUserList(group.members)"
                @remove-group="removeGroup"
                @update-group="updateSingleGroup"
            />

        </div>

        <add-group-form />

    </div>
</template>

<script>
    import {mapActions, mapGetters} from "vuex";
    import AddGroupForm from "./AddGroupForm";
    import LoadingIndicator from "./LoadingIndicator";
    import SyncGroup from "./SyncGroup";

    export default {
        name: "SyncSteamGroups",
        components: {AddGroupForm, LoadingIndicator, SyncGroup},
        props: {},
        data() {
            return {
                isLoading: false,
                isUpdatingAll: false,
                updatingError: '',

                needUpdatingUsers: false,

                updatedGroups: [],
                updatedUsers: [],
                curUpdatingGroup: -1,
                curUpdatingGroupIndex: -1,
                curUpdatingUserIndex: -1,

                actionsBlocked: false

            };
        },
        computed: {
            ...mapGetters({
                groups: 'groups/getGroupList',
                getGroup: 'groups/getGroup',
                getUser: 'users/getUser',
                getLastUpdatedByUserList: 'users/getLastUpdated'
            }),

            groupCount () {
                return this.groups.length;
            }
        },
        watch: {
            curUpdatingGroupIndex: function (newIndex) {

                if (newIndex === -1)
                    return false;

                if (!this.groups.hasOwnProperty(newIndex))
                {
                    this.finishUpdatingAllGroups();
                    return false;
                }

                let groupId = this.groups[newIndex].id;

                this.updateGroup(groupId);
            },

            curUpdatingUserIndex: function (newIndex, oldIndex) {
                if (newIndex === -1)
                    return false;

                let curGroup = this.groups[ this.curUpdatingGroupIndex ];

                if (!curGroup)
                    return false;

                if (!curGroup.members.hasOwnProperty(newIndex))
                {
                    this.finishUpdatingGroup();
                    return false;
                }

                let userId = curGroup.members[newIndex];
                this.updateUser(userId);
            }
        },
        methods: {
            ...mapActions({
                loadGroups: 'groups/loadGroupsWithMembers',
                removeGroupFromList: 'groups/removeGroup',
                updateGroupFromSteam: 'groups/updateGroup',
                updateUserGames: 'users/updateUserGames'
            }),

            isGroupUpdated(groupId) {
                return this.updatedGroups.indexOf(groupId) !== -1;
            },

            setThisGroupUpdating(groupId) {
                let groupIndex = this.groups.map(group => group.id).indexOf(groupId);
                if (groupIndex === -1)
                    return;

                this.curUpdatingGroup = groupId;
                this.curUpdatingGroupIndex = groupIndex;
            },

            updateSingleGroup(groupId) {
                if (this.actionsBlocked)
                    return;

                this.setThisGroupUpdating(groupId);
            },

            updateGroup(groupId) {
                this.actionsBlocked = true;
                this.updatingError = '';

                let indexInUpdatedGroups = this.updatedGroups.indexOf(groupId);
                if (indexInUpdatedGroups !== -1)
                    this.updatedGroups.splice(indexInUpdatedGroups, 1);

                this.setThisGroupUpdating(groupId);

                this.updateGroupFromSteam(groupId)
                    .then(() => {

                        if (this.needUpdatingUsers)
                        {
                            // start updating users from index 0
                            this.curUpdatingUserIndex = 0;
                        }
                        else
                        {
                            this.finishUpdatingGroup();
                        }

                    })
                    .catch(e => {

                        let error = e.response.data.error || e.message;
                        let groupName = this.getGroup(groupId).name;

                        this.updatingError = this.$getCurrentTime() + ' ' + groupName + ': ' + error;

                        this.stopDueToError();

                    });
            },

            finishUpdatingGroup() {
                //this group is set to Updated
                this.updatedGroups.push(this.curUpdatingGroup);

                this.curUpdatingUserIndex = -1;

                // if we are in progress of updating all groups, then change the index
                if (this.isUpdatingAll)
                    this.curUpdatingGroupIndex++;
                else
                {
                    this.curUpdatingGroupIndex = -1;
                    this.curUpdatingGroup = -1;
                    this.actionsBlocked = false;
                }
            },

            updateUser(userId){

                if (this.updatedUsers.indexOf(userId) !== -1)
                {
                    this.curUpdatingUserIndex++;
                    console.log(this.getUser(userId).profileName + ': already updated');
                    return false;
                }

                this.updateUserGames(userId)
                    .then(() => {
                        // add userId to updated list, so it won't be updated once again in another group
                        this.updatedUsers.push(userId);

                        // let next user update
                        this.curUpdatingUserIndex++;
                    })
                    .catch(e => {
                        let error = e.response.data.error || e.message;
                        let userName = this.getUser(userId).profileName;
                        this.updatingError = userName + ': ' + error;

                        this.stopDueToError();
                    });
            },

            removeGroup(groupId) {
                if (this.actionsBlocked)
                    return;

                this.actionsBlocked = true;

                let groupName = this.getGroup(groupId).name;
                let confirmed = confirm(`Are you sure you want to remove the group?\n\n${groupName}`);

                if (!confirmed)
                    return false;

                this.removeGroupFromList(groupId)
                    .then()
                    .catch(e => {
                        this.updatingError = e.response.data.error;
                    })
                    .finally(() => this.actionsBlocked = false);
            },


            startUpdatingAllGroups() {
                if (this.actionsBlocked)
                    return;

                this.updatingError = '';
                this.updatedGroups = [];
                this.updatedUsers = [];
                this.isUpdatingAll = true;
                this.curUpdatingGroup = this.groups[0].id;
                this.curUpdatingGroupIndex = 0;
            },

            finishUpdatingAllGroups() {
                this.isUpdatingAll = false;
                this.curUpdatingGroupIndex = -1;
                this.curUpdatingGroup = -1;
                this.actionsBlocked = false;
            },

            stopUpdatingAllGroups() {
                this.isUpdatingAll = false;
            },

            stopDueToError() {
                this.isUpdatingAll = false;
                this.curUpdatingGroupIndex = -1;
                this.curUpdatingGroup = -1;
                this.curUpdatingUserIndex = -1;
                this.actionsBlocked = false;
            }
        },
        created () {
            this.isLoading = true;
            this.loadGroups().finally(() => this.isLoading = false);
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";
    @import "../assets/blocks/sync";

</style>



