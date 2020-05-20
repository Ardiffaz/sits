<template>
    <div class="container container--simple-page">
        <h1 class="h1">{{pageTitle}}</h1>

        <loading-indicator v-if="isLoading" />

        <template v-else>

            <div class="section">
                <group-box
                    :group-id="groupId"
                    :last-updated="getLastUpdatedByUserList(group.members)"
                />
            </div>

            <h2 class="h2">Member List</h2>

            <div class="user-list">
                <user-box
                    v-for="user in users"
                    :key="'group_'+group.id+'_user_'+user.id"
                    :user-id="user.id"
                />
            </div>

        </template>
    </div>
</template>

<script>
    import {mapActions, mapGetters} from 'vuex';
    import LoadingIndicator from "../components/LoadingIndicator";
    import GroupBox from "../components/GroupBox";
    import UserBox from "../components/UserBox";

    export default {
        name: "GroupPage",
        components: {
            UserBox,
            GroupBox,
            LoadingIndicator
        },
        props: {},
        data() {
            return {
                isLoading: false
            };
        },
        computed: {
            ...mapGetters({
                getGroup: 'groups/getGroup',
                getUsers: 'users/getUsers',
                getLastUpdatedByUserList: 'users/getLastUpdated'
            }),

            groupId: function () {
                return this.$route.params.groupId || null;
            },

            group: function () {
                return this.getGroup( this.groupId );
            },

            users: function () {
                if (this.group)
                    return this.getUsers( this.group.members );

                return [];
            },

            pageTitle: function () {
                return (this.group && this.group.name) ? this.group.name : 'Group Details';
            }
        },
        methods: {
            ...mapActions({
                loadGroup: 'groups/loadGroup'
            })
        },
        created() {
            this.isLoading = true;

            this.loadGroup( this.groupId )
                .finally(() => this.isLoading = false);
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";
    @import "../assets/blocks/user-list";

</style>