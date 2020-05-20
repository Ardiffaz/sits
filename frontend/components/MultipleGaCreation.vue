<template>
    <div class="multiple-form">
        <div class="multiple-form__fields-block">
            <div class="multiple-form__subhead">Fields Common to All Giveaways:</div>
            <div class="multiple-form__hint">These values will be copied to giveaway creation forms and can be edited individually afterwards.</div>
            <div class="multiple-form__block">
                <label for="mgc_d" class="blue-label">Link:</label>
                <input
                    v-model="commonFields.link"
                    id="mgc_l"
                    type="text"
                    class="simpleinput"
                    placeholder="Giveaway Link"
                />
            </div>
            <div class="multiple-form__block">
                <label for="mgc_u" class="blue-label">User:</label>
                <select
                    v-model="commonFields.user"
                    id="mgc_u"
                    class="simpleinput"
                    title="Creator for all selected giveaways"
                >
                    <option value="">---</option>
                    <option
                        v-for="user in gifters"
                        :value="user.id"
                    >{{user.profileName}}</option>
                </select>
            </div>
            <div class="multiple-form__block">
                <label for="mgc_d" class="blue-label">Ending Date:</label>
                <input
                    v-model="commonFields.dateEnded"
                    id="mgc_d"
                    type="date"
                    class="simpleinput"
                    placeholder="Ending date"
                />
            </div>
            <div class="multiple-form__block">
                <label for="mgc_s" class="blue-label">Successful:</label>
                <div>
                    <input
                        v-model="commonFields.successful"
                        type="checkbox"
                        class="checkbox"
                        id="mgc_s"
                        placeholder="Successfully finished"
                    />
                    <label for="mgc_s"></label>
                </div>
            </div>
            <div class="multiple-form__block">
                <label for="mgc_n" class="blue-label">Notes:</label>
                <textarea
                    v-model="commonFields.notes"
                    id="mgc_n"
                    class="simpleinput simpleinput--textarea"
                    placeholder="Additional notes"
                ></textarea>
            </div>
        </div>
        <div class="multiple-form__template-block">
            <div class="multiple-form__subhead">Combined Template:</div>
            <div class="multiple-form__hint">Check gifts to add them. Uncheck to remove.</div>
            <div class="template">{{combinedTemplate}}</div>
        </div>
        <div class="multiple-form__errors">
            <error-box
                v-if="errorCount > 0"
            >Not created, found {{errorCount}} error(s).</error-box>
        </div>
        <div class="multiple-form__buttons">
            <button
                @click="save"
                type="button"
                :class="['button', 'button--accent', {'button--disabled': isSaveButtonDisabled}]"
            >
                <i class="fas fa-fw fa-mr fa-check-circle"></i>Save
            </button>
        </div>
    </div>
</template>

<script>
    import {mapState} from 'vuex';
    import ErrorBox from "./ErrorBox";
    export default {
        name: "MultipleGaCreation",
        components: {ErrorBox},
        props: {
            combinedTemplate: {
                type: String,
                default: ''
            },
            commonFields: {
                type: Object,
                default: () => ({})
            },
            errorCount: {
                type: Number,
                default: 0
            },
            isSaveButtonDisabled: {
                type: Boolean,
                default: false
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
            save: function () {
                this.$emit('save-all');
            }
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .multiple-form{
        display: grid;
        grid-template-areas: 'template fields' '. buttons';
        grid-template-columns: 1fr 450px;
        grid-gap: 30px;

        &__fields-block{
            grid-area: fields;
        }

        &__template-block{
            grid-area: template;
        }

        &__subhead{
            margin-bottom: 10px;
            color: @color-blue;
            font-family: @font-condensed;
            border-bottom: 1px solid @color-blue;
            padding-bottom: 4px;
        }

        &__hint{
            color: @color-gray;
            font-size: 13px;
            margin-bottom: 8px;
        }

        &__block{
            display: grid;
            grid-auto-flow: column;
            grid-template-columns: minmax(min-content, 70px) 1fr;
            align-items: center;
            grid-gap: 6px;
            margin-bottom: 10px;
        }

        &__buttons{
            grid-area: buttons;
        }
    }

</style>