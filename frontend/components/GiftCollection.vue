<template>
    <div class="gift-collection">
        <div class="gift-collection__game">
            <template v-if="isEditMode">
                <div class="blue-label">Game:</div>
                <game-choice-box
                    @select-game="changeGame"
                />
            </template>

            <game-details
                v-if="(isEditMode && editedGameId) || (!isEditMode && gameId)"
                :game-id="isEditMode ? editedGameId : gameId"
            />

            <div v-if="groupId">
                <a
                    :href="sgSearchLink"
                    target="_blank"
                >Search SG group giveaways</a>
                <a
                    :href="groupSearchLink"
                    target="_blank"
                >Check group stats</a>
            </div>

            <span
                v-if="isNew"
                @click.prevent="removeCollection"
                class="link-control link-control--warning link-control--smaller"
            >
                <i class="fas fa-fw fa-times"></i>Remove this game collection
            </span>

        </div>
        <div class="gift-collection__rows">
            <div class="gift-collection__giveaways">
                <giveaway-box
                    v-for="giveawayId in giveaways"
                    :key="'ga_box_'+giveawayId"
                    :giveaway-id="giveawayId"
                />
            </div>
            <div
                v-if="isCreateGaMode"
                class="gift-collection__new-giveaway"
            >
                <div class="blue-label">Giveaway Template:</div>
                <div class="template">{{templateForGa}}</div>
                <giveaway-editable
                    :external-values="externalGiveawayFieldValues"
                    @update-ga="updateGa"
                />
                <div class="gift-collection__errors" v-if="gaErrors.length">
                    <error-box
                        v-for="(error, i) in gaErrors"
                        :key="'ga_e_'+i"
                    >{{error}}</error-box>
                </div>
            </div>
            <div
                v-if="gifts.length"
                class="gift-collection__buttons"
            >
                <a
                    v-if="gifts.length > 1"
                    @click.prevent="toggleCheckAll"
                    class="link-control gift-collection__btn-left"
                >
                    <i class="fas fa-fw fa-mr fa-check-double"></i>{{allChecked ? 'Uncheck All' : 'Check All'}}
                </a>
                <a
                    v-if="!isEditMode && !isCreateGaMode"
                    @click.prevent="startEditing"
                    class="link-control gift-collection__btn"
                >
                    <i class="fas fa-fw fa-mr fa-pencil-alt"></i>Edit
                </a>
                <template v-if="isEditMode">
                    <a
                        @click.prevent="saveEditedGifts"
                        class="link-control link-control--green gift-collection__btn"
                    >
                        <i class="fas fa-fw fa-mr fa-check-circle"></i>Save
                    </a>
                    <a
                        @click.prevent="endEditing"
                        class="link-control link-control--warning gift-collection__btn"
                    >
                        <i class="fas fa-fw fa-mr fa-times-circle"></i>Cancel
                    </a>
                </template>
                <a
                    v-if="!isEditMode && !isCreateGaMode"
                    @click.prevent="startCreatingGa(true)"
                    class="link-control gift-collection__btn"
                >
                    <i class="fas fa-fw fa-mr fa-angle-double-right"></i>Create GA
                </a>
                <a
                    v-if="!isEditMode && !isCreateGaMode"
                    class="link-control gift-collection__btn"
                    href="https://www.steamgifts.com/giveaways/new"
                    target="_blank"
                >
                    <i class="far fa-fw fa-mr fa-square"></i>on SG
                </a>
                <template v-if="isCreateGaMode">
                    <a
                        @click.prevent="createGa"
                        :class="['link-control', 'gift-collection__btn', {'link-control--disabled': !canCreateGa}, {'link-control--green': canCreateGa}]"
                    >
                        <i class="fas fa-fw fa-mr fa-check-circle"></i>Create GA with {{checksCount}} Checked Gift(s)
                    </a>
                    <a
                        @click.prevent="endCreatingGa"
                        class="link-control link-control--warning gift-collection__btn"
                    >
                        <i class="fas fa-fw fa-mr fa-times-circle"></i>Cancel
                    </a>
                </template>
                <a
                    v-if="!isEditMode && !isCreateGaMode"
                    @click.prevent="markAsReserved(true)"
                    :class="['link-control', 'gift-collection__btn']"
                >
                    <i class="fas fa-fw fa-mr fa-thumbtack"></i>Mark as Reserved
                </a>
                <a
                    v-if="!isEditMode && !isCreateGaMode"
                    @click.prevent="markAsReserved(false)"
                    :class="['link-control', 'gift-collection__btn']"
                >
                    <i class="fas fa-fw fa-mr fa-unlock"></i>Remove Reserved Marks
                </a>
                <a
                    v-if="!isEditMode && !isCreateGaMode"
                    @click.prevent="removeCheckedGifts"
                    :class="['link-control', 'link-control--warning', 'gift-collection__btn', {'link-control--disabled': !hasEnoughChecks}]"
                >
                    <i class="fas fa-fw fa-mr fa-times"></i>Remove
                </a>
            </div>
            <div class="gift-collection__gifts">
                <template v-for="giftId in shownGifts">
                    <gift-box
                        v-if="!isEditMode"
                        :key="'gift_'+giftId"
                        :gift-id="giftId"
                        :show-check="true"
                        :checked="isGiftChecked(giftId)"
                        @check-gift="checkGift(giftId, $event)"
                    />
                    <gift-editable
                        v-if="isEditMode"
                        :key="'gift_edit_'+giftId"
                        :gift-id="giftId"
                        @gift-edit="updateEditedGift"
                        @remove-new-gift="removeNewGift"
                    />
                    <error-box
                        v-if="giftErrors[giftId]"
                    >{{giftErrors[giftId]}}</error-box>
                </template>
                <span
                    v-if="isEditMode"
                    @click.prevent="addNewGift"
                    class="link-control"
                >
                    <i class="fas fa-fw fa-mr fa-plus"></i>Add gift
                </span>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex';
    import GiftBox from "./GiftBox";
    import GameDetails from "./GameDetails";
    import ErrorBox from "./ErrorBox";
    import GiftEditable from "./GiftEditable";
    import GiveawayEditable from "./GiveawayEditable";
    import GiveawayBox from "./GiveawayBox";
    import GameChoiceBox from "./GameChoiceBox";
    export default {
        name: "GiftCollection",
        components: {GameChoiceBox, GiveawayBox, GiveawayEditable, GiftEditable, ErrorBox, GameDetails, GiftBox},
        props: {
            gameId: {
                type: Number,
                default: 0
            },
            gifts: {
                type: Array,
                default: () => []
            },
            giveaways: {
                type: Array,
                default: () => []
            },
            isNew: {
                type: Boolean,
                default: false
            },
            externalGiftErrors: {
                type: [Object, Array],
                default: () => ({})
            },
            startCreatingGaCommand: {
                type: Boolean,
                default: false
            },
            externalGiveawayFieldValues: {
                type: Object,
                default: () => ({})
            },
            startCheckingGaCommand: {
                type: Boolean,
                default: false
            },
            externalGiveawayErrors: {
                type: [Object, Array],
                default: () => ({})
            },
            groupId: {
                type: [Number, String],
                default: 0
            }
        },
        data() {
            return {
                newGifts: [],
                checkedGifts: [],
                giftErrors: {},
                gaErrors: [],
                isEditMode: false,
                editedGifts: {},
                editedGameId: 0,
                isCreateGaMode: false,
                newGiveaway: {}
            };
        },
        computed: {
            ...mapGetters({
                getGift: 'gifts/getGift',
                getGame: 'games/getGame',
                getGroup: 'groups/getGroup'
            }),

            game: function () {
                return this.getGame(this.gameId);
            },

            group: function () {
                return this.getGroup(this.groupId);
            },

            shownGifts: function () {
                return this.isEditMode ? [...this.gifts, ...this.newGifts] : this.gifts;
            },

            sgSearchLink() {
                if (!this.group.sgLink)
                    return '';

                let gameName = encodeURIComponent(this.game.name.toLowerCase());

                return this.group.sgLink + '/search?q=' + gameName;
            },

            groupSearchLink() {
                return '/groups/'+this.groupId+'/checker?q='+this.gameId;
            },

            hasEnoughChecks: function () {
                return ((this.checkedGifts.length > 0) || (this.gifts.length === 1));
            },

            canCreateGa: function () {
                if (this.checkedGifts.length > 0)
                    return true;

                return (this.gifts.length === 1);
            },

            checksCount: function () {
                return this.checkedGifts.length;
            },

            allChecked: function () {
                return this.gifts.length === this.checksCount;
            },

            templateForGa: function () {
                if (!this.hasEnoughChecks)
                    return 'Check at least one gift';

                let template = '';
                let gameTemplate = 'https://store.steampowered.com/app/' + this.gameId + '/';
                let first = true;

                let giftIdsForTemplate = this.checkedGifts.length ? [...this.checkedGifts] : this.gifts.length ? [...this.gifts] : [];

                giftIdsForTemplate.forEach(giftId => {
                    let gift = this.getGift(giftId);

                    if (!first)
                        template += '\n';

                    template += gameTemplate + ' ' + gift.key;
                    first = false;
                });

                return template;
            }
        },
        watch: {
            externalGiftErrors: function () {
                this.giftErrors = Object.assign({}, this.giftErrors, this.externalGiftErrors);
            },

            checkedGifts: {
                handler: function () {
                    let template = this.checkedGifts.length > 0 ? this.templateForGa : '';
                    this.$emit('template-update', template);
                },
                immediate: true
            },

            startCreatingGaCommand: function () {
                if (this.startCreatingGaCommand)
                    this.startCreatingGa();
                else
                    this.endCreatingGa();
            },

            startCheckingGaCommand: function () {
                if (!this.startCheckingGaCommand || !this.isCreateGaMode)
                    return;

                this.validateGaFields();
            },

            newGiveaway: function () {
                if (this.isCreateGaMode)
                    this.$emit('giveaway-update', this.newGiveaway);
            },

            externalGiveawayErrors: function () {
                this.clearAllErrors();

                let gaId = this.newGiveaway.id;

                if (this.externalGiveawayErrors[gaId])
                    this.gaErrors.push(this.externalGiveawayErrors[gaId])
                else
                    this.endCreatingGa();
            },
        },
        methods: {
            ...mapActions({
                reserveGifts: 'gifts/reserveGifts',
                removeGifts: 'gifts/removeGifts',
                saveGifts: 'gifts/saveGifts',
                saveGiveaways: 'giveaways/saveGiveaways'
            }),

            clearAllErrors: function () {
                this.giftErrors = {};
                this.gaErrors = [];
            },

            toggleCheckAll: function () {
                if (this.allChecked)
                    this.checkedGifts = [];
                else
                    this.checkedGifts = [...this.gifts];
            },

            checkGift: function (giftId, checkValue) {
                if (checkValue)
                    this.checkedGifts.push(giftId);
                else
                    this.checkedGifts.splice( this.checkedGifts.indexOf(giftId) , 1);
            },

            isGiftChecked: function(giftId) {
                return this.checkedGifts.includes(giftId);
            },

            markAsReserved: function (isReserving) {
                this.clearAllErrors();

                let giftsToMark = [];
                if (this.checkedGifts.length)
                    giftsToMark = [...this.checkedGifts];
                else if (this.gifts.length === 1)
                    giftsToMark =  [...this.gifts];

                if (!giftsToMark.length)
                    return;

                this.reserveGifts({giftIds: giftsToMark, isReserving: isReserving})
                    .then(({errors}) => {
                        this.giftErrors = errors;
                    })
                    .catch(e => console.log(e));
            },

            removeCheckedGifts: function () {
                let giftsToMark = [];
                if (this.checkedGifts.length)
                    giftsToMark = [...this.checkedGifts];
                else if (this.gifts.length === 1)
                    giftsToMark =  [...this.gifts];

                if (!giftsToMark.length)
                    return;

                let confirmation = confirm('Remove ' + giftsToMark.length + ' gift(s) in this collection?');

                if (!confirmation)
                    return;

                this.clearAllErrors();

                this.removeGifts(giftsToMark)
                    .then(({errors}) => {
                        this.giftErrors = errors;
                    })
                    .catch(e => console.log(e));
            },

            startEditing: function () {
                this.clearAllErrors();
                this.editedGameId = this.gameId;
                this.isEditMode = true;
                this.editedGifts = {};
            },

            endEditing: function () {
                this.isEditMode = false;
                this.editedGifts = {};
                this.newGifts = [];
            },

            updateEditedGift: function (gift) {
                this.$set(this.editedGifts, gift.id, gift);
                this.$emit('update-gifts', this.editedGifts);
            },

            saveEditedGifts: function () {
                this.clearAllErrors();

                let savedGifts = {...this.editedGifts};

                for (let key in savedGifts)
                {
                    savedGifts[key].game = this.editedGameId;
                }

                this.saveGifts(savedGifts)
                    .then(({errors}) => {
                        this.giftErrors = errors;

                        if (!Object.keys(errors).length)
                            this.endEditing();
                    })
                    .catch(e => console.log(e));
            },

            addNewGift: function () {
                let newId = this.$store.getters.getUniqueId;
                this.$store.dispatch('updateUniqueId');

                this.newGifts.push(newId);
            },

            removeNewGift: function (giftId) {
                this.newGifts = this.newGifts.filter(id => id !== giftId);
                this.$delete(this.editedGifts, giftId);
            },

            startCreatingGa: function (checkSingle = false) {
                this.clearAllErrors();

                if (checkSingle)
                {
                    if ((this.gifts.length === 1) && (!this.checkedGifts.includes(this.gifts[0])))
                        this.checkedGifts.push(this.gifts[0]);
                }

                this.isCreateGaMode = true;
            },

            endCreatingGa: function () {
                this.isCreateGaMode = false;
                this.$emit('giveaway-create-canceled');
            },

            updateGa: function (giveaway) {
                this.newGiveaway = giveaway;
            },

            validateGaFields: function () {
                this.clearAllErrors();

                if (!this.newGiveaway.user)
                    this.gaErrors.push('The giveaway must have a user selected.');

                if (!this.newGiveaway.dateEnded)
                    this.gaErrors.push('The giveaway must have an ending date.');

                this.newGiveaway.gifts = [...this.checkedGifts];

                if (!this.newGiveaway.gifts.length)
                    this.gaErrors.push('Check at least 1 gift.');

                this.$emit('errors-count', this.gaErrors.length);

                return !this.gaErrors.length;
            },

            createGa: function () {
                let validated = this.validateGaFields();

                if (!validated)
                    return false;

                this.saveGiveaways([this.newGiveaway])
                    .then(({errors}) => {
                        this.gaErrors = errors;

                        if (!errors.length)
                            this.endCreatingGa();
                    })
                    .catch(e => console.log(e));
            },

            changeGame: function (gameId) {
                this.editedGameId = gameId;
                this.$emit('set-gift-game', gameId);
            },

            removeCollection: function () {
                this.$emit('remove-collection');
            }
        },
        created() {

            if (this.isNew)
            {
                this.startEditing();
                this.addNewGift();
            }
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .gift-collection{
        border-bottom: 1px solid @color-green;
        padding: 16px 0;
        display: flex;
        align-items: flex-start;

        &__game{
            width: 200px;
            margin-right: 16px;
            flex-shrink: 0;
        }

        &__rows{
            flex-grow: 1;
        }

        &__giveaways{
            margin-bottom: 6px;
        }

        &__new-giveaway{
            margin-bottom: 6px;
        }

        &__errors{
            padding-top: 10px;
        }

        &__buttons{
            padding: 6px 10px;
            background: fade(@color-gray, 15%);
            display: flex;
            justify-content: flex-end;
            margin-bottom: 6px;
        }

        &__btn{
            margin-left: 16px;
        }

        &__btn-left{
            margin-right: auto;
        }

        &__gifts{
            display: grid;
            grid-template-rows: minmax(min-content, max-content);
        }
    }

</style>