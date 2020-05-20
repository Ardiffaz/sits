import Vue from 'vue';
import VueRouter from 'vue-router';

const IndexPage = () => import( /* webpackChunkName: 'index_page' */ '../pages/IndexPage.vue');
const GroupsPage = () => import( /* webpackChunkName: 'groups_page' */ '../pages/GroupsPage.vue');
const GroupPage = () => import( /* webpackChunkName: 'group_page' */ '../pages/GroupPage.vue');
const GameCheckerPage = () => import( /* webpackChunkName: 'game_checker_page' */ '../pages/GameCheckerPage.vue');
const AdminPage = () => import( /* webpackChunkName: 'admin_page' */ '../pages/AdminPage.vue');
const GiftsPage = () => import( /* webpackChunkName: 'gifts_page' */ '../pages/GiftsPage.vue');
const GiftsAddPage = () => import( /* webpackChunkName: 'gifts_add_page' */ '../pages/GiftsAddPage.vue');
const GiftsImportPage = () => import( /* webpackChunkName: 'gifts_import_page' */ '../pages/GiftsImportPage.vue');
const GiveawaysPage = () => import( /* webpackChunkName: 'gas_page' */ '../pages/GiveawaysPage.vue');

Vue.use(VueRouter);

let router = new VueRouter({
    mode: 'history',
    routes: [
        {
            name: 'index',
            path: '/',
            component: IndexPage,
            meta: { title: 'Stars in the Stash' }
        },
        {
            name: 'groups',
            path: '/groups',
            component: GroupsPage,
            meta: {title: 'Group List'}
        },
        {
            name: 'group',
            path: '/groups/:groupId',
            component: GroupPage,
            meta: {title: 'Group Page'}
        },
        {
            name: 'game_checker',
            path: '/groups/:groupId/checker',
            component: GameCheckerPage,
            meta: {title: 'Game Checker'}
        },
        {
            name: 'gifts',
            path: '/gifts',
            component: GiftsPage,
            meta: {title: 'Gifts'}
        },
        {
            name: 'gifts_add',
            path: '/gifts/add',
            component: GiftsAddPage,
            meta: {title: 'Add New Gifts'}
        },
        {
            name: 'gifts_import',
            path: '/gifts/import',
            component: GiftsImportPage,
            meta: {title: 'Import Gifts From File'}
        },
        {
            name: 'giveaways',
            path: '/giveaways',
            component: GiveawaysPage,
            meta: {title: 'Giveaways'}
        },
        {
            name: 'admin',
            path: '/admin',
            component: AdminPage,
            meta: {title: 'Administration'},
            children: [
                {
                    name: 'sync_steam',
                    path: 'sync_steam',
                    component: () => import ( /* webpackChunkName: 'sync_steam' */ '../components/SyncSteamApps.vue'),
                    meta: {title: 'Synchronization of Steam Applications'}
                },
                {
                    name: 'sync_app_details',
                    path: 'sync_app_details',
                    component: () => import ( /* webpackChunkName: 'sync_app_details' */ '../components/SyncSteamAppDetails.vue'),
                    meta: {title: 'Synchronization of Steam Application Details'}
                },
                {
                    name: 'sync_groups',
                    path: 'sync_groups',
                    component: () => import (/* webpackChunkName: 'sync_groups' */ '../components/SyncSteamGroups.vue'),
                    meta: {title: 'Synchronization of Steam Groups'}
                },
                {
                    name: 'sync_users',
                    path: 'sync_users',
                    component: () => import (/* webpackChunkName: 'sync_users' */ '../components/SyncUsers.vue'),
                    meta: {title: 'Synchronization of Steam Users'}
                },
                {
                    name: 'user_roles',
                    path: 'roles',
                    component: () => import(/* webpackChunkName: 'admin_roles' */ '../components/AdminUserRoles.vue'),
                    meta: {title: 'User Role Management'}
                }
            ]
        }
    ]
});

router.beforeEach((to, from, next) => {
    if (to.meta.title !== undefined)
        document.title = to.meta.title;
    else
        document.title = 'Welcome';

    next();
});

export default router;