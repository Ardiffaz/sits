<!--suppress HtmlUnknownAttribute -->
<template>
    <div
        v-click-outside="closeTooltip"
        class="user-tooltip"
    >
        <transition name="slide-fade">
            <div
                v-if="show"
                class="user-tooltip__content"
            >
                <user-box
                    :user-id="user.id"
                />
            </div>
        </transition>
    </div>
</template>

<script>
    import UserBox from "./UserBox";
    import clickOutsideDirective from "../mixins/clickOutside";

    export default {
        name: "UserTooltip",
        components: {
            UserBox
        },
        mixins: [clickOutsideDirective],
        props: {},
        data() {
            return {
                user: {},
                show: false,
            };
        },
        methods: {
            closeTooltip () {
                this.show = false;
            }
        },
        created() {
            this.$root.$on('set-user-tooltip', user => {
                this.user = user;
                this.show = true;
            });
        },
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .user-tooltip{
        position: fixed;
        top: 57px;
        right: 20px;
        width: 300px;

        &__content{
            background: @color-bg;
        }
    }
</style>