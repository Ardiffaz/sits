<template>
    <div
        @click="selectGame"
        class="game-option"
    >
        <div>
            <img
                :src="'https://steamcdn-a.akamaihd.net/steam/apps/'+game.id+'/capsule_184x69.jpg'"
                :alt="game.name"
                class="game-option__img"
            />
        </div>
        <a
            :href="'https://store.steampowered.com/app/'+game.id+'/'"
            target="_blank"
            class="game-option__id"
        >{{game.id}}</a>
        <div class="game-option__name">{{game.name}}</div>
    </div>
</template>

<script>
    export default {
        name: "GameOption",
        props: {
            game: {
                type: Object,
                default: () => { return {
                    id: 0,
                    name: ''
                }}
            }
        },
        data() {
            return {};
        },
        methods: {
            selectGame() {
                this.$emit('select-game', this.game.id);
            }
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .game-option{
        display: grid;
        grid-template-areas: 'pic id' 'name name';
        grid-template-columns: min-content 1fr;
        grid-gap: 2px 8px;
        justify-items: start;
        align-items: center;
        font-size: 14px;
        line-height: 1.2;
        padding: 6px 0;
        border-bottom: 1px solid @color-gray;
        cursor: pointer;
        transition: background-color 0.3s, border-color 0.3s;

        &:hover{
            background-color: fade(@color-blue, 10%);
        }

        &__img{
            display: block;
            width: 107px;
            height: 40px;
            font-family: @font-condensed;
            font-size: 10px;
            border: 1px solid fade(@color-green, 60%);
        }
        
        &__id{
            font-family: @font-condensed;
        }
        
        &__name{
            grid-area: name;
            color: @color-blue;
            margin-bottom: 2px;
        }
    }

</style>