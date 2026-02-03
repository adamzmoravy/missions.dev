<template>


    <div>

        <ui-page-header class="d-none">

            <div>
                <h1 class="x-large mt-2">{{ $trans('Submissions_page_heading') }}</h1>
                <h2 class="large mt-2">
                    {{ $trans('submissions_page_subheading') }}
                </h2>


            </div>

            <tabs class="mt-3" v-on:update:section="ui.showSection = $event" :tabs="[{display: 'Submissions', value: 'submissions'}]" :current-selection="'submissions'" inline-template>
                <div class="pl-0">
                    <div v-for="tab in tabs" class="tab-wrapper mr-3">
                        <a :class="{'tab-link-selected': currentSelection === tab.value}" class="tab-link mr-2" @click="$emit('update:section', tab.value)">

                            <span v-if="false">
                            <img v-if="currentSelection === tab.value" :src="'/img/checkbox-yellow.svg'">
                                <img v-if="currentSelection !== tab.value" :src="'/img/checkbox.svg'">
                            </span>
                            {{tab.display}}</a>
                    </div>
                </div>

            </tabs>

        </ui-page-header>

        <ui-page-body class="mt-4">
            <div class="d-md-flex mb-2 mb-md-4 justify-content-between">
                <div>
                    <div style="height:70px;" class="mb-4 mb-md-0">
                        <ui-stage-process-explainer icon-type="hexagon" @select-stage="setStage" :selectable="true" style="max-width:350px;" />
                    </div> 
                </div>
                <div>
                    <ui-button button-type="new" @click="$stores.ui.setAction('new-submission')">{{$trans('Add new')}}</ui-button>
                </div>

            </div>
        </ui-page-body>


        <ui-cards-container>
            <experiment-index inline-template 
                class="row"
                :sort-experiments-object="[{key:'likes',name:'Most hearts',initial:false}, {key:'id', name:'Most recent', initial:false}, {key:'relevance', name:'Most relevant', initial:true}]" 
                :show-stages="showStages" 
                :language="$lang" 
                show-team-member="all" 
                get-place="index" 
                show-limit="0" 
                placeholder-image-url="/img/placeholder.png">
                    <div>

                        <submission-preview-haus v-if="experiments.length > 0" :key="experiment.experiment_id"  v-for="experiment in sortedAndFilteredExperiments" :experiment="experiment"></submission-preview-haus>

                        <div class="col-lg-12" v-if="experiments.length == 0">
                            
                        </div>


                    </div>
                        
            </experiment-index>
        </ui-cards-container>

    </div>

</template>

<script>
import SubmissionWizard from '../components/submission-wizard.vue';

export default {
name: "submissions-page",
data() {
    return {
        stage: null,
        ui: {},
        showAddIssue: false
    };
},
methods: {
    setStage: function (stage) {
        this.stage = stage
    }
},
computed: {
    showStages: function () {
        if (this.stage == null) {
            return "0, 1, 2"

        }
        return this.stage
    }
},
components: { SubmissionWizard }
}

</script>