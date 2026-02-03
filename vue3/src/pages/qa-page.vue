<template>


        <div>

            <ui-page-header>

                <div>
                    <h1 class="x-large mt-2">{{ $trans('Questions') }}</h1>
                    <h2 class="large mt-2">
                        {{ $trans('Ask the community for help with an issue you are having') }}
                    </h2>

                </div>

                <ui-tabs class="mt-3" v-on:update:section="ui.showSection = $event" :tabs="[{display: 'Questions', value: 'questions'}, {display: 'Issues', value: 'issues'}]" :current-selection="ui.showSection">
                </ui-tabs>

            </ui-page-header>

            <ui-page-body>

                <div>
                    <ui-button button-type="new" size="md" @click="$stores.ui.setNewSubmission({stage: 10})">+ {{$trans('Ask a question')}}</ui-button>
                    <ui-button class="ml-2" button-type="cancel" size="md" @click="$stores.ui.setNewSubmission({stage: 5})">+ {{$trans('Report an issue')}}</ui-button>

                </div>
            
            </ui-page-body>
            <ui-cards-container v-if="ui.showSection == 'questions'">
                <div class="row">
                    <template v-for="submission in questions">
                        <submission-preview v-if="questions.length > 0" :key="submission.data.experiment_id" :experiment="submission.data" />
                    </template>
                    <div class="col-lg-12" v-if="questions.length == 0"></div>
                </div>
            </ui-cards-container>

            <submission-index :submissions="issues" v-if="ui.showSection == 'issues'"></submission-index>

        </div>

</template>

<script>
import submissionIndex from '../components/submissions/submission-index.vue'

export default {
    name: 'qa-page',
    data() {
        return {
            ui: {
                showSection: 'questions'
            },
            showAddIssue: false
        };
    },
    computed: {
        issues() {
            let entities = this.$stores.submissions.getEntities([
                {
                    sorter: 'created',
                    direction: 'asc'
                }
            ],
            [{
                selection: 'stages',
                value: [5]
            }])

            return entities
        },
        questions() {
            let entities = this.$stores.submissions.getEntities([
                {
                    sorter: 'created',
                    direction: 'asc'
                }
            ],
            [{
                selection: 'stages',
                value: [10]
            }])
            return entities
        },
    },
    components: { submissionIndex }
}

</script>