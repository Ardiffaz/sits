<template>
    <div class="fetched-group">
        <div class="fetched-group__img-block">
            <img
                :src="group.avatarMedium"
                :alt="group.groupName"
                class="fetched-group__img"
            />
            <div class="fetched-group__member-count" title="Member count">
                <i class="fas fa-fw fa-mr fa-users"></i>{{formattedMemberCount}}
            </div>
        </div>
        <div class="fetched-group__info-block">
            <div class="fetched-group__id steam-id">{{group.groupId}}</div>
            <div class="fetched-group__name">{{group.groupName}}</div>

            <div class="fetched-group__links">
                <a
                    :href="group.groupUrl"
                    class="fetched-group__link"
                    target="_blank"
                >{{group.groupUrl}}</a>
            </div>

            <div
                v-if="isAdded"
                class="fetched-group__added"
            >
                <i class="fas fa-fw fa-mr fa-check"></i>Added
            </div>

            <loading-indicator v-else-if="group.isLoading" />

            <div
                v-else-if="group.error"
                class="fetched-group__notice"
            >{{group.error}}</div>

            <button
                v-else-if="group.memberCount <= maxUsersLimit"
                @click="addGroup(group.groupId)"
                type="button"
                class="button"
            >
                <i class="fas fa-fw fa-mr fa-plus-square"></i>Add this group
            </button>
            
            <div
                v-else
                class="fetched-group__notice"
            >Cannot add groups with more than {{maxUsersLimit}} members</div>
        </div>
    </div>
</template>

<script>
    import LoadingIndicator from "./LoadingIndicator";

    export default {
        name: "FetchedGroup",
        components: {
            LoadingIndicator
        },
        props: {
            group: {
                type: Object,
                default: {
                    avatarFull: '',
                    avatarIcon: '',
                    avatarMedium: '',
                    groupName: '',
                    groupUrl: '',
                    groupId: '',
                    headLine: '',
                    memberCount: 0,
                    memberIds: [],
                    summary: '',
                    isLoading: false
                }
            },
            isAdded: {
                type: Boolean,
                default: false
            },
            maxUsersLimit: {
                type: Number,
                default: 0
            }
        },
        data() {
            return {};
        },
        computed: {
            formattedMemberCount() {
                let memberCount = this.group.memberCount;

                let ranges = [
                    { divider: 1e6 , suffix: 'M' },
                    { divider: 1e3 , suffix: 'k' }
                ];

                for (let i = 0; i < ranges.length; i++) {
                    if (memberCount >= ranges[i].divider) {
                        return (memberCount / ranges[i].divider).toFixed(1) + ranges[i].suffix;
                    }
                }
                return memberCount.toString();
            }
        },
        methods: {
            addGroup(groupId) {
                this.$emit('add-group', groupId);
            }
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .fetched-group{
        display: flex;
        padding: 16px;
        border: 1px solid transparent;
        transition: border-color 0.3s;

        &:hover{
            border-color: @color-blue;
        }

        &__img-block{
            flex-shrink: 0;
            flex-grow: 0;
            width: 64px;
            margin-right: 10px;
            padding-top: 3px;
        }

        &__img{
            width: 64px;
            height: 64px;
            margin-bottom: 12px;
            display: block;
            font-size: 12px;
            line-height: 1;
        }

        &__info-block{
            flex-grow: 1;
        }

        &__id{
            color: @color-gray;
            font-size: 12px;
        }

        &__name{
            color: @color-blue;
        }

        &__member-count{
            font-size: 12px;
            text-align: center;
        }

        &__links{
            margin-bottom: 14px;
            font-size: 12px;
        }
        
        &__notice{
            font-size: 12px;
            color: @color-pink;
            font-style: italic;
        }

        &__added{
            font-size: 14px;
            color: @color-green;
            font-weight: bold;
        }
    }

</style>