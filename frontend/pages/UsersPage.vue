<template>
    <main class="container container--simple-page">
        <div class="heading">
            <h1 class="h1 heading__title">User List</h1>
            <div class="heading__right"></div>
        </div>

        <loading-indicator v-if="isLoading" />

        <div
            v-else
            class="user-list"
        >
            <user-box
                v-for="(user, key) in users"
                :key="'group_'+groupId+'_user_'+user.id"
                :user-id="user.id"
            />
        </div>

    </main>
</template>

<script>
    import {mapActions, mapGetters} from 'vuex';
    import UserBox from "../components/UserBox";
    import LoadingIndicator from "../components/LoadingIndicator";

    export default {
        name: "UsersPage",
        components: {LoadingIndicator, UserBox},
        props: {},
        data() {
            return {
                isLoading: false
            };
        },
        computed: {
            ...mapGetters({
                getGroupUsers: 'groups/getGroupUserList'
            }),

            groupId: function () {
                return this.$route.params.groupId || 0;
            },

            users: function () {
                return this.getGroupUsers( this.groupId );
            }
        },
        methods: {
            ...mapActions({
                loadUsers: 'groups/loadGroupUsers'
            })
        },
        created() {
            this.isLoading = true;

            this.loadUsers(this.groupId)
                .finally( () => this.isLoading = false );
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";
    @import "../assets/blocks/user-list";

</style>