<template>


    <ui-expanded-menu-item 
        v-if="submission"
        @selected="$router.push('/' + $lang + '/submission/' + submission.experiment_id + '/')" 
        class="position-relative">

        <template #icon>

            <profile-image-simple :profile="submitter" :width="32"  :height="32" style="width:32px;" />

        </template>

        <template #label>

            <div>
                <strong>{{ submitter.first_name }}</strong> submitted <strong>{{ submission.title }}</strong>
                <div><timeago class="small" :datetime="event.occurred_on_timestamp" :auto-update="60"></timeago></div>

            </div>

        </template>

    </ui-expanded-menu-item>


</template>

<script>

export default {
    name: 'events-new-experiment',
    props: {
        event: {
            required: true
        }
    },
    computed: {
        submission: function () {
            return this.$stores.submissions.getSubmissionPreview(this.event.data.experiment_id)
        },
        submitter: function () {
            return this.$stores.profiles.getProfilePreviewById(this.event.actor_id)

        }
    }    
} 

</script>