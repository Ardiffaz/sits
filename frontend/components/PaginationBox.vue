<template>
    <div class="pagin">
        <component
            v-if="currentPageNumber > 1"
            :is="linkComponentName"
            :to="useRoute ? getRoute(1) : null"
            :title="1"
            class="pagin__link"
            @click.prevent="changePageNumber(1)"
        >
            <i class="fas fa-fw fa-angle-double-left"></i>
        </component>

        <component
            v-for="i in range(minPageToShow, maxPageToShow)"
            :is="(i === currentPageNumber) ? 'span' : linkComponentName"
            :to="useRoute ? getRoute(i) : null"
            :key="'page_'+i"
            :class="(i === currentPageNumber) ? 'pagin__cur' : 'pagin__link'"
            @click.prevent="(i !== currentPageNumber) ? changePageNumber(i) : null"
        >{{i}}</component>

        <component
            v-if="currentPageNumber < maxPageNumber && maxPageToShow !== maxPageNumber"
            :is="linkComponentName"
            :to="useRoute ? getRoute(maxPageNumber) : null"
            :title="maxPageNumber"
            class="pagin__link"
            @click.prevent="changePageNumber(maxPageNumber)"
        >
            <i class="fas fa-fw fa-angle-double-right"></i>
        </component>

    </div>
</template>

<script>
    export default {
        name: "PaginationBox",
        props: {
            currentPageNumber: {
                type: Number,
                default: 1
            },
            maxPageNumber: {
                type: Number,
                default: 1
            },
            useRoute: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {};
        },
        computed: {
            minPageToShow: function() {
                let minPage = this.currentPageNumber - 3;

                if (minPage < 1)
                    minPage = 1;

                return minPage;
            },
            maxPageToShow: function() {
                let maxPage = this.currentPageNumber + 3;

                if (maxPage > this.maxPageNumber)
                    maxPage = this.maxPageNumber;

                return maxPage;
            },
            linkComponentName: function () {
                return this.useRoute ? 'router-link' : 'a';
            }
        },
        methods: {
            range(start, end){
                return Array(end - start + 1).fill(0).map((val, i) => i + start);
            },

            changePageNumber(pageNum){
                if (!this.useRoute)
                    this.$emit('change-page-number', pageNum)
            },

            getRoute(pageNum){
                let newRoute = {
                    name: this.$route.name,
                    params: this.$route.params ? Object.assign({}, this.$route.params) : {},
                    query: this.$route.query ? Object.assign({}, this.$route.query): {}
                };

                if (pageNum !== 1)
                    newRoute.query.page = pageNum;
                else
                    delete newRoute.query.page;

                return newRoute;
            }
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .pagin{
        padding: 6px 0;
        display: flex;
        justify-content: flex-end;
        font-weight: 500;

        &__link, &__cur{
            padding: 0 5px;
        }

        &__cur{
            color: @color-green;
        }
    }

</style>