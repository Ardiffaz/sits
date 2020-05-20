<template>
    <div class="errorbox">
        <i class="fa fa-fw fa-bug errorbox__icon"></i>
        <slot>An unknown error has occured.</slot>
    </div>
</template>

<script>
    export default {
        name: "ErrorBox"
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .errorbox{
        border: 2px solid @color-red;
        margin-bottom: 10px;
        padding: 15px 20px;
        font-size: 14px;
        font-style: italic;
        font-weight: 500;
        color: @color-red;
        white-space: pre-line;

        &__icon{
            margin-right: 6px;
        }
    }

</style>