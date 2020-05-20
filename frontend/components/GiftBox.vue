<template>
    <div
        :class="['gift', {'gift--show-check': showCheck}, {'gift--checked': checked}, {'gift--free': !gift.reservedBy}]"
    >
        <div
            v-if="showCheck"
            class="gift__block gift__block--check"
        >
            <input
                v-model="checked"
                :id="'check_gift_'+gift.id"
                type="checkbox"
                class="checkbox"
                @click="check"
            />
            <label
                :for="'check_gift_'+gift.id"
            ></label>
        </div>
        <div class="gift__block gift__block--key">
            <div class="blue-label gift__label">Key:</div>
            <div class="gift__key">{{gift.key}}</div>
        </div>
        <div class="gift__block gift__block--source">
            <div class="blue-label gift__label">Source:</div>
            <div class="gift__source">
                <a
                    v-if="gift.sourceLink"
                    :href="gift.sourceLink"
                    target="_blank"
                >{{gift.sourceName}}</a>
                <div v-else>{{gift.sourceName}}</div>
            </div>
        </div>
        <div class="gift__block gift__block--price">
            <div class="blue-label gift__label">Price:</div>
            <div class="gift__price">{{gift.price}} ₽</div>
        </div>
        <div class="gift__block gift__block--notes">
            <div class="blue-label gift__label">Notes:</div>
            <div class="gift__notes">
                <div class="gift__id">id {{gift.id}}</div>
                {{gift.notes}}
            </div>
        </div>
        <div
            v-if="gift.reservedBy"
            class="gift__block gift__block--reserved"
        >
            <div class="blue-label">Reserved:</div>
            <div>{{userReservedBy.profileName}}</div>
        </div>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex';
    export default {
        name: "GiftBox",
        props: {
            giftId: {
                type: Number,
                default: 0
            },
            checked: {
                type: Boolean,
                default: false
            },
            showCheck: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {};
        },
        computed: {
            ...mapGetters({
                getGift: 'gifts/getGift',
                getUser: 'users/getUser'
            }),

            gift: function () {
                return this.getGift(this.giftId);
            },

            userReservedBy: function () {
                return this.gift.reservedBy ? this.getUser(this.gift.reservedBy) : null;
            }
        },
        methods: {
            check: function () {
                this.$emit('check-gift', !this.checked);
            }
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .gift{
        display: grid;
        grid-auto-flow: column;
        grid-template-columns: 1fr 20% 100px 16% 18%;
        grid-gap: 6px;
        grid-template-rows: minmax(min-content, max-content);
        align-items: baseline;
        margin-bottom: 6px;
        padding: 4px 6px 4px 0;
        min-height: 0;

        &--show-check{
            grid-template-columns: 22px 1fr 20% 100px 16% 18%;
        }

        &--checked{
            background: fade(@color-blue, 7%);
        }

        &__block{
            align-items: baseline;
            padding-left: 6px;

            &--check{
                transform: translateY(1px);
                display: block;
            }
        }

        &__label{
            display: inline-block;
            margin-right: 2px;
            white-space: nowrap;
        }

        &__key{
            display: inline-block;
            font-family: @font-monospace;
            line-height: 1.2;
            background: fade(@color-gray, 30%);
            padding: 2px 6px 3px;
        }

        &__source, &__price{
            display: inline-block;
        }

        &__notes{
            line-height: 1.2;
            display: inline-block;
        }

        &__id{
            color: @color-gray;
            font-size: 12px;
            display: inline-block;
        }

        &--free &__block--notes{
            grid-column-end: span 2;
        }
    }

</style>