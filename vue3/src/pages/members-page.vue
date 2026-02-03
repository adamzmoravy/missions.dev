<template>
    <div>

        <ui-page-header class="d-none">

            <div>
                <h1 class="x-large mt-2">{{$trans('People')}}</h1>
            </div>

        

        </ui-page-header>

        <div class="container-fluid">


            <div class="container pt-3">

                <div class="ml-3">
                    <h3 class="large mt-2 mb-1">{{$trans('Groups')}}</h3>
                    <h4 class="medium mb-4">{{$trans('groups_page_explainer')}}</h4>

                </div>


                <vue-horizontal v-if="groups.length > 0" class="mb-5">

                    <template v-for="group in groups"  :key="group.happening_id" >
                        <group-preview class="col-md-6 col-lg-4 pl-2" :happening="group"/>
                    </template>

                </vue-horizontal>


                <ui-tabs v-on:update:section="ui.showSection = $event" :tabs="pageTabs" :current-selection="ui.showSection" />

            </div>

        </div>


        <div class="container-fluid bg-white" v-if="ui.showSection == 'users'">

            <div class="container">

                <div class="d-flex justify-content-between pt-4">

                    <div>
                        <ui-input-text v-model="searchTerm" class="search-field" :placeholder="$trans('Search for user')" />
                    </div>

                    <div >
                            <span class="ml-3 pull-right">
                                <a @click="sortOrder = 'asc'" class="selectable-element" :class="{'selected-element': sortOrder == 'asc' }">{{$trans('Newest')}}</a>
                                <a @click="sortOrder = 'desc'" class="selectable-element" :class="{'selected-element': sortOrder == 'desc' }">{{$trans('Oldest')}}</a>
                            </span>
                    </div>


                </div>
    <!--

                <div class="row mt-2 mb-2">

                    <div class="col-lg-12 mt-2 mb-2 col-12">

                            <tags-index inline-template type="experiment" class="mx-md-0 mx-3">

                                <elements-group inline-template :show-num="60" :tolerance="0"  :group="sortedAndFilteredTags">

                                        <vue-horizontal v-if="showWithTolerance.length > 0">
                                            <a class="item" v-for="tag in showWithTolerance" :key="tag.id">
                                                <span class="tag" :class="{selected_tag: tag.id == selected}">
                                                    {{tag.label}}
                                                </span>
                                            </a>
                                    
                                        </vue-horizontal>
                                    
                                </elements-group>

                            </tags-index>
                    </div>
                </div>
    -->

                <div class="row row-eq-height mt-3 pt-2">
                    <div class="col-lg-3 col-md-4 mb-4" v-for="profile in paginatedProfiles">

                        <div class="comment-container bg-white h-100 position-relative">
                            <ui-show-more :max-height="350" :buttonShow="false">

                                <div class="users p-3">

                                    <div class="text-center">
                                        <div class="align-content-center d-flex">
                                                <profile-image-simple class="mx-auto" style="width:80%;" :profile="profile" />
                                        </div>
                                        <div class="user-name text-center pt-2">
                                            <h3><ui-a class="user-preview-link stretched-link" :href="'/profile/' + profile.user_id">{{ profile.first_name + ' ' + profile.last_name }}</ui-a></h3>
                                        </div>
                                        <div class="small-2">
                                            {{ profile.short_description }}
                                        </div>
                                        <div v-show="profile.skills">
                                            <span v-for="(skill, index) in profile.skills"><i>{{ skill }}</i> <span v-if="index !== profile.skills.length - 1">&nbsp;•&nbsp;&nbsp;</span>
                                            </span>    
                                        </div>    

                                    </div>

                                </div>

                            </ui-show-more>

                        </div>

                        
                    </div>
                </div>
            </div>



        </div>


        <div class="pt-0 mt-0 container"  v-if="ui.showSection == 'map'">

            <profile-map class="pt-0 mt-0 jon" :token="$config.platform.geo_provider_key" :profiles="allProfiles" />
            
        </div>



        <div class="container-fluid bg-white" v-if="ui.showSection == 'finder'">



            <div class="container pt-4">

                <div class="row">


                    <div class="col-md-8">

                    
                            <h2 class="large mt-2 mb-1">{{$trans('Members')}}</h2>
                            <hr>




                        <div class="row" v-if="tagsToFilter.length || countryToFilter || submissionsToFilter.length">

                            <div class="col-md-12">
                                <h4 class="medium mb-2">{{$trans('Active filters')}}</h4>
                            </div>
                            <div class="col-md-12" v-if="countryToFilter">
                                <ui-country-flag :country-code="countryToFilter" :show-name="true" />
                            </div>

                            <div class="col-md-12" v-if="tagsToFilter.length">
                                <ui-tags :tag-ids="tagsToFilter" :readonly="true" />
                            </div>

                            <div class="col-md-12" v-if="submissionsToFilter.length">
                                <div v-for="submission in submissionsToFilter">
                                    {{ $stores.submissions.getSubmissionPreview(submission).title }}
                                </div>

                            </div>


                        </div>

                        <div class="row mt-3">


                            <div class="col-md-12 mb-4 px-4" v-for="profile in tagFilteredProfiles">


                                <ui-panel class="bg-white row users">
                                    <div class="col-md-3">
                                                        <profile-image-simple class="mx-auto" style="width:100%;" :profile="profile" />
                                    </div>
                                        <div class="pb-3 mb-3 col-md-9"> 

                                            <div class="text-center">

                                                <div class="user-name text-center pt-2">
                                                    <h3><ui-a class="user-preview-link stretched-link" :href="'/profile/' + profile.user_id">{{ profile.first_name + ' ' + profile.last_name }}</ui-a></h3>
                                                </div>
                                                <div class="small-2  text-center d-flex justify-content-center mb-3 mt-2" v-if="profile.geographic_location && profile.geographic_location.vicinity">
                                                        <span class="d-flex small text-center"><img style="height:1.2em;" :src="'/img/location.svg'">{{$truncate(profile.geographic_location.vicinity, 18) + ', ' + profile.geographic_location.country.name }}</span>
                                                </div>
                                                <div class="small-2">
                                                    {{ $truncate(profile.short_description, 150) }}
                                                </div>
                                                <div v-show="profile.skills">
                                                    <span v-for="(skill, index) in profile.skills"><i>{{ skill }}</i> <span v-if="index !== profile.skills.length - 1">&nbsp;•&nbsp;&nbsp;</span>
                                                    </span>    
                                                </div>    

                                            </div>

                                        </div>

                                </ui-panel>

                                
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 pb-5">



                        <h2 class="large mt-2 mb-3">{{$trans('Filters')}}</h2>
                        <hr>



                        <profile-finder layout="minimalist" :profiles="$stores.profiles.profilePreviews" :filter-by-submissions-type="[8]" @update="filterTags" @update-country="filterCountry" @update-submissions="filterSubmissions"></profile-finder>


                    </div>
                </div>
            </div>



        </div>




    </div>

</template>

<style scoped>

.search-field {
    width: 300px;
}

</style>

<script>

import groupPreview from '../components/groups/group-preview.vue'
import ProfileFinder from '../components/profiles/profile-finder.vue'
import ProfileMap from '../components/profiles/profile-map.vue'

export default {
    name: 'MembersPage',
    data() {
        return {
            ui: {
                showSection: 'users'
            },
            showSection: 'users',
            sortOrder: 'asc',
            searchTerm: '',
            limit: 48,
            tagsToFilter: [],
            countryToFilter: '',
            submissionsToFilter: []
        }
    },
    components: {
        ProfileFinder,
        ProfileMap,
        groupPreview
    },
    computed: {

        pageTabs() {
            let tabs = [
                {
                    display: this.$trans('All users'),
                    value: 'users'
                },
                {
                    display: this.$trans('Map'),
                    value: 'map'
                }
            ]

            if (this.$ui.profileElements.includes('info')) {

                tabs.unshift({
                    display: this.$trans('People finder'),
                    value: 'finder'
                })

                this.ui.showSection = 'finder'
            }


            return tabs
        },
        groups() {
            return this.$stores.groups.groups
        },
        paginatedProfiles() {
            return this.allProfiles.slice(0, this.limit)
        },
        tagFilteredProfiles() {

            let allProfiles = this.allProfiles

            if (this.countryToFilter && this.countryToFilter !== '') {
                allProfiles = allProfiles.filter(profile => {
                    return profile.geographic_location && profile.geographic_location.country && profile.geographic_location.country.code === this.countryToFilter
                })
            }

            if (this.submissionsToFilter.length > 0) {

                allProfiles = allProfiles.filter(profile => {

                    return this.$stores.submissions.getEntities(
                        [], [
                        {
                            selection: 'owner',
                            value: [profile.user_id]
                        },
                        {
                            selection: 'ids',
                            value: [this.submissionsToFilter]
                        }

                    ]).length > 0
                })

            }

            if (this.tagsToFilter.length === 0) {
                return allProfiles.slice(0, this.limit)
            }

            return allProfiles.filter(profile => {
                if (!profile.tags_with_type || !Array.isArray(profile.tags_with_type)) {
                    return false
                }

                const profileTagIds = profile.tags_with_type.map(tagItem => tagItem.tag_id)

                // Check if profile has all tags in tagsToFilter
                return this.tagsToFilter.every(tagId => profileTagIds.includes(tagId))
            }).slice(0, this.limit)
        },
        allProfiles() {
            let entities = this.$stores.profiles.getEntities([
                {
                    sorter: 'date_joined',
                    direction: this.sortOrder
                }
                ],
                [{
                    selection: 'search',
                    value: this.searchTerm
                }])
            
            let profilesPreviews = []

            for (let i = 0; i < entities.length; i++) {
                profilesPreviews.push(entities[i].data)
            }

            return profilesPreviews

        }
    },
    watch:{
        $loadMoreOnScroll: function (newVal) {
            this.loadMore()
        },
        $route (to, from){
            if (to.name == 'members') {
//                this.$refs.profileIndex.setup()
            }
            this.limit = 24
        }
    },
    methods: {
        filterTags(tags) {
            this.tagsToFilter = tags
        },
        filterCountry(country) {
            this.countryToFilter = country
        },
        filterSubmissions(submissions) {

            this.submissionsToFilter = submissions

        },
        loadMore() {
            this.limit += 48
        }
    },
    created() {
/*

        var self = this
        this.$root.eventBus.$on('load-more', function (data) {
                self.loadMoreMembers()
        })        

*/
    }
}

</script>