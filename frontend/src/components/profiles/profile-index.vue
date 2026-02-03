<template>
    <div class="profile-index-container w-100">

        <profile-index-row
            class="w-100"
            :visible-profiles="sortedAndFilteredProfiles"
            :all-profiles="sortedAndFilteredProfilesAll"
            :profile-size="profileSize"
            :margin-right="marginRight"
            :show-name="showName"
            :user-reactions="userReactions"
            v-if="type=='row'"
            @show-all-profiles="$emit('show-all-profiles')"
            @profiles-fit="profilesFit"
        />

        <profile-index-list
            :visible-profiles="sortedAndFilteredProfiles"
            :all-profiles="sortedAndFilteredProfilesAll"
            v-if="type=='list'"
        />

        <profile-index-list
            width="100%"
            :virtual="false"
            :visible-profiles="sortedAndFilteredProfiles"
            :all-profiles="sortedAndFilteredProfilesAll"
            :user-reactions="userReactions"
            v-if="type=='reactions-list'"
        />

        <profile-index-list
            @show-all-profiles="$emit('show-all-profiles')"
            width="100%"
            type="simple"
            :is-user-editor="isUserEditor"
            :virtual="false"
            :visible-profiles="sortedAndFilteredProfiles"
            :all-profiles="sortedAndFilteredProfilesAll"
            v-if="type=='list-simple'"
        />


        <template v-if="type=='list-selectable'">
            <profile-index-list-selectable
                @selected="$emit('selected', $event)"
                @unselected="$emit('unselected', $event)"
                class="w-100"
                v-for="(profile, index) in sortedAndFilteredProfiles" 
                :key="profile.user_id" 
                :source="profile" 
                :index="index"                
                v-if="type=='list-selectable'"
                :selected="alreadyInSelection.includes(profile.user_id)"
            />

        </template>


    </div>

</template>

<script>
import VirtualList from 'vue-virtual-scroll-list'
import backend from '../backend.js'
export default {
    name: 'profile-index',
    data() {
        return {
            profile: this.common.placeholders.profile_preview,
            profilesFitting: null,
            searchTerm: '',
            filterOptions: [],
            tagsForExperiments: [],
            responseStatus: {},
            currentSortObject: false,
            currentSortOptionKey: false,
            localUi: {
                userPopupId: null
            },
            allPublicPlatformsProfiles: [],
            randomProfiles: []
        }
    },
    props: {
        isUserEditor: {
            required: false,
            type: Boolean,
            default: false
        },
        type: {
            required: false,
            default: 'row'
        },
        apiUrl: {
            required: false,
            type: String,
            default: function () {
                return this.$apiUrl
            }
        },
        userReactions: {
            required: false,
            default: null
        },
        randomizePulse: {
            required: false,
            default: null
        },
        allPublicPlatforms: {
            required: false,
            default: false
        },
        showWithProfilePicOnly: {
            required: false,
            default: false
        },
        profileSize: {
            required: false,
            default: 50
        },
        showHiddenProfilesCount: {
            required: false,
            default: true
        },
        maxProfilesDisplay: {
            required: false,
            default: 1000
        },
        stackProfiles: {
            required: false,
            default: false
        },
        showName: {
            required: false,
            default: true
        },
        language: {
            required: false,
            type: String,
            default: function () {
                return this.$lang
            }
        },
        placeholderImageUrl: {
            required: false,
            default: false
        },
        limit: {
            required: false /* Optional. Number between 0 and n, 0 means no limit. E.g. limit="12" */
        },
        userIds: {
            required: false /* Optional. List of user ids or 'all'. E.g. user-ids="5, 22, 155" */
        },
        getTag: { /* Optional server-side filter. Fetch only users that contain this tag. E.g. get-tag="1" */
            required: false,
            default: false
        },
        getLimit: { /* Optional server-side filter. Fetch a limited quantity of experiments. E.g. get-limit="20" */
            required: false,
            default: false
        },
        getOffset: { /* Optional server-side filter. Fetch a experiments starting by a specified offset. E.g. get-offset="5" */
            required: false,
            default: false
        },
        sortProfilesObject: { /* Optional client-side sorting Object. If set, sorting section is displayed. Supported keys: 'oldest', 'newest'. Set key 'initial' to sort on page load. E.g. to sort by oldest on page load: :sort-profiles-object="{oldest:{name:oldest, initial:true}}" */
            required: false,
            type: Array
        },
        usersFilterArray: { /* Optional client-side array of objects. If set, filter section is displayed. Supported keys: 'tag'. Set key 'initial' to show filter on page load. E.g. :users-filter-array="[{tag:{name:tag, initial:true}}]" */
            required: false,
            type: Array
        },
        // inherit global data from parent
        common: {
            required: false,
            type: Object,
            default: function () {
                return this.$common
            }
        },
        openProfileInNewTab: {
            required: false,
            type: Boolean,
            default: false
        },
        experimentId: {
            required: false,
            type: String
        },
        navbarHeaderSearchString: {
            required: false,
            type: String
        },
        selectedProfiles: {
            required: false,
            type: Array
        },
        alreadyInSelection: {
            required: false,
            type: Array
        }
    },
    mixins: [
        users,
        experiments,
        helpers
    ],
    notifications: {
        getUsersPreviewNotification: {}
    },
    watch: {
        searchProp: function (newVal, oldVal) {
            this.searchTerm = newVal
        }
    },
    methods: {
        profilesFit: function (num) {
            this.profilesFitting = num
        },
        shuffleFew: function () {
            let profiles = this.withProfilePic(this.profiles, true)
            let swapCount = 20
            for (let i = 0; i < swapCount; i++) {
                let index1 = Math.floor(Math.random() * profiles.length)
                let index2 = Math.floor(Math.random() * profiles.length)
                let temp = profiles[index1]
                profiles[index1] = profiles[index2]
                profiles[index2] = profiles[index1]
            }
            this.randomProfiles = profiles.slice(0, this.limit)
        },
        shuffleAll: function () {
            let profiles = this.withProfilePic(this.profiles, true)
            this.randomProfiles = this.randomizeArray(profiles).slice(0, this.limit)

        },
        containerWidth: function () {
            alert(this.$refs.profilescontainerdiv[0].clientHeight)
        },
        setup: function() {
            // set up filter options
            if (this.usersFilterArray) {
                if (this.usersFilterArray.length !== 0) {
                    for (var i = 0; i < this.usersFilterArray.length; i++) {
                        var option = this.usersFilterArray[i]
                        option.selected = false
                        this.filterOptions.push(option)
                    }
                }
            }
            // set up sorting options
            if (this.sortProfilesObject) {
                if (this.sortProfilesObject.length !== 0) {
                    for (var j = 0; j < this.sortProfilesObject.length; j++) {
                        if (this.sortProfilesObject[j].initial === true) {
                            this.$set(this, 'currentSortObject', this.sortProfilesObject[j])
                            this.$set(this, 'currentSortOptionKey', this.sortProfilesObject[j].key)
                        }
                    }
                }
            }
            // build query Array......
            var queryArray = []
            if (this.getTag) {
                queryArray.push({
                    tag: this.getTag
                })
            }
            if (this.getLimit) {
                queryArray.push({
                    limit: this.getLimit
                })
            }
            if (this.getOffset) {
                queryArray.push({
                    offset: this.getOffset
                })
            }
            // make API call
//            this.getUsersPreview(this.apiUrl, queryArray)

            if (this.allPublicPlatforms) {
                this.getAllPublicPlatformsProfiles()
            }
            this.getTagsForExperiments(this.apiUrl, this.language)
        },
        getAllPublicPlatformsProfiles: function () {
            backend.get('/all_platforms/users/preview/').then((response) => {
               let profiles = response.data.previews               
               this.allPublicPlatformsProfiles = this.randomizeArray(profiles)
            })            
        },
        randomizeArray (array) {
            let currentIndex = array.length, temporaryValue, randomIndex

            // While there remain elements to shuffle...
            while (0 !== currentIndex) {

                // Pick a remaining element...
                randomIndex = Math.floor(Math.random() * currentIndex)
                currentIndex -= 1

                // And swap it with the current element.
                temporaryValue = array[currentIndex]
                array[currentIndex] = array[randomIndex]
                array[randomIndex] = temporaryValue
            }

            return array
        },
        getUsersPreview: function(apiUrl, language) {
            this.getUsersPreviewMixin(apiUrl, language).then((response) => {
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'getUsersPreview')
                if (response.hasOwnProperty('data')) {
                    if (response.data.hasOwnProperty('previews')) {
                        this.profiles = response.data.previews
                    }
                }
            }, (error) => {
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'getUsersPreview')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.getUsersPreviewNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        getTagsForExperiments: function(apiUrl, language) {
            this.getTagsForExperimentsMixin(apiUrl, language).then((response) => {
                // debug info
                this.debug('getTagsForExperimentsMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'getTagsForExperiments')
                if (response.hasOwnProperty('data')) {
                    if (response.data.hasOwnProperty('tags')) {
                        this.tagsForExperiments = response.data.tags
                    }
                } else {
                    this.debug('hasOwnProperty(data.tags) error: ')
                }
            }, (error) => {
                // debug info
                this.debug('getTagsForExperimentsMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'getTagsForExperiments')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.getTagsForExperimentsNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        getUserName: function(profile) {
            return profile.first_name + ' ' + profile.last_name
        },
        sliceProfiles: function(profiles, start, end) {
            if (parseInt(end) !== 0) {
                // debug info
                this.debug('limiting profile quantity to: ' + end)
                this.debug(profiles)
                this.debug(profiles.slice(start, end))
                return profiles.slice(start, end)
            } else {
                return profiles.slice(start)
            }
        },
        filterById: function(profiles, userIds) {
            if (typeof userIds !== 'undefined') {
                if (typeof userIds === 'string') {
                    var userIdsNumbers = userIds.split(',')
                } else {
                    var userIdsNumbers = userIds
                }
                return profiles.filter(function(profiles) {
                    if (userIds === 'all') {
                        return true
                    } else {
                        // debug info
//                        this.debug('limiting profile IDs to: ' + userIds)
                        for (var i = 0; i < userIdsNumbers.length; i++) {
                            if (userIdsNumbers[i].trim() === profiles.user_id) {
                                return true
                            }
                        }
                    }
                })
            } else {
                return profiles
            }
        },
        keywordFilter: function(profiles, searchTerm) {
            if (searchTerm !== '' && searchTerm !== ' ') {
                this.debug('serching profiles by keyword: ' + searchTerm)
                return profiles.filter(function(profiles) {
                    var firstName = profiles.first_name ? profiles.first_name : ''
                    var lastName = profiles.last_name ? profiles.last_name : ''
                    var nameText = firstName.toLowerCase() + ' ' + lastName.toLowerCase()
                    var description = profiles.short_description ? profiles.short_description : ''
                    var descriptionText = description.toLowerCase()
                    var tagText = ''
                    for (var i = 0; i < profiles.tags.length; i++) {
                        tagText += profiles.tags[i].label + ' '
                    }
                    if (nameText.indexOf(searchTerm.toLowerCase()) !== -1 || descriptionText.indexOf(searchTerm.toLowerCase()) !== -1 || tagText.indexOf(searchTerm.toLowerCase()) !== -1) {
                        return true
                    }
                })
            } else {
                return profiles
            }
        },
        searchProfiles: function() {
            this.keywordFilter(this.profiles, this.searchTerm)
        },
        filterOptionsContain: function(filterKey) {
            if (this.filterOptions) {
                if (this.filterOptions.length > 0) {
                    for (var i = 0; i < this.filterOptions.length; i++) {
                        if (this.filterOptions[i].key === filterKey) {
                            return this.filterOptions[i].selected
                        }
                    }
                }
            } else {
                return false
            }
        },
        sortProfiles: function(profiles) {
            if (this.currentSortObject) {
                if (this.currentSortObject.key === 'oldest') {
                    // debug info
                    this.debug('sorting by oldest')
                    return this.sortProfilesById(profiles, 'asc')
                } else if (this.currentSortObject.key === 'newest') {
                    // debug info
                    this.debug('sorting by newest')
                    return this.sortProfilesById(profiles, 'desc')
                } else {
                    this.debug('unsupported sorting option')
                }
            } else {
                return profiles
            }
        },
        sortProfilesById: function(profiles, direction) {
            if (direction === 'desc') {
                return profiles.sort(function(a, b) {
                    let idA = parseInt(a.user_id)
                    let idB = parseInt(b.user_id)
                    return (idA > idB) ? -1 : (idA < idB) ? 1 : 0
                })
            } else if (direction === 'asc') {
                return profiles.sort(function(a, b) {
                    let idA = parseInt(a.user_id)
                    let idB = parseInt(b.user_id)
                    return (idA < idB) ? -1 : (idA > idB) ? 1 : 0
                })
            } else {
                this.debug('unsupported sorting direction')
            }
        },
        sortProfilesByPic: function(profiles) {
            return profiles.sort(function(a, b) {
                var idA = 1
                var idB = 1
                if (a.image === null) {
                    idA = 0
                }
                if (b.image === null) {
                    idB = 0
                }
                return (idA > idB) ? -1 : (idA < idB) ? 1 : 0
            })
        },
        triggerSort: function(sortOption) {
            if (this.sortProfilesObject.length !== 0) {
                for (var i = 0; i < this.sortProfilesObject.length; i++) {
                    if (sortOption.key === this.sortProfilesObject[i].key) {
                        this.$set(this, 'currentSortObject', this.sortProfilesObject[i])
                    }
                }
            }
        },
        withProfilePic: function (profiles, profilePicOnly = false) {
            if (!profilePicOnly) {
                return profiles
            }
            return profiles.filter(function(profile) {
                if (profile.image !== null) {
                    return true
                }
            })
        },
        profileSelected(profile) {
            this.$emit('profile-selected', profile)
        },
        profileUnselected(profile) {
            this.$emit('profile-unselected', profile)
        },
        emitSelectedOrUnselectedProfile(profile) {
            if (this.alreadyInSelection.includes(profile.user_id)) {
                this.$emit('profile-unselected', profile)
            } else {
                this.$emit('profile-selected', profile)
            }
        }
    },
    computed: {
        adjustedLimit: function () {
            if (this.profilesFitting != null && this.profilesFitting < this.limit) {
                return this.profilesFitting
            }
            return this.limit
        },
        profiles: function () {
            if (this.allPublicPlatforms) {
                return this.allPublicPlatformsProfiles
            }
            return this.$store.state.users
        },
        randomizedProfiles: function() {
            if (this.randomProfiles.length == 0) {
                return this.withProfilePic(this.sortedAndFilteredProfiles, true);
            } else {
                return this.randomProfiles
            }
        },
        marginRight: function () {
            if (this.stackProfiles) {
                return (-this.profileSize / 5) + 'px'
            } else {
                return (this.profileSize / 7) + 'px'
            }
        },
        placeholderImage: function() {
            if (this.placeholderImageUrl) {
                return this.placeholderImageUrl
            } else {
                return this.common.placeholders.defaultPlaceholderImageUrl
            }
        },
        getTagLabelFromId: function() {
            for (var i = 0; i < this.tagsForExperiments.length; i++) {
                // this.debug('checking tag id:' + this.tagsForExperiments[i].id)
                if (this.tagsForExperiments[i].id === parseInt(this.getTag)) {
                    return this.tagsForExperiments[i].label
                }
            }
        },
        sortedAndFilteredProfilesAll() {
            let profiles = JSON.parse(JSON.stringify(this.profiles))
            let sortedProfiles = this.filterById(profiles, this.userIds)
            sortedProfiles = this.withProfilePic(sortedProfiles, this.showWithProfilePicOnly)
            sortedProfiles = this.keywordFilter(sortedProfiles, this.searchTerm)
            sortedProfiles = this.sortProfiles(sortedProfiles)
            sortedProfiles = this.sortProfilesByPic(sortedProfiles)
            return sortedProfiles
        },
        sortedAndFilteredProfiles() {
            let sortedProfiles = this.sortedAndFilteredProfilesAll
            sortedProfiles = this.sliceProfiles(sortedProfiles, 0, this.adjustedLimit)
            return sortedProfiles
        },
        searchedProfiles() {
            let sortedProfiles = this.keywordFilter(this.profiles, this.searchTerm)
            return sortedProfiles
        },
        navbarSearchedProfiles() {
            if (this.navbarHeaderSearchString) {
                let sortedProfiles = this.keywordFilter(this.profiles, this.navbarHeaderSearchString)
                return sortedProfiles
            } else {
                return null
            }
        },
        newTabOrNot() {
            if (this.openProfileInNewTab) {
                return '_blank'
            } else {
                return '_self'
            }
        }
    },
    created: function() {   


        if (this.randomizePulse) {
            setInterval(this.shuffleAll, 10000)
        }



        // set this up
        this.setup()
    }
}
</script>
