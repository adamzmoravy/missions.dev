<template>

    <ui-modal :title="$trans('Create new post')" size="tall" @close="close()" :open="isOpen">
    
    
        <template #body>

            <div class="row w-100" v-if="groupId == null">
                <div class="col-md-12">
                    <h3 class="medium-2 pb-2">
                        {{$trans('Select a group to post in')}}
                    </h3>
                    <entities @selected="selected" :include-link="false" :title="$trans('Groups')" :entities="myGroups" />                    


               </div>
            </div>

            <div class="row w-100" v-else>
                <div class="col-md-12">
                    <h3 class="medium-2 pb-0">
                        Post in <ui-a class="underline" :href="'/group/' + selectedGroup.happening_id">{{ selectedGroup.name }}</ui-a>
                    </h3>
                        <!--
                            <profile-index class="d-flex flex-wrap align-content-center profiles-container w-100"
                                :limit="20"
                                :user-ids="selectedGroup.registrations.join()"
                                :profile-size="30"
                                :show-name="false"
                                :stack-profiles="false"
                                :max-profiles-display="5"
                                style="pointer-events: none;"
                            />
                        -->
                    <comment-topic
                        @new-comment-posted="close()"
                        class="comments mt-2"
                        :post-format="'tiptap-json'"
                        :fetch-comments-url="'/happenings/' + selectedGroup.happening_id + '/topic/'"
                        :post-reply-url="'/happenings/' + selectedGroup.happening_id + '/comment/'"
                        :post-comment-url="'/happenings/' + selectedGroup.happening_id + '/topic/'"
                        :topic="selectedGroup.uuid"
                        :with-subject="true"
                        :offer-hats="false"
                        :calendar-event-choice="true"
                        :email-announcement-choice="true"
                        :post-explainer="$trans('post_explainer')"
                        :show-hats-on-start="false"
                        :add-comment-editor-open="true"
                        :show-comments="false"
                        @cancel-posting="cancelPosting"
                        />
                    
                </div>
            </div>


        </template>




    </ui-modal>

</template>

<style scoped>


</style>

<script>
import commentTopic from '../comments/comment-topic.vue'

export default {
    name: 'group-post-create',
    data: function () {
        return {
            groupId: null
        }
    },
    components: {
        commentTopic
    },
    computed: {
        isOpen: function () {
            return this.$stores.ui.newPost
        },
        selectedGroup: function () {
            return this.$stores.groups.getGroupPreview(this.groupId)
        },
        myGroups: function () {
            return this.$stores.groups.getEntities(
                [{
                    sorter: 'registrations_count',
                    direction: 'asc'
                }],
                [{
                selection: 'member',
                value: this.$stores.auth.userData.user_id
            }])
        }
    },
    methods: {
        cancelPosting: function () {
            this.groupId = null
            this.close()
        },
        selected: function (entity) {
            this.groupId = entity.happening_id
         },
        close: function () {
            this.$stores.ui.newPost = null
        },
    }
}

</script>