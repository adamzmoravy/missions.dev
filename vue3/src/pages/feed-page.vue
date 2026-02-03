<template>

    <div>        

        <!--
        <space-index :size="200" inline-template :api-url="$apiUrl" :language="$language" class="bg-white pb-2">
            <div v-if="spaces.length > 0" class="container-fluid" >
                <div class="px-0 px-sm-2">
                    <div class="d-flex align-content-center align-items-center">
                        <elements-group :show-num="100" :group="spaces" v-slot="slotProps" class="d-block mx-auto" style="max-width:100%;">
                            <vue-horizontal v-if="slotProps.show.length > 0"  style="height:250px;">

                                <space-preview v-for="space in slotProps.show" :space="space" :key="space.space_id"/>

                            </vue-horizontal>
                        </elements-group>

                    </div>
                </div>


            </div>

        </space-index>
        -->
       <div class="container-fluid px-0 px-sm-2 pt-md-4 pt-2">
            <div class="feed-container">
                <div class="feed-left-bar d-none d-md-block">


                    <div
                        v-if="isUserAuthenticated"
                        >
                        <div>

                            <div class="row">
                                <div class="col-auto">
                                    <ui-a href="/profile/me"><profile-image-simple :profile="currentUser" style="width:60px;" /></ui-a>
                                </div>
                                <div class="col-auto px-0 d-flex align-items-center">
                                    <div>
                                            <ui-a href="/profile/me" class="d-block">{{currentUser.first_name}} {{currentUser.last_name}}</ui-a>
                                            <ui-a href="/profile/me" class="underline small d-block">{{ $trans('My profile') }}</ui-a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                        <hr>
                            
                        <h3 class="medium-2">
                            {{$trans('Groups')}}
                        </h3>
                        <entities :title="$trans('Groups')" :entities="groups" :limit="6" />


                        <hr>
                        <h3 class="medium-2 mb-2 mt-3">
                            {{$trans('Members')}}
                        </h3>
                        <div class="row mb-2">

                            <div class="col d-block">

                                <entities :title="$trans('Members')" :entities="profileEntities" :limit="8" />

                            </div>                        
                        </div>
                </div>
                
                <div class="feed-center-bar w-100 mx-0 mx-md-2">
                

                        <div class="mt-2 mt-md-2 container w-100">
                
                            <div class=" px-0 px-sm-2 w-100">

                                <div class="row pb-md-2 pb-0" v-if="isUserAuthenticated">
                                    <div class="col-md-12 mb-1">
                                        <h3 class="medium-2 mb-3">{{$trans('Something to share?')}}</h3>
                                        <ui-button class="mr-2" button-type="action" size="md" @click="$stores.ui.setNewPost()">+ {{ $trans('new_post') }}</ui-button>
                                        <ui-button class="mr-2" button-type="new" size="md" @click="$stores.ui.setNewSubmission()">+ {{ $trans('Ideation') }}</ui-button>
                                        <ui-button class="mr-2" button-type="cancel" size="md" @click="$stores.ui.setNewEvent()">+ {{ $trans('Event') }}</ui-button>

                                        <ui-button class="mr-2 dark-secret d-none" button-type="new" size="md" @click="showSecretDesireModal = true">+ {{ $trans('Secret wish') }}</ui-button>

                                        <ui-modal title="What is your secret wish?" :open="showSecretDesireModal" @close="showSecretDesireModal = false">
                                            <template v-slot:body>
                                                <div class="form-group w-100 form-lg mt-3">
                                                    <textarea class="form-control form-lg w-100" rows="13" placeholder="I wish I could..."
                                                    ></textarea>
                                                </div>
                                            </template>
                                            <template v-slot:footer>
                                                <div class="d-flex justify-content-center">
                                                    <ui-button @click="showSecretDesireModal = false" button-type="remove" size="lg">Never tell anyone</ui-button>

                                                </div>
                                            </template>
                                        </ui-modal>
                                        <ui-button v-if="false" button-type="default" size="md" @click="$stores.ui.setAction('new-submission')">+ {{ $trans('Event') }}</ui-button>
                                        <hr class="mb-0">
                                    </div>

                                </div>

                                <div class="mb-5" v-if="$ui.pinnedComponents.feed.length > 0">
                                    <template v-for="component in $ui.pinnedComponents.feed">
                                        <component :is="component.name" v-bind="component.props"></component>
                                    </template>

                                </div>


                                <div class="row mt-2 mt-md-2">
                                    <div class="w-100">

                                        <div class="px-md-3 px-0 mb-4" v-for="item in feedItems">

                                            <group-comment-feed v-if="item.type == 'comment'" :comment="item.data" :key="item.data.comment_id" />

                                            <submission-feed v-if="item.type == 'submission'" :key="item.data.experiment_id" :experiment="item.data" />

                                        </div>


                                    </div>

                                </div>
                            </div>
                        </div>
                
                </div>

                <div class="feed-right-bar d-none d-xl-block">

                    <template v-if="$stores.spaces.getEntities().length > 0">
                        <hr>
                        <h3 class="medium-2">
                            {{$trans('Spaces')}}
                        </h3>
                        <entities :title="$trans('Spaces')" :entities="$stores.spaces.getEntities()" :limit="6" />

                    </template>


                    <hr>
                    <div class="mb-3" v-if="latestUpdatedIdeation.length > 0">
                        <h3 class="medium-2">
                        {{$trans('Ideation')}}
                        </h3>
                        <entities :title="$trans('Recently updated')" :entities="latestUpdatedIdeation" :limit="6" />
                    </div>

                    <div class="mb-3" v-if="latestUpdatedGuides.length > 0">
                        <h3 class="medium-2">
                            {{$trans('Guides')}}
                        </h3>
                        <entities :title="$trans('Recently updated')" :entities="latestUpdatedGuides" :limit="3" />
                    </div>

                    <div class="mb-3" v-if="latestUpdatedIssues.length > 0">
                        <h3 class="medium-2">
                            {{$trans('Issues')}}
                        </h3>
                        <entities :title="$trans('Recently updated')" :entities="latestUpdatedIssues" :limit="3" />
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.more-profiles {
    text-align:center;
    width:38px;
    display:flex;
    justify-content: center;
    align-items: center;
    background-color:white;
}
.feed-container {
    margin-left:auto;
    margin-right:auto;
    max-width:1700px;
    width:100%;
    display:flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: flex-start;
}
.feed-left-bar {
    margin-left:40px;
    max-width:18vw;
    width:350px;
    margin-right:40px;

}

.dark-secret.btn {
    background-color:#000!important;
    color:white;

}

.feed-center-bar {
/*    margin-left:20px;
    margin-right:20px;
    */
    max-width:680px;
}
.feed-right-bar {
    max-width:18vw;
    width:400px;
    margin-right:20px;
    margin-left:30px;

}
</style>

<script>
    import backend from '../backend.js'
    import groupRecruitment from '../components/groups/group-recruitment.vue'
    import groupCommentFeed from '../components/groups/group-comment-feed.vue'
    import submissionFeed from '../components/submissions/submission-feed.vue'
    export default {
        name: "FeedPage",
        data () {
            return {
                showInFeed: 10,
                showSecretDesireModal: false,
                loadMoreListener () {
                    this.showMoreInFeed()
                }                
            }
        },
        components: {
            groupRecruitment,
            groupCommentFeed,
            submissionFeed
        },
        computed: {
            spaces() {
                return this.$stores.spaces.spaces
            },
            currentUser: function () {
                return this.$currentUser
            },
            latestUpdatedIdeation: function () {
                return this.$stores.submissions.getEntities([{
                    sorter: 'updated',
                    direction: 'asc'
                }],
                [
                    {
                        selection: 'stages',
                        value: [0, 1, 2, 3, 4]
                    }
                ]
                )
            },
            latestUpdatedGuides: function () {
                return this.$stores.submissions.getEntities([{
                    sorter: 'updated',
                    direction: 'asc'
                }],
                [
                    {
                        selection: 'stages',
                        value: [6]
                    }
                ]
                )
            },
            latestUpdatedIssues: function () {
                return this.$stores.submissions.getEntities([{
                    sorter: 'updated',
                    direction: 'asc'
                }],
                [
                    {
                        selection: 'stages',
                        value: [5]
                    }
                ]
                )
            },
            profileEntities: function () {
                return this.$stores.profiles.getEntities([
                    {
                        sorter: 'random'
                    },
                    {
                        sorter: 'profile_pic'
                    }
                ])
            },
            groups: function () {
                return this.$stores.groups.getEntities(
                    [{
                        sorter: 'registrations_count',
                        direction: 'asc'
                    }],
                    [{
                    selection: 'member',
                    value: this.$stores.auth.userData.user_id
                }])
            },            
            groupComments: function () {
                return this.$stores.groups.groupComments
            },
            feedSubmissions: function () {
                let submissions = this.$stores.submissions.submissions.slice().reverse()
                return submissions.slice(0, this.showInFeed)
            },
            feedItems: function () {

                let items = []
                for (let submission of this.feedSubmissions) {
                    let datetime = new Date(submission.last_major_update * 1000)
                    items.push({
                        data: submission,
                        date: datetime,
                        type: 'submission'
                    })
                }
                for (let comment of this.groupComments) {
                    let datetime = new Date(comment.posted_time)
                    items.push({
                        data: comment,
                        date: datetime,
                        type: 'comment'
                    })
                }

                items.sort((a, b) => {
                    return b.date - a.date
                })

                return items

            }
        },
        methods: {
            showMoreInFeed () {
                this.showInFeed++
            }
        },
        created: function () {

//            this.$root.eventBus.$on('load-more', this.loadMoreListener.bind(this))
        },
        beforeDestroy () {
//            this.$root.eventBus.$off('load-more', this.loadMoreListener)            
        }        
    }

</script>