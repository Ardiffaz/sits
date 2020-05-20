<template>
    <div
        :class="['game-stats', {'game-stats--one-line': oneLine}]"
    >
        <div class="game-stats__quality">
            <div class="game-stats__quality-marks">
                <span
                    :class="['game-stats__quality-level', {'game-stats__quality-level--m': isQualityM}]"
                    title="Monthly"
                >M</span>
                <span
                    :class="['game-stats__quality-level', {'game-stats__quality-level--q': isQualityQ}]"
                    title="Quality"
                >Q</span>
                <span
                    :class="['game-stats__quality-level', {'game-stats__quality-level--hq': isQualityHQ}]"
                    title="Highest Quality"
                >HQ</span>
            </div>

            <div class="game-stats__rating">
                <span class="game-stats__number">{{+game.rating}}</span>%
            </div>
            <div class="game-stats__review-count">
                <span
                    :title="+game.reviewCount"
                    class="game-stats__number"
                >{{reviewCountString}}</span> reviews
            </div>
        </div>

        <div class="game-stats__features">
            <a
                :href="'https://www.steamcardexchange.net/index.php?gamepage-appid-'+game.id"
                :class="['game-stats__cards', {'game-stats__cards--has': game.hasTradingCards}]"
                target="_blank"
                title="Trading cards"
            >
                <i class="far fa-fw fa-window-maximize fa-flip-vertical"></i>
            </a>
            <a
                :href="'https://steamcommunity.com/stats/'+game.id+'/achievements'"
                :class="['game-stats__achievements', {'game-stats__achievements--has': game.achievementCount}]"
                target="_blank"
                title="Achievements"
            >
                <i class="fas fa-fw fa-trophy"></i>
            </a>
            <div class="game-stats__price-usd" :title="'Exact price: ' + game.priceUsd+'$'">{{Math.ceil(game.priceUsd)}}$</div>
            <div class="game-stats__price-rub" :title="'Exact price: ' + game.priceRub+' ₽'">{{+game.priceRub}} ₽</div>
        </div>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex';

    export default {
        name: "GameStats",
        props: {
            gameId: {
                type: Number,
                default: 0
            },
            oneLine: {
                type: Boolean,
                default: false
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
                if (!this.gameId)
                    return {};

                return this.getGame( this.gameId );
            },

            reviewCountString: function () {
                let count = +this.game.reviewCount;

                if (count < 1000)
                    return count;

                if (count < 1000000)
                    return (count / 1000).toFixed(1) + 'K';

                return (count / 1000000).toFixed(2) + 'M';
            },

            isQualityM: function () {
                return ((this.game.reviewCount >= 30) && (this.game.rating >= 75));
            },

            isQualityQ: function () {
                if ((this.game.reviewCount >= 1000) && (this.game.rating >= 70))
                    return true;

                if ((this.game.reviewCount >= 500) && (this.game.rating >= 75))
                    return true;

                if ((this.game.reviewCount >= 100) && (this.game.rating >= 80))
                    return true;

                return ((this.game.reviewCount >= 50) && (this.game.rating >= 85));
            },

            isQualityHQ: function () {

                if ((this.game.reviewCount >= 1000) && (this.game.rating >= 80))
                    return true;

                if ((this.game.reviewCount >= 500) && (this.game.rating >= 85))
                    return true;

                if ((this.game.reviewCount >= 100) && (this.game.rating >= 90))
                    return true;

                return ((this.game.reviewCount >= 50) && (this.game.rating >= 95));
            }
        },
        methods: {}
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .game-stats{

        &--one-line{
            display: grid;
            grid-auto-flow: column;
            justify-content: start;
            grid-gap: 20px;
            align-items: baseline;
        }

        &__quality, &__features{
            display: grid;
            grid-auto-flow: column;
            justify-content: start;
            align-items: baseline;
            grid-gap: 6px;
            margin: 6px 0;
            line-height: 1.2;
        }

        &__quality{
            font-family: @font-default;
            font-size: 14px;
        }

        &__number{
            font-family: @font-condensed;
            font-size: 20px;
            color: @color-blue;
            letter-spacing: -0.8px;
        }

        &__quality-marks{
            border: 1px solid @color-blue;
            padding: 3px;
        }

        &__quality-level{
            color: fade(@color-gray, 50%);
            font-family: @font-condensed;
            font-weight: bold;
            font-size: 16px;

            &--m{color: @color-green;}
            &--q{color: @color-orange;}
            &--hq{color: @color-violet;}
        }

        &__price-usd{
            color: @color-green;
            font-size: 18px;
        }

        &__achievements{
            color: fade(@color-gray, 50%);

            &--has{
                color: @color-blue;
            }
        }

        &__cards{
            color: fade(@color-gray, 50%);

            &--has{
                color: @color-green;
            }
        }

    }

</style>