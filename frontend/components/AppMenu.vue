<template>
    <nav
        :class="['menu', 'menu--'+type]"
    >
        <router-link :to="{name: 'index'}" class="menu__link" exact>Index</router-link>
        <router-link :to="{name: 'groups'}" class="menu__link">Groups</router-link>
        <router-link
            v-if="isAdmin || isGifter"
            :to="{name: 'gifts'}"
            class="menu__link"
        >Gifts</router-link>
        <router-link
            v-if="isAdmin || isGifter"
            :to="{name: 'giveaways'}"
            class="menu__link"
        >Giveaways</router-link>
        <router-link
            v-if="isAdmin"
            :to="{name: 'admin'}"
            class="menu__link"
        >Admin</router-link>
    </nav>
</template>

<script>
    import {mapState} from 'vuex';

    export default {
        name: "AppMenu",
        data () {
            return {};
        },
        props: {
            type: {
                type: String,
                default: 'top'
            }
        },
        computed: {
            ...mapState({
                isAdmin: state => state.auth.isAdmin,
                isGifter: state => state.auth.isGifter
            })
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .menu{
        display: flex;
        align-items: stretch;
        height: 100%;
        min-height: 56px;
        font-family: @font-condensed;

        &__link{
            font-size: 16px;
            color: @color-blue;
            text-decoration: none;
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 0 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 0 solid transparent;
            transition: color 0.3s, border-color 0.3s, border-width 0.1s;
            outline: none;

            &:hover{
                border-top: 3px solid @color-yellow;
                color: @color-orange;
            }

            &.router-link-active{
                color: @color-yellow;
                border-top: 3px solid @color-orange;
            }
        }

        &--bottom &__link:hover{
            border-top: none;
            border-bottom: 3px solid @color-orange;
        }

        &--bottom &__link.router-link-active{
            border-top: none;
            border-bottom: 3px solid @color-yellow;
        }
    }

</style>