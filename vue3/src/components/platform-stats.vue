<template>

    <div v-if="stats">
        <div class="d-flex justify-content-around w-100 w-md-75 py-5 my-4">
            <div v-for="stage in ['0', '1', '2']" class="text-center position-relative">
                <img :src="'/img/badge-' + $ui.stageHandles[stage] + '_100.png'" style="width:75px;" class="mx-auto">
                <div class="mt-2">
                    <div class="medium" style="text-transform:lowercase;">{{stats.experiments.stages['stage-' + stage]}} {{$trans($ui.stageHandles[stage] + '_plural')}}</div>
                </div>
            </div>
        </div>
        <div class="d-flex medium w-100 w-md-75  justify-content-center">
            {{stats.users.count}} users &nbsp; • &nbsp;  {{stats.comments.total_comments}} comments &nbsp; • &nbsp; {{stats.experiments.relationships.total}} participations
        </div>
    </div>

</template>

<script>
    import backend from '../backend.js'
    export default {
        name: 'platform-stats',
        data() {
            return {
                stats: null
            }
        },
        computed: {
            submissionsTotal: function () {

            }
        },
        props: ['apiUrl', 'scope'],
        mounted: function() {
            backend.get('/stats/' + this.scope + '/').then((response) => {
                this.stats = response.data.stats
            })
        }
    }
</script>

<style scoped>

    td {
        border-style:solid;
        width:1px;
        border-color:#dee2e6;
    }

</style>
