<template>
    <div class="checked-game">
        <div class="checked-game__pic">
            <img
                :src="'https://steamcdn-a.akamaihd.net/steam/apps/'+game.id+'/capsule_231x87.jpg'"
                :alt="game.name"
                class="checked-game__img"
            />

            <div class="checked-game__info">
                <div class="checked-game__id steam-id">{{game.id}}</div>
                <a
                    :href="'https://store.steampowered.com/app/'+game.id+'/'"
                    target="_blank"
                >Steam</a>
                <a
                    :href="'https://steamcommunity.com/app/'+game.id+'/'"
                    target="_blank"
                >Hub</a>
                <a
                    :href="'http://steamdb.info/app/'+game.id+'/'"
                    target="_blank"
                >DB</a>
                <update-game-link :game-id="game.id" />
            </div>

            <game-stats
                :game-id="game.id"
            />

            <a
                v-if="sgSearchLink"
                :href="sgSearchLink"
            >Check Giveaways on SG</a>

        </div>

        <div class="checked-game__descr">
            <div class="checked-game__title">{{game.name}}</div>

            <div class="checked-game__overview">
                <div
                    class="checked-game__overview-bar checked-game__overview-bar--owned"
                    :style="'width: ' + ownedByPercentage + '%;'"
                    :title="'Owned by ' + ownedByPercentage + '%'"
                ></div>
                <div
                    class="checked-game__overview-bar checked-game__overview-bar--not-owned"
                    :style="'width: ' + notOwnedByPercentage + '%;'"
                    :title="'Not owned by ' + notOwnedByPercentage + '%'"
                ></div>
                <div
                    class="checked-game__overview-bar checked-game__overview-bar--wishlisted"
                    :style="'width: ' + wishlistedByPercentage + '%;'"
                    :title="'Wishlisted by ' + wishlistedByPercentage + '%'"
                ></div>
            </div>

            <div class="checked-game__lists">

                <div class="checked-game__list">
                    <div class="checked-game__list-icon" title="Owned">
                        <i class="fas fa-fw fa-mr fa-plus-circle color-green"></i>
                    </div>
                    <div class="checked-game__list-content">
                        <div class="checked-game__list-count color-green" title="Owned">{{ownedBy.length}}</div>
                        <user-line
                            class="checked-game__user"
                            v-for="userData in ownedBy"
                            :key="'g_'+game.id+'_o_'+userData.user.id"
                            :user-id="userData.user.id"
                            :active="userData.active"
                        />
                    </div>
                </div>

                <div class="checked-game__list">
                    <div class="checked-game__list-icon" title="Wishlists">
                        <i class="fas fa-fw fa-mr fa-heart color-pink"></i>
                    </div>
                    <div class="checked-game__list-content">
                        <div class="checked-game__list-count color-pink" title="Wishlists">{{wishlistedBy.length}}</div>
                        <user-line
                            class="checked-game__user"
                            v-for="user in wishlistedBy"
                            :key="'g_'+game.id+'_wl_'+user.id"
                            :user-id="user.id"
                            :accent="true"
                        />
                    </div>
                </div>

                <div class="checked-game__list">
                    <div class="checked-game__list-icon" title="Not owned">
                        <i class="fas fa-fw fa-mr fa-times-circle color-gray"></i>
                    </div>
                    <div class="checked-game__list-content">
                        <div class="checked-game__list-count color-gray" title="Not owned">{{notOwnedBy.length}}</div>
                        <user-line
                            class="checked-game__user"
                            v-for="user in notOwnedBy"
                            :key="'g_'+game.id+'_no_'+user.id"
                            :user-id="user.id"
                            :accent="(game.wishlistedBy.indexOf(user.id.toString()) !== -1)"
                        />
                    </div>
                </div>

            </div>

        </div>

    </div>
</template>

<script>
    import {mapGetters} from 'vuex';
    import UserLine from "./UserLine";
    import GameStats from "./GameStats";
    import UpdateGameLink from "./UpdateGameLink";

    export default {
        name: "CheckedGame",
        components: {UpdateGameLink, GameStats, UserLine},
        props: {
            gameId: {
                type: Number,
                default: 0
            },
            groupMembers: {
                type: Object,
                default: () => ({})
            },
            groupId: {
                type: [Number, String],
                default: 0
            }
        },
        data() {
            return {};
        },
        computed: {
            ...mapGetters({
                getGroup: 'groups/getGroup',
                getGame: 'games/getGame'
            }),

            game: function () {
                return this.getGame(this.gameId);
            },

            group: function () {
                return this.getGroup(this.groupId);
            },

            sgSearchLink() {
                if (!this.group.sgLink)
                    return '';

                let gameName = encodeURIComponent(this.game.name.toLowerCase());

                return this.group.sgLink + '/search?q=' + gameName;
            },

            wishlistedBy: function () {
                return this.sortUsersByName(
                    this.game.wishlistedBy.map(userId => this.groupMembers[userId])
                );
            },

            ownedBy: function () {

                let users = this.sortUsersByName(
                    Object.keys(this.game.ownedBy).map(userId => this.groupMembers[userId])
                );

                return users.map(user => {
                    return {user: user, active: this.game.ownedBy[user.id]};
                })

            },

            notOwnedBy: function () {
                let allMemberIds = Object.keys(this.groupMembers);
                let ownedByIds = Object.keys(this.game.ownedBy);

                let notOwnedByIds = allMemberIds.filter(userId => (ownedByIds.indexOf(userId) === -1));

                return this.sortUsersByName(
                    notOwnedByIds.map(userId => this.groupMembers[userId])
                );
            },

            groupMemberCount: function () {
                return Object.keys(this.groupMembers).length;
            },

            wishlistedByPercentage: function () {
                return (this.wishlistedBy.length / this.groupMemberCount * 100).toFixed(2);
            },

            ownedByPercentage: function () {
                return (this.ownedBy.length / this.groupMemberCount * 100).toFixed(2);
            },

            notOwnedByPercentage: function () {
                return (this.notOwnedBy.length / this.groupMemberCount * 100).toFixed(2);
            }
        },
        methods: {
            ...mapGetters({
                getUser: 'users/getUser'
            }),

            sortUsersByName(users) {
                users.sort((a, b) => {
                    return a.profileName.toLowerCase().localeCompare(b.profileName.toLowerCase());
                });

                return users;
            }
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .checked-game{
        display: flex;
        padding: 20px 0;
        border-bottom: 1px solid @color-green;

        &__pic{
            width: 231px;
            margin-right: 16px;
            padding-top: 10px;
        }

        &__img{
            display: block;
            width: 231px;
            margin-bottom: 10px;
            padding: 4px;
            border: 1px solid @color-blue;
        }

        &__descr{
            flex-grow: 1;
        }

        &__title{
            color: @color-blue;
            font-family: @font-condensed;
            font-size: 24px;
            margin-bottom: 6px;
        }

        &__id{
            color: @color-gray;
            font-size: 14px;
        }

        &__info{
            display: grid;
            grid-auto-flow: column;
            grid-gap: 10px;
            justify-content: start;
            align-items: baseline;
            margin-bottom: 10px;
        }

        &__list{
            display: flex;
            margin-bottom: 20px;
        }

        &__list-icon{
            font-size: 18px;
            margin-top: -2px;
        }

        &__list-content{
            flex-grow: 1;
        }

        &__list-count{
            display: inline;
            margin-right: 10px;
            font-weight: 500;
            font-size: 14px;
            border: 1px solid;
            padding: 2px 4px 3px;
        }

        &__user{
            margin-right: 8px;

            &:after{
                content: ',';
                display: inline;
            }

            &:last-child:after{
                display: none;
            }
        }

        &__overview{
            height: 12px;
            margin-bottom: 16px;
            position: relative;
        }

        &__overview-bar{
            position: absolute;
            top: 0;
            bottom: 0;
            border-top: 4px solid @color-bg;
            border-bottom: 4px solid @color-bg;

            &--owned{
                background: @color-green;
                right: 0;
            }

            &--not-owned{
                background: @color-gray;
                left: 0;
            }

            &--wishlisted{
                background: @color-pink;
                left: 0;
                opacity: 0.75;
            }
        }
    }
</style>