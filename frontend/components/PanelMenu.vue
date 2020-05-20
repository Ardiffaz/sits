<template>
    <nav class="panel-menu">
        <div class="panel-menu__head">
            <slot name="head"></slot>
        </div>
        <router-link
            v-for="(menuItem, key) in menuItems"
            :key="'menu_'+menuItem.id"
            :to="{name: menuItem.id}"
            class="panel-menu__link"

        >{{menuItem.title}}</router-link>
    </nav>
</template>

<script>
    export default {
        name: "PanelMenu",
        props: {
            menuItems: {
                type: Array,
                default: []
            }
        },
        data() {
            return {};
        },
        methods: {}
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .panel-menu{
        display: flex;
        flex-direction: column;
        margin-bottom: 25px;

        &__head{
            padding: 5px 10px;
            font-size: 20px;
            font-family: @font-condensed;
        }

        &__link{
            text-decoration: none;
            color: @color-pink;
            font-size: 16px;
            font-weight: 500;
            border-bottom: 1px solid @color-green;
            padding: 10px 10px;
            z-index: 1;
            position: relative;
            transition: color 0.3s, padding 0.3s;

            &:before{
                content: '';
                display: block;
                border-top: 1px solid @color-green;
                position: absolute;
                top: -1px;
                left: 0;
                width: 100%;
                height: 0;
                z-index: 1;
            }

            &:after{
                content: '';
                display: block;
                position: absolute;
                top: 0;
                left: 0;
                width: 5px;
                height: 100%;
                background-color: transparent;
            }

            &:hover, &.router-link-active{
                color: @color-yellow;
                border-bottom-color: @color-yellow;
                z-index: 5;
                padding-left: 13px;

                &:before{
                    border-top-color: @color-yellow;
                    z-index: 5;
                }
            }

            &.router-link-active{
                padding-left: 13px;

                &:after{
                    background-color: @color-yellow;
                }
            }
        }
    }
</style>