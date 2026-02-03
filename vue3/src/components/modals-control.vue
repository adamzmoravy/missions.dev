<template>

    <div>


        <component v-if="uiStore.modal" :is="uiStore.modal" @close="closeModal()" />

        <submission-create v-if="$stores.ui.newSubmission != null" />

        <group-post-create v-if="$stores.ui.newPost != null" />

        <group-preview-modal />

        <group-event-new @event-added="newEventAdded" v-if="$stores.ui.newEvent != null" @close="$stores.ui.newEvent = null" :pre-selected-group-id="$stores.ui.newEvent.groupId" :open="$stores.ui.newEvent != null" />

    </div>
    

</template>

<script>

import { useUIStore } from '../stores/ui'
import { mapStores } from 'pinia'

import loginModal from './authentication/login-modal.vue'
import logoutModal from './authentication/logout-modal.vue'
import submissionCreate from './submissions/submission-create.vue'
import groupPreviewModal from './groups/group-preview-modal.vue'
import groupPostCreate from './groups/group-post-create.vue'
import groupEventNew from './groups/group-event-new.vue'

export default {
    name: 'modals-control',
    components: {
        loginModal,
        logoutModal,
        submissionCreate,
        groupPostCreate,
        groupPreviewModal,
        groupEventNew
    },
    data () {
        return {

        }
    },
    computed: {
        ...mapStores(useUIStore)
    },
    methods: {
        closeModal: function () {
            this.uiStore.modal = null
        }
    }
}

</script>