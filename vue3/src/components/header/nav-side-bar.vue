<template>
    <div class="nav-side-bar pl-4 pt-4">
        <div class="search-input-wrapper pr-4">
            <ui-input-text v-model="findOrCreate" class="find-or-create mb-3 mr-3" :placeholder="$trans('Find or create')"></ui-input-text>
        </div>
        <div class="pl-1 entities-wrapper w-100">
            <entities class="entities" :title="$trans('Entities')" :entities="entities" :limit="999" />              
            <div class="ml-2 medium-2 mt-3">
                → <ui-a @click="$stores.ui.setAction('new-submission')">{{$trans('New submission')}}</ui-a>

            </div>

        </div>

    </div>
</template>

<style scoped>
.search-input-wrapper {
}
.entities-wrapper {
    width:270px;
    overflow-x: auto;
    height:100%;
}
.entities {
    padding-right:2.5rem;

}

.find-or-create {
}
.nav-side-bar {
    border-right-style: solid;
    border-right-width:1px;
    border-right-color:lightgrey;
    max-width:270px;
    height:100%;
    filter:drop-shadow(0px 1px 4px rgba(0, 0, 0, 0.07));
}

</style>

<script>
export default {
    name: 'nav-side-bar',
    data() {
        return {
            findOrCreate: ''
        }
    },
    computed: {
        entities: function () {
            return this.$stores.entities.getFilteredEntities([{
                selection: 'search',
                value: this.findOrCreate
            }])
        }
    },
    props: {
    }
}
</script>
