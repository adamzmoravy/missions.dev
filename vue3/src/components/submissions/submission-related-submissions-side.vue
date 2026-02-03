<template>

    <div class="mb-3">
        <div class="position-relative">
            <div class="vertical-line"></div>
            <div class="mb-2 icon-label">
                <div class="stage-accenter large" :class="'stage-' + submission.stage + '-accenter'">
                    <div class="d-flex align-items-center">
                        <stage-icon :size="40" class="stage-icon" :stage="submission.stage"/>
                        <span class="stage-icon-explainer">{{$trans($ui.stageHandles[submission.stage])}}</span>
                    </div>
                </div>

            </div>

            <entities :edit="isUserEditor" @edit="ui.relatedExperimentsModalOpen = true" class="on-dotted-line" style="position:relative;left:-5px;" :title="$trans('Related submissions')" :entities="relatedSubmissionsEntities" :limit="5" />

        </div>

        <entities 
            @close="ui.relatedExperimentsModalOpen = false" 
            v-if="ui.relatedExperimentsModalOpen" 
            :multi-select="true" 
            :modal-only="true"                                     
            :show-search-bar="true" 
            @selected="submissionInteractor.addRelatedSubmission" 
            @unselected="submissionInteractor.removeRelatedSubmission" 
            :multi-select-selected="submission.related_experiments" 
            :include-link="false" 
            :sub-title="$trans('Select related submissions.')"
            :title="$trans('Related submissions')" 
            :entities="allSubmissionsEntities" 
            :limit="5000" />

    </div>        

</template>

<style scoped>                

    .icon-label {
        z-index:10;
    }
    .vertical-line {
        z-index:0;
        position:absolute;
        border-left: 1px dashed var(--very-dark);
        height:calc(100% - (1rem + 30px));
        left:0.8rem;
        top:1rem;    
    }

    .stage-icon-explainer {
        position: relative;
    }
    .stage-icon {
        z-index:10;
        position: relative;
        left:-7px;
        margin-right:0.3rem;
    }

    .stage-accenter span {

        line-height:100%;
    }

    .stage-0-accenter {
        color: #F7BCB6;
    }


    .stage-0-accenter .stage-icon-explainer {
       top: -0.1rem;
    }
    .stage-1-accenter .stage-icon-explainer {
       top: 0.4rem;
    }
    .stage-2-accenter .stage-icon-explainer {
       top: -0.2rem;
    }

    .stage-1-accenter img {

    }
    .stage-1-accenter {
        color: #f8dd6c;
    }
    .stage-2-accenter {
        color: #73BF98;
    }


    .stage-accenter {
        z-index:10;
        padding-bottom:0.4rem;
        border-bottom-style:none;
        border-bottom-width:3px;
        display:inline-block;
        font-weight:200;
        text-transform: capitalize;
    }

</style>

<script>

export default {
    name: 'submisson-related-submissions-side',
    data() {
        return {
            ui: {
                relatedExperimentsModalOpen: false,
                allSubmissionsModalOpen: false
            }
        }
    },
    computed: {
        relatedSubmissionsEntities: function () {
            return this.$stores.submissions.getEntities([], [{
                selection: 'ids',
                value: this.submission.related_experiments
            }])
        },
        allSubmissionsEntities: function () {
            return this.$stores.submissions.getEntities()
        },
        totalSubmissionsCount: function () {
            return this.$store.getters.countSubmissions()
        }
    },
    props: {
        isUserEditor: {
            type: Boolean,
            required: false,
            default: false
        },
        submissionInteractor: {
            required: true
        },
        submission: {
            type: Object,
            required: true
        }
    },
    methods: {
        addRelatedSubmission(related) {
            this.submissionInteractor.addRelatedSubmission(related).then(response => {
                this.$emit('submission-updated')
            })
        },
        removeRelatedSubmission(related) {
            this.submissionInteractor.removeRelatedSubmission(related).then(response => {
                this.$emit('submission-updated')
            })
        }

    }
}

</script>