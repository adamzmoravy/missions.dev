<template>

    <div ref="scrollcontainer">
        <div v-if="!accessible">
            <platform-not-accessible/>      
        </div>

        <spa v-if="appType == 'spa' && accessible"/>

        <modals-control @close="$stores.ui.modal = null" />


    </div>

</template>

<script>

    import modalsControl from './modals-control.vue'
    import spa from './spa.vue'
    import platformNotAccessible from './platform-not-accessible.vue'
    import backend from '../backend.js'
    export default {
        head() {
            return {
                title: this.$config.platform.site_title,
                link: [
                    {
                        rel: 'icon',
                        type: 'image/svg',
                        sizes: '16x16',
                        href: this.$ui.shortcut_icon
                    },
                    {
                        rel: 'shortcut icon',
                        type: 'image/svg',
                        sizes: '16x16',
                        href: this.$ui.shortcut_icon
                    }                    
                ],
                meta: [
                    {
                        name: 'description',
                        content: this.$config.platform.mission_statement,
                    }
                ]
            }
        },
        name: 'platform',
        directives: {
        },
        data() {
            return {
                sleepScrollLoadMore: false,
                lastPingTime: null
            }
        },
        components: {
            spa,
            platformNotAccessible,
            modalsControl
        },
        props: {
            appType: {
                required: false,
                type: String,
                default: 'spa'
            },
            language: {
                required: false,
                type: String,
                default: 'en'
            }
        },
        notifications: {
            generalNotification: {}
        },
        mixins: [
        ],
        computed: {
            accessible: function () {

                let access = this.$config.platform.access.site

                if (access === 'public') {
                    return true
                }
                
                if (this.$route.meta.byPassAuthentication === true) {
                    return true
                }

                if (access === 'members_only' && this.$isUserAuthenticated) {
                    return true
                }

                if (access === 'members_only' && !this.$isUserAuthenticated) {
                    return false
                }

            }
        },
        watch:{
            $route (to, from){
                this.$stores.ui.modal = null
                this.$stores.ui.action = null

                if (to.params.lang != from.params.lang) {
                    this.$stores.translations.setLanguage(to.params.lang)
                }
            },
            $latestEvent: function(event) {

                this.$stores.submissions.handleEvent(event)
                this.$stores.groups.handleEvent(event)
                this.$stores.profiles.handleEvent(event)
                this.$stores.auth.handleEvent(event)

                if (event.eventName === 'user-logout') {
                    this.initializePlatformData()
                }
                if (event.eventName === 'user-login') {
                    this.initializePlatformData()
                }
                if (event.eventName === 'user-not-login') {
                    this.initializePlatformData()
                }

            }
        },
        methods: {
            ping: function() {

                if (this.$isUserAuthenticated) {

                    let pingInterval = 1000 * 60

                    if (this.lastPingTime == null || (new Date() - this.lastPingTime) > pingInterval) {
                        backend.get('/platform/ping/').then(response => {
                            console.log('ping')

                            this.lastPingTime = new Date()

                        }).catch(error => {
                            console.log('Ping error:', error)
                            this.lastPingTime = new Date()
                        })
                    }

                } else {
                    return true
                }

            },
            handleScroll: function () {
                if (this.sleepScrollLoadMore) {
                    return true
                }
                let element = this.$refs.scrollcontainer
                if (element.getBoundingClientRect().bottom < (window.innerHeight + 200)) {
                    this.sleepScrollLoadMore = true
                    if (this.$stores.ui.loadMoreOnScroll >= 1000) {
                        this.$stores.ui.loadMoreOnScroll = 0
                    } else {
                        this.$stores.ui.loadMoreOnScroll++
                    }
                    // after 0.5 second, allow the scroll to trigger again
                    setTimeout(() => {
                        this.sleepScrollLoadMore = false
                        if (element.getBoundingClientRect().bottom < (window.innerHeight + 200)) {                            this.$stores.ui.loadMoreOnScroll++

                        }
                    }, 500)
                }

            },
            setup: function() {
            },
            initializePlatformData: function() {

                if (this.$isUserAuthenticated || this.$config.platform.access.site === 'public') {
                    this.$stores.submissions.fetchSubmissions()
                    this.$stores.profiles.fetchProfilePreviews()
                    this.$stores.groups.fetchGroupPreviews()
                    this.$stores.groups.fetchCalendarEvents()
                    this.$stores.spaces.fetchSpacesPreviews()
                    this.$stores.tags.fetchTags()
                }

            }

        },
        created: function() {

            console.log(import.meta.env.MODE)

            this.$stores.auth.login()
//            this.$stores.translations.setLanguage(this.$route.params.lang)
            this.$stores.translations.setLanguage(this.$route.params.lang)

            this.$stores.translations.fetchPlatformTranslations()

            this.$stores.events.initiatePusher(
                this.$stores.platform.config.pusher_key
            )
            this.$stores.events.subscribeToChannel(
                this.$stores.platform.config.platform_key
            )
        },
        mounted: function() {
            this.$stores.ui.platformName = this.$config.platform.site_title
            this.$stores.ui.setPageTitle()
            this.translate
            window.addEventListener("scroll", this.handleScroll)

            // If a login token is provided in the URL, attempt to log in with it
            const loginParam = this.$route.query.login
            if (loginParam) {
                this.$stores.auth.setToken(loginParam)
                // remove the token from the URL to prevent re-login
                this.$router.replace({ query: { ...this.$route.query, login: undefined } })
            }

            this.ping()

            this.pingInterval = setInterval(() => {
                this.ping()
            }, 5000)


        },
        beforeUnmount: function() {
            window.removeEventListener("scroll", this.handleScroll)

            clearInterval(this.pingInterval)
        }
    }
</script>
