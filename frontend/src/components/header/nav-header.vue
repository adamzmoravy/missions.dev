<template>

    <div v-if="!hideHeader" class="header-dropshadow navbar navbar-light fixed-top flex-lg-row bd-navbar-md noselect p-0">

        <div id="logo-search" class="logo-search-container d-flex align-items-center">

            <ui-a :href="'/front'" class="pl-2 platform-identifier" v-if="searchFocus != true && $ui.headerFeatures.includes('platform-identifier')">
                <platform-identifier></platform-identifier>
            </ui-a>
  
            <nav-header-search v-if="$ui.headerFeatures.includes('search')"  :common="common" class="w-100" />

        </div>

        <div id="center-options" v-if="$ui.headerFeatures.includes('navigation-bar')" style="padding:0em;" :class="{'d-none d-sm-flex': searchFocus}" class="mx-auto ml-auto mr-auto flex-lg-row bd-navbar-md navbar">

            <template v-for="item in mainMenu" >
                <header-menu-item 
                    class="menu-item"
                    @selected="linkSelected"
                    :icon-files="$ui.iconFiles[item.icon]"
                    :active="item.page == activePage"
                    :link="item.link" 
                    :name="item.page"
                    :icon="item.icon" 
                    :label="item.label"
                    :page-mode="pageMode"
                />
            </template>

        </div>


        <div id="right-options" :class="{'d-none': searchFocus, 'd-flex': !searchFocus}" class="d-sm-flex">

            <div class="d-none d-sm-none mr-2">
                <ui-icon-control>
                    <img src="./img/search.png" style="width:80%;height:80%;">   
                </ui-icon-control>
            </div>


            <div v-for="lang in $config.platform.site_languages" class="main-menu-language-selector position-relative mr-2" v-if="$config.platform.site_languages.length > 1">
                
                <div @click="changeLang(lang)" v-tooltip="$trans(lang.toUpperCase() + '_language')">

                    <ui-icon-control :selected="lang == translationsStore.selectedLanguage">
                        <span>{{ lang.toUpperCase() }}</span>
                    </ui-icon-control>

                </div>

            </div>

            <nav-header-notifications v-tooltip="$trans('Activity')" v-if="($ui.headerFeatures.includes('activity') && $config.platform.header_elements.includes('activity') && isUserAuthenticated) || isUserAuthenticated && isUserAdmin" class="mr-2 ml-0 ml-md-0" />

            <nav-header-admin-menu v-if="isUserAuthenticated && isUserAdmin" />

            <template v-if="$ui.headerFeatures.includes('profile')">
                <a id="nav-login" v-if="!isUserAuthenticated" class="btn btn-xs d-none d-md-inline">
                    <span  @click="uiStore.modal = 'login-modal'">{{$trans('Login')}}</span>
                </a>
                <a id="nav-login" v-if="!isUserAuthenticated" class="btn btn-x d-none d-lg-inline">
                    <ui-a :href="'/onboarding/'">{{$trans('Register')}}</ui-a>
                </a>

                <nav-header-profile-menu id="nav-login" v-if="isUserAuthenticated "/>

            </template>

            <nav-header-more-menu v-if="$ui.headerFeatures.includes('dropdown-menu')" :menu-items="$ui.mainMenu" />

        </div>


    </div>


</template>

<style scoped>

.logo-search-container {
    width:350px;
}


@media (min-width:200px) and (max-width:299px) {
    .navbar .menu-item:nth-child(n+5) {
            display:none;
        }
        .platform-identifier {
            display:none;
        }
}

@media (min-width:300px) and (max-width:349px) {
    .navbar .menu-item:nth-child(n+6) {
            display:none;
        }

        .platform-identifier {
            display:none;
        }
}

@media (min-width:350px) and (max-width:449px) {
    .navbar .menu-item:nth-child(n+7) {
            display:none;
        }
        .platform-identifier {
            display:none;
        }

}

@media (max-width:500px) {

    .main-menu-language-selector {
        display:none;
    }    

}

@media (min-width:450px) and (max-width:799px) {
    .navbar .menu-item:nth-child(n+9) {
            display:none;
        }

}

@media (min-width:800px) and (max-width:899px) {
    .navbar .menu-item:nth-child(n+6) {
            display:none;
        }

    .main-menu-language-selector {
        display:none;
    }

}


@media (min-width:900px) and (max-width:1150px) {
    .navbar .menu-item:nth-child(n+6) {
            display:none;
        }

        .main-menu-language-selector {
        display:none;
    }
}

@media only screen and (max-width: 1000px) {

    .logo-search-container {
        width:auto;
    }

}

.vertical-align-content {
    display: flex;
    align-items: center;
}
.navbar {
    height:70px;
    bottom:env(safe-area-inset-bottom);
}

</style>

<style>

</style>

<script>

import platformIdentifier from '../platform-identifier.vue'
import navHeaderSearch from './nav-header-search.vue'
import navHeaderNotifications from './nav-header-notifications.vue'
import headerMenuItem from './header-menu-item.vue'
import navHeaderProfileMenu from './nav-header-profile-menu.vue'
import navHeaderMoreMenu from './nav-header-more-menu.vue'
import navHeaderAdminMenu from './nav-header-admin-menu.vue'


import { useProfilesStore } from '../../stores/profiles'
import { useTranslationsStore } from '../../stores/translations'
import { useUIStore } from '../../stores/ui'
import { useAuthStore } from '../../stores/auth'
import { mapStores } from 'pinia'

export default {
    name: 'nav-header',
    components: {
        platformIdentifier,
        navHeaderSearch,
        navHeaderNotifications,
        headerMenuItem,
        navHeaderProfileMenu,
        navHeaderMoreMenu,
        navHeaderAdminMenu
    },
    data() {
        return {
            ui: {
                showSearchMobile: false,
                loginModal: false,
                registerModal: false
            },
            notificationsPanel: false,
            selectedActivePage: null,
            searchFocus: false,
            showExpanded: false
        }
    },
    computed: {
        mainMenu: function() {

            let menu = []
            for (let i = 0; i < this.$ui.mainMenuShow.length; i++) {
                let item = this.$ui.mainMenuShow[i]
                let menuItem = this.menu.find(menuItem => {
                    return menuItem.name == item
                })
                if (menuItem) {
                    menu.push(menuItem)
                }
            }

            return menu
        },
        isUserAdmin: function() {
            if (this.isUserAuthenticated && this.$stores.auth.userData.user_type == 'admin') {
                return true
            }
            return false
        },
        $ui: function() {
            return this.$stores.platform.ui
        },
        hideHeader: function () {
            return this.$route.meta.byPassAuthentication || this.$route.meta.hideHeader
        },
        menu: function() {
            return this.$ui.mainMenu
        },
        ...mapStores(useProfilesStore, useUIStore, useAuthStore, useTranslationsStore),
        activePage: function() {
            if (this.selectedActivePage) {
                return this.selectedActivePage
            }
            if (this.pageMode == 'multiPage') {
                return this.$page
            }
            return this.$route.name
        }
    },
    props: {
        // inherit global data from parent
        currentPage: {
            required: false,
            type: String
        },
        common: {
            required: false,
            type: Object
        },
        pageMode: {
            required: false,
            default: 'multiPage',
            type: String
        },
    },
    methods: {
        changeLangOld(newLang) {
            let currentUrl = window.location.href
            let currentLang = this.$lang
            let newUrl = currentUrl.replace('/' + currentLang + '/', '/' + newLang + '/')

            if (currentLang !== newLang) {
                window.location.href = newUrl
            }
        },
        changeLang(newLang) {
            let path = this.$route.path
            let newPath = path.replace('/' + this.$route.params.lang + '/', '/' + newLang + '/')
            this.$router.push(newPath).catch(err => {
                if (
                    err.name !== 'NavigationDuplicated' &&
                    !err.message.includes('Avoided redundant navigation to current location')
                    ) {
                    logError(err)
                    } else {
                        this.$root.eventBus.$emit('new-page-navigation')
                    }
            })
        },
        linkSelected: function (name, link) {
            this.selectedActivePage = name
            setTimeout(function() {

                this.selectedActivePage = null
            }.bind(this), 1000)

            if (this.pageMode == 'multiPage') {

                console.log(link)

            } else {
                setTimeout(function() {
                    this.$router.push(
                        {
                            name: name,
                            params: {lang: this.$lang}
                        }   
                    ).catch(err => {
                        if (
                            err.name !== 'NavigationDuplicated' &&
                            !err.message.includes('Avoided redundant navigation to current location')
                            ) {
                            logError(err)
                            } else {
                                this.$root.eventBus.$emit('new-page-navigation')
                            }
                    })

                }.bind(this), 1)

            }
        },
        closeExpanded: function () {
            alert('close expanded')
            this.notificationsPanel = false
            this.showExpanded = false
        },
        setup: function() {
            this.getUserData()
        }
    },
    created: function() {

        /*
        Here are login events, they need to be moved somwhere else
        this.$root.eventBus.$on('login', function() {
            this.setup()
            this.profilesStore.fetchProfile('me')
        }.bind(this))
        this.$root.eventBus.$on('logout', function() {
            this.setup()
        }.bind(this))
        this.$root.eventBus.$on('profile', function() {
            this.setup()
        }.bind(this))

        */
    },
    mounted: function() {

    }
}
</script>
