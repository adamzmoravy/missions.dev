<template>

    <ui-modal :title="title" @close="$emit('close')" :open="true" size="tall">
        <template #header-post>

        <div class="d-flex flex-column" style="justify-content:space-between;">

            <div :class="{ 'working': working }">
                <div class="d-flex justify-content-center py-2 py-md-0">
                    <span v-for="(s, i) in stages" class="mx-1" style="cursor:pointer;" @click="selectedStage = i">
                        <img v-if="stage >= i" :src="'/img/checkbox-' + stepsStyle + '.svg'">
                        <img v-if="stage < i" :src="'/img/checkbox.svg'">
                    </span>

                </div>
            </div>
            <slot name="top" :stage="stage" :stageName="stageName">
            </slot>
        </div>
        </template>
        <template #body>
            <slot name="body" :stage="stage" :stageName="stageName">

            </slot>

        </template>
        <template #footer>
            <slot name="bottom" :stage="stage" :stageName="stageName">
                    
            </slot>
            <div class="py-3 pb-4 px-3 d-flex" style="justify-content:space-between;">

            <div :class="{ 'working': working }">
                <button v-if="stage != 0" type="button" name="button" class="wizard-button continue-button"
                @click="previousStage()">
                ← {{$trans('Back')}}
                </button>
            </div>
            <div :class="{ 'working': working }">
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
        </template>
    </ui-modal>
</template>

<style scoped>

.working {
    opacity: 0.6;
    pointer-events: none;
    user-select: none;
}

</style>

<script>

    export default {
        name: 'ui-steps-modal',
        components: {
            
        },
        props: {
            title: {
                required: false,
                default: ''
            },
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
            },
            working: {
                required: false,
                default: false
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
                height: 'auto',
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
