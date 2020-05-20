<template>
    <div class="gift-editable">
        <div class="gift-editable__block">
            <label
                :for="'edit_gift_s_key'+giftId"
                class="blue-label"
            >{{isNew ? 'Key(s):' : 'Key:'}}</label>
            <component
                :is="isNew ? 'textarea' : 'input'"
                v-model="gift.key"
                :value="gift.key"
                @input="gift.key = $event.target.value"
                :id="'edit_gift_s_key'+giftId"
                :class="['simpleinput', 'gift-editable__block-line', {'simpleinput--textarea': isNew}]"
                :placeholder="isNew ? 'Key(s) content' : 'Key content'"
                type="text"
            />
            <div
                v-if="isNew"
                class="gift-editable__keys-count"
            >{{foundKeysCount}} keys found.</div>
        </div>

        <div class="gift-editable__block">
            <div class="blue-label">Source:</div>
            <div class="gift-editable__lineform">
                <label
                    :for="'edit_gift_s_name'+giftId"
                    class="blue-label"
                >Name:</label>
                <input
                    v-model="gift.sourceName"
                    :id="'edit_gift_s_name'+giftId"
                    type="text"
                    class="simpleinput"
                    placeholder="Source name"
                />
            </div>
            <div class="gift-editable__lineform">
                <label
                    :for="'edit_gift_s_link'+giftId"
                    class="blue-label"
                >Link:</label>
                <input
                    v-model="gift.sourceLink"
                    :id="'edit_gift_s_link'+giftId"
                    type="text"
                    class="simpleinput"
                    placeholder="Source link"
                />
            </div>
        </div>

        <div class="gift-editable__block">
            <label
                :for="'edit_gift_price_'+giftId"
                class="blue-label"
            >Price:</label>
            <input
                v-model="gift.price"
                :id="'edit_gift_price_'+giftId"
                type="text"
                class="simpleinput gift-editable__block-line"
                placeholder="Price"
            />
        </div>

        <div class="gift-editable__block">
            <label
                :for="'edit_gift_notes'+giftId"
                class="blue-label"
            >Notes:</label>
            <div class="gift-editable__id">id {{giftId}}</div>
            <textarea
                v-model="gift.notes"
                :id="'edit_gift_notes'+giftId"
                class="simpleinput simpleinput--textarea gift-editable__block-line"
                placeholder="Additional notes"
            ></textarea>
            <div
                v-if="isNew"
                class="gift-editable__block-line gift-editable__block-line--right"
            >
                <span
                    @click.prevent="remove"
                    class="link-control link-control--warning link-control--smaller"
                >
                    <i class="fas fa-fw fa-times"></i>Remove
                </span>
            </div>

        </div>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex';
    export default {
        name: "GiftEditable",
        props: {
            giftId: {
                type: [Number, String],
                default: 0
            }
        },
        data() {
            return {
                gift: {
                    key: '',
                    sourceName: '',
                    sourceLink: '',
                    price: '',
                    notes: ''
                },
                isNew: false,
                foundKeysCount: 0
            };
        },
        computed: {
            ...mapGetters({
                getGift: 'gifts/getGift'
            })
        },
        methods: {
            remove: function () {
                this.$emit('remove-new-gift', this.giftId);
            }
        },
        watch: {
            gift: {
                handler: function () {
                    this.$emit('gift-edit', this.gift);
                },
                deep: true
            },
            'gift.key': function () {
                if (!this.isNew)
                    return;

                let keys = this.gift.key.split(/\r|\r\n|\n/);
                keys = keys.filter( key => !(!key || (key.trim() === '')) );

                this.foundKeysCount = keys.length;
            }
        },
        created() {
            if (Number.isInteger(this.giftId))
            {
                let originalGift = this.getGift(this.giftId);

                this.gift = {
                    id: originalGift.id,
                    key: originalGift.key,
                    sourceName: originalGift.sourceName,
                    sourceLink: originalGift.sourceLink,
                    price: originalGift.price,
                    notes: originalGift.notes.trim()
                };
            }
            else
            {
                this.$set(this.gift, 'id', this.giftId);
                this.isNew = true;
            }
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .gift-editable{
        display: grid;
        grid-auto-flow: column;
        grid-template-columns: 1fr 1fr 70px 0.85fr;
        grid-gap: 8px;
        align-items: baseline;
        margin-bottom: 6px;
        padding: 4px 6px 8px;

        &:hover{
            background: fade(@color-blue, 7%);
        }

        &__block{
            display: grid;
            grid-auto-flow: row;
            grid-template-areas: 'label id' 'input input';
            grid-template-columns: min-content 1fr;
            align-items: baseline;
            grid-gap: 6px;
        }

        &__block-line{
            grid-column-start: 1;
            grid-column-end: span 2;
            display: flex;

            &--right{
                justify-self: end;
            }
        }

        &__lineform{
            display: grid;
            grid-gap: 6px;
            grid-auto-flow: column;
            grid-template-columns: 30px 1fr;
            align-items: center;
            grid-column: 1 / -1;
        }

        &__id{
            grid-area: id;
            color: @color-gray;
            font-size: 12px;
            line-height: 0.8;
        }

        &__keys-count{
            color: @color-gray;
            font-size: 12px;
            font-style: italic;
            grid-column: 1 / -1;
        }
    }

</style>