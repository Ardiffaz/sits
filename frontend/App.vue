<template>
    <div class="app">
        <app-header class="app__header" />
        <router-view class="app__content" :key="$route.path" />
        <app-footer class="app__footer" />
    </div>
</template>

<script>
    import {mapActions} from 'vuex';
    import AppHeader from "./components/AppHeader";
    import AppFooter from "./components/AppFooter";

    export default {
        name: "App",
        components: {
            AppFooter,
            AppHeader
        },
        data() {
            return {};
        },
        created() {
            this.loadProfile();
        },
        methods: {
            ...mapActions({
                loadProfile: 'auth/loadProfile'
            }),
        }
    }
</script>

<style lang="less">
    @import "./assets/vars";

    @import "~sanitize.css";
    @import "~@fortawesome/fontawesome-free/less/fontawesome";
    @import "~@fortawesome/fontawesome-free/less/regular";
    @import "~@fortawesome/fontawesome-free/less/solid";
    @import "~@fortawesome/fontawesome-free/less/brands";

    @import "./assets/common";

    .app{
        min-height: 100%;
        background: @color-bg;
        color: @color-text;
        font-size: 16px;
        font-family: @font-default;
        display: grid;
        grid-template-columns: 1fr;
        grid-template-areas: "header" "content" "footer";
        grid-template-rows: auto 1fr auto;

        &__header{
            grid-area: header;
        }

        &__content{
            grid-area: content;
            padding-top: 16px;
        }

        &__footer{
            grid-area: footer;
        }
    }
</style>