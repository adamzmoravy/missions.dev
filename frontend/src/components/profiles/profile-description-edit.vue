<template>
    <div>
        <ui-textarea 
                    v-model="short_description"
                    :rows="4"
                    :max-length="400">
        </ui-textarea>

    </div>

</template>

<script>
import backend from '../../backend.js'
export default {
    name: 'profile-description-edit',
    data() {
        return {
            short_description: '',
        }
    },
    computed: {

    },
    props: {
        profile: {
            required: true
        }
    },
    mixins: [
    ],
    notifications: {
    },
    methods: {
        saveDescription: function () {
            backend.patch('/users/me/profile', {
                short_description: this.short_description
            }).then(response => {
                this.$emit('saved')
            })
        }
    },
    beforeUnmount () {
        this.saveDescription()
    },
    created: function() {

    },
    watch: {
        profile: function (newProfile) {
           this.short_description = newProfile.short_description
        }
    },
    mounted: function() {
        this.short_description = this.profile.short_description
    }

}
</script>