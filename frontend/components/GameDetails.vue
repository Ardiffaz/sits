<template>
    <div class="game-details">
        <img
            :src="'https://steamcdn-a.akamaihd.net/steam/apps/'+game.id+'/capsule_184x69.jpg'"
            :alt="game.name"
            class="game-details__img"
        />
        <div class="game-details__links">
            <div class="game-details__id steam-id">{{game.id}}</div>
            <a
                :href="'https://store.steampowered.com/app/'+game.id+'/'"
                target="_blank"
            >
                <i class="fab fa-fw fa-steam"></i>
            </a>
            <a
                :href="'https://steamcommunity.com/app/'+game.id+'/'"
                target="_blank"
            >Hub</a>
            <a
                :href="'http://steamdb.info/app/'+game.id+'/'"
                target="_blank"
            >DB</a>
            <update-game-link
                :game-id="game.id"
                class="game-details__link-update"
            />
        </div>
        <div class="game-details__name">{{game.name}}</div>
        <game-stats
            :game-id="game.id"
        />
    </div>
</template>

<script>
    import {mapGetters} from 'vuex';
    import GameStats from "./GameStats";
    import UpdateGameLink from "./UpdateGameLink";
    export default {
        name: "GameDetails",
        components: {UpdateGameLink, GameStats},
        props: {
            gameId: {
                type: Number,
                default: 0
            }
        },
        data() {
            return {};
        },
        computed: {
            ...mapGetters({
                getGame: 'games/getGame'
            }),

            game: function () {
                return this.getGame(this.gameId);
            }
        },
        methods: {}
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .game-details{

        &__img{
            margin-bottom: 6px;
            outline: 1px solid @color-green;
            outline-offset: 2px;
            display: block;
            width: 184px;
            height: 69px;
            transform: translateX(3px);
        }

        &__links{
            display: grid;
            grid-auto-flow: column;
            grid-gap: 6px;
            justify-content: start;
            align-items: baseline;
            margin-bottom: 4px;
        }

        &__id{
            color: @color-gray;
        }

        &__name{
            font-family: @font-condensed;
            color: @color-blue;
            font-size: 18px;
            line-height: 1.2;
            margin-bottom: 10px;
        }
    }

</style>