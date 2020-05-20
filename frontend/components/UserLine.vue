<template>
    <div
        :class="[
            'user-line',
            {'user-line--accent': accent},
            {'user-line--faded': !user.activeInGroups},
            {'user-line--inactive': !active}
        ]"
        @click.stop="onClick"
        ref="has-user-tooltip"
        :title="title"
        :data-id="user.steamId64"
    >
        {{user.profileName}}
        <span
            v-if="user.customName"
            class="user-line__custom-name"
        >{{user.customName}}</span>
        <span
            v-if="user.isSkipped"
            class="user-line__skip-mark"
            title="There were some errors while updating, data may be inaccurate."
        >
            <i class="fas fa-fw fa-exclamation"></i>
        </span>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex';
    export default {
        name: "UserLine",
        props: {
            userId: {
                type: Number,
                default: 0
            },
            accent: {
                type: Boolean,
                default: false
            },
            active: {
                type: Boolean,
                default: true
            }
        },
        data() {
            return {};
        },
        computed: {
            ...mapGetters({
                getUser: 'users/getUser'
            }),

            user: function () {
                return this.getUser(this.userId);
            },

            title: function () {
                return this.active ? null : 'The game is no longer seen on this account';
            }
        },
        methods: {
            onClick() {
                this.$root.$emit('set-user-tooltip', this.user);
            }
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .user-line{
        display: inline-flex;
        align-items: baseline;
        font-size: 16px;
        cursor: pointer;
        transition: color 0.3s;
        
        &:hover{
            color: @color-yellow;
        }
        
        &--accent{
            border-bottom: 1px solid @color-pink;
        }

        &--faded{
            color: @color-red;
            opacity: 0.4;
        }

        &--inactive{
            color: @color-gray;
        }

        &__custom-name{
            padding-left: 4px;
            font-size: 14px;
            color: fade(@color-green, 70%);

            &:before, &:after{
                display: inline;
            }

            &:before{
                content: '[ ';
            }

            &:after{
                content: ' ]';
            }
        }
        
        &__skip-mark{
            color: @color-violet;
            font-size: 14px;
        }
    }

</style>