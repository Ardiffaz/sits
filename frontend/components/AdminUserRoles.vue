<template>
    <div>
        <h2 class="h3">User Role Management</h2>
        <div class="section">
            Manage available roles of users.
        </div>

        <loading-indicator v-if="isLoading" />

        <template v-else>

            <div class="section">
                <user-roles
                    v-for="userId in userIds"
                    :key="userId"
                    :user-id="userId"
                />
            </div>

            <h3 class="h3">Add User</h3>
            <div class="section">
                Select a user from the list to whom you want to give a role.<br />
                <span
                    @click="startSelectingUser"
                    class="link-control"
                >Show User List</span>
            </div>

            <user-select-list
                v-if="isSelectingUser"
                @cancel-selecting-user="cancelSelectingUser"
                @change-user="addUser"
            />

        </template>

    </div>
</template>

<script>
    import {mapActions} from 'vuex';
    import LoadingIndicator from "./LoadingIndicator";
    import UserRoles from "./UserRoles";
    import UserSelectList from "./UserSelectList";
    export default {
        name: "AdminUserRoles",
        components: {LoadingIndicator, UserRoles, UserSelectList},
        props: {},
        data() {
            return {
                isLoading: false,
                isSelectingUser: false,
                userIds: []
            };
        },
        computed: {},
        methods: {
            ...mapActions({
                loadUsersWithRoles: 'users/loadUsersWithRoles'
            }),

            startSelectingUser: function () {
                this.isSelectingUser = true;
            },

            cancelSelectingUser: function () {
                this.isSelectingUser = false;
            },

            addUser: function (user) {
                if (!this.userIds.includes(user.id))
                    this.userIds.push(user.id);

                this.cancelSelectingUser();
            }
        },
        created() {
            this.isLoading = true;
            this.loadUsersWithRoles()
                .then(() => {
                    this.userIds = [...this.$store.state.users.userIdList];
                })
                .finally(() => this.isLoading = false);
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

</style>