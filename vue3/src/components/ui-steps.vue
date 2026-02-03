<template>

    <div class="d-flex flex-column" style="justify-content:space-between;">

        <div class="">
            <div class="d-flex justify-content-center py-2 py-md-0">
                <span v-for="(s, i) in stages" class="mx-1" style="cursor:pointer;" @click="selectedStage = i">
                    <img v-if="stage >= i" :src="'/img/checkbox-' + stepsStyle + '.svg'">
                    <img v-if="stage < i" :src="'/img/checkbox.svg'">
                </span>

            </div>
        </div>


        <slot :stage="stage" :stageName="stageName">

        </slot>


        <div class="py-3 pb-4 px-3 d-flex" style="justify-content:space-between;">

            <div>
                <button v-if="stage != 0" type="button" name="button" class="wizard-button continue-button"
                @click="previousStage()">
                ← {{$trans('Back')}}
                </button>
            </div>
            <div>
                <button v-if="stage != (stages.length - 1)" type="button" name="button" class="wizard-button continue-button"
                @click="nextStage()">
                  {{$trans('Next')}} →
                </button>
                <button v-if="stage == (stages.length - 1)" type="button" name="button" class="wizard-button continue-button"
                @click="$emit('finished')">
                  {{$trans('Finish')}}
                </button>

            </div>

        </div>
    </div>
    
</template>

<script>
    export default {
        name: 'ui-steps',
        props: {
            stepsStyle: {
                required: false,
                default: 'green'
            },
            stages: {
                required: true
            },
            startStage: {
                required: false,
                default: 0
            }
        },
        methods: {
            nextStage: function() {
                this.selectedStage++
            },
            previousStage: function() {
                this.selectedStage--
            }
        },
        computed: {
            stageName: function() {
                return this.stages[this.selectedStage].name
            },
            stage: function () {
                return this.selectedStage
            }
        },
        data () {
            return {
                selectedStage: 0
            }
        },
        mounted() {
            this.selectedStage = this.startStage
        },
        watch: {
            selectedStage: function (newStage, oldStage) {
                this.$emit('stage-changed', newStage)
            }
        }
    }

</script>
