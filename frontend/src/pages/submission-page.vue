<template>
    <div>
        <submission v-if="$route.params.id" :key="$route.fullPath" :submission-id="$route.params.id"></submission>
    </div>

</template>

<script>

import submission from '../components/submissions/submission.vue'

export default {
    name: 'SubmissionPage',
    data() {
        return {
            limit: 24
        }
    },
    components: {
        submission
    },
    props: {

    },
    methods: {

    }
}

</script>