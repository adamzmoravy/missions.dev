<template>

    <div class="container">
        <div class="row">
            <div class="col-md-2">

            </div>
            <div class="col-md-10 mt-4" v-if="platformConfig">
                    <ui-steps pt-3 :stepsStyle="'yellow'" @finished="$emit('finished')" class="col-lg-10 pt-4" v-slot:default="slotProps" :stages="stages" @stage-changed="stageChanged">

                      <div class="mt-3">

                            <div v-if="slotProps.stageName == 'platform_name'">
                                <p class="medium-2 mb-1">
                                    <strong>{{$trans('Platform info')}}</strong>
                                </p>
                                <p class="medium-2 pb-3">
                                    <span>{{$trans('Platform_wizard_1')}}</span>
                                    <div class="row">
                                      <div class="col-md-12">
                                          <div class="form-group mt-3 pb-2">
                                              <label for="site_title">{{$trans('Title')}}</label>
                                              <p class="mb-2">Full title of the platform</p>
                                              <ui-input-text :max-length="80" id="site_title" v-model="platformConfig.site_title" />
                                          </div>
                                           <div class="form-group mt-3 pb-2">
                                              <label for="site_title">{{$trans('Label')}}</label>
                                              <p class="mb-2">Short version, or acronym, of the platform title to show when space is limited.</p>
                                              <ui-input-text style="max-width:300px;" :max-length="20" id="site_label" v-model="platformConfig.site_label" />
                                          </div>
                                           <div class="form-group mt-3 pb-2">
                                              <label for="mission_statement">{{$trans('Mission statement')}}</label>
                                              <p class="mb-2">What is the community using this platform working towards?</p>
                                              <ui-textarea :max-length="400" id="mission_statement" v-model="platformConfig.mission_statement" />
                                          </div>

                                        </div>
                                     </div>
                                </p>
                              </div>

                              <div v-if="slotProps.stageName == 'platform_logo'" class="mb-4">
                                <p class="medium-2 mb-1">
                                    <strong>{{$trans('Platform logo')}}</strong>
                                </p>
                                <p>Choose a logo for your platform, which will be seen in the top-left corner. We reccomend 1:1 shape, or at least not much wider than 16:9.</p>
                                <p>Uploading a platform logo is optional.</p>

                                 <platform-identifier class="mt-2"></platform-identifier>

                                 <ui-button v-if="platformConfig.platform_logo != '' && platformConfig.platform_logo != null" button-type="cancel" size="sm" @click="removeLogo()">Clear</ui-button>
                                <ui-image-upload size="md" class="mt-3" :button-label="$trans('Choose image')" @uploaded="saveLogo" ref="image-upload" />                                

                              </div>

                              <div v-if="slotProps.stageName == 'platform_access'" class="mb-4">
                                <p class="medium-2 mb-1">
                                  <strong>{{$trans('Platform access')}}</strong>
                                </p>

                                <p>You can decide if your platform is accessible to anyone, or if it is only accessible to current users when they log in.</p>

                                <div class="form-group mt-3 pb-0">
                                 <h3 class="medium">{{ $trans('Access') }}</h3>

                                  <div>
                                    <label class="me-3">
                                      <input
                                        name="access"
                                        type="radio"
                                        value="public"
                                        v-model="siteAccess"
                                      />
                                      {{$trans('Public')}} 
                                      <span class="text-muted small ms-1">({{$trans('Anyone can view and join')}})</span>
                                    </label><br>
                                    <label>
                                      <input
                                        name="access"
                                        type="radio"
                                        value="members_only"
                                        v-model="siteAccess"
                                      />
                                      {{$trans('Private')}} 
                                      <span class="text-muted small ms-1">({{$trans('Only current users can access')}})</span>
                                    </label>
                                  </div>
                                </div>

                                <ui-panel class="bg-white pb-2" v-if="platformConfig.access.site != 'public'">
                                  <h3 class="medium">{{ $trans('Secret access link') }}</h3>

                                <div class="w-100">
                                    <input  class="mr-1"
                                    id="secret_access_code_active" type="checkbox" v-model="platformConfig.secret_access_code_active">
                                    <label for="secret_access_code_active"> Enable access link</label>
                                </div>
                                <div class="w-100">
                                    <input :disabled="!platformConfig.secret_access_code_active" type="text" :value="secretAccessLink" readonly :size="secretAccessLink.length" style="max-width:100%;">
                                    <button @click="copySecretAccessLink">Copy link</button>                                        
                                </div>
                                <p class="mt-2 small">When the platform is set to private mode, anyone with this link will be able to register and gain access. Only share with those who are supposed to have access to the platform.</p>
                                </ui-panel>

                              </div>                              

                              <div v-if="slotProps.stageName == 'platform_thankyou'" class="mb-4">
                                <p class="medium-2 mb-1">
                                    <strong>{{$trans('You have configured the basics')}}</strong>
                                </p>
                                <p>You have now configured the basics of your platform. To keep modifying it, you can use the platform admin (cog icon top right).</p>
                                <p>For some configurations and customisations, you will need to talk to us manually. Feel free to reach out to us to have a chat about what you can do.</p>

                              </div>               

                          </div>
                    </ui-steps>
            </div>
        </div>
    </div>

</template>

<script setup>

</script>

<script>
import PlatformIdentifier from '../platform-identifier.vue'
import backend from '../../backend.js'
export default {
    name: 'AdminPlatformWizard',
    props: {
        stages: {
            type: Array,
            default: () => [
                { name: 'platform_name', label: 'Platform Name' },
                { name: 'platform_logo', label: 'Platform Logo' },
                { name: 'platform_access', label: 'Platform Access' },
                { name: 'platform_thankyou', label: 'Well done' }

             ]
        }
    },
    components: {
        PlatformIdentifier
    },
    emits: ['finished'],
    data() {
        return {
            siteAccess: null,
            stage: '',
            platformConfig: null,
            platformAdminConfig: {
              secret_access_code: ''
            }
        };
    },
    methods: {
        copySecretAccessLink () {
            // copy this.secretAccessLink to clipboard
            navigator.clipboard.writeText(this.secretAccessLink)
            alert('Link has been copied to clipboard')
        },
        removeLogo() {
            this.platformConfig.platform_logo = ''
            this.updatePlatformConfig()
        },
        saveLogo: function(data) {
          console.log('saveLogo', data)
            this.platformConfig.platform_logo = data.file.url
            this.updatePlatformConfig()
        },
        fetchPlatformConfig: function() {

            this.$stores.platform.fetchConfig()

        },
        fetchPlatformAdminConfig: function() {

            backend.get('/platform/admin/').then((response) => {
                this.platformAdminConfig = response.data.platform
            })

        },
        updatePlatformConfig() {

            backend.patch('/platform/', this.platformConfig).then((response) => {
                this.fetchPlatformConfig()
            })

        },
        stageChanged (stage) {
            this.stage = stage

            // compare the json of this.platformConfig with this.$config.platform
            if (JSON.stringify(this.platformConfig) !== JSON.stringify(this.$config.platform)) {
                this.updatePlatformConfig()
            }
        },
        finished() {
            this.$emit('finished')
        },
        draftConfig() {
            const config = JSON.parse(JSON.stringify(this.$config))
            this.platformConfig = config.platform
        } 
    },
    computed: {
        secretAccessLink: function() {
            return window.location.origin + '/' + this.$lang + '/access/' + this.platformAdminConfig.secret_access_code + '/'
        },
        configSiteAccess: function() {
            return this.$config.platform.access.site
        }
    },
    watch: {
        configSiteAccess: {
          handler: function(newValue, oldValue) {
            this.siteAccess = newValue
          },
          immediate: true
        },
        siteAccess: {
          handler: function(newValue, oldValue) {

            if (oldValue) {
              if (!confirm('Are you sure you want to change the platform access setting? Effects till take place right away.')) {
                this.siteAccess = this.platformConfig.access.site
                return
              }
            }
            this.platformConfig.access.site = newValue
            this.updatePlatformConfig()
            
          },
          immediate: false
        },
        $config: function(config) {
          this.draftConfig()
        }
    },
    mounted() {
         // Initialize the platform configuration if needed
        this.draftConfig()
        this.siteAccess = this.$config.platform.access.site
        this.fetchPlatformAdminConfig()
    }
}



</script>
