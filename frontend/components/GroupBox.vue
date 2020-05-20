<template>
    <div class="group-box">
        <div class="group-box__title-line">
            <span class="group-box__title">{{group.name}}</span>
            <span class="group-box__id steam-id">{{group.steamId}}</span>
        </div>
        <div class="group-box__column group-box__column--img">
            <img
                :src="group.avatar"
                class="group-box__img"
                :alt="group.name"
            />
        </div>

        <div class="group-box__column">
            <a
                :href="group.url"
                target="_blank"
                class="group-box__link"
            >
                <i class="fab fa-fw fa-mr fa-steam-square"></i>Steam
            </a>
            <a
                :href="'http://steamgifts.com/go/group/'+group.steamId"
                target="_blank"
                class="group-box__link"
            >
                <i class="far fa-fw fa-mr fa-square"></i>Steamgifts
            </a>
            <router-link
                :to="{name: 'group', params: {groupId: group.id}}"
                class="group-box__members-count group-box__link color-green"
            >
                <i class="fas fa-fw fa-mr fa-users"></i>{{group.memberCount}} members
            </router-link>
        </div>

        <div class="group-box__column">
            <a
                v-if="group.sgLink"
                :href="group.sgLink"
                target="_blank"
                class="group-box__link"
            >
                <i class="far fa-fw fa-mr fa-square"></i>Steamgifts Real Link
            </a>
            <router-link
                v-if="showCheckerBtn"
                :to="{name: 'game_checker', params: {groupId: group.id}}"
                class="button"
            >
                <i class="fas fa-fw fa-mr fa-puzzle-piece"></i>Game Checker
            </router-link>
            <div
                :title="$getExactDateTime(lastUpdated)"
                class="group-box__update-date"
            >Updated {{$getRelativeDateTime(lastUpdated)}}</div>
        </div>

    </div>
</template>

<script>
    import {mapGetters} from 'vuex';
    import LoadingIndicator from "./LoadingIndicator";
    export default {
        name: "GroupBox",
        components: {
            LoadingIndicator
        },
        props: {
            groupId: {
                type: [Number, String],
                default: 0
            },
            lastUpdated: {
                type: String,
                default: ''
            }
        },
        data () {
            return {};
        },
        computed: {
            ...mapGetters({
                getGroup: 'groups/getGroup'
            }),

            group: function () {
                return this.getGroup( this.groupId );
            },

            showCheckerBtn: function () {
                return this.$route.name !== 'game_checker';
            }
        },
        methods: {}
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .group-box{
        display: grid;
        grid-template-columns: minmax(min-content, auto) minmax(min-content, 1fr) 1.5fr;
        align-items: center;
        padding: 15px 20px 25px;
        margin: 4px;
        border: 4px solid fade(@color-blue, 25%);
        position: relative;
        transition: background-color 0.3s, border 0.3s;

        &:hover{
            border-color: fade(@color-blue, 60%);
        }

        &__title-line{
            margin-bottom: 10px;
            grid-column: ~"1/-1";
        }

        &__title{
            color: @color-blue;
            font-size: 22px;
            font-family: @font-condensed;
        }

        &__id{
            color: @color-gray;
            font-size: 12px;
            margin-left: 6px;
        }

        &__img{
            width: 64px;
            height: 64px;
            margin-right: 20px;
            font-size: 12px;
            line-height: 1.2;
            background-color: @color-gray;
            color: @color-bg;
        }

        &__column{
            display: grid;
            grid-auto-rows: min-content;
            grid-gap: 5px;
            justify-self: start;

            &--img{
                padding-top: 6px;
            }
        }

        &__link{
            font-size: 14px;
            white-space: nowrap;
        }

        &__controls{
            display: flex;
            justify-content: space-between;
            padding-top: 5px;
        }

        &__update-date{
            font-size: 12px;
            padding-top: 6px;
        }
    }

</style>