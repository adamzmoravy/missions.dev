<template>

    <ui-expanded-menu-item 
        @selected="$router.push('/' + $lang + '/group/' + group.happening_id + '/')" 
        class="position-relative">

        <template #icon>

            <profile-image-simple :profile="commenter" style="width:32px;" :width="32" :height="32" />

        </template>

        <template #label :hello="'jon'">

             <div>
                <strong>{{ commenter.first_name }} {{ commenter.last_name }} </strong> <template v-if="group">posted in <strong>{{group.name}}</strong></template>
                <div><timeago class="small" :datetime="event.occurred_on_timestamp" :auto-update="60"></timeago></div>
            
            </div>

        </template>

    </ui-expanded-menu-item>


</template>

<style scoped>

</style>

<script>
    export default {
        name: 'events-comment-happening',
        props: {
            event: {
                required: true
            }
        },
        computed: {
            group: function () {
                return this.$stores.groups.getGroupPreviewByUUID(this.event.data.topic_id)
            },
            commenter: function () {
                return this.$stores.profiles.getProfilePreviewById(this.event.data.commenter.user_id)

            }
        }
    }
</script>