<template>
    <div class="container container--simple-page">
        <h1 class="h1">Checking Group Wishlists/Ownership</h1>

        <loading-indicator v-if="isLoading" />

        <template v-else>
            <div class="section">
                <group-box
                    :group-id="groupId"
                    :last-updated="getLastUpdatedByUserList(group.members)"
                />
            </div>

            <h2 class="h2">Games</h2>

            <div class="game-checker">
                <form
                    @submit.prevent="updateQuery"
                    class="game-checker__search"
                >
                    <input
                        v-model="searchParam"
                        type="text"
                        name="q"
                        class="simpleinput simpleinput--big game-checker__search-field"
                        placeholder="Game title or IDs..."
                    />
                    <button type="submit" class="button">Search</button>
                </form>

                <div class="game-checker__results">

                    <pagination-box
                        v-if="searchMaxPageNumber > 1"
                        :current-page-number="searchCurPageNumber"
                        :max-page-number="searchMaxPageNumber"
                        :use-route="true"
                    />

                    <checked-game
                        v-for="(game, key) in games"
                        :key="'checker_'+game.id"
                        :game-id="game.id"
                        :group-members="membersMap"
                        :group-id="groupId"
                    />

                    <pagination-box
                        v-if="searchMaxPageNumber > 1"
                        :current-page-number="searchCurPageNumber"
                        :max-page-number="searchMaxPageNumber"
                        :use-route="true"
                    />

                </div>
            </div>

        </template>

        <user-tooltip />

    </div>
</template>

<script>
    import {mapActions, mapGetters, mapState} from 'vuex';
    import LoadingIndicator from "../components/LoadingIndicator";
    import GroupBox from "../components/GroupBox";
    import CheckedGame from "../components/CheckedGame";
    import UserTooltip from "../components/UserTooltip";
    import PaginationBox from "../components/PaginationBox";

    export default {
        name: "GameCheckerPage",
        components: {
            PaginationBox,
            UserTooltip,
            CheckedGame,
            GroupBox,
            LoadingIndicator
        },
        props: {},
        data() {
            return {
                isLoading: false,
                searchParam: '',
                searchNewPageNumber: 1,
                searchMaxPageNumber: 1,
                searchCurPageNumber: 1
            };
        },
        computed: {
            ...mapGetters({
                getGroup: 'groups/getGroup',
                games: 'games/getGames',
                getUsers: 'users/getUsers',
                getLastUpdatedByUserList: 'users/getLastUpdated'
            }),

            ...mapState({
                users: state => state.users.users
            }),

            groupId: function () {
                return this.$route.params.groupId;
            },

            group: function () {
                return this.getGroup( this.groupId );
            },

            membersMap: function () {
                let members = this.getUsers( this.group.members );

                let {map} = this.$normalizeData(members);
                return map;
            }
        },
        watch: {
            $route: function () {
                this.searchParam = this.$route.query.q || '';
                this.searchNewPageNumber = parseInt(this.$route.query.page) || 1;
                this.reloadGames();
            },
            searchParam: function () {
                this.searchNewPageNumber = 1;
            }
        },
        methods: {
            ...mapActions({
                loadGameChecker: 'groups/loadGameChecker'
            }),

            updateQuery() {
                let currentQ = this.$route.query.q || '';
                let currentPage = parseInt(this.$route.query.page) || 1;

                let queryData = {};

                if (this.searchParam)
                    queryData.q = this.searchParam;

                if (this.searchNewPageNumber !== 1)
                    queryData.page = this.searchNewPageNumber;

                if (currentQ !== queryData.q || currentPage !== queryData.page)
                {
                    this.$router.push({
                        name: 'game_checker',
                        params: {
                            groupId: this.groupId
                        },
                        query: queryData
                    });
                }
            },

            reloadGames () {
                this.loadGameChecker({groupId: this.groupId, searchParam: this.searchParam, pageNumber: this.searchNewPageNumber})
                    .then(({maxPageNumber, curPageNumber}) => {
                        this.searchMaxPageNumber = maxPageNumber;
                        this.searchCurPageNumber = curPageNumber;
                    })
                    .catch(e => console.log(e))
                    .finally(() => this.isLoading = false);
            }
        },
        created() {
            this.searchParam = this.$route.query.q || '';
            this.searchNewPageNumber = parseInt(this.$route.query.page) || 1;
            this.isLoading = true;
            this.reloadGames();
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .game-checker{

        &__search{
            display: flex;
            margin-bottom: 20px;
        }

        &__search-field{
            margin-right: 10px;
            flex-grow: 1;
        }
    }

</style>