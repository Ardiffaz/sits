<template>
    <div class="container container--simple-page">
        <div class="heading">
            <h1 class="heading__title h1">Add New Gifts</h1>
            <div class="heading__right heading__right--gap">
                <router-link :to="{name: 'gifts_import'}" class="button">
                    <i class="far fa-fw fa-mr fa-file-excel"></i>Import from File
                </router-link>
                <button
                    @click.prevent="addNewCollection"
                    type="button"
                    class="button"
                >Add New Game</button>
                <button
                    @click.prevent="createAll"
                    type="button"
                    class="button button--accent"
                >
                    <i class="fas fa-fw fa-mr fa-gifts"></i>Create All Gifts
                </button>
            </div>
        </div>

        <div>
            <gift-collection
                v-for="giftCollectionId in newCollections"
                :key="'g_new_coll_'+giftCollectionId"
                :is-new="true"
                :external-gift-errors="giftErrors"
                @update-gifts="updateGiftsData(giftCollectionId, $event)"
                @set-gift-game="updateGiftsGame(giftCollectionId, $event)"
                @remove-collection="removeCollection(giftCollectionId)"
            />
        </div>

        <div class="buttons-line">
            <button
                @click.prevent="addNewCollection"
                type="button"
                class="button"
            >Add New Game</button>
            <button
                @click.prevent="createAll"
                type="button"
                class="button button--accent"
            >
                <i class="fas fa-fw fa-mr fa-gifts"></i>Create All Gifts
            </button>
        </div>

        <div v-if="Object.keys(createdGiftCollections).length > 0">
            <h3 class="h3">Created Gifts</h3>
            <gift-collection
                v-for="giftCollection in createdGiftCollections"
                :key="'g_coll_'+giftCollection.gameId"
                :game-id="parseInt(giftCollection.gameId)"
                :gifts="giftCollection.gifts"
                :giveaways="giftCollection.giveaways"
            />
        </div>

    </div>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex';
    import GiftCollection from "../components/GiftCollection";
    export default {
        name: "GiftsAddPage",
        components: {GiftCollection},
        props: {},
        data() {
            return {
                newCollections: [],
                giftCollectionsData: {},
                giftCollectionsGame: {},
                giftErrors: {}
            };
        },
        computed: {
            ...mapGetters({
                createdGiftCollections: 'gifts/getGiftCollections'
            })
        },
        methods: {
            ...mapActions({
                clearGifts: 'gifts/clearGiftsState',
                clearGiveaways: 'giveaways/clearGiveawaysState',
                saveGifts: 'gifts/saveGifts'
            }),

            addNewCollection: function () {
                let newId = this.$store.getters.getUniqueId;
                this.$store.dispatch('updateUniqueId');

                this.newCollections.push(newId);
            },

            removeCollection: function (collectionKey) {
                this.newCollections = this.newCollections.filter(id => id !== collectionKey);
                this.$delete(this.giftCollectionsData, collectionKey);

                if (this.newCollections.length === 0)
                    this.addNewCollection();
            },

            updateGiftsData: function (key, gifts) {
                this.$set(this.giftCollectionsData, key, gifts);
            },

            updateGiftsGame: function(key, gameId) {
                this.$set(this.giftCollectionsGame, key, gameId);
            },

            createAll: function () {
                let savedGifts = [];

                for(let key in this.giftCollectionsData)
                {
                    if (!this.giftCollectionsData.hasOwnProperty(key))
                        continue;

                    let collection = this.giftCollectionsData[key];
                    let gameId = this.giftCollectionsGame[key];

                    Object.values(collection).forEach(gift => {
                        gift.game = gameId;
                        savedGifts.push(gift);
                    });
                }

                this.saveGifts(savedGifts)
                    .then(({errors}) => {
                        this.giftErrors = errors;

                        let errorGiftKeys = Object.keys(errors);
                        let currentCollectionKeys = [...this.newCollections];

                        currentCollectionKeys.forEach(collectionKey => {
                            let collectionGiftKeys = Object.keys(this.giftCollectionsData[collectionKey]);
                            let updatedCollectionData = {...this.giftCollectionsData[collectionKey]};

                            collectionGiftKeys.forEach(giftKey => {
                                if (!errorGiftKeys.includes(giftKey))
                                    delete updatedCollectionData[giftKey];
                            });

                            this.$set(this.giftCollectionsData, collectionKey, updatedCollectionData);
                        });

                        Object.keys(this.giftCollectionsData).forEach(collectionKey => {
                            let data = this.giftCollectionsData[collectionKey];
                            if (!Object.keys(data).length)
                                this.removeCollection(collectionKey);
                        });

                    })
                    .catch(e => console.log(e));
            }
        },
        created() {
            this.clearGiveaways();
            this.clearGifts();
            this.addNewCollection();
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

</style>