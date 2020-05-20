<template>
    <div class="giveaway-editable">
        <div class="giveaway-editable__block giveaway-editable__link">
            <label
                :for="'ga_link_'+id"
                class="blue-label"
            >Link:</label>
            <input
                v-model="giveaway.link"
                :id="'ga_link_'+id"
                type="text"
                class="simpleinput"
                placeholder="Giveaway link"
            />
        </div>
        <div class="giveaway-editable__block">
            <label
                :for="'ga_user_'+id"
                class="blue-label"
            >User:</label>
            <select
                v-model="giveaway.user"
                :id="'ga_user_'+id"
                class="simpleinput"
                title="Giveaway user"
            >
                <option
                    v-for="user in gifters"
                    :value="user.id"
                >{{user.profileName}}</option>
            </select>
        </div>
        <div class="giveaway-editable__block">
            <label
                :for="'ga_date_'+id"
                class="blue-label"
            >Ending Date:</label>
            <input
                v-model="giveaway.dateEnded"
                :id="'ga_date_'+id"
                type="date"
                class="simpleinput"
                placeholder="Ending date"
            />
        </div>
        <div class="giveaway-editable__block giveaway-editable__block--check">
            <label
                :for="'ga_success_'+id"
                class="blue-label"
            >Successful:</label>
            <input
                v-model="giveaway.successful"
                :id="'ga_success_'+id"
                type="checkbox"
                class="checkbox"
                placeholder="Successfully finished"
            />
            <label :for="'ga_success_'+id"></label>
        </div>
        <div class="giveaway-editable__block giveaway-editable__block--notes giveaway-editable__notes">
            <label
                :for="'ga_notes_'+id"
                class="blue-label"
            >Notes:</label>
            <textarea
                v-model="giveaway.notes"
                :id="'ga_notes_'+id"
                class="simpleinput simpleinput--textarea"
                placeholder="Additional notes"
            ></textarea>
        </div>
    </div>
</template>

<script>
    import {mapState, mapGetters} from 'vuex';
    export default {
        name: "GiveawayEditable",
        props: {
            giveawayId: {
                type: Number,
                dafault: 0
            },
            externalValues: {
                type: Object,
                default: () => ({})
            }
        },
        data() {
            return {
                giveaway: {
                    link: '',
                    user: 0,
                    dateEnded: '',
                    successful: false,
                    notes: ''
                },
                id: 'ga'
            };
        },
        computed: {
            ...mapState({
                gifters: state => state.users.gifters,
                loggedUserId: state => state.auth.loggedUserId
            }),

            ...mapGetters({
                getGiveaway: 'giveaways/getGiveaway'
            })
        },
        watch: {
            giveaway: {
                handler: function () {
                    this.$emit('update-ga', this.giveaway);
                },
                deep: true
            },
            'externalValues.link': function () {
                this.$set(this.giveaway, 'link', this.externalValues.link);
            },
            'externalValues.user': function () {
                this.$set(this.giveaway, 'user', this.externalValues.user);
            },
            'externalValues.dateEnded': function () {
                this.$set(this.giveaway, 'dateEnded', this.externalValues.dateEnded);
            },
            'externalValues.successful': function () {
                this.$set(this.giveaway, 'successful', this.externalValues.successful);
            },
            'externalValues.notes': function () {
                this.$set(this.giveaway, 'notes', this.externalValues.notes);
            }
        },
        methods: {},
        created() {
            if (this.giveawayId)
            {
                let originalGiveaway = this.getGiveaway(this.giveawayId);
                this.id = 'ga_'+originalGiveaway.id;

                this.giveaway.id = originalGiveaway.id;
                this.giveaway.link = originalGiveaway.link;
                this.giveaway.user = originalGiveaway.user;
                this.giveaway.dateEnded = this.$getHTMLFormatDate(originalGiveaway.dateEnded);
                this.giveaway.successful = originalGiveaway.successful;
                this.giveaway.notes = originalGiveaway.notes.trim();
            }
            else
            {
                this.id = this.$store.getters.getUniqueId;
                this.$store.dispatch('updateUniqueId');

                this.$set(this.giveaway, 'id', this.id);
                this.$set(this.giveaway, 'user', this.loggedUserId);
            }
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .giveaway-editable{
        display: grid;
        grid-auto-flow: column;
        grid-template-areas: 'link link link notes' 'user date success notes';
        grid-template-columns: 1fr 1fr 100px 220px;
        grid-template-rows: min-content 1fr;
        grid-gap: 6px 8px;
        align-items: start;
        margin-bottom: 4px;

        &__block{
            display: grid;
            grid-auto-flow: column;
            grid-gap: 6px;
            grid-template-columns: minmax(min-content, 28px) 1fr;
            justify-content: start;
            align-items: center;

            &--check{
                align-self: center;
            }

            &--notes{
                grid-auto-flow: row;
                grid-template-columns: auto;
                justify-content: stretch;
            }
        }

        &__link{
            grid-area: link;
        }

        &__notes{
            grid-area: notes;
        }
    }

</style>