<template>
    <div class="preview-wrapper col-md-4 mb-3 mb-sm-4" :class="experiment.status">
        <div class="preview-container open-bottom position-relative">
            <div class="location-label">
                <span class="d-flex small align-items-center float-right" v-if="experiment.geographic_location && experiment.geographic_location.vicinity" v-tooltip="experiment.geographic_location.formatted_address"><img style="height:1.2em;" :src="'/img/location.svg'">{{$truncate(experiment.geographic_location.vicinity, 18) }}</span>
            </div>

            <div class="top-head px-3" :class="'stage-out-' + experiment.stage">
                <div class="top-head-inner">
                    <h2 class="medium-2 submission-title">
                        <ui-a class="title multiline-ellipsis stretched-link" :href="'/submission/' + experiment.experiment_id + '/'">{{experiment.title}}</ui-a>
                    </h2>
                </div>
            </div>
            <div class="body" :class="'body-stage-out-' + experiment.stage">
                <ui-img-dynamic :height-ratio="0.5625" class="experiment-image" v-if="experiment.image" :src="experiment.image" />
                <div class="short-desription multiline-ellipsis">{{shortDescriptionTruncated}}</div>
            </div>
            <div class="stage-accenter medium" :class="'stage-' + experiment.stage + '-accenter'">
                <span>{{$trans(stageHandles[experiment.stage])}}</span>
            </div>

            <div class="reactions-container d-flex align-items-center mr-1 justify-content-start">
                <span class="d-flex align-items-center" >

                    <template v-for="(reaction, key) in $ui.submissionReactions">
                        <div class="d-flex align-items-center mr-2" v-tooltip="experiment.participants[key + '_count'] + reaction.label" v-if="experiment.participants[key + '_count'] > 0">
                        <div class="mr-1"><reactions-icon class="reaction-icon-component" :mode="'selected'" :reaction="key" :size="'18px'" /></div><div>{{experiment.participants[key + '_count']}}</div> 
                    </div>

                    </template>
                    <div class="d-flex align-items-center mr-2" v-tooltip="'Feedback'" v-if="experiment.comment_count > 0">
                        <div class="mr-1"><reactions-icon class="reaction-icon-component" :mode="'selected'" :reaction="'feedback'" :size="'18px'" /></div><div>{{experiment.comment_count}}</div> 
                    </div>
                </span>
            </div>

            <div v-if="false" class="reactions-container d-flex align-items-center mr-1 justify-content-start">
                <span class="d-flex align-items-center">
                    <div class="d-flex align-items-center justify-content-center mr-2" :title="experiment.comment_count + ' feedbacks given'" v-if="experiment.comment_count > 0"> 
                        <span class="material-symbols-outlined md-24">comment</span> 
                        <span style="height:28px;margin-left:3px;">{{experiment.comment_count}}</span>
                    </div>
                </span>
            </div>

        </div>

    </div>
</template>

<style scoped>

.resolved {
    opacity:0.5;
}

.resolved .submission-title {
    text-decoration: line-through;  
}

.short-desription {
    padding-left:1em;
    padding-right:1em;
    margin-bottom:1em;
    -webkit-line-clamp: 4;
           line-clamp: 4; 

}

.title {
    -webkit-line-clamp: 3; 
    line-clamp: 3;
}
.multiline-ellipsis {
    overflow: hidden;
   display: -webkit-box;
   -webkit-box-orient: vertical;
}
.reactions-container {
    height:2rem;
    margin-bottom:1rem;
    margin-left:1rem;
    margin-top:-3px;
}
.location-label {
    padding-top:0.5rem;
    padding-right:0.5rem;
    opacity:0.7;
    margin-bottom:-0.5rem;

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

.preview-wrapper {
    display: flex;
    flex-direction: column;
}
.preview-container {
    flex:auto;
    box-sizing:border-box;
    transition-property: all;
    transition-duration:.2s;
    border-color:lightgrey;
    border-style:solid;
    border-width:1px;
    border-radius:6px;
    background-color:white;
    display: flex;
    flex-direction: column;
    cursor:pointer;

}

.open-bottom {
    border-bottom-right-radius:0px;
    border-bottom-left-radius:0px;
}

.preview-container:hover {
    transform:translateY(-2px);
    filter: drop-shadow(0 0 1.2rem rgb(214, 213, 213));
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
    padding-right:1rem;
    border-bottom-style:none;
    border-width:1px;
    border-color:lightgrey;
    padding-left:0.2em;
    min-height:5rem;
    display:flex;
    align-items:center;

}

.top-head-inner {
    margin-top:-0.5rem;
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


.stage-accenter {
    width:6rem;
    margin-left:1rem;
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


</style>

<script>
import reactionsIcon from '../reactions-icon.vue'

export default {
    name: 'submission-preview',
    data() {
        return {

        }
    },
    components: {
        reactionsIcon
    },
    computed: {
        shortDescriptionTruncated () {
            return this.experiment.short_description.substring(0, this.descriptionCharacterCount) + (this.experiment.short_description.length > this.descriptionCharacterCount ? '...' : '')
        },
        stageHandles: function () {
            return this.$ui.stageHandles
        },
        descriptionCharacterCount () {
            if (this.experiment.image) {
                return this.descCharLimit / 3.5
            } else {
                return this.descCharLimit
            }
        },
        target () {
            if (this.openInNewWindow) {
                return "_blank"
            }
            return ""
        }
    },
    props: {
        openInNewWindow: {
            required: false,
            default: false
        },
        descCharLimit: {
            type: Number,
            required: false,
            default: 400
        },
        experiment: {
            type: Object,
            required: true
        }
    },
    mixins: [
    ],
    notifications: {
    },
    methods: {
    },
    beforeDestroy () {
    },
    created: function() {

    }
}
</script>