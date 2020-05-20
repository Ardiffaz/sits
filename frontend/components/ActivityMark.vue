<template>
    <div
        :class="['activity-mark', {'activity-mark--false': !isActive}, {'activity-mark--disabled': isDisabled}]"
    >
        <div class="activity-mark__switch"></div>
        <div class="activity-mark__text">
            {{isActive ? texts[0] : texts[1]}}
        </div>
    </div>
</template>

<script>
    export default {
        name: "ActivityMark",
        props: {
            isActive: {
                type: Boolean,
                default: true
            },
            isDisabled: {
                type: Boolean,
                default: false
            },
            texts: {
                type: Array,
                default: () => ['Active', 'Not active']
            }
        },
        data() {
            return {};
        },
        methods: {}
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .activity-mark{
        --activity-color: @color-green;

        color: var(--activity-color);
        display: flex;
        width: 130px;
        transition: color 0.3s;
        cursor: pointer;

        &--false{
            --activity-color: @color-red;
        }

        &--disabled{
            cursor: default;
            opacity: 0.7;
        }

        &__switch{
            width: 42px;
            height: 24px;
            border: 3px solid var(--activity-color);
            border-radius: 12px;
            padding: 3px;
            margin-right: 4px;
            flex-shrink: 0;
            transform: translateX(0);
            transition: border-color 0.3s;

            &:after{
                content: '';
                display: block;
                width: 12px;
                height: 12px;
                background-color: var(--activity-color);
                border-radius: 50%;
                transition: background-color 0.3s, transform 0.3s;
            }
        }
        
        &--false &__switch:after{
            transform: translateX(150%);
        }

        &:not(&--disabled):hover &__switch{
            border-color: @color-yellow;

            &:after{
                background-color: @color-yellow;
            }
        }
    }
</style>