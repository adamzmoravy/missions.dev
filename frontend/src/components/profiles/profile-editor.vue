<template>

    <div>

                <div class="form-group">
                    <label>{{ $trans('First name') }}</label>
                    <div class="input-group" style="width:100%">
                        <input ref="profileInputRef" type="text" name="first_name" class="form-control" :placeholder="$trans('First name')" v-model="profile.first_name" >
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ $trans('Last name') }}</label>
                    <div class="input-group" style="width:100%">
                        <input type="text" class="form-control" :placeholder="$trans('Last name')" name="last_name" v-model="profile.last_name" >
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ $trans('About me') }}</label>
                    <div class="input-group" style="width:100%;">
                        <textarea class="form-control" name="short_description" v-model="profile.short_description"  rows="5" :maxlength="$ui.submissions.shortDescriptionMaxLength"></textarea>
                    </div>
                </div>

                <ui-button @click="saveProfile()" type="submit" class="" :disabled="!isProfileModified">
                    {{ $trans('Update') }} 
                </ui-button>
    </div>
    

</template>

<script>

import backend from '../../backend.js'
export default {
    name: 'profile-editor',
    data() {
        return {
            profile: null
        }
    },
    computed: {
        isProfileModified() {
            // if this.profile object is not same as this.originalProfile object, then it is modified
            return JSON.stringify(this.profile) != JSON.stringify(this.originalProfile)
        }
    },
    props: {
        originalProfile: {
            type: Object
        }
    },
    created: function() {
        // copy this.profile object from this.originalProfile object
        this.profile = JSON.parse(JSON.stringify(this.originalProfile))
    },
    methods: {
        saveProfile: function () {
            backend.patch('/users/me/profile', {
                short_description: this.profile.short_description,
                first_name: this.profile.first_name,
                last_name: this.profile.last_name
            }).then(response => {
                this.$emit('saved')
            })
        }
    }
}

</script>