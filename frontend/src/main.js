// Start the basics

// Import the main Vue libraries
import { createApp } from 'vue'
import App from './App.vue'
const app = createApp(App)

// Router
import definedRoutes from './routes.js'
const routes = definedRoutes.routes
import { createWebHistory, createRouter } from 'vue-router'
const router = createRouter({
    scrollBehavior(to, from, savedPosition) {
      if (savedPosition) {
        return savedPosition
      } else {
        return { top: 0 }
      }    
    },
    history: createWebHistory(),
    routes,
  })

app.use(router)

// Pinia for state management  
import { createPinia } from 'pinia'
const pinia = createPinia()
app.use(pinia)

// Header management of metadata
import { createHead, VueHeadMixin } from '@unhead/vue'
const head = createHead()
app.use(head)
app.mixin(VueHeadMixin)

// Backend, to interact with the API
import backend from './backend'
app.config.globalProperties.backend = backend


// Globally accessible 
import globalMixin from './mixins/global'
app.mixin(globalMixin)


// Initialize the platform store
import { usePlatformStore } from './stores/platform'
const platformStore = usePlatformStore()

if (import.meta.env.MODE === 'development') {

  let apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8888'
  platformStore.initialize(apiUrl)

} else {

  let apiUrl = '/api'
  platformStore.initialize(apiUrl)

  // dynamically import plausible and initialize with current domain
  import('@plausible-analytics/tracker').then(({ init }) => {
  const domain = (typeof window !== 'undefined' && window.location && window.location.hostname)
    ? window.location.hostname
    : ''

    let domains = []

    if (domains.includes(domain)) {
      init({ domain })
    }

  })

}

// Global dependencies

import materialSymbolsPlugin from '@dbetka/vue-material-symbols'
import 'material-symbols/index.css'
app.use(materialSymbolsPlugin)


import PortalVue from 'portal-vue'
app.use(PortalVue)

import VTooltip from 'v-tooltip'
app.use(VTooltip)

import VueClosable from 'vue-closable'
app.use(VueClosable)

import timeago from 'vue-timeago3'
app.use(timeago)

import VueHorizontal from 'vue-horizontal'
app.component('VueHorizontal', VueHorizontal)

import VueVirtualScroller from 'vue-virtual-scroller'
app.use(VueVirtualScroller)

// Here we globally load components which are used widely throughout the app

import uiTextFitter from './components/ui-text-fitter.vue'
import uiButton from './components/ui-button.vue'
import uiButtonTheme from './components/ui-button-theme.vue'
import uiButtonThemeHaus from './components/ui-button-theme-haus.vue'
import uiA from './components/ui-a.vue'
import uiIconControl from './components/ui-icon-control.vue'
import uiModal from './components/ui-modal.vue'
import uiExpandedMenu from './components/ui-expanded-menu.vue'
import uiExpandedMenuItam from './components/ui-expanded-menu-item.vue'
import uiImgDynamic from './components/ui-img-dynamic.vue'
import uiPanel from './components/ui-panel.vue'
import uiTabs from './components/ui-tabs.vue'
import uiPageBody from './components/ui-page-body.vue'
import uiPageHeader from './components/ui-page-header.vue'
import uiPageLayout from './components/ui-page-layout.vue'
import uiTextarea from './components/ui-textarea.vue'
import uiImageUpload from './components/ui-image-upload.vue'
import uiReadMore from './components/ui-read-more.vue'
import uiImageLightbox from './components/ui-image-lightbox.vue'
import uiShowMore from './components/ui-show-more.vue'
import uiInputText from './components/ui-input-text.vue'
import uiInputKennitala from './components/ui-input-kennitala.vue'
import uiInputList from './components/ui-input-list.vue'
import uiStageProcessExplainer from './components/ui-stage-process-explainer.vue'
import uiCardsContainer from './components/ui-cards-container.vue'
import uiSteps from './components/ui-steps.vue'
import uiStepsModal from './components/ui-steps-modal.vue'
import uiButtonRow from './components/ui-button-row.vue'
import uiJsonEditor from './components/ui-json-editor.vue'
import uiCustomLanguage from './components/ui-custom-language.vue'
import uiHtml from './components/ui-html.vue'
import uiTimespanSelector from './components/ui-timespan-selector.vue'
import uiLanguageField from './components/ui-language-field.vue'
import uiLogos from './components/ui-logos.vue'
import uiDocumentFrame from './components/ui-document-frame.vue'
import uiQuestions from './components/ui-questions.vue'
import uiContextMenu from './components/ui-context-menu.vue'
import uiLocationSelector from './components/ui-location-selector.vue'
import uiTags from './components/ui-tags.vue'
import uiInputEmailList from './components/ui-input-email-list.vue'
import uiInputPhoneNumber from './components/ui-input-phone-number.vue'
import uiInputEmail from './components/ui-input-email.vue'
import uiInputUrl from './components/ui-input-url.vue'
import uiCountryFlag from './components/ui-country-flag.vue'
import uiImageAdjust from './components/ui-image-adjust.vue'



app
  .component('uiTextFitter', uiTextFitter)
  .component('uiButton', uiButton)
  .component('uiA', uiA)
  .component('uiIconControl', uiIconControl)
  .component('uiModal', uiModal)
  .component('uiExpandedMenu', uiExpandedMenu)
  .component('uiExpandedMenuItem', uiExpandedMenuItam)
  .component('uiButtonTheme', uiButtonTheme)
  .component('uiImgDynamic', uiImgDynamic)
  .component('uiPanel', uiPanel)
  .component('uiTabs', uiTabs)
  .component('uiPageBody', uiPageBody)
  .component('uiPageHeader', uiPageHeader)
  .component('uiPageLayout', uiPageLayout)
  .component('uiTextarea', uiTextarea)
  .component('uiImageUpload', uiImageUpload)
  .component('uiReadMore', uiReadMore)
  .component('uiImageLightbox', uiImageLightbox)
  .component('uiShowMore', uiShowMore)
  .component('uiInputText', uiInputText)
  .component('uiInputList', uiInputList)
  .component('uiStageProcessExplainer', uiStageProcessExplainer)
  .component('uiCardsContainer', uiCardsContainer)
  .component('uiSteps', uiSteps)
  .component('uiStepsModal', uiStepsModal)
  .component('uiButtonRow', uiButtonRow)
  .component('uiInputKennitala', uiInputKennitala)
  .component('uiJsonEditor', uiJsonEditor)
  .component('uiCustomLanguage', uiCustomLanguage)
  .component('uiHtml', uiHtml)
  .component('uiTimespanSelector', uiTimespanSelector)
  .component('uiLanguageField', uiLanguageField)
  .component('uiButtonThemeHaus', uiButtonThemeHaus)
  .component('uiLogos', uiLogos)
  .component('uiDocumentFrame', uiDocumentFrame)
  .component('uiQuestions', uiQuestions)
  .component('uiContextMenu', uiContextMenu)
  .component('uiLocationSelector', uiLocationSelector)
  .component('uiTags', uiTags)
  .component('uiInputEmailList', uiInputEmailList)
  .component('uiInputPhoneNumber', uiInputPhoneNumber)
  .component('uiInputEmail', uiInputEmail)
  .component('uiInputUrl', uiInputUrl)
  .component('uiCountryFlag', uiCountryFlag)
  .component('uiImageAdjust', uiImageAdjust)


  

import entities from './components/entities.vue'
import entity from './components/entity.vue'
import profileImageSimple from './components/profiles/profile-image-simple.vue'
import profileIndexRow from './components/profiles/profile-index-row.vue'
import profileIndexList from './components/profiles/profile-index-list.vue'
import submissionPreview from './components/submissions/submission-preview.vue'
import stageIcon from './components/submissions/stage-icon.vue'
import adminPanelTable from './components/admin/admin-panel-table.vue'
import commentTopic from './components/comments/comment-topic.vue'

app
  .component('entities', entities)
  .component('entity', entity)
  .component('profileImageSimple', profileImageSimple)
  .component('profileIndexRow', profileIndexRow)
  .component('profileIndexList', profileIndexList)
  .component('submissionPreview', submissionPreview)
  .component('stageIcon', stageIcon)
  .component('adminPanelTable', adminPanelTable)
  .component('commentTopic', commentTopic)


import onboardingTheme from './components/profiles/onboarding-theme.vue'
import onboardingThemeHaus from './components/profiles/onboarding-theme-haus.vue'

app.component('onboarding-theme', onboardingTheme)
app.component('onboarding-theme-haus', onboardingThemeHaus)

app.mount('#app')