<template>

    <ui-modal @close="$emit('close')" :title="submission.title" :open="true" size="tall">
        <template #body>

            <div class="preview-container open-bottom position-relative w-100" >

                <div class="body">
                    <ui-show-more :max-height="380" :button-show="false">
                        <div>
                            <ui-a class="stretched-link" :href="'/submission/' + experiment.experiment_id">
                                <ui-img-dynamic :height-ratio="0.4" class="experiment-image" v-if="experiment.image" :src="experiment.image" />
                            </ui-a>
                        </div>
                        <p class="mb-0 pb-0 px-0">{{experiment.short_description}} </p>
                    </ui-show-more>
                </div>
                <div class="footer">
                    <div class="left-footer pt-5 px-0">
                        <div class="stage-accenter medium" :class="'stage-' + experiment.stage + '-accenter'">
                            <span>{{$trans($ui.stageHandles[experiment.stage])}}</span>
                        </div>
                        <div class="reactions-container d-flex align-items-center mr-1 justify-content-start">
                            <span class="d-flex align-items-center">
                                <template  v-for="(reaction, key) in reactionTypes">
                                    <div class="d-inline-block mr-2" v-if="experiment.participants[key +'_count'] > 0">
                                    <div class="d-flex ml-1">
                                        <div class="d-flex align-items-center">
                                            <reactions-icon class="reaction-icon-component" mode="selected" :reaction="key" :size="'20px'" />
                                        </div>
                                        <div style="font-size:18px;margin-left:5px;padding-bottom:1px;"> {{ experiment.participants[key +'_count'] }}</div>

                                    </div>
                                    </div>
                                </template>

    
                        </span>
                        </div>
                    </div>
                </div>
       

            </div>

        </template>
        <template #footer>

            <div class="d-flex justify-content-center">
                <ui-button :href="'/submission/' + experiment.experiment_id + '/'" :label="$trans('View')" class="mr-2" />
            </div>

        </template> 

    </ui-modal>

</template>

<style scoped>

.footer {
    display:flex;
    justify-content: space-between;
}

.reactions-container {
    height:2rem;
    margin-bottom:1rem;
    margin-left:0rem;
    margin-top:-3px;
}
.location-label {
    opacity:0.7;
    height:20px;
    padding-right:0.5rem;
}
.experiment-image {
    width:100%;
    margin-bottom:1rem;
}
.icon {
    margin-right:0.5rem;
    margin-left:0.3rem;
    height:50%;
}

.preview-wrapper-out {
    display: flex;
    flex-direction: column;
}
.preview-container {
    max-height:500px;
    box-sizing:border-box;
    cursor:pointer;

}


.stage-0 {
    background-color: #F7BCB6;
}
.stage-1 {
    background-color: #f8dd6c;
}
.stage-2 {
    background-color: #73BF98;
}

.body-stage-0 {
    background-color: #fde0dd;
}
.body-stage-1 {
    background-color: #fff0b3;
}
.body-stage-2 {
    background-color: #8fcbac;
}


.top-head {
    padding-right:0.8rem;
    border-bottom-style:none;
    border-width:1px;
    border-color:lightgrey;
    padding-left:0.8rem;
    height:5rem;
    display:flex;
    align-items:center;
}
.top-head h2 {
    font-weight:200!important;
    margin-top:0px;
    margin-bottom:0px;
}
.top-head a {
    font-weight:300;
    text-decoration:none;
}

.print .top-head {
    padding-top:1%;
    padding-bottom:2%;

    height:auto!important;
}

.body {
    flex:auto;
    min-height:12rem;
}
.body p {
    padding:1rem;
    padding-top:0rem;
    padding-bottom:2rem;
}


.stage-accenter span {
    filter: brightness(90%);
}

.print .stage-accenter span {
    filter: brightness(100%);
}

.stage-icon {
    margin-right:0.6rem;
}


.stage-accenter {
    width:6rem;
    margin-left:0rem;
    padding-bottom:0.5rem;
    border-top-style:solid;
    border-top-width:3px;
    display:inline-block;
    font-weight:200;
    text-transform: capitalize;
}

.stage-0-accenter {
    color: #F7BCB6;
}
.stage-1-accenter {
    color: #f8dd6c;
}
.stage-2-accenter {
    color: #73BF98;
}

.stage-3-accenter {
    color: #95C1E2;
}

</style>

<script>

import reactionsIcon from '../reactions-icon.vue'

export default {
    name: 'submission-preview-modal',
    props: {
        submissionId: String
    },
    methods: {
        close() {
            this.$emit('close')
        }
    },
    components: {
        reactionsIcon
    },
    computed: {
        reactionTypes: function () {
            return this.$ui.submissionReactions
        },
        submission: function () {
            return this.$stores.submissions.getSubmissionPreview(this.submissionId);
        },
        experiment: function () {
            return this.submission
        },
    }
}

</script>