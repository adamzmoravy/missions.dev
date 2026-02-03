<template>

    <entities 
            @close="ui.relatedExperimentsModalOpen = false" 
            :multi-select="true" 
            :max-select="1"
            @selected="joinSubmission" 
            @unselected="leaveSubmission"
            :multi-select-selected="joinedSubmissions" 
            :include-link="false" 
            :show-search-bar="true"
            :entities="allSubmissionsEntities" 
            :limit="5000" />    

</template>

<script>

import backend from '../../backend.js'

export default {
    name: 'submission-join',
    data() {
        return {

        }
    },
    methods: {
        joinSubmission(data) {

            backend.post('/experiments/' + data.experiment_id + '/team/me/').then(response => {
                this.$emit('joined')
                this.$stores.auth.refreshUserData()
            })
            console.log(data)

        },
        leaveSubmission(data) {
            backend.delete('/experiments/' + data.experiment_id + '/team/' + this.$stores.auth.userData.user_id + '/').then(response => {
                this.$emit('joined')
                this.$stores.auth.refreshUserData()
            })

        }
    },
    props: {
        stages: {
            required: false,
            default: function () {
                return [
                    8
                ]
            }
        }
    },
    computed: {
        joinedSubmissions() {
            let submissonIds = []

            this.submissions.forEach(submission => {
                if (this.stages.includes(submission.stage)) {
                    submissonIds.push(submission.experiment_id)
                }
            })

            return submissonIds
        },
        submissions: function () {
            return this.$stores.auth.userData.experiments
        },
        allSubmissionsEntities() {
            let entities = this.$stores.submissions.getEntities([
                {
                    sorter: 'created',
                    direction: 'asc'
                }
            ],
            [{
                selection: 'stages',
                value: this.stages
            }])

            return entities
        }
    },
}

</script>