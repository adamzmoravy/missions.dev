
import { useTranslationsStore } from '../stores/translations'
import { useEventsStore } from '../stores/events'
import { useProfilesStore } from '../stores/profiles'
import { useUIStore } from '../stores/ui'
import { useAuthStore } from '../stores/auth'
import { useEntitiesStore } from '../stores/entities'
import { useSubmissionsStore } from '../stores/submissions'
import { useGroupsStore } from '../stores/groups'
import { useSpacesStore } from '../stores/spaces'
import { usePlatformStore } from '../stores/platform'
import { useTagsStore } from '../stores/tags'
import { getCurrentInstance } from 'vue';
import { useBreakpoints } from '@vueuse/core'


export default {
    data: () => {
        return {
            mounted: false
        }
    },
    props: {
    },
    methods: {
        $truncate: function (string, length) {
            if (string.length > length) {
                return string.substring(0, length) + '...'
            } else {
                return string
            }
        },
        $trans: function (string) {
            return this.$stores.translations.trans(string)
          },
          $theme: function (componentName) {
            let theme = componentName + '-theme-' + this.$stores.platform.ui.theme

            let isComponentExists = theme in getCurrentInstance().appContext.components
            let themeCamelCase = theme.replace(/-([a-z])/g, function (g) { return g[1].toUpperCase(); });

            let isComponentExistsCamelCase = themeCamelCase in getCurrentInstance().appContext.components

            if (isComponentExists || isComponentExistsCamelCase) {
                return componentName + '-theme-' + this.$stores.platform.ui.theme
            } else {
                return componentName + '-theme'
            }
          }
    },
    computed: {
        $breakpoints() {
            return useBreakpoints({
                mobile: 0, // optional
                tablet: 640,
                laptop: 1024,
                desktop: 1280,
              })
        },
        $stores() {
            return {
                translations: useTranslationsStore(),
                events: useEventsStore(),
                profiles: useProfilesStore(),
                ui: useUIStore(),
                auth: useAuthStore(),
                entities: useEntitiesStore(),
                submissions: useSubmissionsStore(),
                groups: useGroupsStore(),
                spaces: useSpacesStore(),
                platform: usePlatformStore(),
                tags: useTagsStore()
            }
        },
        $config() {
            return {
                platform: this.$stores.platform.config
            }
        },
        $ui() {
            return this.$stores.platform.ui
        },
        $currentUser() {
            if (this.$stores.auth.userData) {
                return this.$stores.auth.userData
            }
            return false
        },
        $lang() {
            const translationsStore = useTranslationsStore()
            return translationsStore.selectedLanguage
        },
        isUserAuthenticated() {
            return this.$isUserAuthenticated
        },
        $isUserAuthenticated() {
            return this.$stores.auth.isAuthenticated
        },
        $latestEvent() {
            return this.$stores.events.getLatestEvent()
        },
        $loadMoreOnScroll() {
            return this.$stores.ui.loadMoreOnScroll
        }
    }
}
