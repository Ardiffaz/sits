<template>
    <footer class="footer">
        <div class="container footer__container">
            <div class="footer__text">
                2018-2020 created by Ardiffaz, powered by Steam
            </div>
            <app-menu
                type="bottom"
                class="footer__menu"
            />
        </div>
    </footer>
</template>

<script>
    import AppMenu from "./AppMenu";

    export default {
        name: "AppFooter",
        components: {
            AppMenu
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .footer{
        border-top: 1px solid @color-green;

        &__container{
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        &__text{
            font-size: 12px;
            line-height: 1.1;
            color: @color-gray;
        }

        &__menu{
            margin-left: auto;
        }
    }

</style>