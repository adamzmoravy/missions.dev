<template>


        <ui-modal :title="happening.name" @close="close()" :open="groupPreviewId !== null">

            <template #body>
                <div class="w-100 h-100" v-if="happening">
                    <div v-if="registrations.length > 0" class="w-100">
                        <h2 class="medium-2 mb-3 ">{{$trans('Members')}}</h2>
                        <profile-index-row
                                    class="w-100 mb-4"
                                    :profiles="profiles"
                                    :profile-size="30"
                                    :max-show-num-profiles="20"
                                    :show-name="false"
                                    @show-all-profiles=""
                                    @profiles-fit=""
                                />

                    </div>


                    <ui-custom-language v-if="happening.custom_language && happening.custom_language.intro"  :custom-language="happening.custom_translations" :field="'intro'" />

                    <ui-questions v-if="isRequiredQuestions" :questions="happening.config.survey" @update="updateAnswer" />



                </div>

            </template>


            <template #footer>

                <div class="d-flex justify-content-center">

                    <ui-button v-if="!isUserRegistered" :working="ui.joining" @click="join" button-type="new">{{$trans('Join')}}</ui-button>
                    <ui-button v-else  @click="viewGroup">{{$trans('View')}}</ui-button>

                </div>

            </template>

        </ui-modal>

</template>

<script>

import { useGroup } from './group.js'
var group = useGroup()

export default {
    name: 'group-preview-modal',
    data () {
        return {
            answers: [],
            ui: {
                joining: false,
                mode: 'preview'
            }

        }
    },
    methods: {
        updateAnswer (answers) {
            this.answers = answers
        },
        viewGroup () {
            this.$router.push('/' +  this.$lang + '/group/' + this.groupPreviewId)
            this.close()
        },
        joined () {
            this.ui.joining = false
            this.viewGroup()
            this.$stores.ui.notifySuccess(this.$trans('You are now a member of this group.'))
        },
        join() {
            
            this.ui.joining = true

            let registrationInfo = null

            if (this.answers.length > 0) {
                registrationInfo = this.answers
            } 

            group.join(this.$stores.auth.userData.user_id, registrationInfo).then(() => {
                this.$stores.groups.fetchGroupPreviews().then(() => {

                    if (this.$stores.groups.isCurrentUserRegistered(this.groupPreviewId)) {

                        this.joined()

                    } else {

                        this.ui.joining = false
                    }
                })

            })
        },
        close () {
            this.$stores.groups.groupPreview = null
        }
    },
    watch: {
        groupPreviewId: function (val) {
            if (val !== null) {
                group.fetch(val)
            }
        }
    },
    computed: {
        profiles: function () {
            return this.$stores.profiles.getProfilePreviewsByIds(this.registrations)
        },
        isRequiredQuestions: function () {
            if (this.happening.config === undefined) {
                return false
            }
            return this.happening.config.survey && this.happening.config.survey.length > 0
        },
        isUserRegistered: function () {
            return false
            return group.isUserRegistered(this.$stores.auth.userData.user_id)
        },
        registrations: function () {
            if (this.happening.registrations === undefined) {
                return []
            }
            return this.happening.registrations.map(registration => registration.user_id)
        },
        happening: function () {
            return group.data.value
        },
        groupPreviewId: function () {
            return this.$stores.groups.groupPreview
        },
        groupName: function () {
            if (this.groupPreviewId === null) {
                return ''
            }
            return this.$stores.groups.getGroupPreview(this.groupPreviewId).name
        }

    }
}

</script>