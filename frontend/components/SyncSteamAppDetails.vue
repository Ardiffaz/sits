<template>
    <div class="sync">
        <h2 class="h3">Sync Steam Application Details</h2>
        <div class="section">
            Sync information about prices, ratings, reviews and so on.<br />
            The most long ago updated items go first.<br />
            Max 1000 items.
        </div>

        <div class="sync__options section">
            <div class="sync__option">
                <input
                    v-model="updateType"
                    type="radio"
                    name="update_type"
                    id="update_type_all"
                    value="all"
                    class="radiobox"
                />
                <label for="update_type_all">Update all games</label>
            </div>
            <div class="sync__option">
                <input
                    v-model="updateType"
                    type="radio"
                    name="update_type"
                    id="update_type_gifts"
                    value="gifts"
                    class="radiobox"
                />
                <label for="update_type_gifts">Update games related to existed [not given away yet] gifts</label>
            </div>
            <div class="sync__option">
                <input
                    v-model="updateType"
                    type="radio"
                    name="update_type"
                    id="update_type_backlog"
                    value="backlog"
                    class="radiobox"
                    disabled
                />
                <label for="update_type_backlog">Update games related to existed backlog items</label>
            </div>
        </div>

        <h2 class="h3">Sync Steam Applications From External Source</h2>
        <div class="section">
            Source: https://data.world/insideone/steam-search/ <br />
            No game count limit.
        </div>

        <div class="sync__options section">
            <div class="sync__option">
                <input
                    v-model="updateType"
                    type="radio"
                    name="update_type"
                    id="update_type_dw"
                    value="dw"
                    class="radiobox"
                />
                <label for="update_type_dw">Update all games</label>
            </div>
        </div>

        <div class="section">
            <button
                v-if="!isLoading"
                @click="startSyncing"
                class="button"
            >Start</button>
        </div>

        <loading-indicator v-if="isLoading" :add-margin="true">
            updating...
        </loading-indicator>

        <div class="section sync__success" v-if="isResult">
            <i class="fas fa-fw fa-2x fa-mr fa-check-circle"></i>
            <div class="sync__success-text">
                <div class="sync__result-line">Updated {{updatedCount}} apps</div>
                <div
                    v-if="lastUpdatedGame"
                    class="sync__result-line"
                >Last updated game: {{lastUpdatedGame.id}} - {{lastUpdatedGame.name}}</div>
            </div>
        </div>

        <error-box v-if="error">{{error}}</error-box>
    </div>
</template>

<script>
    import apiSync from '../api/sync';
    import LoadingIndicator from "./LoadingIndicator";
    import ErrorBox from "./ErrorBox";

    export default {
        name: "SyncSteamAppDetails",
        components: {ErrorBox, LoadingIndicator},
        props: {},
        data() {
            return {
                isLoading: false,
                updateType: 'all',
                error: '',
                isResult: false,
                updatedCount: 0,
                lastUpdatedGame: {}
            };
        },
        methods: {
            startSyncing() {
                this.isLoading = true;
                this.error = '';

                apiSync.syncAppDetails(this.updateType)
                    .then(({data: data}) => {
                        this.updatedCount = data.count;
                        this.lastUpdatedGame = data.game;
                        this.isResult = true;
                    })
                    .catch(e => {
                        this.error = e.response.data.error || e.message;
                    })
                    .finally(() => this.isLoading = false);
            }
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";
    @import "../assets/blocks/sync";

</style>