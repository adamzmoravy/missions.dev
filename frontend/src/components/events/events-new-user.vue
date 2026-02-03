<template>


    <ui-expanded-menu-item 
        @selected="$router.push('/' + $lang + '/profile/' + user.user_id + '/')" 
        class="position-relative">

        <template #icon>

            <profile-image-simple :profile="user" style="width:32px;" :width="32" :height="32" />

        </template>

        <template #label>

            <div>
                <strong>{{ user.first_name }} {{ user.last_name }}</strong> joined
                <div><timeago class="small" :datetime="event.occurred_on_timestamp" :auto-update="60"></timeago></div>
            </div>

        </template>

    </ui-expanded-menu-item>


</template>

<script>

export default {
    name: 'events-new-user',
    props: {
        event: {
            required: true
        }
    },
    computed: {
        user: function () {
            return this.$stores.profiles.getProfilePreviewById(this.event.data.user_id)  
        }
    }    
} 

</script>