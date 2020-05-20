<template>
    <div class="sync-item">
        <div class="sync-item__pic">
            <img
                :src="user.avatar"
                :alt="user.profileName"
                class="sync-item__img"
            />
        </div>
        <div class="sync-item__info">
            <div class="sync-item__name-line">
                <div class="sync-item__name">{{user.profileName}}</div>

                <template v-if="isEditNameMode">
                    <input
                        v-model="editedName"
                        type="text"
                        class="simpleinput sync-item__custom-name-input"
                        placeholder="Custom user name"
                    />
                    <span
                        @click="submitEditName"
                        :class="['link-control', 'link-control--green', {'link-control--disabled': actionsBlocked}]"
                    ><i class="fas fa-fw fa-mr fa-check"></i></span>
                    <span
                        @click="cancelEditNameMode"
                        class="link-control link-control--warning"
                    ><i class="fas fa-fw fa-mr fa-times"></i></span>
                </template>

                <template v-else>
                    <div
                        v-if="user.customName"
                        class="sync-item__custom-name"
                    >{{user.customName}}</div>
                    <a
                        @click.prevent="startEditNameMode"
                        :class="['link-control', {'link-control--disabled': actionsBlocked}]"
                    ><i class="fas fa-fw fa-mr fa-edit"></i></a>
                </template>

            </div>
            <div class="sync-item__row sync-item__data">
                <span class="sync-item__id steam-id">{{user.steamId64}}</span>
                <span
                    v-if="user.isSkipped"
                    class="color-violet"
                    title="There were some errors while updating, data may be inaccurate."
                >
                    <i class="fas fa-fw fa-exclamation-triangle"></i>
                </span>
                <span>
                    <i class="fas fa-fw fa-mr fa-plus-circle color-green"></i>{{+user.ownedCount}}
                </span>
                <span>
                    <i class="fas fa-fw fa-mr fa-heart color-pink"></i>{{+user.wishlistedCount}}
                </span>
                <a
                    :href="user.profileUrl"
                    target="_blank"
                >
                    <i class="fab fa-fw fa-mr fa-steam-square"></i>Steam
                </a>
                <a
                    :href="'http://steamgifts.com/go/user/'+user.steamId64"
                    target="_blank"
                >
                    <i class="far fa-fw fa-mr fa-square"></i>Steamgifts
                </a>
            </div>
            <div class="sync-item__status">
                <span
                    v-for="(group, key) in groups"
                    :key="'user_'+user.id+'_group_'+key"
                    class="sync-item__list-item"
                >{{group.name}}</span>
            </div>
        </div>

        <div
            v-if="isUpdating"
            class="sync-item__loading">
            <loading-indicator :show-content="false" />
        </div>
        <div
            v-if="isUpdated && !isUpdating"
            class="sync-item__success"
        >
            <i class="fas fa-fw fa-check-circle"></i>
        </div>

        <div class="sync-item__controls">
            <div class="sync-item__row">
                <activity-mark
                    :is-active="user.activeInGroups"
                    :is-disabled="actionsBlocked"
                    @click.native="changeActivity"
                />

                <div class="sync-item__col">
                    <span
                        @click="update"
                        :class="['link-control', {'link-control--disabled': actionsBlocked}]"
                    >
                        <i class="fas fa-fw fa-mr fa-sync"></i>Update
                    </span>
                    <span
                        @click="remove"
                        :class="['link-control', 'link-control--warning', {'link-control--disabled': actionsBlocked}]"
                    >
                        <i class="fas fa-fw fa-mr fa-times"></i>Remove
                    </span>
                    <div
                        :title="$getExactDateTime(lastUpdatedTimestamp)"
                        class="sync-item__update-date"
                    >
                        Updated {{$getRelativeDateTime(lastUpdatedTimestamp)}}
                    </div>
                </div>


            </div>

        </div>


    </div>
</template>

<script>
    import {mapGetters} from 'vuex';
    import LoadingIndicator from "./LoadingIndicator";
    import ActivityMark from "./ActivityMark";

    export default {
        name: "SyncUser",
        components: {ActivityMark, LoadingIndicator},
        props: {
            userId: {
                type: Number,
                default: 0
            },
            groups: {
                type: Array,
                default: []
            },
            isUpdating: {
                type: Boolean,
                default: false
            },
            isUpdated: {
                type: Boolean,
                default: false
            },
            nameChanged: {
                type: Boolean,
                default: false
            },
            actionsBlocked: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                isEditNameMode: false,
                editedName: ''
            };
        },
        computed: {
            ...mapGetters({
                getUser: 'users/getUser'
            }),

            user: function () {
                return this.getUser(this.userId);
            },

            lastUpdatedTimestamp: function () {
                return (+Date.parse(this.user.lastUpdated)/1000).toString();
            }
        },
        watch: {
            nameChanged: function (newValue) {
                if (newValue)
                    this.isEditNameMode = false;
            }
        },
        methods: {
            changeActivity() {
                if (this.actionsBlocked)
                    return;

                this.$emit('update-user-activity', {userId: this.userId, active: !this.user.activeInGroups});
            },

            remove() {
                if (this.actionsBlocked)
                    return;

                let confirmed = confirm(`Are you sure you want to remove the user?\n\n${this.user.profileName}`);
                if (!confirmed)
                    return false;

                this.$emit('remove-user', this.userId);
            },

            update() {
                if (this.actionsBlocked)
                    return;

                this.$emit('update-user', this.userId);
            },

            startEditNameMode() {
                if (this.actionsBlocked)
                    return;

                this.editedName = this.user.customName;
                this.isEditNameMode = true;
            },

            cancelEditNameMode() {
                this.isEditNameMode = false;
                this.editedName = this.user.customName;
            },

            submitEditName() {
                if (this.actionsBlocked)
                    return;

                this.$emit('change-custom-name', {userId: this.userId, customName: this.editedName});
            }
        },
        created() {

        }
    }
</script>

<style lang="less">
    @import "../assets/vars";
    @import "../assets/blocks/sync-item";

</style>