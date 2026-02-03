
<style>

.tilted-comment-hat {
    height:50px;width:50px;position:absolute;top:-30px;left:-18px;transform: rotate(-30deg);
}
.comment-container-inner {
    display:inline-block;height:100%;width:100%;position:relative;
}
.comment-container {

}

.comment-still-posting {
    opacity: 0.5;
}
</style>

<style>

.interact-with-comment {
    position:absolute;
    right:3px;
    font-size:70%;
}

.comment-container-padding {
    padding:1em;
}
.comment-container {
    border: 1px solid #e4e6eb;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    background: #fff;
    position: relative;
    border-radius:6px;
    border-color:lightgrey;
    border-style:solid;
    border-width:0px;
}

.comment-image-containar {
    max-height:450px;
}

.comment-menu {
    position:absolute;
    right:14px;
    top: 12px;
}

</style>

<template>

    <div class="comment-container">
        <div class="comment-container-padding">


            <ui-context-menu class="comment-menu" v-if="commentCanBeRemoved" :options="[
                    {label: $trans('Remove'), action: 'remove'},
                    {label: $trans('Convert'), action: 'convert'},

                    ]" 
                    @remove="removeComment"
                    @convert="convertComment"
                
                />
                
            <comment-convert-modal v-if="showConvertModal" @closed="showConvertModal = false" :comment-id="comment.comment_id" />            
                
            <div class="comment-container-inner">

                <div class="float-right pt-1">
                    <span class="color-explainer" :class="comment.type">{{colorToSentiment(comment.type)}}</span>
                </div>

                <div class="d-flex">
                    <div style="height:60px;width:60px;">
                            <div class="hat tilted-comment-hat" :class="comment.type + '-hat-bg'"></div>        
                        <ui-a v-if="commenter.user_id" :href="'/' + 'profile' + '/' + commenter.user_id + '/'">
                            <profile-image-simple :width="50" :height="50" :profile="commenter" style="margin-right:10px;"/>
                        </ui-a>
                        <profile-image-simple v-else :width="50" :height="50" :profile="commenter" style="margin-right:10px;"/>
                    </div>
                    <div>
                        <h4 v-if="commenter.user_id" class="comment-name medium mb-0 mt-2"><strong><ui-a :href="'/' + 'profile' + '/' + commenter.user_id + '/'">{{ commenter.first_name}} {{ commenter.last_name}}</ui-a></strong></h4>
                        <h4 v-else class="comment-name medium mb-0 mt-2"> <span v-if="false">[{{$trans('User removed')}}]</span> </h4>
                        <div class="comment-time  mb-2" style="font-size:80%;"><timeago :datetime="comment.posted_time" :auto-update="60"></timeago></div>
            
                    </div>
            
                </div>
                    <vue-horizontal v-if="comment.images && comment.images.length" ref="horizontal" class="pb-3" style="max-height:450px;">
                            <div class="w-100"  @click="lightbox(i)" v-for="(image, i) in comment.images">
                                <ui-img-dynamic :img-style="{height: '100%', width: '100%', objectFit: 'contain'}" :heightRatio="null" class="clickable"  :src="image.filename" :key="image.filename" />
                            </div>
                    </vue-horizontal>
                    
                    <ui-image-lightbox v-if="lightboxImageIndex !== null" :lightbox-image-index-prop="lightboxImageIndex" @lightbox-image-index-change="changeLightBoxImage" :images="comment.images" />

                    <h2 class="medium-2 mt-0 ml-1 font-weight-normal d-none" v-if="comment.subject">{{comment.subject}}</h2>

                    <p style="white-space: pre-line;width:100%;" class="ml-1 whitespace-pre comment-text">

                    <ui-read-more v-if="comment.format == 'plaintext'" more-str="See more" :text="convertUrlsToLinks(comment.value)" link="#" less-str="See less" :max-chars="400" />
                    
                    <ui-show-more v-else-if="comment.format == 'tiptap-json'">

                        <comment-editor v-model="comment.value" />

                    </ui-show-more>

                    <span class="interact-with-comment d-none">
                        <a v-if="commentCanBeRemoved" class="remove-comment" @click="removeComment">Remove</a>
                    </span>
                </p>

                <!--
                <div v-for="attachment in comment.attachments">
                    <component class="m-4" :is="attachment.component" v-bind="attachment.params"></component>
                    <hr>
                </div>
-->
            
                <reactions v-if="isUserAuthenticated && !submissionsAttached" :reactionTypes="$ui.commentReactions" :comment-id="comment.comment_id" :current-user-id="userProfile.user_id" :reactions-from-comment="comment.reactions" />

            </div>

            <div class="mt-3" v-if="!submissionsAttached">
                <div v-if="hiddenRepliesAmount > 0" class="mb-4">
                    <a @click="showAllReplies = true" class="medium">View {{ hiddenRepliesAmount }} more replies</a>
                </div>


                <template v-for="reply in replies">
            
                    <comment-reply :reply="reply" :removable="replyCanBeRemoved(reply.commenter.user_id)" @remove-comment="removeComment"/>
            
                </template>

                <div v-if="isUserAuthenticated && allowReplies" class="mt-2 mb-2 d-flex align-content-center flex-wrap comment-reply">
                    <div style="width:50px;height:50px;">
                        <div v-if="showProfilePicture" class="">
                            <profile-image-simple :profile="userProfile" style="margin-right:10px;" />
                        </div>
                    </div>
    
                    <textarea v-if="false" v-on:focus="showPostButton = true" v-on:blur="textAreaBlurred()" :min-height="30" :max-height="200" placeholder="" class="my-auto mr-3 form-control form-control-lg" style="font-size:90%;max-width:50%;" v-model="next_comment_value"></textarea>
                    <div style="font-size:90%;width:50%;">
                        <comment-editor v-on:focus="showPostButton = true" v-on:blur="textAreaBlurred()" :type="'reply'" :editable="true" :disabled="postingReply || !isUserAuthenticated" v-model="next_comment_value"/>
                    </div>

                    <div v-if="showPostButton" class="pl-2">
                        <ui-button :working="postingReply" :label="$trans('Reply')" button-type="new" v-on:click="replyToComment" :disabled="next_comment_value == ''"></ui-button>
                    </div>
                </div>
                <div v-if="!isUserAuthenticated && allowReplies" class="pr-2 mb-3 d-flex align-content-center flex-wrap">
                    <div class="ml-auto"><a @click="$root.eventBus.$emit('show-modal', 'login-modal', true)">Reply</a></div>
                </div>

            </div>
        </div>

            <vue-horizontal class="w-100 px-4 pt-0 pb-4" v-if="typeof comment.submissions !== 'undefined' && comment.submissions.length > 0 && $stores.submissions.submissions.length">
                <template v-for="submissionId in comment.submissions">
                    <submission-feed :experiment="$stores.submissions.getSubmissionPreview(submissionId)" class="w-100" />
                </template>
            </vue-horizontal>

    </div>



</template>


<script>
    import submissionFeed from '../submissions/submission-feed.vue'
    import commentConvertModal from './comment-convert-modal.vue'
    import submissionPreviewFeed from '../submissions/submission-preview-feed.vue'
    import groupRecruitment from '../groups/group-recruitment.vue'
    import groupInvitation from '../groups/group-invitation.vue'
    import commentReply from './comment-reply.vue'
    import reactions from '../reactions.vue'
    import commentEditor from '../editor/comment-editor.vue'
    import backend from '../../backend.js'
    export default {
        name: 'comment-single',
        data() {
            return {
                showConvertModal: false,
                showAllReplies: false,
                lightboxImageIndex: null,
                next_comment_value: null,
                next_comment_type: 'reply',
                showPostButton: false,
                postingReply: false,
                postingReplies: []
            }
        },
        components: {
            submissionFeed,
            commentConvertModal,
            groupInvitation,
            commentReply,
            commentEditor,
            reactions,
            groupRecruitment,
            submissionPreviewFeed
        },
        props: {
            postFormat: {
                required: false,
                default: 'plaintext'
            },
            postReplyUrl: {
                required: true,
                type: String
            },
            showProfilePicture: {
                required: false,
                default: true
            },
            showNumReplies: {
                required: false,
                default: 3
            },
            allowReplies: {
                required: false,
                default: true
            },
            comment: {
                required: true,
                type: Object
            }
        },
        computed: {
            submissionsAttached: function () {
                if (typeof this.comment.submissions == 'undefined') {
                    return false
                } 

                if (this.comment.submissions.length > 0) {
                    return true
                } else {
                    return false

                }
            },
            userType: function () {
                return this.$currentUser.user_type
            },
            userIdNumber: function () {
                return this.userProfile.user_id
            },
            userProfile: function () {
                return this.$currentUser
            },
            hiddenRepliesAmount () {
                if (this.comment.comment_replies.length <= this.replies.length) {
                    return 0
                }
                return this.comment.comment_replies.length - this.showNumReplies
            },
            repliesAndPostingReplies: function () {
                
                let repliesAndPostingReplies = this.comment.comment_replies.concat(this.postingReplies)

                return repliesAndPostingReplies

            },
            replies: function () {

                let replies = []

                if (this.showAllReplies) {

                    replies = this.repliesAndPostingReplies

                } else if (this.repliesAndPostingReplies.length == this.showNumReplies + 1) {

                    replies = this.repliesAndPostingReplies

                } else {

                    replies = this.repliesAndPostingReplies.slice(-this.showNumReplies)

                }

                return replies

            },
            commenter: function () {
                return this.$stores.profiles.getProfilePreviewById(this.comment.commenter.user_id, true)
            },
            commentCanBeRemoved: function () {
                if (this.comment.commenter.user_id === this.userIdNumber) {
                    return true
                }
                if (this.userType === 'admin') {
                    return true
                }
                return false
            },
            commentHasUrl: function () {
                if (this.comment.urls_in_comment.length === 0) {
                    return false
                } else {
                    return true
                }
            }
        },
        watch: {
            comment: function () {
                this.postingReplies = []
            }
        },
        methods: {
            convertComment: function () {
                this.showConvertModal = true
            },
            removeComment: function () {
                let commentId = this.comment.comment_id
                if (confirm('Are you sure you want to remove this comment?')) {
                    backend.delete('/comments/' + commentId + '/').then((response) => {

                        this.$emit('remove-comment', commentId)
                        
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
            },
            changeLightBoxImage: function (event) {
                this.lightboxImageIndex = event.index
            },
            lightbox: function (index) {
                this.lightboxImageIndex = index
            },
            convertUrlsToLinks: function (text) {
                let urls = this.comment.urls_in_comment
                for (let i = 0; i < urls.length; i++) {
                    let url = urls[i]
                    let urlRegex = new RegExp(url, 'g')
                    text = text.replace(urlRegex, '<a href="' + url + '" target="_blank">' + url + '</a>')
                }
                return text
            },
            colorToSentiment (color) {
                let colors = {
                    'blue': this.$trans('Planning'),
                    'green': this.$trans('New ideas'),
                    'black': this.$trans('Critical'),
                    'white': this.$trans('Information'),
                    'red': this.$trans('Feelings'),
                    'yellow': this.$trans('Positive')
                }
                return colors[color]
            },
            replyCanBeRemoved(replierId) {
                if (replierId === this.userIdNumber) {
                    return true
                }
                if (this.userType === 'admin') {
                    return true
                }
                return false
            },
            textAreaBlurred: function () {
                if (this.next_comment_value === '') {
                    this.showPostButton = false
                }
            },
            doShowPostButton: function () {
                this.showPostButton = true
            },
            replyToComment: function () {
                if (this.postFormat === 'tiptap-json') {
                    this.postReplyJson(this.next_comment_value, this.comment.comment_id)
                } else {
                    this.postReplyPlaintext(this.next_comment_value, this.comment.comment_id)
                }
            },
            postReplyPlaintext: function (replyValue = 'some comment', commentId) {
                this.postingReply = true

                let newReply = {
                    value: replyValue,
                    commenter: this.userProfile,
                    posted_time: new Date().toISOString()
                }

                this.postingReplies.push(newReply)

                backend.post(this.postReplyUrl + commentId + '/reply/', {
                    value: replyValue,
                    type: 'reply'
                }).then((response) => {
                    this.postingReply = false
                    this.next_comment_value = ''
                    this.showPostButton = false
                }).catch((error) => {
                    this.postingReply = false
                    console.log(error)
                })
            },
            postReplyJson: function (replyValue = 'some comment', commentId) {
                this.postingReply = true

                let newReply = {
                    value: replyValue,
                    commenter: this.userProfile,
                    posted_time: new Date().toISOString(),
                    format: 'tiptap-json'
                }

                this.postingReplies.push(newReply)

                backend.post(this.postReplyUrl + commentId + '/reply/', {
                    value: replyValue,
                    type: 'reply',
                    format: 'tiptap-json'
                }).then((response) => {
                    this.postingReply = false
                    this.next_comment_value = ''
                    this.showPostButton = false
                }).catch((error) => {
                    this.postingReply = false
                    console.log(error)
                })
            }
        },
        beforeDestroy () {
        },
        mounted: function() {
//            this.$store.dispatch('getUserIfNotPresent', this.comment.commenter.user_id)
        }
    }
</script>
