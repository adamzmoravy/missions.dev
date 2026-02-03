<style scoped>
.color-explainer-under-hat {
    font-size:10px;
}
</style>

<template>

    <ul style="list-style-type: none;" class="col-lg-12 px-0" ref="comments">
        
        <li class="pb-4 mb-2 bg-white comment-container" v-if="addCommentEditorOpen && state!='waiting'">
            <div class="comment-container-padding">
                <vue-horizontal ref="horizontal" class="mt-2" style="max-height:450px;">
                    <div class="w-100" v-for="image in newPostImages" :key="image.filename" >
                        <ui-img-dynamic :img-style="{height: '100%', width: '100%', objectFit: 'contain'}"  :heightRatio="null" :src="image.filename"/>
                    </div>
                </vue-horizontal>


                <p v-if="offerHats" class="color-explainer small" :class="hat">{{colorToExplainer(hat)}}&nbsp;</p>

                <ui-textarea v-if="postFormat == 'plaintext'" :placeholder="postExplainer" style="width:100%;" :min-height="70" :max-height="350" :class="hat" class="comment-textarea form-control form-control-lg" rows="4" :disabled="state == 'posting' || !isUserAuthenticated" v-model="next_comment_value" />
                <comment-editor v-else :editable="true" :placeholder="postExplainer" :disabled="state == 'posting' || !isUserAuthenticated" :class="hat" v-model="next_comment_value"/>


                <div v-if="isUserAuthenticated">
                    <div class="d-flex justify-content-end w-100 pt-2">
                        <ui-image-upload @uploaded="addImage" :button-label="$trans('Image')" size="sm"  />
                        <ui-button @click="ui.selectSubmissions = true" size="sm" class="ml-2">{{ $trans('Submissions') }}</ui-button>
                    </div>
                </div>


                <entities 
                    @close="ui.selectSubmissions = false" 
                    v-if="ui.selectSubmissions" 
                    :multi-select="true" 
                    :modal-only="true"                                     
                    :show-search-bar="true" 
                    @selected="addSubmissionToNewComment" 
                    @unselected="removeSubmissionToNewComment" 
                    :multi-select-selected="next_comment_submissions" 
                    :include-link="false" 
                    :sub-title="$trans('Select submissions to include')"
                    :title="$trans('Submissions to share')" 
                    :entities="allSubmissionsEntities" 
                    :limit="5000" />

                <p class="pl-1 mb-3 d-flex align-items-center" v-if="emailAnnouncementChoice">
                    <input type="checkbox" class="" v-model="emailNotification" id="emailNotification"> <label for="emailNotification" class="mb-0 ml-2">{{ $trans('send_via_email') }}</label>
                </p>
                <p class="pl-3 d-flex align-items-center" v-if="withSubject && emailNotification">

                    <input type="text" :class="hat" class="mt-0 form-control comment-textarea" v-model="subject" placeholder="E-mail subject">
                </p>

                <p class="pl-3 d-flex align-items-center" v-if="emailNotification && calendarEventChoice">
                    <input type="checkbox" class="" v-model="includeCalendarInvitation" id="calendarInvitation"> <label for="calendarInvitation" class="mb-0 ml-2">Include Calendar invitation.</label>
                    <div class="pl-5" v-if="includeCalendarInvitation">

                        <p>
                            <calendar-event-picker @event-update="calendarEvent = $event"></calendar-event-picker>
                        </p>
                    
                    </div>
                </p>

                <vue-horizontal class="my-3 mx-3 " v-if="next_comment_submissions.length > 0">
                    <template v-for="submissionId in next_comment_submissions">
                        <submission-preview-feed class="w-100" :experiment="$stores.submissions.getSubmissionPreview(submissionId)" />
                    </template>
                </vue-horizontal>

                <div class="d-lg-flex d-none align-content-center justify-content-between w-100 mt-2">
                    <div v-if="isUserAuthenticated">
                        
                        <ui-button :button-type="'action'" size="lg" :working="state=='posting'" :label="$trans('Post')" :disabled="state == 'posting' || next_comment_value==''" @click="postCommentJson">
                        </ui-button>
                        <ui-button v-if="offerCancel" :button-type="'cancel'" class="ml-2" :disabled="state == 'posting'" @click="cancelPosting">{{$trans('Cancel')}}</ui-button>
                    </div>

                    <div v-if="offerHats && isUserAuthenticated" class="d-flex align-content-end">
                        <div v-for="(hat, i) in ui.hats" @click="setHat(hat.color)" class="hat-selection" style="text-align:center;">

                            <div style="height:60%;margin:auto;" :class="hat.color + '-hat-bg'" class="hat"></div>
                            <span class="color-explainer color-explainer-under-hat" :class="hat.color">{{$trans(hat.meaning)}}</span>

                        </div>

                    </div>
                </div>

                <div class="d-inline-block d-lg-none align-content-center align-items-center w-100 mt-2">

                    <div v-if="offerHats && isUserAuthenticated" class="d-flex w-100 flex-wrap align-content-end">
                        <div v-for="(hat, i) in ui.hats" @click="setHat(hat.color)" class="hat-selection" style="text-align:center;">
                            <div style="height:60%;margin:auto;" :class="hat.color + '-hat-bg'" class="hat"></div>
                            <span class="color-explainer small" :class="hat.color">{{$trans(hat.meaning)}}</span>
                        </div>
                    </div>
                    <div v-if="isUserAuthenticated">

                        <ui-button :disabled="state == 'posting' || next_comment_value==''" @click="postComment" button-type="new"><div v-if="state=='posting'" class="lds-ellipsis align-center"><div></div><div></div><div></div><div></div></div><span v-if="state!='posting'">{{$trans('Post')}}</span></ui-button>

                    </div>
                </div>


                <div v-if="!isUserAuthenticated" class="pb-4 mt-0 px-2">
                <div style="font-size:120%">{{$trans('To participate in the discussion, you must first')}} <a @click="$stores.ui.modal = 'login-modal'">{{$trans('log in')}}</a> {{$trans('or')}} <ui-a :href="'/onboarding/'">{{$trans('register')}}</ui-a>.</div>
                </div>
            </div>
        </li>

        <template v-if="showComments">
            <div class="col-md-12 d-none">
                <div class="mt-3">
                    <span class="ml-3 pull-right"><a for="sort_1" class="selectable-element selected-element">Most recent</a></span>
                    <span class="ml-3 pull-right"><a for="sort_0" class="selectable-element">Upvoted</a></span>
                </div>
            </div>
            <div class="col-md-12 pb-2" v-if="numHiddenComments !== 0">
                <a @click="viewMoreComments" class="mb-4">View {{numHiddenComments}} previous comments</a>
            </div>
            <div v-if="sortedAndFilteredComments.length === 0 && showEmptyMessage" class="comment-wrapper">
                <div class="text-center py-4">
                    <p class="large mb-1">No one has posted annything here yet. </p><p class="large">Do you want to be the first one?</p>
                </div>

            </div>


            <div class="mt-0 py-2 comment-wrapper" v-for="(comment, index) in sortedAndFilteredComments" :key="comment.comment_id">

                <portal-target v-if="index === 1 || index === 2" :name="'comment-' + topic + '-' + index">
                </portal-target>
    

                <comment-single
                    post-format="tiptap-json"
                    :allow-replies="allowReplies" 
                    :comment="comment" 
                    :language="language"
                    :post-reply-url="postReplyUrl"
                    @remove-comment="removeComment" 
                />


            </div>
            <div class="mt-0 py-2 comment-wrapper" v-for="item in 3" v-if="loadingComments && sortedAndFilteredComments.length > 5">
                <div class="comment-container p-y">
                    <div class="comment-container-inner py-5 my-5">
                        <div class="text-center py-5 my-5">
                            <p class="large mb-1">&nbsp;&nbsp;&nbsp;</p>
                        </div>
                    </div>
                </div>
            </div>

        </template>
        
    </ul>



</template>

<style scoped>

.comment-textarea {
    border-style:none;
    padding:0;
    margin:0;
    font-size: 1.2rem;
    line-height: 1.5;
    font-family: 'Open Sans', sans-serif;
    border-radius: 0.25rem;
    border: 1px solid #e0e0e0;
    color: #333;
    resize: none;
    overflow: hidden;
    transition: height 0.2s;
    width: 100%;
    padding: 0.7rem;
    margin:0;
}

.modal-content .comment-container {
    border-style:none;
    padding:0;
}

.clicklable {
    cursor: pointer;
    
}

</style>

<script>

    import submissionPreviewFeed from '../submissions/submission-preview-feed.vue'
    import backend from '../../backend.js'
    import commentEditor from '../editor/comment-editor.vue'
    import commentSingle from './comment-single.vue'
    import calendarEventPicker from '../calendar/calendar-event-picker.vue'

    export default {
        name: 'comment-topic',
        data() {
            return {
                ui: {
                    selectSubmissions: false,
                    showHats: false,
                    hats: [{'color': 'black', 'meaning': 'Critical'}, {'color': 'white', 'meaning': 'Information'}, {'color': 'red', 'meaning': 'Feelings'}, {'color': 'green', 'meaning': 'New ideas'}, {'color': 'blue', 'meaning': 'Planning'}, {'color': 'yellow', 'meaning': 'Positive'}]
                },
                lastTimeCommentsWereLoaded: null,
                timeSinceCommentsWereLoaded: null,
                loadingComments: true,
                commentsFullyLoaded: false,
                loadingStatus: 'loading',
                showNewPostImages: false,
                newPostImages: [],
                emailNotification: false,
                includeCalendarInvitation: false,
                subject: null,
                comments: [],
                next_comment_submissions: [],
                next_comment_value: null,
                next_comment_type: '',
                state: 'ready',
                hat: null,
                timer: '',
                calendarEvent: {},
                loadMoreListener () {
                    this.loadMore()
                }
            }
        },
        emits: ['new-comment-posted', 'cancel-posting', 'comments-update', 'view-more-comments', 'posting-finished'],
        components: {
            calendarEventPicker,
            commentEditor,
            commentSingle,
            submissionPreviewFeed
        },
        props: {
            postFormat: {
                type: String,
                required: false,
                default: 'plaintext'
            },
            offerCancel: {
                type: Boolean,
                required: false,
                default: true
            },
            showComments: {
                type: Boolean,
                required: false,
                default: true
            },
            autoFetchCommentsPeriodically: {
                type: Boolean,
                required: false,
                default: true
            },
            loadMoreOnScroll: {
                type: Boolean,
                required: false,
                default: true
            },
            showEmptyMessage: {
                type: Boolean,
                required: false,
                default: false
            },
            topicComments: {
                type: Array,
                required: false,
                default: function () {
                    return []
                }
            },
            calendarEventChoice: {
                type: Boolean,
                requried: false,
                default: false
            },
            emailAnnouncementChoice: {
                type: Boolean,
                requried: false,
                default: false
            },
            withSubject: {
                type: Boolean,
                required: false,
                default: false
            },
            limit: {
                type: Number,
                required: false,
                default: 9999
            },
            order: {
                type: String,
                required: false,
                default: 'desc'
            },
            postExplainer: {
                type: String,
                required: false,
                default: ''
            },
            addCommentEditorOpen: {
                type: Boolean,
                required: false,
                default: true
            },
            allowReplies: {
                type: Boolean,
                required: false,
                default: true
            },
            showHatsOnStart: {
                type: Boolean,
                required: false,
                default: true
            },
            topic: {
                type: String,
                required: false,
                default: ''
            },
            fetchCommentsUrl: {
                required: true,
                type: String
            },
            postReplyUrl: {
                required: true,
                type: String
            },
            postCommentUrl: {
                required: true,
                type: String
            },
            apiUrl: {
                required: false,
                type: String,
                default: function () {
                    return ''
                }
            },
            offerHats: {
                required: false,
                default: true
            }
        },
        computed: {
            allSubmissionsEntities: function () {
                return this.$stores.submissions.getEntities()
            },
            language: function () {
                return this.$lang
            },
            calendarInvite: function () {
                return this.calendarEvent
            },
            numHiddenComments: function () {
                if (this.comments) {
                    if (this.comments.length < this.limit) {
                    return 0
                }
                    return this.comments.length - this.limit

                }
                return 0
            },
            sortedAndFilteredComments: function () {
                let sortedComments = this.comments
                // sort sortedComments by posted_time where the newest is first
                sortedComments.sort(function (a, b) {
                    return new Date(b.posted_time) - new Date(a.posted_time)
                })

                if (this.order === 'asc') {
                    sortedComments = sortedComments.slice().reverse()
                }
                sortedComments = sortedComments.slice(-this.limit)
                return sortedComments
            }
        },
        watch: {
            $loadMoreOnScroll: function (newVal) {
                this.loadMore()
            },
            $latestEvent: function(event) {
                let reactToEvents = ['comment-changed']
                if (reactToEvents.includes(event.eventName)) {
                    if (event.data.topic === this.topic) {
                        this.fetchSingleComment(event.data.comment_id)
                    }
                }

            },
            topic: function (newVal, oldVal) {

                this.subscribeToTopicEvents()

            },
            comments: function (newVal) {
                this.$emit('comments-update', newVal)
            },
            $route (to, from){
                this.comments = []
            },
            topicComments: function (newVal, oldVal) {
                this.comments = newVal
            }
        },
        methods: {
            addSubmissionToNewComment: function (submission) {
                this.next_comment_submissions.push(submission.experiment_id)
            },
            removeSubmissionToNewComment: function (submission) {
                let submissions = this.next_comment_submissions
                for (let i = 0; i < submissions.length; i++) {
                    if (submissions[i] === submission.experiment_id) {
                        submissions.splice(i, 1)
                        break
                    }
                }
                this.next_comment_submissions = submissions
            },
            subscribeToTopicEvents: function () {

                if (this.topic) {   
                    this.$stores.events.subscribeToChannel(this.$config.platform.platform_key + '-comments-' + this.topic)
                }

            },
            viewMoreComments: function () {
                this.$emit('view-more-comments')
            },
            setTimeSinceCommentsWereLoaded: function () {
                if (this.lastTimeCommentsWereLoaded === null) {
                    return null
                }
                this.timeSinceCommentsWereLoaded = Math.floor((new Date().getTime() - this.lastTimeCommentsWereLoaded.getTime()) / 1000)
            },
            addImage: function (image) {
                this.newPostImages.push(image.image)
                this.$refs.horizontal.refresh()
                let waitAMoment = setTimeout(
                    function () {
                        this.$refs.horizontal.scrollToIndex(this.newPostImages.length - 1, true)
                    }.bind(this), 500)
            },
            showMore: function () {
            },
            setup: function() {
                this.subscribeToTopicEvents()
                if (this.topicComments.length > 0) {
                    this.comments = this.topicComments                    
                } else {
                    this.fetchComments()
                }
                this.timer = setInterval(this.fetchCommentsIfNeeded, 20000)
                setInterval(this.setTimeSinceCommentsWereLoaded, 1000)
            },
            colorToExplainer (color) {
                let colors = {
                    'blue': this.$trans('Planning_expl'),
                    'green': this.$trans('Ideas_expl'),
                    'black': this.$trans('Critical_expl'),
                    'white': this.$trans('Information_expl'),
                    'red': this.$trans('Feelings_expl'),
                    'yellow': this.$trans('Positive_expl')
                }
                return colors[color]
            },
            setHat: function (hat) {
                this.hat = hat
                this.state = 'ready'
            },
            updatedComments: function (newComments) { 
                
                let oldComments = this.comments
                let updatedComments = newComments

                // add old comments to updated comments if commment_id is not there
                for (let i = 0; i < oldComments.length; i++) {
                    let oldComment = oldComments[i]
                    let found = false
                    for (let j = 0; j < updatedComments.length; j++) {
                        let updatedComment = updatedComments[j]
                        if (oldComment.comment_id === updatedComment.comment_id) {
                            found = true
                            break
                        }
                    }
                    if (!found) {
                        updatedComments.push(oldComment)
                    }
                }
                return updatedComments

            },
            newComment: function () {
                this.fetchComments(3, 0)
            },
            loadMore: function () {
                if (!this.commentsFullyLoaded) {
                    this.loadingComments = true
                    this.fetchCommentsRequest(5, this.comments.length).then((response) => {
                        this.loadingComments = false
                        if (response.data.comments.length === 0) {
                            this.commentsFullyLoaded = true
                        } else {
                            this.comments = this.updatedComments(response.data.comments)
                        }
                    })
                }
            },
            fetchSingleComment: function (commentId) {
                backend.get(this.fetchCommentsUrl, null, {comment_id: commentId}).then((response) => {
                    if (response.data.comments.length > 0) {
                        this.comments = this.updatedComments(response.data.comments)
                    } else {
                        this.disappearComment(commentId)
                    }
                })
            },
            disappearComment: function (commentId) {
                let comments = this.comments
                for (let i = 0; i < comments.length; i++) {
                    let comment = comments[i]
                    if (comment.comment_id === commentId) {
                        comments.splice(i, 1)
                        break
                    }
                }
                this.comments = comments
            },
            fetchCommentsRequest: function (limit = 40, offset = 0) {
                return backend.get(this.fetchCommentsUrl, null, 
                    { 
                        limit: limit,
                        offset: offset 
                    }
                )
            },
            fetchCommentsIfNeeded: function () {
                if (this.lastTimeCommentsWereLoaded === null) {
                    this.fetchComments()
                    return true
                }
                let timeSinceLastFetch = Math.floor((new Date().getTime() - this.lastTimeCommentsWereLoaded.getTime()) / 1000)

                if (timeSinceLastFetch > 30 && this.autoFetchCommentsPeriodically) {
                    this.fetchComments(10)
                }

            },
            fetchComments: function (limit = 10, offset = 0) {
                this.fetchCommentsRequest(limit, offset).then((response) => {
                    this.loadingComments = false
                    this.comments = this.updatedComments(response.data.comments)
                    this.lastTimeCommentsWereLoaded = new Date()

                })
            },
            cancelPosting: function () {
                this.state = 'ready'
                this.$emit('cancel-posting')
            },
            getTeamPicUrl: function (placeholderImage, image, h, w, pixelate = 10) {
                if (image) {
                    var picUrl = 'https://d34m5sods6fnic.cloudfront.net/image/fetch/c_thumb,h_' + h + ',w_' + w + ',e_pixelate:' + pixelate + '/' + image
                    return picUrl
                } else {
                    return placeholderImage
                }
            },
            upvoteComment: function (commentId) {
                this.$http.post(this.apiUrl + '/comments/' + commentId + '/upvote/', {}, {
                    headers: {
                        'Authorization': this.getAuthorizationHeaderMixin()
                    }
                }).then((response) => {
                    this.fetchComments()
                }, () => {
                    var message = this.getErrorMessage(this, {
                        data: {
                            status: 'error_upvoting',
                            message: 'Error upvoting comment'
                        }
                    })
                    this.errorCommentingNotification({
                        message: message,
                        timeout: 10000,
                        type: 'error'
                    })
                })
            },
            downvoteComment: function (commentId) {
                backend.delete('/comments/' + commentId + '/upvote/').then((response) => {
                    this.fetchComments()
                }, () => {
                    var message = this.getErrorMessage(this, {
                        data: {
                            status: 'error_upvoting',
                            message: 'Error upvoting comment'
                        }
                    })
                    this.errorCommentingNotification({
                        message: message,
                        timeout: 10000,
                        type: 'error'
                    })
                })
            },
            removeComment: function (commentId) {
                // remove from this.comments where comment_id === commentId
                let comments = this.comments
                for (let i = 0; i < comments.length; i++) {
                    let comment = comments[i]
                    if (comment.comment_id === commentId) {
                        comments.splice(i, 1)
                        break
                    }
                }
                this.comments = comments
                this.fetchComments()

            },
            postReply: function (replyValue = 'some comment', commentId) {
                this.$http.post(this.apiUrl + this.postReplyUrl + commentId + '/reply/', {
                    value: replyValue,
                    type: 'reply'
                }, {
                    headers: {
                        'Authorization': this.getAuthorizationHeaderMixin()
                    }
                }).then((response) => {
                    this.fetchSingleComment(commentId)
                }, () => {
                    var message = this.getErrorMessage(this, {
                        data: {
                            status: 'error_commenting',
                            message: 'Error adding comment'
                        }
                    })
                    this.errorCommentingNotification({
                        message: message,
                        timeout: 10000,
                        type: 'error'
                    })
                })
            },
            postComment: function () {
                if (this.postFormat === 'tiptap-json') {
                    this.postCommentJson()
                } else {
                    this.postCommentPlaintext()
                }
            },
            postCommentJson: function () {
                this.state = 'posting'
                backend.post(this.postCommentUrl, {
                    value: this.next_comment_value,
                    type: this.hat,
                    images: this.newPostImages,     
                    subject: this.subject,
                    submissions: this.next_comment_submissions,
                    email_announcement: this.emailNotification,
                    calendar_invite: this.calendarInvite,
                    include_calendar_invitation: this.includeCalendarInvitation,
                    format: 'tiptap-json'
                }).then(response => {
                    this.fetchSingleComment(response.data.comment.comment_id)
                    this.newPostImages = []
                    this.next_comment_value = null
                    this.state = 'ready'
                    this.$emit('new-comment-posted', response.data.comment)
                    this.include_calendar_invitation = false
                    this.subject = null
                    this.$emit('posting-finished')
                    this.next_comment_value = null
                    this.type = null

                }).catch(error => {
                    console.log('error')
                    console.log(error)
                    this.state = 'ready'

                })
            },
            postCommentPlaintext: function () {
                this.state = 'posting'
                this.$http.post(this.apiUrl + this.postCommentUrl, {
                    value: this.next_comment_value,
                    type: this.hat,
                    images: this.newPostImages,
                    subject: this.subject,
                    email_announcement: this.emailNotification,
                    calendar_invite: this.calendarInvite,
                    include_calendar_invitation: this.includeCalendarInvitation,
                    format: 'plaintext'                    
                }, {
                    headers: {
                        'Authorization': this.getAuthorizationHeaderMixin()
                    }
                }).then((response) => {
                    this.fetchSingleComment(response.data.comment.comment_id)
                    this.newPostImages = []
                    this.next_comment_value = ''
                    this.state = 'ready'
                    this.$emit('new-comment-posted', response.data.comment)
                }, () => {
                    var message = this.getErrorMessage(this, {
                        data: {
                            status: 'error_commenting',
                            message: 'Error adding comment'
                        }
                    })
                    this.errorCommentingNotification({
                        message: message,
                        timeout: 10000,
                        type: 'error'
                    })
                    this.state = 'ready'
                })
            }
        },
        beforeUnmount () {
            clearInterval(this.timer)
        },
        created: function () {
            if (this.loadMoreOnScroll) {
//                this.$root.eventBus.$on('load-more', this.loadMoreListener.bind(this))
            }
        },
        activated () {
            this.setup()
        },

        mounted: function() {

            this.ui.showHats = this.showHatsOnStart
            this.setup()
        
        }
    }
</script>
