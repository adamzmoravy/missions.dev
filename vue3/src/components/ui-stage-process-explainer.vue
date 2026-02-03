<template>
    <div class="stage-container">
        <div class="stage-outer">
            <div class="stage-inner" :class="{ selectable: selectable }" @click="selectStage('0')">
                <img v-if="iconType == 'just_icon'" :src="'/img/stage-0-icon.svg'">
                <submission-type class="submission-type"  v-if="iconType == 'hexagon'" stage="0"></submission-type>
                <div class="label medium" :class="{'selected-element': (stageSelected == '0')}">{{$trans('Challenge')}}</div>
            </div>
        </div>
        <div class="large arrow">→</div>
        <div class="stage-outer">
            <div class="stage-inner" :class="{ selectable: selectable, raise: raise}" @click="selectStage('1')" >
                <img v-if="iconType == 'just_icon'" :src="'/img/stage-1-icon.svg'">
                <submission-type class="submission-type"  v-if="iconType == 'hexagon'" stage="1"></submission-type>
                <div class="label medium" :class="{'selected-element': (stageSelected == '1')}">{{$trans('Idea')}}</div>
            </div>
        </div>
        <div class="large arrow">→</div>
        <div class="stage-outer">
            <div class="stage-inner" :class="{ selectable: selectable }"  @click="selectStage('2')">
                <img v-if="iconType == 'just_icon'" :src="'/img/stage-2-icon.svg'">
                <submission-type class="submission-type"  v-if="iconType == 'hexagon'" stage="2"></submission-type>
                <div class="label medium" :class="{'selected-element': (stageSelected == '2')}">{{$trans('Project')}}</div>
            </div>
        </div>
    </div>
</template>

<script>
    import SubmissionType from './submissions/submission-type.vue'
    export default {
        name: 'ui-stage-process-explainer',
        data() {
            return {
                stageSelected: null
            }
        },
        props: {
            iconType: {
                required: false,
                default: 'just_icon',
                type: String
            },
            selectable: {
                required: false,
                default: false
            }
        },
        components: {
            SubmissionType
        },
        mixins: [
        ],
        computed: {
            raise: function() {
                return this.iconType == 'just_icon'
            }
        },
        methods: {
            selectStage: function (stage) {
                if (this.selectable) {
                    if (this.stageSelected == stage) {
                        this.stageSelected = null
                    } else {
                        this.stageSelected = stage
                    }
                    this.$emit('select-stage', this.stageSelected)
                }
            }
        },
        created: function() {
        },
        mounted: function() {
        }
    }
</script>

<style scoped>
    .label {

    }
    .raise {
      top:-10%;height:60%!important;        
    }
    .submission-type {
        width:50px;
    }
    .label {
        font-weight:400;
    }
    .stage-container {
        position:relative;
        top:-10%;
        height:100%;display:grid;grid-template-columns:repeat(5, 1fr);grid-template-rows: 100% 1fr;text-align: center;
    }
    .stage-outer {
        position:relative;
        height:100%;
        justify-self:center;
        text-align:center;
        display:flex;
        align-items: center;        
    }

    .selectable {
        cursor:pointer;
    }

    .stage-inner {
        position:relative;
        height:50%;text-align: center;        
    }

    .stage-inner img {
        height:100%;    
    }

    .arrow {
        height:100%;justify-self:center;text-align:center;display:flex;align-items: center;        
    }

</style>
