<template>

    <div>

        <nav class="navbar navbar-expand-lg navbar-dark" style="background-color:#4c4c4c;">
            <h2 style="font-size: 4em;color:white;" class="marker mr-4 ml-4">{{ $trans('Admin') }}</h2>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" style="background-color:#4c4c4c;" id="navbarNav">
                <ul class="navbar-nav">

                    <li v-for="(section) in $ui.adminSections" class="nav-item" :class="{ active: currentView==section }">
                        <ui-a :href="'/admin/' + section + '/'" class="nav-link">{{$trans('admin_' + section)}}</ui-a>
                    </li>

                </ul>
            </div>
        </nav>

        <div v-if="currentView === 'wizard'">

            <admin-platform-wizard v-if="!wizardFinished" @finished="wizardFinished = true" />
            <div v-else class="pt-4">
                <h2 class="medium-2 text-center mt-4 mb-4">You have finished the platform admin wizard.</h2>
                <h2 class="medium-2 text-center mt-4 mb-4">You can continue to configure the platform in the section above, or start the wizard again.

                </h2>
                <h2 class="medium-2 text-center mt-4 mb-4">.

                    <ui-button size="md" @click="wizardFinished=false">Start again</ui-button>
                </h2>


            </div>

        </div>


        <div v-if="currentView === 'users'">

            <admin-users />

        </div>

        <div v-if="currentView === 'submissions'">
            <admin-submissions/>
        </div>

        <div v-if="currentView === 'tags'">
            <admin-tags/>
        </div>

        <div v-if="currentView === 'groups'">

            <admin-groups/>

        </div>

        <div v-if="currentView === 'spaces'">
            <admin-spaces/>
        </div>
        <div v-if="currentView === 'config'">
            <admin-config/>
        </div>

        <div v-if="currentView === 'platform' || currentView === 'admin'">

            <admin-platform/>

        </div>


        <div v-if="currentView === 'accounts'">

            <admin-accounts class="mt-4"/>

        </div>

        <div v-if="currentView === 'translations'">

            <admin-translations/>

        </div>


        <div v-if="currentView === 'resources'">
            <admin-resources />
        </div>

        <div v-if="currentView === 'stats'">
            <admin-stats />
        </div>


    </div>

</template>

<script>

import adminUsers from '../components/admin/admin-users.vue'
import adminSubmissions from '../components/admin/admin-submissions.vue'
import adminTags from '../components/admin/admin-tags.vue'
import adminGroups from '../components/admin/admin-groups.vue'
import adminSpaces from '../components/admin/admin-spaces.vue'
import adminConfig from '../components/admin/admin-config.vue'
import adminPlatform from '../components/admin/admin-platform.vue'
import adminAccounts from '../components/admin/admin-accounts.vue'
import adminTranslations from '../components/admin/admin-translations.vue'
import adminResources from '../components/admin/admin-resources.vue'
import adminPlatformWizard from '../components/admin/admin-platform-wizard.vue'
import adminStats from '../components/admin/admin-stats.vue'


export default {
    props: {

    },
    methods: {
        setCurrentView(view) {
            this.$router.push({ name: 'admin', params: { adminView: this.currentView } })
        }
    },
    data() {
        return {
            wizardFinished: false
        }
    },
    components: {
        adminUsers,
        adminSubmissions,
        adminTags,
        adminGroups,
        adminSpaces,
        adminConfig,
        adminPlatform,
        adminAccounts,
        adminTranslations,
        adminResources,
        adminPlatformWizard,
        adminStats
    },
    computed: {
        currentView() {
            return this.$route.params.section
        }
    },
    mounted() {
        this.currentView = this.$route.params.adminView
    },
    watch: {
        currentView(view) {
            localStorage.currentView = this.currentView
        }
    }
}
</script>

<style lang="css" scoped>
</style>
