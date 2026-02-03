<template>
    <ui-modal :open="$stores.ui.modal == 'logout-modal'" @close="$emit('close')">
        <template #header>
            <h2>Logout</h2>
        </template>
        <template #body>

                <h1 class="large m-auto">{{$trans('Are you sure you want to log out?')}}</h1>

        </template>
        <template #footer>
            <div class="modal-buttons">
                <ui-button id="modal-logout-submit" type="submit" :label="$trans('logout')" button-type="new" @click="logout()"></ui-button>
                <ui-button button-type="cancel" :label="$trans('Cancel')" type="button" @click="close"></ui-button>    
            </div>
        </template>
    </ui-modal>
</template>

<style>

.login-body {

}

</style>

<script>

export default {
    name: 'logout-modal',
    methods: {
        logout() {
            this.$stores.auth.logout()
            this.close()
        },
        close: function () {
            this.$emit('close')
        },
    }
}


</script>