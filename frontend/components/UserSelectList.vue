<template>
    <div class="user-select-list">

        <div class="user-select-list__notice">
            <div class="user-select-list__notice-text">
                Select a user from the list or
                <a @click="cancelSelectingUser" class="link-control">cancel</a>.
            </div>

            <input
                v-model="userFilter"
                type="text"
                class="simpleinput"
                placeholder="Filter users..."
            />

        </div>

        <loading-indicator v-if="isLoading" />

        <div
            v-else
            class="user-select-list__users"
        >
            <div
                v-for="(user, key) in shownUsers"
                :key="'user_option_'+user.id"
                @click="changeUser(user)"
                class="user-select-list__user"
            >
                <a
                    :href="user.profileUrl"
                    target="_blank"
                >{{user.profileName}}</a>
                <span class="user-select-list__user-id steam-id">{{user.steamId64}}</span>
            </div>

        </div>

    </div>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex';
    import LoadingIndicator from "./LoadingIndicator";

    export default {
        name: "UserSelectList",
        components: {
            LoadingIndicator
        },
        props: {

        },
        data () {
            return {
                isLoading: false,
                userFilter: ''
            };
        },
        computed: {
            ...mapGetters({
                users: 'users/getUserList'
            }),

            shownUsers() {
                if (!this.userFilter)
                    return this.users;

                return this.users.filter(user => {
                    return user.profileName.toLowerCase().indexOf( this.userFilter.toLowerCase() ) !== -1;
                });
            },
        },
        methods: {
            ...mapActions({
                loadUsers: 'users/loadUsers'
            }),

            cancelSelectingUser() {
                this.$emit('cancel-selecting-user');
            },

            changeUser(newUser) {
                this.$emit('change-user', newUser);
            }
        },
        created () {
            this.isLoading = true;
            this.loadUsers()
                .finally(() => this.isLoading = false);
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .user-select-list{

        &__notice{
            margin-bottom: 12px;
            display: grid;
            grid-auto-flow: column;
            grid-gap: 10px;
            align-items: center;
            justify-content: space-between;
            width: 510px;
            grid-template-columns: 1fr 240px;
        }

        &__users{
            width: 510px;
            max-height: 410px;
            overflow-y: scroll;
            margin-bottom: 20px;
        }

        &__user{
            border-bottom: 1px solid @color-blue;
            padding: 8px;
            transition: background 0.3s;
            cursor: pointer;

            &:hover{
                background: fade(@color-blue, 10%);
            }
        }

        &__user-id{
            font-size: 12px;
            color: @color-gray;
        }

    }

</style>