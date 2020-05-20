<template>
    <div class="game-choice">
        <input
            v-model="searchString"
            type="text"
            class="simpleinput"
            placeholder="Game name or appID..."
        />
        <game-option
            v-for="(game, key) in foundGames"
            :key="'game_'+game.id"
            :game="game"
            @select-game="selectGame"
        />

        <pagination-box
            v-if="foundGames.length && maxPageNumber > 1"
            :current-page-number="curPageNumber"
            :max-page-number="maxPageNumber"
            @change-page-number="changePageNumber"
        />

        <div
            v-if="notFound && searchString !== ''"
            class="not-found"
        >No games match your request.</div>
    </div>
</template>

<script>
    import {mapActions} from 'vuex';
    import apiGames from "../api/games";
    import GameOption from "./GameOption";
    import PaginationBox from "./PaginationBox";
    export default {
        name: "GameChoiceBox",
        components: {PaginationBox, GameOption},
        props: {},
        data() {
            return {
                searchString: '',
                foundGames: [],
                maxPageNumber: 1,
                curPageNumber: 1,
                notFound: false
            };
        },
        computed: {},
        watch: {
            searchString: function () {
                this.$debounce(() => {
                    this.searchGames(1);
                }, 500)();
            }
        },
        methods: {
            ...mapActions({
                addGameToState: 'games/addGame',
            }),

            searchGames(newPageNumber) {
                if (this.searchString === '')
                    return false;

                this.notFound = false;

                apiGames.findGames(this.searchString, newPageNumber)
                    .then(({data: gamesResponse}) => {

                        if (gamesResponse.games.length === 0)
                            this.notFound = true;

                        this.foundGames = gamesResponse.games;
                        this.maxPageNumber = gamesResponse.maxPageNumber;
                        this.curPageNumber = gamesResponse.curPageNumber;
                    })
                    .catch(e => console.log(e));
            },

            changePageNumber(newPageNumber) {
                this.searchGames(newPageNumber);
            },

            selectGame(gameId) {
                let game = this.foundGames.find(game => game.id === gameId);
                this.addGameToState(game);
                this.foundGames = [];
                this.searchString = '';

                this.$emit('select-game', gameId);
            }
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .game-choice{
        margin-bottom: 8px;
    }

</style>