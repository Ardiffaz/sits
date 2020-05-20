<template>
    <div class="container container--simple-page">
        <div class="heading">
            <h1 class="heading__title h1">Gifts</h1>

            <div class="heading__right heading__right--gap">
                <router-link
                    v-if="isAllowed"
                    :to="{name: 'gifts_add'}"
                    class="button"
                >
                    <i class="fas fa-fw fa-mr fa-plus-square"></i>Add new gifts
                </router-link>
            </div>
        </div>

        <gifts-filter
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

                <div class="list-nav__center">
                    <label for="g_gr" class="blue-label list-nav__label">Selected Group:</label>
                    <select
                        v-model="selectedGroupId"
                        id="g_gr"
                        class="simpleinput"
                    >
                        <option value="">---</option>
                        <option
                            v-for="group in groups"
                            :value="group.id"
                        >{{group.name}}</option>
                    </select>
                </div>

                <pagination-box
                    v-if="maxPageNumber > 1"
                    :current-page-number="curPageNumber"
                    :max-page-number="maxPageNumber"
                    :use-route="true"
                />
            </div>

            <div
                v-if="!Object.values(giftCollections).length"
                class="not-found"
            >No gifts matching your request were found.</div>

            <gift-collection
                v-for="giftCollection in giftCollections"
                :key="'g_coll_'+giftCollection.gameId"
                :game-id="parseInt(giftCollection.gameId)"
                :gifts="giftCollection.gifts"
                :giveaways="giftCollection.giveaways"
                :start-creating-ga-command="startCreatingGaCommand"
                :external-giveaway-field-values="commonGaFields"
                :start-checking-ga-command="startCheckingCommand"
                :external-giveaway-errors="giveawayCreationErrors"
                :group-id="selectedGroupId"
                @template-update="updateGaTemplate(giftCollection.gameId, $event)"
                @giveaway-update="updateGiveawayData(giftCollection.gameId, $event)"
                @giveaway-create-canceled="clearGiveawayData(giftCollection.gameId)"
                @errors-count="updateErrorsCount"
            />

            <pagination-box
                v-if="maxPageNumber > 1"
                :current-page-number="curPageNumber"
                :max-page-number="maxPageNumber"
                :use-route="true"
            />

            <div class="buttons-line">
                <button
                    v-if="!isCreatingAll"
                    @click="startCreatingAll"
                    type="button"
                    class="button"
                >
                    <i class="fas fa-fw fa-mr fa-angle-double-right"></i>Create All GAs
                </button>
                <button
                    v-if="isCreatingAll"
                    @click="endCreatingAll"
                    type="button"
                    class="button"
                >
                    <i class="fas fa-fw fa-mr fa-times"></i>Cancel Creating All GAs
                </button>
                <router-link :to="{name: 'gifts_add'}" class="button">
                    <i class="fas fa-fw fa-mr fa-plus-square"></i>Add new gifts
                </router-link>
            </div>

            <multiple-ga-creation
                v-if="isCreatingAll"
                :combined-template="combinedTemplate"
                :common-fields.sync="commonGaFields"
                :error-count="giveawayErrorsCount"
                :is-save-button-disabled="isSaveButtonDisabled"
                @save-all="checkGaDataBeforeSaving"
            />

        </div>

    </div>
</template>

<script>
    import {mapState, mapGetters, mapActions} from 'vuex';
    import LoadingIndicator from "../components/LoadingIndicator";
    import GiftCollection from "../components/GiftCollection";
    import GiftsFilter from "../components/GiftsFilter";
    import PaginationBox from "../components/PaginationBox";
    import SortBox from "../components/SortBox";
    import MultipleGaCreation from "../components/MultipleGaCreation";
    import ErrorBox from "../components/ErrorBox";
    export default {
        name: "GiftsPage",
        components: {ErrorBox, GiftCollection, GiftsFilter, LoadingIndicator, MultipleGaCreation, PaginationBox, SortBox},
        props: {},
        data() {
            return {
                isLoading: false,
                filter: {
                    key: '',
                    source: '',
                    lts: null,
                    notes: '',
                    exnotes: '',
                    game: '',
                    reserved: '',
                    price_from: '',
                    price_to: '',
                    quality: '',
                    cards: null,
                    achievements: null
                },
                maxPageNumber: 1,
                curPageNumber: 1,
                newPageNumber: 1,
                sortParams: {price: 'Game Price', rating: 'Game Rating', count: 'Keys count'},
                curSortParam: '',
                curSortDir: '',

                selectedGroupId: '',

                isCreatingAll: false,
                gaTemplates: {},
                commonGaFields: {
                    link: '',
                    user: '',
                    dateEnded: '',
                    successful: false,
                    notes: ''
                },
                startCreatingGaCommand: false,
                startCheckingCommand: false,

                giveawayData: {},
                giveawayErrorsCount: 0,
                isSaveButtonDisabled: false,
                giveawayCreationErrors: {}
            };
        },
        computed: {
            ...mapState({
                loggedUserId: state => state.auth.loggedUserId,
                isAdmin: state => state.auth.isAdmin,
                isGifter: state => state.auth.isGifter
            }),

            ...mapGetters({
                giftCollections: 'gifts/getGiftCollections',
                groups: 'groups/getAllSortedGroups',
                getGroup: 'groups/getGroup'
            }),

            isAllowed: function () {
                return this.isAdmin || this.isGifter;
            },

            combinedTemplate: function () {
                let template = '';
                let isFirst = true;

                Object.values(this.giftCollections).forEach(giftCollection => {
                    let gameId = giftCollection.gameId;
                    let curTemplate = this.gaTemplates[gameId];

                    if (curTemplate)
                    {
                        if (!isFirst)
                            template += '\n';

                        template += curTemplate;
                        isFirst = false;
                    }
                });
                return template;
            }
        },
        watch: {
            $route: function () {
                this.clearFilter();
                this.loadFilterFromQuery();
                this.callLoadGifts();
            },
            selectedGroupId: function () {
                this.$cookie.set('selected_group', this.selectedGroupId, { expires: '3M' });
            }
        },
        methods: {
            ...mapActions({
                loadGifts: 'gifts/loadGifts',
                loadGifters: 'users/loadGifters',
                loadGroups: 'groups/loadGroups',
                saveGiveaways: 'giveaways/saveGiveaways'
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
                    key: '',
                    source: '',
                    notes: '',
                    exnotes: '',
                    game: '',
                    reserved: '',
                    price_from: '',
                    price_to: '',
                    quality: '',
                    cards: null,
                    achievements: null
                };
            },

            filterSubmit: function () {
                this.reloadGifts();
            },

            filterReset: function () {
                this.clearFilter();
                this.reloadGifts();
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

            reloadGifts: function (firstRun = false) {
                this.isCreatingAll = false;
                let query = this.getCleanFilter();

                query.sort = this.curSortParam;
                query.dir = this.curSortDir;

                if (firstRun)
                {
                    this.callLoadGifts(true);
                }
                else
                {
                    this.$router.push({
                        name: 'gifts',
                        query: query
                    }, this.callLoadGifts, this.callLoadGifts);
                }
            },

            callLoadGifts: function (firstRun = false) {
                let cleanFilter = this.getCleanFilter();

                this.isLoading = true;

                let promises = [
                    this.loadGifts({filter: cleanFilter, pageNumber: this.newPageNumber, sortParam: this.curSortParam, sortDir: this.curSortDir})
                ];

                if (firstRun)
                {
                    promises.push( this.loadGifters() );
                    promises.push( this.loadGroups() );
                }


                Promise.all(promises)
                    .then(([giftsResult, giftersResult, groupsResult]) => {
                        this.maxPageNumber = giftsResult.maxPageNumber;
                        this.curPageNumber = giftsResult.curPageNumber;
                    })
                    .finally(() => this.isLoading = false)
            },

            startCreatingAll: function () {
                this.giveawayData = {};
                this.giveawayErrorsCount = 0;

                this.startCheckingCommand = false;
                this.commonGaFields = {link: '', user: '', dateEnded: '', successful: false, notes: ''};

                if (this.loggedUserId)
                    this.$set(this.commonGaFields, 'user', this.loggedUserId);

                this.isCreatingAll = true;
                this.startCreatingGaCommand = true;
            },

            endCreatingAll: function () {
                this.isCreatingAll = false;
                this.startCreatingGaCommand = false;
                this.isSaveButtonDisabled = false;
            },

            updateGaTemplate: function (gameId, template) {
                this.$set(this.gaTemplates, gameId, template);
            },

            updateErrorsCount: function(count) {
                this.giveawayErrorsCount += count;
            },

            updateGiveawayData: function(gameId, giveawayData) {
                this.$set(this.giveawayData, gameId, giveawayData);
            },

            clearGiveawayData: function(gameId) {
                this.$delete(this.giveawayData, gameId);
            },

            checkGaDataBeforeSaving: function() {
                if (this.isSaveButtonDisabled)
                    return false;

                this.giveawayErrorsCount = 0;
                this.isSaveButtonDisabled = true;
                this.startCheckingCommand = true;

                setTimeout(() => {
                    if (this.giveawayErrorsCount === 0)
                        this.submitGAs();
                    else
                    {
                        this.startCheckingCommand = false;
                        this.isSaveButtonDisabled = false;
                    }
                }, 500);
            },

            submitGAs: function() {
                this.saveGiveaways(Object.values(this.giveawayData))
                   .then(({errors}) => {
                       this.giveawayCreationErrors = {...errors};
                       this.isSaveButtonDisabled = false;

                       if (!Object.values(errors).length)
                           this.endCreatingAll();
                   })
                   .catch(e => console.log(e));
            }
        },
        created() {
            this.loadFilterFromQuery();
            this.reloadGifts(true);

            let selectedGroupId = this.$cookie.get('selected_group');
            if (selectedGroupId)
                this.selectedGroupId = selectedGroupId;
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

</style>