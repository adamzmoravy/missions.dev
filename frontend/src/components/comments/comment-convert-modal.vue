<template>

    <ui-modal
        :title="$trans('Convert comment to submission')"
        :open="true"
        @close="close">

        <template #body>
            <div>
                <div class="mb-4">
                    <p>{{ $trans('Select the type of submission you would like to create from this comment.') }}</p>
                </div>
                <div class="row">
                        <div class="col-md-12">

                            <div v-for="stage in submissionStages" class="d-md-flex align-items-center position-relative mb-4 mb-md-0">
                                <div @click="convertToStage(stage)" class="d-flex align-items-center my-md-3 my-2">
                                    <stage-icon :stage="stage"></stage-icon>
                                    <div class="capitalize ml-4 mr-2 stage-name-link"><a class="stretched-link">{{ $trans(submissionStageHandles[stage]) }}</a></div>
                                </div>
                                <div>{{$trans(submissionStageHandles[stage] + '_explanation')}}</div>
                            </div>
                        </div>
                    </div>
            </div>


        </template>

    </ui-modal>

</template>


<script>

import backend from '../../backend'

export default {

    name: 'comment-convert-modal',
    props: {
        commentId: {
            type: String,
            required: true
        }
    },
    computed: {
        submissionStageHandles: function () {
            return this.$ui.stageHandles
        },        
        submissionStages: function () {
            return this.$ui.stages
        }        
    },
    methods: {
        convertToStage(stage) {

            backend.post('/experiments/from_comment/', {
                stage: stage,
                comment_id: this.commentId
            }).then(response => {
                this.$emit('closed')
            }).catch(error => {
                this.$emit('error', error)
            })

        },
        close() {
            this.$emit('closed')
        }
    }
}

</script>