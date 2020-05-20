<template>
    <div class="giveaway">

        <template v-if="!isEditingGa">
            <div>
                <a
                    :href="giveaway.link"
                    class="giveaway__link"
                    target="_blank"
                >{{giveaway.link}}</a>
            </div>
            <div class="">
                <i class="far fa-fw fa-mr fa-calendar-check"></i>{{$getDate(giveaway.dateEnded)}}
            </div>
            <div class="">
                <div
                    v-if="giveaway.successful"
                    class="giveaway__success"
                >
                    <i class="far fa-fw fa-mr fa-check-circle"></i>Finished
                </div>
                <span
                    v-else
                    @click.prevent="markFinished"
                    class="link-control"
                >
            <i class="far fa-fw fa-mr fa-circle"></i>Mark Finished
        </span>
            </div>
            <div class="giveaway__block">
                <div class="blue-label">Creator:</div>
                {{user.profileName}}
            </div>
            <div class="giveaway__block giveaway__notes">
                <div class="blue-label">Notes:</div>
                <div>
                    <div class="giveaway__id">id {{giveawayId}}</div>
                    {{giveaway.notes}}
                </div>
            </div>
        </template>

        <giveaway-editable
            v-if="isEditingGa"
            :giveaway-id="giveawayId"
            @update-ga="updatedEditedGiveaway"
            class="giveaway__edit-block"
        />

        <div class="giveaway__buttons">
            <template v-if="!isEditingGa">
                <span
                    @click.prevent="startEditing"
                    class="link-control"
                >
                    <i class="fas fa-fw fa-mr fa-pencil-alt"></i>Edit
                </span>
                <span
                    @click.prevent="remove"
                    class="link-control link-control--warning"
                >
                    <i class="fas fa-fw fa-mr fa-times"></i>Remove
                </span>
            </template>
            <template v-if="isEditingGa">
                <span
                    @click.prevent="saveEdited"
                    class="link-control link-control--green"
                >
                    <i class="far fa-fw fa-mr fa-check-circle"></i>Save
                </span>
                <span
                    @click.prevent="endEditing"
                    class="link-control link-control--warning"
                >
                    <i class="far fa-fw fa-mr fa-times-circle"></i>Cancel
                </span>
            </template>
        </div>

        <div
            v-if="gaErrors.length"
            class="giveaway__errors"
        >
            <error-box
                v-for="(error, i) in gaErrors"
                :key="'ga_edit_'+giveawayId+'_e_'+i"
            >{{error}}</error-box>
        </div>

        <div class="giveaway__gifts">
            <div class="giveaway__header">
                <span class="giveaway__header-text">Gifts ({{giveaway.gifts.length}}):</span>
                <span
                    v-if="!isEditingGifts"
                    @click.prevent="startEditingGifts"
                    class="link-control"
                ><i class="fas fa-fw fa-mr fa-pencil-alt"></i>Edit gifts</span>
                <span
                    v-if="isEditingGifts"
                    @click.prevent="saveEditedGifts"
                    class="link-control"
                ><i class="fas fa-fw fa-mr fa-check-circle"></i>Save</span>
                <span
                    v-if="isEditingGifts"
                    @click.prevent="endEditingGifts"
                    class="link-control link-control--warning"
                ><i class="fas fa-fw fa-mr fa-times-circle"></i>Cancel</span>
            </div>
            <div
                v-show="isEditingGa"
                class="giveaway__hint"
            >Remove a check to exclude a gift from this giveaway. Unchecked gifts will be returned to the free gifts pool.</div>
            <template
                v-for="giftId in giveaway.gifts"

            >
                <gift-box
                    v-if="!isEditingGifts"
                    :gift-id="giftId"
                    :key="'ga_gift_'+giftId"
                    :show-check="isEditingGa"
                    :checked="isGiftChecked(giftId)"
                    @check-gift="checkGift(giftId, $event)"
                />

                <gift-editable
                    v-if="isEditingGifts"
                    :key="'ga_gift_'+giftId"
                    :gift-id="giftId"
                    @gift-edit="updateEditedGift"
                />

                <error-box
                    v-if="giftErrors[giftId]"
                >{{giftErrors[giftId]}}</error-box>

            </template>

        </div>
    </div>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex';
    import GiftBox from "./GiftBox";
    import GiveawayEditable from "./GiveawayEditable";
    import ErrorBox from "./ErrorBox";
    import GiftEditable from "./GiftEditable";
    export default {
        name: "GiveawayBox",
        components: {GiftEditable, ErrorBox, GiveawayEditable, GiftBox},
        props: {
            giveawayId: {
                type: Number,
                default: 0
            }
        },
        data() {
            return {
                isEditingGa: false,
                isEditingGifts: false,
                checkedGifts: [],
                editedGiveaway: {},
                editedGifts: {},
                gaErrors: [],
                giftErrors: {}
            };
        },
        computed: {
            ...mapGetters({
                getGiveaway: 'giveaways/getGiveaway',
                getUser: 'users/getUser'
            }),

            giveaway: function () {
                return this.getGiveaway(this.giveawayId);
            },

            user: function () {
                return this.getUser(this.giveaway.user);
            }
        },
        methods: {
            ...mapActions({
                finishGiveaway: 'giveaways/markGiveawayFinished',
                removeGiveaway: 'giveaways/removeGiveaway',
                saveGiveaways: 'giveaways/saveGiveaways',
                saveGifts: 'gifts/saveGifts'
            }),

            markFinished() {
                this.finishGiveaway( this.giveaway.id )
                    .catch(e => console.log(e));
            },

            remove() {
                let confirmation = confirm('Remove this giveaway?\n\n' + this.giveaway.link);

                if (!confirmation)
                    return;

                this.removeGiveaway(this.giveaway.id)
                    .catch(e => console.log(e));
            },

            isGiftChecked: function(giftId) {
                return this.checkedGifts.includes(giftId);
            },

            checkGift: function (giftId, checkValue) {
                if (checkValue)
                    this.checkedGifts.push(giftId);
                else
                    this.checkedGifts.splice( this.checkedGifts.indexOf(giftId) , 1);
            },

            startEditing() {
                this.isEditingGa = true;
                this.editedGifts = {};
            },

            endEditing() {
                this.isEditingGa = false;
            },

            updatedEditedGiveaway(giveaway) {
                this.editedGiveaway = giveaway;
            },

            validateFields: function () {
                if (!this.editedGiveaway.user)
                    this.gaErrors.push('The giveaway must have a user selected.');

                if (!this.editedGiveaway.dateEnded)
                    this.gaErrors.push('The giveaway must have an ending date.');

                if (!this.editedGiveaway.gifts.length)
                    this.gaErrors.push('The giveaway must have at least one checked gift.');

                return !this.gaErrors.length;
            },

            saveEdited() {
                this.clearAllErrors();

                this.editedGiveaway.gifts = [...this.checkedGifts];
                let validated = this.validateFields();

                if (!validated)
                    return false;

                this.saveGiveaways([this.editedGiveaway])
                    .then(({errors}) => {
                        this.gaErrors = errors;

                        if (!errors.length)
                            this.endEditing();
                    })
                    .catch(e => console.log(e));
            },

            clearAllErrors: function () {
                this.giftErrors = {};
                this.gaErrors = [];
            },

            startEditingGifts() {
                this.isEditingGifts = true;
            },

            endEditingGifts() {
                this.isEditingGifts = false;
            },

            updateEditedGift: function (gift) {
                this.$set(this.editedGifts, gift.id, gift);
            },

            saveEditedGifts() {
                this.clearAllErrors();

                this.saveGifts(this.editedGifts)
                    .then(({errors}) => {
                        this.giftErrors = errors;

                        if (!Object.keys(errors).length)
                            this.endEditingGifts();
                    })
                    .catch(e => console.log(e));
            }
        },
        created() {
            this.checkedGifts = [...this.giveaway.gifts];
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .giveaway{
        display: grid;
        grid-template-columns: 1fr 172px 130px 100px;
        grid-template-areas: 'link date status buttons' 'user notes notes buttons' 'gifts gifts gifts gifts';
        grid-template-rows: minmax(min-content, max-content);
        grid-gap: 6px 10px;
        border: 4px solid fade(@color-blue, 30%);
        padding: 6px 8px;
        margin-bottom: 10px;

        &__buttons{
            grid-area: buttons;
            display: grid;
            grid-auto-flow: row;
            justify-content: end;
            align-content: start;
        }

        &__block{

        }

        &__link{
            color: @color-blue;
            font-family: @font-condensed;
        }

        &__success{
            color: @color-green;
        }

        &__notes{
            grid-column-end: span 2;
        }

        &__edit-block{
            grid-column-end: span 3;
        }

        &__header{
            margin-bottom: 3px;
            white-space: nowrap;
        }

        &__header-text{
            font-family: @font-condensed;
            color: @color-blue;
            margin-right: 10px;
        }

        &__gifts{
            grid-area: gifts;
            border-top: 1px solid fade(@color-blue, 30%);
            padding-top: 6px;
            align-items: baseline;
            grid-gap: 0 10px;
        }

        &__hint{
            color: @color-gray;
            font-size: 12px;
            justify-self: end;
        }

        &__gift-row{

        }

        &__errors{

        }

        &__id{
            color: @color-gray;
            font-size: 12px;
            display: inline-block;
        }
    }

</style>