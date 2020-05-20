<template>
    <div class="sync">
        <h2 class="h3">Sync Steam Applications</h2>
        <div class="section">
            Upload new app IDs and update app titles.
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
                <div class="sync__result-line">Created {{createdCount}} apps</div>
            </div>
        </div>

        <error-box v-if="error">{{error}}</error-box>
    </div>
</template>

<script>
    import apiSync from '../api/sync';
    import ErrorBox from "./ErrorBox";
    import LoadingIndicator from "./LoadingIndicator";

    export default {
        name: "SyncSteamApps",
        components: {
            ErrorBox,
            LoadingIndicator
        },
        props: {},
        data() {
            return {
                isLoading: false,
                error: '',
                isResult: false,
                updatedCount: 0,
                createdCount: 0
            };
        },
        methods: {
            startSyncing() {
                this.isLoading = true;
                this.error = '';

                apiSync.syncGames()
                    .then(({data: data}) => {
                        this.updatedCount = data.result.updated;
                        this.createdCount = data.result.created;
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