
<template>
    <div class="container">
        <password-reset :step="$route.name">

        </password-reset>

    </div>
</template>


<script>

import { useUIStore } from '../stores/ui'
import { mapStores } from 'pinia'

import PasswordReset from '../components/password-reset.vue'

export default {
    name: 'PasswordResetPage',
    methods: {
        loginAndRedirect: function () {
            this.$stores.ui.modal = 'login-modal'
        }
    },
    components: {
        PasswordReset
    },
    watch: {
        '$stores.auth.userData': function (newVal) {
            if (newVal !== null) {
                this.$router.push('/' + this.$lang + '/')
            }
        }
    },
    computed: {
        ...mapStores(useUIStore),
    }
}

</script>