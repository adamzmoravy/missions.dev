<template>

    <div>

        <ui-page-header v-if="profile">

            <div class="pt-2 ml-0 pb-0 w-100">

                <div class="w-100 profile-top" style="grid-auto-columns:110px auto auto;grid-auto-flow: column;">
                    <div>
                        <profile-image-simple :profile="profile" style="width:110px;height:110px;" />
                    </div>
                    <div style="display: flex;align-items: center;" class="pl-4">
                        <div class="mt-2 pr-2">
                            <h1 class="large mb-0">{{ profile.first_name }} {{ profile.last_name }}</h1>
                            <p class="regular mt-1 mb-1" style="max-width:600px;">{{ profile.short_description }}</p>
                            <div v-show="profile.skills">
                                <span v-for="(skill, index) in profile.skills"><i>{{ skill }}</i> <span v-if="index !== profile.skills.length - 1">&nbsp;•&nbsp;&nbsp;</span>
                                </span>    
                            </div>    

                            <div v-if="$ui.profileElements.includes('submissions') && false" class="mt-2 mt-3" style="float:left;">
                                        <div style="float:left;display:flex;align-items:center;margin-right:10px;">
                                            <img src="/img/badge-challenge_50.png" style="width:40px;margin-right:8px;">
                                            <div class="regular">{{profile.experiments.filter(experiment => experiment.stage == 0).length}}</div>
                                        </div>
                                        <div style="float:left;display:flex;align-items:center;margin-right:10px;">
                                            <img src="/img/badge-idea_50.png" style="width:40px;margin-right:8px;">
                                            <div class="regular">{{profile.experiments.filter(experiment => experiment.stage == 1).length}}</div>
                                        </div>
                                        <div style="float:left;display:flex;align-items:center;margin-right:10px;">
                                            <img src="/img/badge-project_50.png" style="width:40px;margin-right:8px;">
                                            <div class="regular">{{profile.experiments.filter(experiment => experiment.stage == 2).length}}</div>
                                        </div>
                                    <template v-if="false">
                                        <div  style="float:left;display: flex;align-items:center;margin-right:15px;" v-for="color in hats" v-if="profile.comment_types && profile.comment_types[color]">
                                        <hat :color="color" style="width:35px;height:35px;"></hat> {{profile.comment_types[color]}}
                                    </div>

                                    </template>
                            </div>


                        </div>

                    </div>

                    <div style="display: flex;justify-content:right;align-items: center;">
                        <!--

                        <profile-contact :profile="profile"></profile-contact>
                    -->
                    </div>
                </div>

                <ui-tabs class="mt-4 pt-2" v-on:update:section="ui.showSection = $event" :tabs="sections" :current-selection="ui.showSection" inline-template>
                    <div>
                        <div v-for="tab in tabs" class="tab-wrapper mr-3">
                            <a :class="{'tab-link-selected': currentSelection === tab.value}" class="tab-link mr-2" @click="$emit('update:section', tab.value)">

                                <span v-if="false">
                                <img v-if="currentSelection === tab.value" src="/img/checkbox-yellow.svg">
                                    <img v-if="currentSelection !== tab.value" src="/img/checkbox.svg">
                                </span>
                                {{$trans(tab.display)}}</a>
                        </div>
                    </div>

                </ui-tabs>

            </div>


            </ui-page-header>


            <div class="container pt-3" v-show="ui.showSection == 'info'">

                <div class="row" v-if="profile">

                    <div  class="col-md-12" v-if="typesAndTags['offers'] && typesAndTags['offers'].length">
                        <h3 class="medium-2 mt-2">{{ $trans('Contact me for') }}</h3>

                            <div class="container-for-tags" style="display: inline-block;">

                                <span v-for="tag in typesAndTags['offers']" class="d-inline-flex py-2 my-1 mr-2 tag">
                                    <div>
                                        <reactions-icon class="reaction-icon-component" :mode="'selected'" :reaction="'help'" :size="'20px'" style="margin-top:2px;margin-right:8px;" />
                                    </div>
                                    <div>                                   
                                    <span class="contact-me-for-label"> 
                                        {{ $trans(tag.label) }}</span>

                                    </div>
                                </span>

                            </div>
                            
                        <hr>

                    </div>

                    <div class="col-md-8">
                        
                        <div class="d-flex justify-content-end">
                            <ui-button v-if="isEditable" @click="ui.editTags = !ui.editTags" button-type="primary" size="sm">
                                {{ $trans('Edit tags') }}
                            </ui-button>
                        </div>

                        <template v-for="tagType in $ui.profileTagTypes">
                            <div v-if="typesAndTags[tagType.type]" class="mb-3">

                                <h4 class="medium mb-2">{{ $trans(tagType.label) }}</h4>
                                <ui-tags v-if="!ui.editTags" :tags="typesAndTags[tagType.type]" :readonly="true"></ui-tags>
                                <profile-tags-edit v-else :profile="profile" :tag-type-set="tagType.type" :tag-types="[tagType.type]"></profile-tags-edit>

                            </div>

                        </template>

                    </div>

                </div>
                

            </div>


            <div class="container pt-4 pl-0" v-show="ui.showSection == 'submissions'">


                <ui-cards-container v-if="userSubmissions.length != 0">
                    <div class="row" >
                        <template v-for="submission in userSubmissions">
                            <submission-preview :experiment="submission.data" />
                        </template>
                    </div>
                </ui-cards-container>

            </div>


            <div class="container pt-4" v-show="ui.showSection == 'profile-contact'">

                    <div class="row" >
                        <div class="col-md-8">

                            <div class="d-flex justify-content-end">
                                <ui-button v-if="isEditable" class="mb-2" @click="ui.editContactInfo = !ui.editContactInfo" button-type="primary" size="sm">
                                    {{ $trans('Edit contact info') }}
                                </ui-button>

                            </div>

                            <ui-panel class="bg-white mb-4" v-if="ui.editContactInfo">

                                <p>Your contact information is visible to other registered users on this platform.</p>

                                <profile-contact-edit @saved="contactInfoSaved" v-if="profile" :profile="profile"></profile-contact-edit>

                            </ui-panel>                            

                           <profile-contact-information ref="contact-info" v-if="profile" :profile="profile"></profile-contact-information>

                        </div>
                    </div>

                    <div class="row mt-5" v-if="profile">
                        <div class="col-md-8">

                            <div class="d-flex justify-content-end">
                            <ui-button v-if="isEditable" class="mb-2" @click="ui.editLinks = !ui.editLinks" button-type="primary" size="sm">
                                {{ $trans('Edit links') }}
                            </ui-button>

                            </div>

                            <ui-panel class="bg-white mb-4" v-if="ui.editLinks">

                                <p>Share links to your websites and social profiles.</p>

                                <profile-links-edit @saved="fetchProfile" v-if="profile" :profile="profile"></profile-links-edit>

                            </ui-panel>                            

                            <ui-panel class="bg-white" v-if="this.profile.links && this.profile.links.length">

                               <profile-links v-if="profile" :profile="profile"></profile-links>

                            </ui-panel>

                        </div>
                    </div>


            </div>

            <div class="container pt-4" v-show="ui.showSection == 'edit'">

                <div class="row" v-if="profile">
                    <div class="col-md-8">
                        <h3 class="medium-2">{{ $trans('Basic info') }}</h3>

                        <ui-panel class="bg-white mb-3">
                            <profile-editor @saved="fetchProfile()" :original-profile="profile"/>
                        </ui-panel>

                    </div>
                    <div class="col-md-8">
                        <h3 class="medium-2">{{ $trans('Profile image') }}</h3>
                        <ui-panel class="bg-white mb-4 p-md-5">

                            <profile-image-upload @uploading="profile.image_collection.profile.status = 'loading'" @uploaded="fetchProfile()" :profile="profile"></profile-image-upload>

                        </ui-panel>

                        <h3 class="medium-2">{{ $trans('Skills') }}</h3>

                        <ui-panel class="bg-white mb-4 p-3 p-md-3">
                                    <p class="medium">{{$trans('Example')}} <i>{{$trans('programming')}}</i>, <i>{{$trans('dancing')}}</i>, <i>{{$trans('accounting')}}</i></p>


                                    <profile-skills-edit :profile="profile"></profile-skills-edit>



                        </ui-panel>


                        <template v-if="$config.platform.platform_meta_definitions">
                            <h3 class="medium-2">{{ $trans('Platform meta') }}</h3>
                            <ui-panel class="bg-white mb-3">
                                <profile-meta-editor class="mt-2" :profile="profile" :platform-meta-definitions="$config.platform.platform_meta_definitions" @saved="fetchProfile()" :original-profile="profile"/>
                            </ui-panel>
                        </template>

                    </div>
                </div>

            <!-- skills editor -->

            <div class="row" v-if="false">
                <div class="col">
                    <h2 class="x-large marker pt-4">Skills</h2>
                        <h6>{{ $trans('Add a skill') }}</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <input ref="profileInputSkillsRef" v-on:keyup.enter="addNewSkill" v-model="newSkill" type="text" class="form-control-lg form-lg" style="width:100%" :placeholder="$trans('Name of the skill')">
                        </div>
                        <div class="col-md-4">
                            <ui-button @click="addNewSkill" type="buttom" :disabled="!isNewSkillModified" class="float-left" :class="{success:isNewSkillModified, secondary:!isNewSkillModified}">
                               {{ $trans('Add') }}
                            </ui-button>

                        </div>
                    </div>

                    <div class="mt-4">
                        <h6>{{$trans('Present skills')}}</h6>
                    </div>
                    <div class="" style="padding:10px;max-height:15em;overflow:hidden;">
                            <span class="tag label" @click="removeSkill(index)" v-for="(skill, index) in selectedSkills" style="cursor:pointer;">
                                <i class="fi-x small"></i>
                                {{ skill }}</span>
                    </div>
                    <br/>
                    <div class="">
                        <form action="#" @submit.prevent="setSkillsForUser(apiUrl)">
                            <ui-button button-type="new" type="submit" :disabled="!areSkillsModified" :class="{success:areSkillsModified, secondary:!areSkillsModified}">
                              {{ $trans('Save') }}
                            </ui-button>
                        </form>
                    </div>
                </div>
            </div>

        </div>                

    </div>

</template>

<style scoped>
.contact-me-for-label {
    font-size: 1rem;
    font-weight: 400;
    margin-right:4px;
}
</style>

<script>
import { useAuthStore } from '../../stores/auth'
import { useProfilesStore } from '../../stores/profiles'
import { mapStores } from 'pinia'

import profileContact from './profile-contact.vue'
import profileEditor from './profile-editor.vue'
import profileMetaEditor from './profile-meta-editor.vue'
import profileImageUpload from './profile-image-upload.vue'

import profileSkillsEdit from './profile-skills-edit.vue'
import profileContactInformation from './profile-contact-information.vue'
import profileContactEdit from './profile-contact-edit.vue'
import profileLinks from './profile-links.vue'
import profileLinksEdit from './profile-links-edit.vue'
import profileTagsEdit from './profile-tags-edit.vue'
import reactionsIcon from '../reactions-icon.vue'

import backend from '../../backend.js'
import hat from '../comments/hat.vue'

export default {
    name: 'profile-of-user',
    data() {
        return {
            ui: {
                showSection: 'submissions',
                editTags: false,
                editLinks: false,
                editContactInfo: false
            },
            profile: null
        }
    },
    props: {
        profileId: {
            type: String
        }
    },
    components: {
        hat,
        profileContact,
        profileEditor,
        profileMetaEditor,
        profileImageUpload,
        profileSkillsEdit,
        profileContactInformation,
        profileContactEdit,
        profileLinks,
        profileLinksEdit,
        profileTagsEdit,
        reactionsIcon
    },
    methods: {
        contactInfoSaved() {
            this.ui.editContactInfo = false
            this.$refs['contact-info'].refreshContactInfo()
        },
        fetchProfile () {
            backend.get('/users/' + this.profileId + '/profile').then(response => {
                this.profile = response.data.profile
            })        
        }
    },
    computed: {
        typesAndTags: function () {
            return this.$stores.tags.getTypesAndTags(this.profile.tags_with_type)
        },
        userSubmissions() {
            if (this.profile) {
                return this.$stores.submissions.getEntities([{
                    sorter: 'updated',
                    direction: 'asc'
                }],
                [
                    {
                        selection: 'owner',
                        value: this.profile.user_id.toString()
                    }
                ])
            }
            return []
        },
        ...mapStores(useProfilesStore, useAuthStore),
        loggedInProfile: function () {
            return this.authStore.userData.user_id
        },
        profileIdComputed: function () {
            return this.profile.user_id
        },
        isEditable: function () {
            if (!this.profile) {
                return false
            }
            if (this.authStore.userData && this.authStore.userData.user_id == this.profile.user_id) {
                return true
            } else {
                return false
            }
        },
        sections: function () {
            let sections = []
            if (this.$ui.profileElements.includes('info')) {
                sections.push({display: 'Info', value: 'info'})
                this.ui.showSection = 'info'
            }
            if (this.$ui.profileElements.includes('submissions')) {
                sections.push({display: 'Submissions', value: 'submissions'})
            }
            if (this.$ui.profileElements.includes('profile-contact')) {
                sections.push({display: 'Contact & links', value: 'profile-contact'})
            }
            if (this.isEditable) {
                sections.splice(10, 0, {display: 'Edit profile', value: 'edit'})
            }
            return sections
        }
    },
    created() {
        this.fetchProfile()
    }
}

</script>