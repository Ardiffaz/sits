<template>
    <main class="container container--simple-page">
        <h1 class="h1">Administration</h1>

        <error-box v-if="!isAdmin">You are not supposed to be here.</error-box>

        <div v-else class="layout">
            <div class="layout__side">
                <panel-menu
                    :menu-items="menuItems.sync"
                >
                    <template slot="head">
                        <i class="fas fa-fw fa-mr fa-sync-alt"></i>Synchronization
                    </template>
                </panel-menu>

                <panel-menu
                    :menu-items="menuItems.users"
                >
                    <template slot="head">
                        <i class="fas fa-fw fa-mr fa-sync-alt"></i>Users
                    </template>
                </panel-menu>

            </div>
            <div class="layout__main">
                <router-view />
            </div>
        </div>

    </main>


</template>

<script>
    import {mapState} from 'vuex';
    import PanelMenu from "../components/PanelMenu";
    import ErrorBox from "../components/ErrorBox";
    export default {
        name: "AdminPage",
        components: {
            ErrorBox,
            PanelMenu
        },
        data() {
            return {
                menuItems: {
                    sync: [
                        {id: 'sync_steam', title: 'Steam Applications'},
                        {id: 'sync_app_details', title: 'Steam Application Details'},
                        {id: 'sync_groups', title: 'Steam Groups'},
                        {id: 'sync_users', title: 'Users'}
                    ],
                    users: [
                        {id: 'user_roles', title: 'Role Management'}
                    ]
                },
            };
        },
        computed: {
            ...mapState({
                isAdmin: state => state.auth.isAdmin
            })
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

</style>