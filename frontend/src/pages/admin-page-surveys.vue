<template>

    <div class="container">
        <h1 class="my-5">{{ surveyName }}</h1>
        <div v-for="response in responsesReversed">
            <div v-for="question in response.questions">
                <div v-for="step in question">
                    <h2>#{{ response.survey_results_id }}</h2>
                    <h3 class="mb-4">{{ step.title }}</h3>
                    <div v-for="question in step.questions">
                        <strong class="medium">{{ question.label }}</strong>
                        <p class="medium answer">{{ response.answers[question.name] }}</p>
                    </div>
                </div>
            </div>
            <hr class="my-5">
        </div>
    </div>

</template>

<style scoped>

    .answer {
        margin-top:1rem;
        background-color: white;
        padding:1rem;
        margin-bottom: 1rem;
        white-space: pre-line;
    }
</style>

<script>


export default {
    name: 'AdminPageSurveys',
    data: function() {
        return {
            responses: []
        }
    },
    computed: {
        responsesReversed: function() {
            // order array this.responses by survey_results_id
            return this.responses.slice().sort((a, b) => b.survey_results_id - a.survey_results_id)
            
        },
        surveyName: function () {
            return this.$route.params.surveyName
        },
        isUserAdmin: function() {
            if (this.isUserAuthenticated && this.$stores.auth.user_type == 'admin') {
                return true
            }
            return false
        }
    },
    methods: {
        setup: function() {
            this.backend.get('/survey_results/' + this.surveyName + '/').then(response => {
                this.responses = response.data.results
            })
        }
    },
    mounted: function() {
        this.setup()
    }

}

</script>