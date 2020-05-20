<template>
    <div :class="['loading-indicator', {'loading-indicator--margin': addMargin}]">
        <div
            v-if="showSpinner"
            :class="['loading-indicator__spinner', {'loading-indicator__spinner--nomargin': !showContent}]"
        ></div>
        <div
            v-if="showContent"
            class="loading-indicator__content"
        >
            <slot>loading...</slot>
        </div>
    </div>
</template>

<script>
    export default {
        name: "LoadingIndicator",
        props: {
            showSpinner: {
                type: Boolean,
                default: true
            },
            showContent: {
                type: Boolean,
                default: true
            },
            addMargin: {
                type: Boolean,
                default: false
            }
        },
    }
</script>

<style lang="less">
    @import "../assets/vars";

    @keyframes loading-animation {
        0%, 100% {
            animation-timing-function: cubic-bezier(0.5, 0, 1, 0.5);
        }
        0% {
            transform: rotateY(0deg) scale(1);
            opacity: 1;
        }
        50% {
            transform: rotateY(540deg) scale(0.3);
            animation-timing-function: cubic-bezier(0, 0.5, 0.5, 1);
            opacity: 0.2;
        }
        100% {
            transform: rotateY(1080deg) scale(1);
            opacity: 1;
        }
    }

    .loading-indicator{
        display: flex;
        align-items: center;

        &--margin{
            margin-bottom: 20px;
        }

        &__spinner{
            width: 34px;
            height: 34px;
            background: url(/logo.svg);
            background-size: contain;
            animation: loading-animation 1.5s infinite;
            margin-right: 12px;
            flex-shrink: 0;

            &--nomargin{
                margin-right: 0;
            }
        }

        &__content{
            flex-grow: 1;
            font-style: italic;
            font-size: 14px;
            color: @color-green;
        }
    }

</style>