<template>
    <div>
        <div class="row">
            <div class="col-md-8"
                style="display:flex;align-items:center;">
                <input v-on:keyup.enter="addNewSkill()"
                    v-model="newSkill"
                    type="text"
                    maxlength="100"
                    class="form-control-lg form-lg"
                    style="width:100%"
                    placeholder="">
            </div>
            <div class="col-md-4 pl-md-0"
                style="display:flex;align-items:center;">
                <ui-button @click="addNewSkill()"
                        type="buttom"
                        :disabled="validNewSkill"
                        class="float-left">
                    {{$trans('Add')}}</ui-button>
            </div>
        </div>
        <div class="row">
            <div class="col pt-3">
                <span class="tag" v-for="(skill, index) in localSkills" @click="removeSkill(index)">
                    {{skill}} <img src="../img/delete-icon.png" class="delete-icon">
                </span>
            </div>
        </div>

    
    </div>
</template>

<style>
.delete-icon {
    width: 15px;
    height: 15x;
    margin-left: 5px;
    margin-right:5px;
    cursor: pointer;
}
</style>

<script>
import backend from '../../backend.js'

export default {
    name: 'profile-skills-edit',
    data() {
        return {
            newSkill: '',
            localSkills: []
        }
    },
    computed: {
        validNewSkill: function () {
            return this.stripSpaces(this.newSkill).length < 1
        }
    },
    props: {
        profile: {

        }
    },
    mixins: [
    ],
    notifications: {
    },
    methods: {
        stripSpaces: function (string) {
            // strip spaces from beginning and end of string
            return string.replace(/^\s+|\s+$/g, '')
        },
        removeSkill: function (index) {
            this.localSkills.splice(index, 1)
            this.save()
        },
        addNewSkill: function () {
            let newSkills = this.newSkill.split(',')
            for (var i = 0; i < newSkills.length; i++) {
                this.localSkills.push(this.stripSpaces(newSkills[i]))
            }
            this.newSkill = ''
            this.save()
        },
        save: function () {
            let data = {
                skills: this.localSkills
            }
            backend.post('/users/me/skills/', data)
                .then(response => {
                    this.$emit('saved')
                })            
        }
    },
    watch: {
        profile: function (newProfile) {
           this.localSkills = newProfile.skills
        }
    },
    beforeDestroy () {
    },
    created: function() {
        this.localSkills = this.profile.skills
    }
}
</script>