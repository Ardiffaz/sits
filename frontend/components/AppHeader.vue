<template>
    <header class="header">
        <div class="container header__container">
            <div class="header__logo">
                <img src="/logo.svg" class="header__logo-img" alt="Stars in the Stash" />
            </div>
            <steam-login-block class="header__user" />
            <app-menu class="header__menu" />
        </div>
    </header>
</template>

<script>
    import AppMenu from "./AppMenu";
    import SteamLoginBlock from "./SteamLoginBlock";

    export default {
        name: "AppHeader",
        components: {
            SteamLoginBlock,
            AppMenu
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .header{
        border-bottom: 1px solid @color-green;

        &__container{
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        &__logo{
            margin-right: 20px;
            width: 42px;
            height: 42px;
        }

        &__logo-img{
            width: 100%;
            height: 100%;
        }

        &__user{
            margin-right: 20px;
        }

        &__menu{
            margin-left: auto;
        }
    }

</style>