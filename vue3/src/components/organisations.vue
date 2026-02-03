<template>


    <div>

        <ui-page-header>

            <div>
                <h1 class="x-large mt-2">{{ $trans('Organisations') }}</h1>
                <h2 class="large mt-2">
                    {{ $trans('organisations_page_subheading') }}
                </h2>
            </div>
            <ui-tabs class="mt-3" v-on:update:section="ui.showSection = $event" :tabs="[{display: 'Map', value: 'map'}, {display: 'Organisations', value: 'organisations'}]" :current-selection="ui.showSection">
                    <div v-for="tab in tabs" class="tab-wrapper mr-3">
                        <a :class="{'tab-link-selected': currentSelection === tab.value}" class="tab-link mr-2" @click="$emit('update:section', tab.value)">

                            <span v-if="false">
                            <img v-if="currentSelection === tab.value" :src="'/img/checkbox-yellow.svg'">
                                <img v-if="currentSelection !== tab.value" :src="'/img/checkbox.svg'">
                            </span>
                            {{tab.display}}</a>
                    </div>

            </ui-tabs>

        </ui-page-header>


        <template v-if="ui.showSection == 'organisations'">

            <ui-page-body v-if="canAddOrganisation">

                <div>
                    <ui-button button-type="new" size="md" @click="$stores.ui.setNewSubmission({stage: 8})">+ {{$trans('Add new')}}</ui-button>
                </div>

            </ui-page-body>


            <ui-cards-container>
                <div class="row">
                    <template v-for="submission in organisations">
                        <submission-preview v-if="organisations.length > 0" :key="submission.data.experiment_id" :experiment="submission.data" />
                    </template>
                    <div class="col-lg-12" v-if="organisations.length == 0"></div>
                </div>
            </ui-cards-container>

        </template>


        <div class="container"  v-if="ui.showSection == 'map'">

            <submissions-map :token="$config.platform.geo_provider_key" :stages="[8]" />
            
        </div>


    </div>

</template>

<script>

import submissionsMap from './submissions/submissions-map.vue'

export default {
    name: "organisations",
    data() {
        return {
            ui: {
                showSection: 'map'
            },
            showAddIssue: false
        }
    },
    components: {
        submissionsMap
    },
    computed: {
        canAddOrganisation() {
            if (this.$currentUser && this.$currentUser.user_type == 'admin') {
                return true
            }
            return false
        },
        organisations() {
            let entities = this.$stores.submissions.getEntities([
                {
                    sorter: 'created',
                    direction: 'asc'
                }
            ],
            [{
                selection: 'stages',
                value: [8]
            }])

            return entities
        }
    },
    methods: {
        setup: function() {
    //        this.$refs.submissions.setup()
        },
    },
}

</script>