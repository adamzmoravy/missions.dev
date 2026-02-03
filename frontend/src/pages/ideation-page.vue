<template>


    <div>



        <ui-page-body class="mt-4">
            <div class="d-md-flex mb-2 mb-md-4 justify-content-between">
                <div>
                    <div style="height:70px;" class="mb-4 mb-md-0">
                        <ui-stage-process-explainer icon-type="hexagon" @select-stage="setStage" :selectable="true" style="max-width:350px;" />
                    </div> 
                </div>
                <div>
                    <ui-button button-type="new" @click="$stores.ui.setNewSubmission()">{{$trans('Add new')}}</ui-button>
                </div>

            </div>
        </ui-page-body>


            <ui-cards-container>

                
                <div class="row">

                <template v-for="submission in ideation"  :key="submission.data.experiment_id" >
                    <submission-preview :experiment="submission.data"></submission-preview>

                </template>
               
                </div>                  

            </ui-cards-container>
    </div>

</template>

<script>
import SubmissionWizard from '../components/submissions/submission-wizard.vue';

export default {
name: "ideation-page",
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
    ideation: function () {
        return this.$stores.submissions.getEntities(
            [{
                sorter: 'created',
                direction: 'asc'
            }],
            [{
                selection: 'stages',
                value: this.showStages

            }])
    },
    showStages: function () {
        if (this.stage == null) {
            return [3, 0, 1, 2]

        }
        return [Number(this.stage)]
    }
},
components: { SubmissionWizard }
}

</script>