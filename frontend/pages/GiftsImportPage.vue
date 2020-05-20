<template>
    <div class="container container--simple-page">
        <h1 class="h1">Import Gifts from .xlsx File</h1>

        <form @submit.prevent="loadFile">
            <div class="section">
                <label for="gift_import_file">Select a .xlsx file you want to import gifts from:</label>
            </div>

            <div class="section">
                <input
                    @change="changeFile"
                    type="file"
                    class="simpleinput simpleinput--big simpleinput--file"
                    id="gift_import_file"
                />
            </div>

            <div class="section">
                <error-box
                    v-for="(error, key) in errors"
                    :key="'gift_import_error_'+key"
                >{{error}}</error-box>
                <button type="submit" class="button">Load Data from Selected File</button>
            </div>

            <div
                v-if="parsedGifts.length"
                class="imported-gifts"
            >
                <table class="imported-gifts__table">
                    <tr>
                        <th class="imported-gifts__th-id">ID</th>
                        <th class="imported-gifts__th-game">Game ID</th>
                        <th class="imported-gifts__th-keys">Keys</th>
                        <th class="imported-gifts__th-source-name">Source Name</th>
                        <th class="imported-gifts__th-source-link">Source Link</th>
                        <th class="imported-gifts__th-price">Price</th>
                        <th class="imported-gifts__th-notes">Notes</th>
                        <th class="imported-gifts__th-bundled">Bundled</th>
                    </tr>
                    <tr v-for="(item, key) in parsedGifts">
                        <td class="imported-gifts__td-id">{{item.id}}</td>
                        <td>{{item.game}}</td>
                        <td class="imported-gifts__td-keys">{{item.key}}</td>
                        <td>{{item.sourceName}}</td>
                        <td>{{item.sourceLink}}</td>
                        <td>{{item.price}}</td>
                        <td>{{item.notes}}</td>
                        <td>{{item.bundled}}</td>
                    </tr>
                </table>

                <button
                    @click="createParsedGifts"
                    type="button"
                    class="button"
                >
                    <i class="fas fa-fw fa-mr fa-table"></i>Create Parsed Gifts
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

        </form>
    </div>
</template>

<script>
    import {mapActions, mapGetters} from 'vuex';
    import papa from 'papaparse';
    import ErrorBox from "../components/ErrorBox";
    import GiftCollection from "../components/GiftCollection";
    export default {
        name: "GiftsImportPage",
        components: {ErrorBox, GiftCollection},
        props: {},
        data() {
            return {
                file: '',
                errors: [],
                parsedGifts: [],
            };
        },
        computed: {
            ...mapGetters({
                createdGiftCollections: 'gifts/getGiftCollections'
            })
        },
        methods: {
            ...mapActions({
                saveGifts: 'gifts/saveGifts'
            }),

            changeFile: function ($event) {
                if ($event.target.files && $event.target.files[0])
                    this.file = $event.target.files[0];
            },
            loadFile: function () {
                this.errors = [];

                if (!this.file)
                {
                    this.errors.push('No file selected');
                    return;
                }

                papa.parse(this.file, {
                    header: true,
                    skipEmptyLines: true,
                    complete: (results) => {

                        if (results.errors.length)
                        {
                            this.errors = this.errors.concat(results.errors);
                            return;
                        }

                        let neededFields = ['Game Link', 'Keys', 'Source Name', 'Source Link', 'Price', 'Notes', 'Bundled'];

                        neededFields.forEach(fieldName => {
                            if (!results.meta.fields.includes(fieldName))
                                this.errors.push(`Column ${fieldName} is not found in the file.`);
                        });

                        if (this.errors.length)
                            return;

                        let newGifts = [];

                        results.data.forEach((item, index) => {
                            let gameId = parseInt(item['Game Link'].match(/app\/(\d+)/)[1]);

                            if (!gameId)
                            {
                                this.errors.push('Can\'t find game id in line #'.index);
                                return true;
                            }

                            let newId = this.$store.getters.getUniqueId;
                            this.$store.dispatch('updateUniqueId');

                            let notes = item.Notes;

                            if (item.Bundled === '*')
                                notes += ' #Bundled';
                            else if (item.Bundled === '**')
                                notes += ' #noCV';

                            newGifts.push({
                                key: item.Keys,
                                sourceName: item['Source Name'],
                                sourceLink: item['Source Link'],
                                price: item.Price,
                                notes: notes,
                                game: gameId,
                                id: newId,
                                bundled: item.Bundled
                            });
                        });

                        this.parsedGifts = [...newGifts];
                    }
                });
            },

            createParsedGifts: function () {
                this.errors = [];

                this.saveGifts(this.parsedGifts)
                    .then(({errors}) => {
                        this.errors = errors;
                        let errorGiftKeys = Object.keys(errors);
                        let giftsWithErrors = [];

                        this.parsedGifts.forEach(gift => {
                            if (errorGiftKeys.includes(gift.id))
                                giftsWithErrors.push(gift);
                        });

                        this.parsedGifts = [...giftsWithErrors];

                    })
                    .catch(e => console.log(e));
            }

        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .imported-gifts{

        &__table{
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 16px;

            th{
                border: 1px solid @color-gray;
                padding: 8px;
                background: fade(@color-blue, 10%);
            }

            td{
                border: 1px solid @color-gray;
                vertical-align: top;
                padding: 8px;
            }
        }

        &__th-id{
            width: 70px;
            padding: 8px 4px;
        }

        &__th-game{
            width: 100px;
        }

        &__th-keys{
            width: 150px;
        }

        &__th-source-link{
            width: 25%;
        }

        &__th-price{
            width: 60px;
        }

        &__th-notes{
            width: 25%;
        }

        &__td-id{
            font-size: 12px;
            color: @color-gray;
        }

        &__td-keys{
            white-space: pre-line;
            font-family: @font-monospace;
            font-size: 14px;
        }

    }

</style>