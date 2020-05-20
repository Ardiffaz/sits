<template>
    <form
        @submit.prevent="submit"
        @reset.prevent="clear"
        class="filter"
    >
        <div class="filter__column">
            <div class="filter__block">
                <label for="gaf_l" class="blue-label">Link:</label>
                <input
                    v-model="filter.link"
                    name="gaf_l"
                    id="gaf_l"
                    type="text"
                    class="simpleinput"
                    placeholder="Giveaway link"
                />
            </div>
            <div class="filter__block">
                <label for="gaf_c" class="blue-label">Creator:</label>
                <select
                    v-model="filter.creator"
                    class="simpleinput"
                    id="gaf_c"
                    name="gaf_c"
                >
                    <option value="">---</option>
                    <option
                        v-for="user in gifters"
                        :value="user.id"
                    >{{user.profileName}}</option>
                </select>
            </div>
            <div class="filter__block">
                <div class="blue-label">Finished:</div>

                <div class="filter__options">
                    <input
                        v-model="filter.finished"
                        value="1"
                        type="radio"
                        class="radiobox"
                        name="gaf_f"
                        id="gaf_f_y"
                    />
                    <label for="gaf_f_y" title="Monthly" class="filter__quality filter__quality--m">Yes</label>

                    <input
                        v-model="filter.finished"
                        value="0"
                        type="radio"
                        class="radiobox"
                        name="gaf_f"
                        id="gaf_f_n"
                    />
                    <label for="gaf_f_n" title="Monthly" class="filter__quality filter__quality--m">No</label>

                    <input
                        v-model="filter.finished"
                        value=""
                        type="radio"
                        class="radiobox"
                        name="gaf_f"
                        id="gaf_f_na"
                    />
                    <label for="gaf_f_na" title="Monthly" class="filter__quality filter__quality--m">---</label>
                </div>
            </div>
        </div>

        <div class="filter__column">
            <div class="filter__block">
                <label for="gaf_g" class="blue-label">Game:</label>
                <input
                    v-model="filter.game"
                    name="gaf_g"
                    id="gaf_g"
                    type="text"
                    class="simpleinput"
                    placeholder="Game name or app ID"
                />
            </div>
            <div class="filter__block">
                <label for="gaf_k" class="blue-label">Key:</label>
                <input
                    v-model="filter.key"
                    name="gaf_k"
                    id="gaf_k"
                    type="text"
                    class="simpleinput"
                    placeholder="Gifted key content"
                />
            </div>
            <div class="filter__block">
                <label for="gaf_n" class="blue-label">Notes:</label>
                <input
                    v-model="filter.notes"
                    name="gaf_n"
                    id="gaf_n"
                    type="text"
                    class="simpleinput"
                    placeholder="Notes"
                />
            </div>
        </div>

        <div class="filter__column">

            <div class="filter__buttons">
                <button type="submit" class="button button--accent">
                    <i class="fas fa-fa fa-mr fa-filter"></i>Show
                </button>
                <button type="reset" class="button">
                    <i class="fas fa-fw fa-mr fa-sun"></i>Clear
                </button>
            </div>
        </div>
    </form>
</template>

<script>
    import {mapState} from 'vuex';
    export default {
        name: "GiveawaysFilter",
        props: {
            filter: {
                type: Object,
                default: () => ({})
            }
        },
        data() {
            return {};
        },
        computed: {
            ...mapState({
                gifters: state => state.users.gifters
            }),
        },
        methods: {
            submit() {
                this.$emit('filter-submit');
            },

            clear() {
                this.$emit('filter-clear');
            }
        }

        /* TODO
        1 - create game not in list
         */
    }
</script>

<style lang="less">
    @import "../assets/vars";
    @import "../assets/blocks/filter";

</style>