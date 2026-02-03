<template>

    <div class="container-fluid hexgrid-page">

    <experiment-mesh :experiments="experiments" :mesh-start-margin="3000" :active-board="$route.params.board" :mesh-start-zoom="1" >
        
    </experiment-mesh>
            



    <div class="space-container d-none d-md-flex justify-content-center" >

        <space-preview style="user-select: none;" @hover-over-space-preview="ui.hoverOverSpacePreview=true" v-for="space in spaces" :space="space" :key="space.space_id"/>

    </div>

</div>


</template>

<style scoped>

.space-container {
    margin-top:calc(100vh - 240px);


}
.space-selector {
    background-color:red;
    position:relative;
    top:calc(100vh - 240px);
    transition: 0.8s;
    transition-delay: 0.2s;
}

.spaces-container  {
    pointer-events: auto;
 
}
</style>

<script>

    import ExperimentMesh from './experiment-mesh.vue'
    import SpacePreview from './space-preview.vue'

    export default {
        name: 'meshContainer',
        data() {
            return {
                ui: {
                    hoverOverSpacePreview: false
                }
            }
        },
        components: {
            ExperimentMesh,
            SpacePreview
        },
        computed: {
            spaces: function () {
                return this.$stores.spaces.spaces
            },
            experiments: function () {
                return this.$stores.submissions.submissions
            },
            language: function () {
                return this.$lang
            },
            currentBoard: function () {
                return this.$route.params.board
            }
        }
    }

</script>