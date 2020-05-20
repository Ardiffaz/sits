<template>
    <div class="steam-login-block">
        <a
            v-if="userId"
            :href="user.profileUrl"
            target="_blank"
            class="steam-login-block__user-block"
        >{{user.profileName}}</a>

        <form
            v-else
            method="post"
            action="https://steamcommunity.com/openid/login"
        >
            <input type="hidden" name="openid.ns" value="http://specs.openid.net/auth/2.0" />
            <input type="hidden" name="openid.mode" value="checkid_setup" />
            <input type="hidden" name="openid.identity" value="http://specs.openid.net/auth/2.0/identifier_select" />
            <input type="hidden" name="openid.claimed_id" value="http://specs.openid.net/auth/2.0/identifier_select" />
            <input
                :value="returnTo"
                type="hidden"
                name="openid.return_to"
            />
            <input
                :value="returnTo"
                type="hidden"
                name="openid.realm"
            />
            <button type="submit" class="steam-login-block__button">
                <img src="https://steamcommunity-a.akamaihd.net/public/images/signinthroughsteam/sits_01.png" alt="Sign in through Steam" />
            </button>
        </form>


    </div>

</template>

<script>
    import {mapState} from 'vuex';

    export default {
        name: "SteamLoginBlock",
        data () {
            return {
                returnTo: global.location.origin  + '/login'
            };
        },
        computed: {
            ...mapState({
                user: state => state.auth.loggedUser,
                userId: state => state.auth.loggedUserId
            })
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .steam-login-block{

        &__user-block{
            text-decoration: none;
            color: @color-blue;
            font-style: italic;
            font-weight: 500;
        }

        &__button{
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
        }
    }
</style>