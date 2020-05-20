<template>
    <div class="sort">
        <div class="blue-label">Sort by:</div>
        <div
            v-for="(name, key) in params"
            class="sort__item"
        >
            {{name}}

            <component
                :is="(key === curParam && 'asc' === curDir) ? 'span' : 'router-link'"
                :to="getRoute(key, 'asc')"
                class="sort__link"
            ><i class="fas fa-fw fa-caret-up"></i></component>
            <component
                :is="(key === curParam && 'desc' === curDir) ? 'span' : 'router-link'"
                :to="getRoute(key, 'desc')"
                class="sort__link"
            ><i class="fas fa-fw fa-caret-down"></i></component>
        </div>
    </div>
</template>

<script>
    export default {
        name: "SortBox",
        props: {
            params: {
                type: Object,
                default: () => ({})
            },
            curParam: {
                type: String,
                default: ''
            },
            curDir: {
                type: String,
                default: 'desc'
            }
        },
        data() {
            return {};
        },
        computed: {},
        methods: {
            getRoute: function (sortParam, dir) {

                let query = this.$route.query ? Object.assign({}, this.$route.query): {};

                if (query.page)
                    delete query.page;

                let newRoute = {
                    name: this.$route.name,
                    params: this.$route.params ? Object.assign({}, this.$route.params) : {},
                    query: query
                };

                newRoute.query.sort = sortParam;
                newRoute.query.dir = dir;

                return newRoute;
            }
        }
    }
</script>

<style lang="less">
    @import "../assets/vars";

    .sort{
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;

        &__item{
            margin-left: 14px;
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
        }

        &__link{
            display: block;
            width: 20px;
            height: 30px;
            margin: 0 4px;
        }
    }

</style>