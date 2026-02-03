<template>

    <p>{{ $trans('Use search to find the relevant location') }}</p>

    <ui-location-selector @location-selected="newLocationSelected" :token="$config.platform.geo_provider_key" />

    <div class="mt-2">
        Saved location: 
        <template v-if="currentLocation">
            <strong>{{ currentLocation.formatted_address }}</strong>
        </template>
        <template v-else>
            <em>{{ $trans('No location set') }}</em>
        </template>

    </div>
    <div v-if="newLocation">
        New location: <strong>{{ newLocation.formatted_address }}</strong>
    </div>

    <ui-button size="sm" :disabled="!locationChanged" :working="saving" @click="saveLocation" class="mt-2">Save</ui-button>
    <ui-button size="sm" button-type="remove" :disabled="!currentLocation" :working="clearing" @click="clearLocation" class="mt-2 ml-2">Remove</ui-button>
    
</template>
 

<script>

import backend from '../../backend.js'

export default {
    
    name: 'submission-location-edit',
    components: {

    },
    props: {
        submission: {
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
            if (!this.submission.geographic_location) {
                return null
            }
            return this.submission.geographic_location
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
            this.newLocation = location
        },
        clearLocation: function () {
            this.clearing = true
            this.newLocation = null
            backend.patch('/experiments/' + this.submission.experiment_id + '/settings/', {
                settings: {
                    geographic_location: null
                }
            }).then(response => {
                this.$emit('saved')
                this.clearing = false
            })
        },
        saveLocation() {
            this.saving = true
            backend.patch('/experiments/' + this.submission.experiment_id + '/settings/', {
                settings: {
                    geographic_location: this.newLocation
                }
            }).then(response => {
                this.$emit('saved')
                this.newLocation = null
                this.saving = false
            })

        }
    }
}

</script>