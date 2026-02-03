<template>


    <ui-location-selector @location-selected="newLocationSelected" :token="$config.platform.geo_provider_key" />

    <div v-if="currentLocation" :class="{disabled: newLocation}" class="mt-2">
        <p class="mt-3">{{ currentLocation.formatted_address }}</p>
        <ui-button size="sm" button-type="remove" :working="clearing" @click="clearLocation">Clear location</ui-button>
    </div>

    
</template>


<style scoped>

    .disabled {
        opacity: 0.5;
        pointer-events: none;
    }
</style>
 

<script>

import backend from '../../backend.js'

export default {
    
    name: 'profile-location-edit',
    components: {

    },
    emits: ['saved'],
    props: {
        profile: {
            required: true
        }
    },
    data() {
        return {
            saving: false,
            clearing: false,
            newLocation: null
        }
    },
    computed: {
        currentLocation() {

            if (this.newLocation) {
                return this.newLocation
            }
            if (!this.$currentUser.geographic_location) {
                return null
            }
            return this.$currentUser.geographic_location
        },
        locationChanged() {
            if (this.newLocation && !this.currentLocation) {
                return true
            }
            return this.newLocation && this.currentLocation && this.newLocation.location.lat !== this.currentLocation.location.lat
        }
    },
    methods: {
        newLocationSelected (location) {
            if (!location) {
                return
            }
            this.newLocation = location
            this.saveLocation()
        },
        clearLocation: function () {
            this.clearing = true
            backend.patch('/users/me/profile/geographic_location', {
                geographic_location: null

            }).then(response => {
                this.$emit('saved')
                this.clearing = false
                this.newLocation = null
            })
        },
        saveLocation() {
            this.saving = true
            backend.patch('/users/me/profile/geographic_location', {
                geographic_location: this.newLocation

            }).then(response => {
                this.$emit('saved')
                this.saving = false
                this.newLocation = null
            })

        }
    }
}

</script>