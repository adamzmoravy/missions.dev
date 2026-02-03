<template>

    <div class="container">
        <div class="row">

            <div class="col-md-12 mt-4">
                    <ui-steps pt-3 :stepsStyle="'yellow'" @finished="$emit('finished')" class="wizard-steps pt-4" v-slot:default="slotProps" :stages="stages" @stage-changed="stageChanged">
                    
                        <div class="mt-3">
                            
                            <div v-if="slotProps.stageName == 'profile_picture'">
                                <p class="medium-2 mb-1">
                                    <strong>{{$trans('Profile picture')}}</strong>
                                </p>
                                <p class="medium-2 pb-3">
    
                                    <span v-if="role == 'user'">{{$trans('Profile_wizard_1')}}</span>
                                    <span v-if="role == 'mentor'">{{$trans('Profile_wizard_1_mentor')}}</span>

                                    <div class="row">
                                        <profile profile-id="me" v-slot:default="profile" class="col-md-12">

                                            <profile-image-upload @uploading="profile.profileData.image_collection.profile.status = 'loading'" @uploaded="profile.getProfile" :profile="profile.profileData"></profile-image-upload>

                                        </profile>

                                    </div>

                                </p>
                            </div>

                            <div v-if="slotProps.stageName == 'profile_tags_1'" class="pb-3">

                                <hr class="my-4">

                                <p class="medium-2 mb-1">
                                    What degrees have you completed?
                                </p>
                                <p class="medium">Select all that apply</p>

                                <profile-tags-edit class="mb-4" :profile="profile.profileData" :tag-types="['degrees']" tag-type-set="degrees"></profile-tags-edit>



                                <hr class="my-4">

                                <p class="medium-2 mb-1 mt-4">
                                    What is your current or most recent role(s)?
                                </p>
                                <p class="medium">Select all that apply</p>

                                
                                <profile-tags-edit class="mb-4" :profile="profile.profileData" :tag-types="['roles']" tag-type-set="roles"></profile-tags-edit>

                                <hr class="mt-5 mb-4">

                                <profile profile-id="me" v-slot:default="profile">

                                    <profile-meta-editor :include-meta-keys="['orcid_id']" :save-on-destroy="true" class="mt-2" :profile="profile.profileData" :platform-meta-definitions="$config.platform.platform_meta_definitions" @saved="profile.getProfile" :original-profile="profile.profileData"/>

                                </profile>


                            </div>


                            <div v-if="slotProps.stageName == 'profile_tags_2'" class="pb-3">
                                 <hr class="my-4">

                                <p class="medium-2 mb-1">
                                    What population(s) do you primarily work with? 
                                </p>
                                <p class="medium">Select all that apply</p>

                                
                                <profile-tags-edit :profile="profile.profileData" :tag-types="['populations']" tag-type-set="populations"></profile-tags-edit>


                                 <hr class="my-4">

                                <p class="medium-2 mb-1 mt-4">
                                    What environment(s) do you primarily work in?
                                </p>
                                <p class="medium">Select all that apply</p>

                                
                                <profile-tags-edit :profile="profile.profileData" :tag-types="['environments']" tag-type-set="environments"></profile-tags-edit>

                            </div>                            


                            <div v-if="slotProps.stageName == 'profile_tags_3'" class="pb-3">

                                <hr class="my-4">

                                <p class="medium-2 mb-1">
                                    Which best describes your area(s) of interest
                                </p>
                                <p class="medium">Select all that apply</p>

                                
                                <profile-tags-edit :profile="profile.profileData" :tag-types="['interests']" tag-type-set="interests"></profile-tags-edit>

                                <hr class="my-4">

                                <p class="medium-2 mb-1">
                                    Which areas of expertise, advice, or services do you wish to offer the ASTRO ACCEL Community? 
                                </p>
                                <p class="medium">Select all that apply</p>

                                
                                <profile-tags-edit :profile="profile.profileData" :tag-types="['offers']" tag-type-set="offers"></profile-tags-edit>
                                
                                <profile profile-id="me" v-slot:default="profile" class="mt-5">

                                    <profile-meta-editor :include-meta-keys="['presentation_topics']" :save-on-destroy="true" class="mt-2" :profile="profile.profileData" :platform-meta-definitions="$config.platform.platform_meta_definitions" @saved="profile.getProfile" :original-profile="profile.profileData"/>

                                </profile>                                


                            </div>          

                            <div v-if="slotProps.stageName == 'profile_locale'" class="pb-3">

                                <hr class="my-4">

                                <p class="medium-2 mb-1">
                                    Where are you based?
                                </p>
                                <p class="medium">Select closest city or town</p>


                                <profile-location-edit @saved="profile.getProfile" :profile="profile"></profile-location-edit>

                                <hr class="my-4">

                                <p class="medium-2 mb-1">
                                  What languages do you speak on an advanced level?
                                </p>
                                <p class="medium">Select all that you can converse about personal and professional topics in</p>
                                <profile-tags-edit :profile="profile.profileData" :tag-types="['languages']" tag-type-set="languages"></profile-tags-edit>



                            </div> 
                            
                            <div v-if="slotProps.stageName == 'profile_contact_links'" class="pb-5">

                                <p class="medium-2 mb-1">
                                    <strong>
                                        <span v-if="role=='user'">{{$trans('Contact information')}} (optional)</span>
                                    </strong>
                                </p>

                                <p class="medium mb-4">
                                    <span>Your contact information is visible to other registered users.</span>
                                </p>

                                <profile-contact-edit :save-on-destroy="true" :profile="$currentUser"></profile-contact-edit>

                                <hr class="mt-5 mb-3">
                                <p class="medium-2 mb-1">
                                    <strong>
                                        <span v-if="role=='user'">{{$trans('Links and profiles')}}</span>
                                    </strong>
                                </p>

                                <p class="medium mb-4">
                                    <span>Share your social media profiles and other relevant links with the community, i.e. your website, Instagram, Github, etc.</span>
                                </p>

                                <profile-links-edit :save-on-destroy="true" :profile="$currentUser"></profile-links-edit>


                            </div>


                            <div v-if="slotProps.stageName == 'profile_description'">                                    

                                <p class="medium-2 mb-1">
                                    <strong>
                                        <span v-if="role=='user'">{{$trans('What are you about?')}}</span>
                                        <span v-if="role=='mentor'">{{$trans('Profile_wizard_heading_2_mentor')}}</span>

                                    </strong>
                                </p>
                                <p class="medium-2 pb-0">
                                 <span v-if="role == 'user'">{{$trans('Connect with people by sharing what drives you')}}</span>
                                 <span v-if="role == 'mentor'">{{$trans('Profile_wizard_2_mentor')}}</span>
                                </p>
                                <profile profile-id="me" v-slot:default="profile">

                                    <profile-description-edit @saved="profile.getProfile" :profile="profile.profileData"></profile-description-edit>

                                </profile>

                            </div>


                            
                            <div v-if="slotProps.stageName == 'profile_skills'">
                            
                                <p class="medium-2 mb-2"><strong>
                                    <span v-if="role=='user'">{{$trans('Add skills and interests')}}</span>
                                    <span v-if="role=='mentor'">{{$trans('Add skills and interests mentor')}}</span>

                                </strong></p>

                                <span v-if="role=='user'">

                                    <p class="medium">{{$trans('Example')}} <i>{{$trans('programming')}}</i>, <i>{{$trans('dancing')}}</i>, <i>{{$trans('accounting')}}</i></p>

                                </span>

                                <span v-if="role=='mentor'">

                                    <p class="medium">{{$trans('Example')}} <i>{{$trans('fundraising')}}</i>, <i>{{$trans('design')}}</i>, <i>{{$trans('marketing')}}</i></p>

                                </span>

                                <profile profile-id="me" v-slot:default="profile">

                                    <profile-skills-edit @saved="profile.getProfile" :profile="profile.profileData"></profile-skills-edit>

                                </profile>

                            </div>
                            <div v-if="slotProps.stageName == 'getting_help'">
                            
                                <p class="medium-2 mb-2"><strong>{{ $trans('Getting help') }}</strong></p>
                                <p class="medium" v-html="$trans('onboarding_getting_help_explainer_guide')"></p>
                                <p class="medium" v-html="$trans('onboarding_getting_help_explainer_issues')"></p>


                            </div>
                            <div v-if="slotProps.stageName == 'official_stuff'">
                            
                                <p class="medium-2 mb-2"><strong>Official stuff</strong></p>
                                <p class="medium">{{ $trans('onboarding_official_stuff_explainer') }}</p>

                                <profile profile-id="me" v-slot:default="profile">

                            
                                    <profile-meta-editor :save-on-destroy="true" class="mt-2" :profile="profile.profileData" :platform-meta-definitions="$config.platform.platform_meta_definitions" @saved="profile.getProfile" :original-profile="profile.profileData"/>

                            
                                </profile>

                            </div>
                            <div v-if="slotProps.stageName == 'choose_organisation'">

                                <p class="large">{{ $trans('Select the organisation you belong to') }}</p>

                                <div class="submission-join">

                                    <submission-join :stages="[8]" />

                                </div>


                        </div>
                        </div>


                    </ui-steps>
            </div>

        </div>

    </div>

</template>

<style scoped>
    .wizard-steps {
        max-width:600px;
        margin-left: auto;
        margin-right: auto;
    }
    .submission-join {
        max-height:400px;
        overflow-y:auto;
    }

</style>

<script setup>
    import { storeToRefs } from 'pinia'
    import ProfileSkillsEdit from './profile-skills-edit.vue'
    import ProfileImageUpload from './profile-image-upload.vue'
    import ProfileDescriptionEdit from './profile-description-edit.vue'
    import ProfileMetaEditor from './profile-meta-editor.vue'
    import SubmissionJoin from '../submissions/submission-join.vue'
    import ProfileLocationEdit from './profile-location-edit.vue'
    import ProfileContactEdit from './profile-contact-edit.vue'
    import ProfileLinksEdit from './profile-links-edit.vue'
    
</script>

<script>
    import profile from './profile.vue'
    import profileTagsEdit from './profile-tags-edit.vue'
    export default {
        setup () {
            return {
            }
        },
        name: 'profile-wizard',
        data() {
            return {
            }
        },
        components: {
            profile,
            ProfileSkillsEdit,
            ProfileImageUpload,
            ProfileDescriptionEdit,
            ProfileMetaEditor,
            SubmissionJoin,
            profileTagsEdit,
            ProfileLocationEdit,
            ProfileContactEdit,
            ProfileLinksEdit
        },
        computed: {

        },
        props: {
            role: {
                required: false,
                default: 'user'
            },
            stages: {
                required: false,
                default: function () {
                    return [
                        {
                            'name': 'profile_picture'
                        },
                        {
                            'name': 'profile_description'
                        },
                        {
                            'name': 'profile_skills'
                        }
                    ]
                }
            }
        },
        methods: {
            newLocationSelected(location) {
                console.log('New location selected:', location)
            },
            fetchPlatformConfig: function() {

                this.$stores.platform.fetchConfig()

            },
            updatePlatformConfig() {

                backend.patch('/platform/', this.platformConfig).then((response) => {
                    this.fetchPlatformConfig()
                })

            },
            stageChanged (stage) {
                this.stage = stage
                window.scrollTo({ top: 0, behavior: 'smooth' })

            }
        },
        beforeDestroy () {
        },
        created: function() {

        },
        mounted: function() {


        }
    }
</script>