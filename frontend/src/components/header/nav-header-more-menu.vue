<template>

    <div class="mr-2">
        <OnClickOutside @trigger="showExpanded = false">

            <div ref="button" @click="showExpanded=!showExpanded">
                <ui-icon-control :selected="showExpanded">
                    <img src="./img/expand-menu.svg" style="width:65%;height:65%;">
                </ui-icon-control>
            </div>
            <ui-expanded-menu @close="showExpanded = false" :direction="'right'" v-if="showExpanded">
                <!-- v-closable="{exclude: ['button'], handler: 'closeExpanded'}" -->
                <template #menu-items>
                    <template v-for="item in $ui.mainMenu">
                        <ui-expanded-menu-item 
                            v-if="$ui.mainMenuShow.includes(item.name)"
                            @selected="$parent.linkSelected(item.page)" 
                            :key="item.link" 
                            class="position-relative">

                            <template #icon>

                                <img class="menu-item-smaller" :src="$ui.iconFiles[item.icon].normal">

                            </template>

                            <template #label>

                                <a>{{ $trans(item.label) }}</a>

                            </template>

                        </ui-expanded-menu-item>
                    </template>

                    <template v-if="isUserAuthenticated && isUserAdmin">
                        <hr class="my-2 mb-0">
                        <ui-expanded-menu-item @selected="$parent.linkSelected('admin')">
                            <template #icon>

                                <div>
                                    <ui-icon-control  :size="32">
                                        <img src="./img/admin-icon.svg" style="width:65%;height:65%;">
                                    </ui-icon-control>
                                </div>

                            </template>
                            <template #label>

                                <a @click="$parent.linkSelected('admin')">{{$trans('Admin')}}</a>

                            </template>
                        </ui-expanded-menu-item>
                    </template>

                    <template v-if="$config.platform.site_languages.length > 1">
                        <hr class="my-2 mb-0">

                        <ui-expanded-menu-item 
                            v-for="lang in $config.platform.site_languages"
                            :key="lang"
                            @selected="$parent.changeLang(lang)"
                        >

                            <template #icon>

                                <ui-icon-control :size="32" :selected="lang == $lang">
                                    <span >{{ lang.toUpperCase() }}</span>
                                </ui-icon-control>

                            </template>

                            <template #label>

                            <a>{{$trans(lang.toUpperCase() + '_language')}}</a>

                            </template>

                        </ui-expanded-menu-item>
                    </template>
                    <hr class="my-2 mb-0">

                    <ui-expanded-menu-item v-if="!isUserAuthenticated" @selected="$parent.uiStore.modal = 'login-modal'">

                        <template #icon>

                            <profile-image-simple :profile="{}" ></profile-image-simple>

                        </template>

                        <template #label>

                            <a>{{$trans('Login')}}</a>

                        </template>

                    </ui-expanded-menu-item>

                    <ui-expanded-menu-item v-if="!isUserAuthenticated" @selected="$parent.linkSelected('onboarding')">

                        <template #icon>

                            <profile-image-simple :profile="{}" ></profile-image-simple>

                        </template>

                        <template #label>

                            <a>{{$trans('Register')}}</a>

                        </template>

                    </ui-expanded-menu-item>

                    <ui-expanded-menu-item v-if="isUserAuthenticated" @selected="$parent.uiStore.modal = 'logout-modal'">

                        <template #icon>

                            <profile-image-simple :profile="$currentUser"></profile-image-simple>

                        </template>

                        <template #label>

                            <a>{{$trans('Logout')}}</a>

                        </template>

                    </ui-expanded-menu-item>

                    <hr class="my-2 mb-0">





                    <div style="padding-left:1em;">
                        <div><a class="small" href="/policies/toc/" target="_blank">Terms and Conditions</a></div>
                        <div><a class="small" href="/policies/privacy/" target="_blank">Privacy Policy</a></div>
                    </div>

                    <hr class="my-2 mb-0" v-if="$ui.showPoweredBy">

                    <ui-expanded-menu-item @selected="externalLinkSelected('https://missions.dev/')" v-if="$ui.showPoweredBy">

                        <template #icon>


                        </template>

                        <template #label>



                        </template>

                    </ui-expanded-menu-item>
                </template>

            </ui-expanded-menu>

        </OnClickOutside>

    </div>

</template>

<style scoped>
.menu-item-smaller {
max-height:1.5em;
}
</style>

<script>

import { OnClickOutside } from '@vueuse/components'

export default {
    name: 'nav-header-more-menu',
    data() {
        return {
            showExpanded: false
        }
    },
    components: {
        OnClickOutside
    },
    computed: {
        isUserAdmin: function() {
            if (this.isUserAuthenticated && this.$stores.auth.user_type == 'admin') {
                return true
            }
            return false
        }
    },
    props: ['menuItems'],
    methods: {
        externalLinkSelected(link) {
            window.open(link, '_blank')
        },
        closeExpanded() {
            this.showExpanded = false
        }
    }
}


</script>