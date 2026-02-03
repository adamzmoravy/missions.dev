<script>
  export default {
    name: 'Missions.dev',
    computed: {
      platformClass() {
        if (this.$config.platform) {
          return 'platform-' + this.$config.platform.platform_key
        }
        return' platform-default'
      }
    },
    watch: {
        platformClass(newVal) {
          document.body.classList.add(newVal)
        }
      }    
  }
</script>

<script setup>
    // import css
    import 'bootstrap/dist/css/bootstrap.css'
    import './assets/css/fonts-ggx7onp.css'
//    import 'material-symbols'
    import './assets/css/MyFontsWebfontsKit.css'
    import './assets/css/admin.css'
    import './assets/css/animation.css'
    import './assets/css/buttons-links.css'
    import './assets/css/comments.css'
    import './assets/css/layout.css'
    import './assets/css/profile.css'
    import './assets/css/tags.css'
    import './assets/css/text.css'
    import './assets/css/tooltip.css'
    import './assets/css/video-conferencing.css'
    import './assets/css/welcome.css'
    import './assets/css/activity.css'
    import './assets/css/header.css'
    import './assets/css/modals.css'
    import './assets/css/happening.css'
    import './assets/platform-themes.scss'
    import './assets/css/hexgrid.css'
    import './assets/css/idea-village.css'
    import './assets/css/mission.css'
    import './assets/css/experiment.css'

    import platform from './components/platform-v2.vue'
    import { usePlatformStore } from './stores/platform'
    let platformStore = usePlatformStore()

</script>

<template>

    <platform v-if="platformStore.platformLoaded" class="theme theme-missions" :class="platformClass" />

    <div v-else class="container pt-5">
        <div class="loading">
            <div v-if="platformStore.loadingError">Error loading {{ platformStore.apiUrl }}</div>
        </div>
    </div>

</template>

<style scoped>



</style>
