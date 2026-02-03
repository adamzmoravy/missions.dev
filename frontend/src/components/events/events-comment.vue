<template>

    <ui-expanded-menu-item 
        @selected="$router.push('/' + $lang + '/submission/' + submission.experiment_id + '/')" 
        class="position-relative">

        <template #icon>

            <profile-image-simple :profile="commenter" style="width:32px;" :width="32" :height="32" />

        </template>

        <template #label :hello="'jon'">

             <div>
                <strong>{{ commenter.first_name }} {{ commenter.last_name }} </strong> commented on <strong>{{submission.title}}</strong>
                <div><timeago class="small" :datetime="event.occurred_on_timestamp" :auto-update="60"></timeago></div>
            
            </div>

        </template>

    </ui-expanded-menu-item>


</template>

<style scoped>

</style>

<script>
    export default {
        name: 'events-comment',
        props: {
            event: {
                required: true
            }
        },
        computed: {
            submission: function () {
                return this.$stores.submissions.getSubmissionPreviewByUUID(this.event.data.topic_id)
            },
            commenter: function () {
                return this.$stores.profiles.getProfilePreviewById(this.event.data.commenter.user_id)
            }
        }
    }
</script>