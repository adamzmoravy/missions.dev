<template>

    <template v-if="contactLoaded">
        <p class="medium mb-1">
    
            {{ $trans('Phone number') }}
        </p>
        <ui-input-phone-number v-model="contact.phone_number" :valid-only="true" :placeholder="$trans('Enter your phone number')" class="mt-2" />

        <div class="services-select mt-2">

            <label v-for="service in servicesOptions" :key="service" class="mr-2 small"><input type="checkbox" :value="service" v-model="contact.services" /> {{ service }}</label>
        </div>
            <p class="medium mb-1 mt-2">
            {{ $trans('Contact email address') }}   
        </p>
        <ui-input-email v-model="contact.email_address" :placeholder="$trans('Enter your email address')" class="mt-2 mb-4" />

        <ui-button v-if="!saveOnDestroy" @click="saveContact()" :label="$trans(updateButtonLabel)"></ui-button>

    </template>

</template>

<style scoped>

    label {
            cursor: pointer;
    }
    .services-select {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    
</style>

<script>

import backend from '../../backend.js'

export default {
    name: 'profile-contact-edit',
    data() {
        return {
            contactLoaded: false,
            contact: {
                phone_number: null,
                services: [],
                email_address: null
            }
        }
    },
    components: {
    },
    props: {
        profile: {
            type: Object,
            required: true
        },
        saveOnDestroy: {
            type: Boolean,
            default: false
        },
        updateButtonLabel: {
            type: String,
            default: 'Save'
        }
    },
    computed: {
        servicesOptions: function () {
            return ['WhatsApp', 'Telegram', 'Signal']
        }
    },
    methods: {
        saveContact: function () {
            backend.post('/users/me/profile/contact_information', {
                contact_information: this.contact,
            }).then(response => {
                this.$emit('saved')
            })
        }
    },
    mounted () {
        backend.get('/users/' + this.profile.user_id + '/profile/contact_information').then(response => {
            if (response.data.contact_information)
            this.contact = response.data.contact_information
        }).then(() => {
            this.contactLoaded = true
        })
    },
    beforeUnmount () {
        if (this.saveOnDestroy) {
            this.saveContact()
        }
    },    
}


</script>