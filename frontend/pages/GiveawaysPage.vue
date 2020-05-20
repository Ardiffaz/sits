<template>
    <div class="container container--simple-page">
        <h1 class="h1">Giveaways</h1>

        <giveaways-filter
            v-if="isAllowed"
            :filter.sync="filter"
            @filter-submit="filterSubmit"
            @filter-clear="filterReset"
        />

        <loading-indicator v-if="isLoading" />
        <error-box v-if="!isAllowed">You are not supposed to be here.</error-box>

        <div v-if="isAllowed && !isLoading">

            <div class="list-nav">
                <sort-box
                    :params="sortParams"
                    :cur-param="curSortParam"
                    :cur-dir="curSortDir"
                />

                <div>
                    <span class="blue-label list-nav__label">Total:</span> {{totalItemsCount}} giveaways<br />
                    <span class="blue-label list-nav__label">On this page:</span> {{totalUSDValue}} $
                </div>

                <pagination-box
                    v-if="maxPageNumber > 1"
                    :current-page-number="curPageNumber"
                    :max-page-number="maxPageNumber"
                    :use-route="true"
                />
            </div>

            <div
                v-if="!Object.values(giveawayCollections).length"
                class="not-found"
            >No giveaways matching your request were found.</div>

            <gift-collection
                v-for="giveawayCollection in giveawayCollections"
                :key="'ga_coll_'+giveawayCollection.giveaways[0]"
                :game-id="parseInt(giveawayCollection.gameId)"
                :gifts="giveawayCollection.gifts"
                :giveaways="giveawayCollection.giveaways"
            />

            <pagination-box
                v-if="maxPageNumber > 1"
                :current-page-number="curPageNumber"
                :max-page-number="maxPageNumber"
                :use-route="true"
            />

            <div class="buttons-line">
                <button
                    v-if="!isShowingCombinedTemplate"
                    @click="showCombinedTemplate"
                    type="button"
                    class="button"
                >
                    <i class="fas fa-fw fa-mr fa-tasks"></i>Show Combined Template
                </button>
                <button
                    v-if="isShowingCombinedTemplate"
                    @click="hideCombinedTemplate"
                    type="button"
                    class="button"
                >
                    <i class="fas fa-fw fa-mr fa-times"></i>Hide Combined Template
                </button>

                <button
                    v-if="!isShowingCombinedGiveawayLinks"
                    @click="showCombinedGiveawayLinks"
                    type="button"
                    class="button"
                >
                    <i class="fas fa-fw fa-mr fa-tasks"></i>Show All Links Together
                </button>
                <button
                    v-if="isShowingCombinedGiveawayLinks"
                    @click="hideCombinedGiveawayLinks"
                    type="button"
                    class="button"
                >
                    <i class="fas fa-fw fa-mr fa-times"></i>Hide Links
                </button>
            </div>

            <div class="template" v-show="isShowingCombinedTemplate">{{combinedTemplate}}</div>

            <div class="template" v-show="isShowingCombinedGiveawayLinks">{{combinedGiveawayLinks}}</div>
        </div>
    </div>
</template>

<script>
    import {mapState, mapGetters, mapActions} from 'vuex';
    import LoadingIndicator from "../components/LoadingIndicator";
    import GiftCollection from "../components/GiftCollection";
    import GiveawaysFilter from "../components/GiveawaysFilter";
    import PaginationBox from "../components/PaginationBox";
    import SortBox from "../components/SortBox";
    import ErrorBox from "../components/ErrorBox";
    export default {
        name: "GiveawaysPage",
        components: {ErrorBox, GiftCollection, GiveawaysFilter, LoadingIndicator, PaginationBox, SortBox},
        props: {},
        data() {
            return {
                isLoading: false,
                filter: {
                    link: '',
                    creator: '',
                    finished: null,
                    game: '',
                    key: '',
                    notes: ''
                },
                maxPageNumber: 1,
                curPageNumber: 1,
                newPageNumber: 1,
                totalItemsCount: 0,
                sortParams: {date: 'Ending Date', id: 'Creation Time'},
                curSortParam: '',
                curSortDir: '',
                isShowingCombinedTemplate: false,
                isShowingCombinedGiveawayLinks: false
            };
        },
        computed: {
            ...mapState({
                giveaways: state => state.giveaways.giveaways,
                isAdmin: state => state.auth.isAdmin,
                isGifter: state => state.auth.isGifter
            }),

            ...mapGetters({
                giveawayCollections: 'giveaways/getGiveawayCollections',
                getGiveaway: 'giveaways/getGiveaway',
                getGift: 'gifts/getGift',
                getGame: 'games/getGame'
            }),

            isAllowed: function () {
                return this.isAdmin || this.isGifter;
            },

            combinedTemplate: function () {
                let template = '';
                let isFirst = true;

                Object.values(this.giveawayCollections).forEach(giveawayCollection => {
                    let gameId = giveawayCollection.gameId;
                    let gameTemplate = 'https://store.steampowered.com/app/' + gameId + '/';

                    giveawayCollection.giveaways.forEach(giveawayId => {
                        let giveaway = this.getGiveaway(giveawayId);

                        giveaway.gifts.forEach(giftId => {
                            let gift = this.getGift(giftId);

                            if (!isFirst)
                                template += '\n';

                            template += gameTemplate + ' ' + gift.key;
                            isFirst = false;

                        });

                    });
                });
                return template;
            },

            totalUSDValue: function () {
                let value = 0;

                Object.values(this.giveawayCollections).forEach(giveawayCollection => {
                    let gameId = giveawayCollection.gameId;
                    let game = this.getGame(gameId);

                    giveawayCollection.giveaways.forEach(giveawayId => {
                        let giveaway = this.getGiveaway(giveawayId);

                        value += Math.ceil(game.priceUsd) * giveaway.gifts.length;

                    });
                });

                return value;
            },

            combinedGiveawayLinks: function () {
                let template = '';
                let isFirst = true;

                Object.values(this.giveawayCollections).forEach(giveawayCollection => {

                    giveawayCollection.giveaways.forEach(giveawayId => {
                        let giveaway = this.getGiveaway(giveawayId);

                        if (!isFirst)
                            template += '\n';

                        template += giveaway.link;
                        isFirst = false;
                    });
                });
                return template;
            }
        },
        watch: {
            $route: function () {
                this.clearFilter();
                this.loadFilterFromQuery();
                this.callLoadGiveaways();
            },
        },
        methods: {
            ...mapActions({
                loadGifters: 'users/loadGifters',
                loadGiveaways: 'giveaways/loadGiveaways'
            }),

            loadFilterFromQuery: function () {
                Object.keys(this.filter).forEach(paramKey => {

                    if (this.$route.query[paramKey])
                        this.$set(this.filter, paramKey, this.$route.query[paramKey]);
                });

                this.newPageNumber = this.$route.query.page;
                this.curSortParam = this.$route.query.sort;
                this.curSortDir = this.$route.query.dir;
            },

            clearFilter: function () {
                this.filter = {
                    link: '',
                    creator: '',
                    finished: null,
                    game: '',
                    key: '',
                    notes: ''
                };
            },

            filterSubmit: function () {
                this.reloadGiveaways();
            },

            filterReset: function () {
                this.clearFilter();
                this.reloadGiveaways();
            },

            getCleanFilter: function () {
                let cleanFilter = {};
                for (let propName in this.filter) {
                    if (!this.filter.hasOwnProperty(propName))
                        continue;

                    if (this.filter[propName] !== '' && this.filter[propName] !== null && this.filter[propName] !== undefined) {
                        cleanFilter[propName] = this.filter[propName];
                    }
                }

                return cleanFilter;
            },

            reloadGiveaways: function (firstRun = false) {
                let query = this.getCleanFilter();

                query.sort = this.curSortParam;
                query.dir = this.curSortDir;

                if (firstRun)
                {
                    this.callLoadGiveaways(true);
                }
                else
                {
                    this.$router.push({
                        name: 'giveaways',
                        query: query
                    }, this.callLoadGiveaways, this.callLoadGiveaways);
                }
            },

            callLoadGiveaways: function (firstRun = false) {
                let cleanFilter = this.getCleanFilter();
                this.isLoading = true;

                let promises = [
                    this.loadGiveaways({filter: cleanFilter, pageNumber: this.newPageNumber, sortParam: this.curSortParam, sortDir: this.curSortDir})
                ];

                if (firstRun)
                    promises.push(this.loadGifters());

                Promise.all(promises)
                    .then(([giveawaysResult, giftersResult]) => {
                        this.maxPageNumber = giveawaysResult.maxPageNumber;
                        this.curPageNumber = giveawaysResult.curPageNumber;
                        this.totalItemsCount = giveawaysResult.totalItemsCount;
                    })
                    .finally(() => this.isLoading = false)
            },

            showCombinedTemplate: function () {
                this.isShowingCombinedTemplate = true;
            },

            hideCombinedTemplate: function () {
                this.isShowingCombinedTemplate = false;
            },

            showCombinedGiveawayLinks: function () {
                this.isShowingCombinedGiveawayLinks = true;
            },

            hideCombinedGiveawayLinks: function () {
                this.isShowingCombinedGiveawayLinks = false;
            }
        },
        created() {
            this.loadFilterFromQuery();
            this.reloadGiveaways(true);
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

</style>