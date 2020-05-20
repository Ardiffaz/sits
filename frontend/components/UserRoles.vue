<template>
    <div class="user-roles">
        <div class="user-roles__user-data">
            {{user.profileName}}
            <div class="user-roles__user-id steam-id">{{user.steamId64}}</div>
        </div>
        <div class="user-roles__role">
            <div
                v-if="!user.admin"
                class="blue-label user-roles__label"
            >Admin:</div>
            <activity-mark
                :is-active="user.admin"
                :texts="['Admin', 'No']"
                @click.native="changeRole('admin')"
                class="user-roles__mark"
            />
        </div>
        <div class="user-roles__role">
            <div
                v-if="!user.gifter"
                class="blue-label user-roles__label"
            >Gifter:</div>
            <activity-mark
                :is-active="user.gifter"
                :texts="['Gifter', 'No']"
                @click.native="changeRole('gifter')"
                class="user-roles__mark"
            />
        </div>
        <div class="user-roles__errors">
            <error-box v-if="error">{{error}}</error-box>
        </div>
    </div>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex';
    import ActivityMark from "./ActivityMark";
    import ErrorBox from "./ErrorBox";
    export default {
        name: "UserRoles",
        components: {ErrorBox, ActivityMark},
        props: {
            userId: {
                type: Number,
                default: 0
            }
        },
        data() {
            return {
                error: ''
            };
        },
        computed: {
            ...mapGetters({
                getUser: 'users/getUser'
            }),

            user: function () {
                return this.getUser(this.userId);
            }
        },
        methods: {
            ...mapActions({
                grantRole: 'users/grantUserRole',
                revokeRole: 'users/revokeUserRole'
            }),

            changeRole(role) {
                this.error = '';

                if (this.user[role])
                {
                    this.revokeRole({userId: this.user.id, role})
                        .catch(e => {
                            this.error = e.response.data.error || e.message;
                        });
                }
                else
                {
                    this.grantRole({userId: this.user.id, role})
                        .catch(e => {
                            this.error = e.response.data.error || e.message;
                        });
                }
            }
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .user-roles{
        display: grid;
        grid-auto-flow: column;
        grid-template-columns: 1fr 200px 200px;
        grid-template-areas: 'name role1 role2' 'error error error';
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid @color-green;

        &__user-id{
            color: @color-gray;
        }

        &__role{
            display: grid;
            grid-template-areas: 'label mark';
            grid-template-columns: 50px 150px;
            align-items: center;
            justify-content: end;
        }

        &__label{
            grid-area: label;
        }

        &__mark{
            grid-area: mark
        }

        &__errors{
            grid-area: error;

            &:not(:empty){
                padding-top: 14px;
            }
        }
    }

</style>