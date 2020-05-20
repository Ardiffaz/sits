<template>
    <div class="sync-item">
        <div class="sync-item__pic">
            <img
                :src="group.avatar"
                :alt="group.name"
                class="sync-item__img"
            />
        </div>
        <div class="sync-item__info">
            <div class="sync-item__name-line">
                <div class="sync-item__name">{{group.name}}</div>
            </div>
            <div class="sync-item__row sync-item__data">
                <span class="sync-item__id steam-id">{{group.steamId}}</span>
                <router-link :to="{name: 'sync_users', query: {group: group.id}}">
                    <i class="fas fa-fw fa-mr fa-users"></i>{{group.memberCount}} members
                </router-link>
                <a
                    :href="group.url"
                    target="_blank"
                >
                    <i class="fab fa-fw fa-mr fa-steam-square"></i>Steam
                </a>
                <a
                    :href="'http://steamgifts.com/go/group/'+group.steamId"
                    target="_blank"
                >
                    <i class="far fa-fw fa-mr fa-square"></i>Steamgifts
                </a>
            </div>
            <div class="sync-item__row sync-item__data">
                <div class="blue-label">Steamgifts Real Link:</div>
                <a
                    v-if="group.sgLink && !isEditingSgLink"
                    :href="group.sgLink"
                    target="_blank"
                >{{group.sgLink}}</a>
                <span
                    @click="startEditingSgLink"
                    :class="['link-control', {'link-control--disabled': actionsBlocked}]"
                ><i class="fas fa-fw fa-mr fa-edit"></i></span>
                <template v-if="isEditingSgLink">
                    <input
                        v-model="editedSgLink"
                        type="text"
                        class="simpleinput sync-item__long-input"
                        placeholder="SteamGifts Link for the group"
                    />
                    <span
                        @click="submitSgLink"
                        class="link-control link-control--green"
                    ><i class="fas fa-fw fa-mr fa-check"></i></span>
                    <span
                        @click="endEditingSgLink"
                        class="link-control link-control--warning"
                    ><i class="fas fa-fw fa-mr fa-times"></i></span>
                </template>
            </div>
            <div
                v-if="isUpdatingInfo"
                class="sync-item__status"
            >Updating group info</div>
            <div
                v-if="isUpdatingUsers"
                class="sync-item__status"
            >Updating users: {{updatingUserIndex}} of {{group.memberCount}}</div>
        </div>

        <div
            v-if="isUpdating"
            class="sync-item__loading">
            <loading-indicator :show-content="false" />
        </div>
        <div
            v-if="isUpdated"
            class="sync-item__success"
        >
            <i class="fas fa-fw fa-check-circle"></i>
        </div>

        <div class="sync-item__controls">
            <div class="sync-item__row">
                <a
                    @click.prevent="updateGroup"
                    :class="['button', {'button--disabled': actionsBlocked}]"
                >
                    <i class="fas fa-fw fa-mr fa-sync-alt"></i>Update
                </a>
                <a
                    @click.prevent="removeGroup"
                    :class="['link-control', 'link-control--warning', {'link-control--disabled': actionsBlocked}]"
                >
                    <i class="fas fa-fw fa-mr fa-times"></i>Remove
                </a>
            </div>
            <div
                :title="$getExactDateTime(lastUpdated)"
                class="sync-item__update-date"
            >
                Updated {{$getRelativeDateTime(lastUpdated)}}
            </div>
        </div>
    </div>
</template>

<script>
    import {mapActions, mapGetters} from 'vuex';
    import LoadingIndicator from "./LoadingIndicator";
    export default {
        name: "SyncGroup",
        components: {LoadingIndicator},
        props: {
            groupId: {
                type: [Number, String],
                default: 0
            },
            lastUpdated: {
                type: String,
                default: ''
            },
            isUpdating: {
                type: Boolean,
                default: false
            },
            isUpdated: {
                type: Boolean,
                default: false
            },
            actionsBlocked: {
                type: Boolean,
                default: false
            },
            updatingUserIndex: {
                type: Number,
                default: -1
            }
        },
        data() {
            return {
                isEditingSgLink: false,
                editedSgLink: ''
            };
        },
        computed: {
            ...mapGetters({
                getGroup: 'groups/getGroup',
            }),

            group: function () {
                return this.getGroup(this.groupId);
            },

            isUpdatingInfo: function () {
                return this.isUpdating && (this.updatingUserIndex === -1);
            },

            isUpdatingUsers: function () {
                return this.isUpdating && (this.updatingUserIndex !== -1);
            }

        },
        methods: {
            ...mapActions({
                updageGroupSgLink: 'groups/updageGroupSgLink'
            }),

            updateGroup() {
                if (!this.actionsBlocked)
                    this.$emit('update-group', this.groupId);
            },

            removeGroup() {
                if (!this.actionsBlocked)
                    this.$emit('remove-group', this.groupId);
            },

            startEditingSgLink() {
                if (this.actionsBlocked)
                    return;

                this.editedSgLink = this.group.sgLink;
                this.isEditingSgLink = true;
            },

            endEditingSgLink() {
                this.isEditingSgLink = false;
            },

            submitSgLink() {
                if (this.actionsBlocked)
                    return;

                this.updageGroupSgLink({groupId: this.groupId, sgLink: this.editedSgLink})
                    .finally(() => this.endEditingSgLink());
            }

        }
    }
</script>

<style lang="less">
    @import "../assets/vars";
    @import "../assets/blocks/sync-item";

</style>