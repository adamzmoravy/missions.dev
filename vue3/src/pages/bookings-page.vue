<template>


    <div>

        <ui-page-header>

            <div>
                <div class="pt-0 pb-0 w-100">
                    <div>
                        <h1 class="x-large mt-2">{{ $trans('Book') }}</h1>
                        <h2 class="large mt-2">
                            {{ $trans('book_page_subheading') }}
                        </h2>

                    </div>

                </div>
                <ui-tabs class="mt-3" v-on:update:section="ui.showSection = $event" :tabs="[{display: 'Rooms', value: 'rooms'}]" :current-selection="'rooms'" inline-template>
                    <div class="pl-1">
                        <div v-for="tab in tabs" class="tab-wrapper mr-3">
                            <a :class="{'tab-link-selected': currentSelection === tab.value}" class="tab-link mr-2" @click="$emit('update:section', tab.value)">

                                <span v-if="false">
                                <img v-if="currentSelection === tab.value" :src="'/img/checkbox-yellow.svg'">
                                    <img v-if="currentSelection !== tab.value" :src="'/img/checkbox.svg'">
                                </span>
                                {{tab.display}}</a>
                        </div>
                    </div>

                </ui-tabs>

            </div>
            </ui-page-header>


            <ui-cards-container class="mt-4">

            <resource-index class="row" ref="resources"></resource-index>

            </ui-cards-container>


    </div>

</template>

<script>

import resourceIndex from '../components/resources/resource-index.vue'

export default {
    name: "bookings-page",
    data() {
        return {
            ui: {},
            showAddIssue: false
        }
    },
    components: {
        resourceIndex
    },
    watch:{
        $route (to, from){
            if (to.name == 'book') {
                this.$refs.resources.setup()
            }
        }
    }
}

</script>