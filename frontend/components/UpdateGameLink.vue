<template>
    <a
        @click.prevent="updateGame"
        class="update-game-link"
        title="Update game ratings and prices"
    >
        <i :class="['fas', 'fa-fw', 'fa-sync-alt', {'fa-spin': isUpdating}]"></i>
    </a>
</template>

<script>
    import {mapActions} from 'vuex';

    export default {
        name: "UpdateGameLink",
        props: {
            gameId: {
                type: Number,
                default: 0
            }
        },
        data() {
            return {
                isUpdating: false
            };
        },
        methods: {
            ...mapActions({
                updateGameStats: 'games/updateGameStats'
            }),

            updateGame() {
                this.isUpdating = true;

                this.updateGameStats(this.gameId)
                    .catch(e => console.log(e))
                    .finally(() => this.isUpdating = false);
            }
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .update-game-link{
        color: @color-violet;
    }

</style>