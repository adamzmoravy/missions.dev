<template>
    <div class="preview-wrapper">
        <div class="location-label" v-if="experiment.geographic_location && experiment.geographic_location.vicinity">
            <span class="d-flex small align-items-center float-right" v-tooltip="experiment.geographic_location.formatted_address"><img style="height:1.2em;" :src="'/img/location.svg'">{{ $truncate(experiment.geographic_location.vicinity, 18) }}</span>
        </div>
        <div class="preview-container open-bottom position-relative">
            <div class="top-head" :class="'stage-out-' + experiment.stage">
                    <stage-icon :stage="experiment.stage" class="stage-icon" :size="30" v-if="false" />
                     <h2 class="medium-2" v-if="!blank">
                        <ui-a class="stretched-link" :target="target" :href="'/submission/' + experiment.experiment_id">{{experiment.title}}</ui-a>
                    </h2>
            </div>
            <div class="body" :class="'body-stage-out-' + experiment.stage">
                <ui-show-more :max-height="600" :button-show="false">
                    <ui-img-dynamic :height-ratio="0.5625" class="experiment-image" v-if="experiment.image" :src="experiment.image" />
                    <p class="mb-0" v-if="!blank">{{experiment.short_description}}</p>
                </ui-show-more>
            </div>
            <div class="footer">
                <div class="left-footer">
                    <div class="stage-accenter medium" :class="'stage-' + experiment.stage + '-accenter'">
                        <span>{{$trans($ui.stageHandles[experiment.stage])}}</span>
                    </div>
                    <div class="reactions-container d-flex align-items-center mr-1 justify-content-start">
                        <span class="d-flex align-items-center" v-if="!blank">
                            <template  v-for="(reaction, key) in reactionTypes">
                                <div class="d-inline-block mr-2" v-if="experiment.participants[key +'_count'] > 0">
                                <div class="d-flex ml-1">
                                    <div class="d-flex align-items-center"><reactions-icon class="reaction-icon-component" mode="selected" :reaction="key" :size="'20px'" /></div>
                                    <div style="font-size:18px;margin-left:5px;padding-bottom:1px;"> {{ experiment.participants[key +'_count'] }}</div>

                                </div>
                                </div>
                            </template>

 
                       </span>
                    </div>
                </div>
                <div class="right-footer">
                    <div class="url-show align-items-center mr-1 justify-content-end" v-if="!blank">
                        <div>
                            <div>
                                <qrcode-vue :value="url" :size="100" class="float-right" />
                            </div>
                            <div>
                                {{ url }}
                            </div>

                        </div>
                    </div>
                </div>

            </div>


        </div>

    </div>
</template>

<style scoped>

.print .left-footer {
    display:flex;
    align-items:end;
}
.footer {
    display:flex;
    justify-content: space-between;
}

.url-show {
    display:none;
    margin-left:0.7rem;
    margin-bottom:1rem;
    font-size:170%;
}

.print .url-show {
    display: inline-block;
}

.reactions-container {
    height:2rem;
    margin-bottom:1rem;
    margin-left:0.7rem;
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

/*
.comment-container .preview-container {
    border-width:0px!important;
    border-bottom-width:1px!important;
    border-top-width:1px!important;
    border-radius:0%!important;
}

.open-bottom {
    border-bottom-right-radius:0px;
    border-bottom-left-radius:0px;
}
*/
.preview-container:hover {
    filter: drop-shadow(0 0 1.2rem rgb(214, 213, 213));
}
.print .preview-container:hover {
    filter: none;
}


.print .top-heaad {
    height:auto!important;
    padding-top:5%;
    padding-bottom:5%;
}

.print .type-icon {
    height:100%!important;
}

.print .icon {
    height:60%;
    margin-right:1%;
}

.print .preview-container {
    border-style:none;
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

.print h2 {
    font-size:400%!important;
}
.print p {
    font-size:300%!important;
}

.print .medium {
    font-size:300%!important;
}

.print .stage-accenter {
    border-top-width:6px;
}

.print .reactions-container {
    display:none!important;
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

.stage-3-accenter {
    color: #95C1E2;
}

</style>

<script>
import QrcodeVue from 'qrcode.vue'
import reactionsIcon from '../reactions-icon.vue'
export default {
    name: 'submission-preview-feed',
    data() {
        return {
        }
    },
    computed: {
        reactionTypes: function () {
            return this.$ui.submissionReactions
        },
        url () {
            return window.location.protocol + '//' + window.location.host + '/' + this.$lang + '/experiment/' + this.experiment.experiment_id
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
    components: {
        QrcodeVue,
        reactionsIcon
    },
    props: {
        blank: {
            required: false,
            type: Boolean,
            default: false
        },
        iconSize: {
            type: String,
            required: false,
            default: "100"
        },
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