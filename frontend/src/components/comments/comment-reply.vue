<template>
    <div class="mb-4 d-flex" :class="{'comment-still-posting': !comment.comment_id}" style="position:relative;">
        <div class="align-top" style="display:inline-block;position:relative;">
            <div style="width:50px;height:50px;">
                <ui-a :href=" '/' + 'profile' + '/' + comment.commenter.user_id + '/'">
                    <profile-image-simple :width="40" :height="40" :profile="commenter" style="margin-right:10px;"/>
                </ui-a>
            </div>

        </div>
        <div class="flex-grow-1" style="overflow:hidden;">
            <div >
                <h4 class="comment-name medium mb-0"><strong>
                    <ui-a :href="'/' + 'profile' + '/' + comment.commenter.user_id + '/'">{{ commenter.first_name}} {{ commenter.last_name}}</ui-a>
                </strong></h4>
                <div class="comment-time mb-2" style="font-size:80%;"><timeago :datetime="comment.posted_time" :auto-update="60"></timeago></div>

                <div style="max-width:100%;" v-if="comment.format == 'tiptap-json' && typeof comment.value === 'object'">

                    <comment-editor :type="'reply'" :editable="false" v-model="comment.value"/>                

                </div>
                <p v-else style="white-space: pre-line;" class="whitespace-pre comment-text">
                
                
                    <ui-read-more more-str="See more" :text="comment.value" link="#" less-str="See less" :max-chars="300" />

                </p>
                
                <div class="react-container mt-2" v-if="comment.reactions">
                    <reactions v-if="isUserAuthenticated" 
                        mode="subtle" 
                        :reactionTypes="$ui.commentReactions" 
                        :comment-id="comment.comment_id" 
                        :current-user-id="$currentUser.user_id" 
                        :reactions-from-comment="comment.reactions" />
                </div>


                <div class="comment-menu">
                    <ui-context-menu v-if="removable" :options="[
                        {label: $trans('Remove'), action: 'remove'}

                        ]" 
                        @remove="removeComment"
                    
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>


</style>

<script>
import commentEditor from '../editor/comment-editor.vue'
import backend from '../../backend'
import reactions from '../reactions.vue'
export default {
    props: {
        reply: {
            type: Object,
            required: true
        },
        removable: {
            type: Boolean,
            default: false
        }
    },
    methods: {
        removeComment: function () {
            let commentId = this.reply.comment_id
                if (confirm('Are you sure you want to remove this comment?')) {
                    backend.delete('/comments/' + commentId + '/').then((response) => {
                        
                    }, () => {
                        var message = this.getErrorMessage(this, {
                            data: {
                                status: 'error_commenting',
                                message: 'Error removing comment'
                            }
                        })
                        this.errorCommentingNotification({
                            message: message,
                            timeout: 10000,
                            type: 'error'
                        })
                    })
                }
        }
    },
    components: {
        commentEditor,
        reactions
    },
    computed: {
        comment: function () {
            return this.reply
        },
        commenter: function () {
                return this.$stores.profiles.getProfilePreviewById(this.comment.commenter.user_id)
        },
    },
    mounted: function () {
    }
}

</script>