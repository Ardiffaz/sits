<template>
    <form
        @submit.prevent="submit"
        @reset.prevent="clear"
        class="filter"
    >

        <div class="filter__column">
            <div class="filter__block">
                <label for="gf_g" class="blue-label">Game:</label>
                <input
                    v-model="filter.game"
                    name="gf_g"
                    id="gf_g"
                    type="text"
                    class="simpleinput"
                    placeholder="Game name or app ID"
                />
            </div>
            <div class="filter__block">
                <div class="blue-label">Game price</div>
                <div class="filter__options">
                    <div class="filter__inline-block">
                        <label for="gf_pf" class="blue-label">From:</label>
                        <input
                            v-model="filter.price_from"
                            type="text"
                            name="gf_pf"
                            id="gf_pf"
                            class="simpleinput filter__price-input"
                            placeholder="Price From"
                        />
                    </div>
                    <div class="filter__inline-block">
                        <label for="gf_pt" class="blue-label">To:</label>
                        <input
                            v-model="filter.price_to"
                            type="text"
                            name="gf_pt"
                            id="gf_pt"
                            class="simpleinput filter__price-input"
                            placeholder="Price To"
                        />
                    </div>
                </div>
            </div>
            <div class="filter__block">
                <div class="blue-label">Quality:</div>

                <div class="filter__options">
                    <input
                        v-model="filter.quality"
                        value="m"
                        type="radio"
                        class="radiobox"
                        name="gf_q"
                        id="gf_q_m"
                    />
                    <label for="gf_q_m" title="Monthly" class="filter__quality filter__quality--m">M</label>

                    <input
                        v-model="filter.quality"
                        value="q"
                        type="radio"
                        class="radiobox"
                        name="gf_q"
                        id="gf_q_q"
                    />
                    <label for="gf_q_q" title="Quality" class="filter__quality filter__quality--q">Q</label>

                    <input
                        v-model="filter.quality"
                        value="hq"
                        type="radio"
                        class="radiobox"
                        name="gf_q"
                        id="gf_q_hq"
                    />
                    <label for="gf_q_hq" title="High Quality"  class="filter__quality filter__quality--hq">HQ</label>

                    <input
                        v-model="filter.quality"
                        value="ds"
                        type="radio"
                        class="radiobox"
                        name="gf_q"
                        id="gf_q_ds"
                    />
                    <label for="gf_q_ds" title="Dark Side"  class="filter__quality filter__quality--ds">DS</label>

                    <input
                        v-model="filter.quality"
                        value="u"
                        type="radio"
                        class="radiobox"
                        name="gf_q"
                        id="gf_q_u"
                    />
                    <label for="gf_q_u" title="Unreviewed"  class="filter__quality filter__quality--u">U</label>

                    <input
                        v-model="filter.quality"
                        value=""
                        type="radio"
                        class="radiobox"
                        name="gf_q"
                        id="gf_q_no"
                    />
                    <label for="gf_q_no" title="Not Set"  class="filter__quality">---</label>
                </div>
            </div>
            <div class="filter__block filter__block--custom">
                <div class="filter__inline-block">
                    <label for="gf_c" class="blue-label">Cards:</label>
                    <input
                        :checked="!!parseInt(filter.cards)"
                        @change="filter.cards = !!parseInt(filter.cards) ? '' : 1"
                        type="checkbox"
                        class="checkbox"
                        name="gf_c"
                        id="gf_c"
                    />
                    <label for="gf_c"></label>
                </div>

                <div class="filter__inline-block">
                    <label for="gf_a" class="blue-label">Achievements:</label>
                    <input
                        :checked="!!parseInt(filter.achievements)"
                        @change="filter.achievements = !!parseInt(filter.achievements) ? '' : 1"
                        type="checkbox"
                        class="checkbox"
                        name="gf_a"
                        id="gf_a"
                    />
                    <label for="gf_a"></label>
                </div>
            </div>
        </div>

        <div class="filter__column">
            <div class="filter__block">
                <label for="gf_s" class="blue-label">Source:</label>
                <input
                    v-model="filter.source"
                    type="text"
                    name="gf_s"
                    id="gf_s"
                    class="simpleinput"
                    placeholder="Source name or link content"
                />
            </div>
            <div class="filter__block">
                <div class="filter__inline-block">
                    <label for="gf_lts" class="blue-label">Low-trusted Sources:</label>
                    <div class="filter__inline-block">
                        <input
                            :checked="!!parseInt(filter.lts)"
                            @change="filter.lts = !!parseInt(filter.lts) ? '' : 1"
                            type="checkbox"
                            class="checkbox"
                            name="gf_lts"
                            id="gf_lts"
                        />
                        <label for="gf_lts"></label>
                    </div>
                </div>
            </div>
            <div class="filter__block">
                <label for="gf_r" class="blue-label">Reserved by:</label>
                <select
                    v-model="filter.reserved"
                    class="simpleinput"
                    id="gf_r"
                    name="gf_r"
                >
                    <option value="">---</option>
                    <option
                        v-for="user in gifters"
                        :value="user.id"
                    >{{user.profileName}}</option>
                </select>
            </div>

        </div>

        <div class="filter__column">
            <div class="filter__block">
                <label for="gf_k" class="blue-label">Key:</label>
                <input
                    v-model="filter.key"
                    type="text"
                    class="simpleinput"
                    name="gf_k"
                    id="gf_k"
                    placeholder="Key content"
                />
            </div>
            <div class="filter__block">
                <label for="gf_n" class="blue-label">Notes:</label>
                <input
                    v-model="filter.notes"
                    type="text"
                    name="gf_n"
                    id="gf_n"
                    class="simpleinput"
                    placeholder="Notes content"
                />
            </div>
            <div class="filter__block">
                <label for="gf_n" class="blue-label">Notes Excluded:</label>
                <input
                    v-model="filter.exnotes"
                    type="text"
                    name="gf_en"
                    id="gf_en"
                    class="simpleinput"
                    placeholder="Excluded notes"
                />
            </div>
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
        name: "GiftsFilter",
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
    }
</script>

<style lang="less">
    @import "../assets/vars";
    @import "../assets/blocks/filter";

</style>