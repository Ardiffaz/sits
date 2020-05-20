<template>
    <main class="container container--simple-page">
        <h1 class="h1">Group List</h1>

        <loading-indicator v-if="isLoading" :add-margin="true" />

        <div class="group-list">
            <group-box
                v-for="(group, index) in groups"
                :key="'gr_'+index"
                :group-id="group.id"
                :last-updated="getLastUpdatedByUserList(group.members)"
            />
        </div>

    </main>
</template>

<script>
    import {mapGetters, mapActions, mapState} from 'vuex';
    import AddGroupForm from '../components/AddGroupForm.vue';
    import GroupBox from "../components/GroupBox";
    import LoadingIndicator from "../components/LoadingIndicator";
    export default {
        name: "GroupsPage",
        components: {AddGroupForm, GroupBox, LoadingIndicator },
        data() {
            return {
                isLoading: false
            };
        },
        computed: {
            ...mapState({
                isAdmin: state => state.auth.isAdmin
            }),

            ...mapGetters({
                groups: 'groups/getGroupList',
                getLastUpdatedByUserList: 'users/getLastUpdated'
            })
        },
        methods: {
            ...mapActions({
                loadGroups: 'groups/loadGroupsWithMembers'
            })
        },
        created() {
            this.isLoading = true;
            this.loadGroups().finally(() => this.isLoading = false);
        }

    }
</script>

<style lang="less">
    @import "../assets/vars";
    @import "../assets/blocks/group-list";

</style>