<template>
    <div :class="['user', {'user--me': isMe}, {'user--active': user.activeInGroups}]">
        <div class="user__column-pic">
            <img
                :src="user.avatar"
                :alt="user.profileName"
                class="user__pic"
            />

            <div
                v-if="user.isSkipped"
                class="user__skip-mark"
                title="There were some errors while updating, data may be inaccurate."
            >
                <i class="fas fa-fw fa-exclamation-triangle"></i>
            </div>

        </div>
        <div class="user__column-text">
            <div class="user__name-line">
                <div class="user__name">{{user.profileName}}</div>
                <div class="user__custom-name">{{user.customName}}</div>
                <div class="user__id steam-id">{{user.steamId64}}</div>
            </div>
            <div class="user__links">
                <a
                    :href="user.profileUrl"
                    class="user__link"
                    target="_blank"
                >
                    <i class="fab fa-fw fa-mr fa-steam-square"></i>Steam
                </a>
                <a
                    :href="'http://steamgifts.com/go/user/'+user.steamId64"
                    class="user__link"
                    target="_blank"
                >
                    <i class="far fa-fw fa-mr fa-square"></i>Steamgifts
                </a>
            </div>

            <div class="user__stats">
                <div class="user__stat">
                    <i class="fas fa-fw fa-mr fa-plus-circle color-green"></i>{{+user.ownedCount}}
                </div>
                <div class="user__stat">
                    <i class="fas fa-fw fa-mr fa-heart color-violet"></i>{{+user.wishlistedCount}}
                </div>
            </div>

            <div class="user__status">
                <span
                    v-if="user.activeInGroups"
                    class="color-green"
                ><i class="fas fa-fw fa-mr fa-check"></i>Active</span>
                <span
                    v-else
                    class="color-red"
                ><i class="fas fa-fw fa-mr fa-minus-circle"></i>Inactive</span>

                <div
                    class="user__update-date"
                    :title="$getExactDateTime(lastUpdatedTimestamp)"
                >Updated {{$getRelativeDateTime(lastUpdatedTimestamp)}}</div>
            </div>

        </div>
    </div>
</template>

<script>
    import {mapGetters, mapState} from 'vuex';
    export default {
        name: "UserBox",
        props: {
            userId: {
                type: Number,
                default: 0
            }
        },
        data() {
            return {};
        },
        computed: {
            ...mapGetters({
                getUser: 'users/getUser'
            }),

            ...mapState({
                loggedUserId: state => state.auth.loggedUserId
            }),

            user: function () {
                return this.getUser(this.userId);
            },

            isMe: function () {
                return this.userId === this.loggedUserId;
            },

            lastUpdatedTimestamp: function () {
                return (+Date.parse(this.user.lastUpdated)/1000).toString();
            }
        },
        methods: {}
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .user{
        display: flex;
        padding: 15px 15px;
        border: 4px solid fade(@color-blue, 25%);
        transition: border-color 0.3s;
        font-size: 14px;
        line-height: 1.3;

        &:hover{
            border-color: fade(@color-red, 40%);
        }

        &--active:hover{
            border-color: fade(@color-green, 75%);
        }

        &--me{
            background-color: fade(@color-green, 7%);
        }

        &__column-pic{
            width: 64px;
            margin-right: 10px;
            flex-shrink: 0;
            padding-top: 5px;
        }

        &__column-text{
            flex-grow: 1;
        }

        &__pic{
            width: 64px;
            height: 64px;
            display: block;
            margin-bottom: 10px;
        }

        &__name-line{
            margin-bottom: 3px;
        }

        &__name{
            color: @color-blue;
            font-weight: 500;
            font-size: 16px;
        }
        
        &__id{
            font-size: 12px;
            color: @color-gray;
        }

        &__link{

            & + &{
                margin-left: 10px;
            }
        }

        &__links{
            padding-bottom: 8px;
            border-bottom: 1px solid @color-gray;
        }

        &__stats{
            padding: 5px 0 7px;
        }

        &__stats{
            display: flex;
        }

        &__stat{

            & + &{
                margin-left: 14px;
            }
        }

        &__status{
            display: grid;
            grid-auto-flow: column;
            grid-gap: 14px;
            justify-content: start;
        }

        &__update-date{
            color: @color-gray;
        }

        &__skip-mark{
            color: @color-pink;
            font-size: 24px;
            text-align: center;
        }

    }

</style>