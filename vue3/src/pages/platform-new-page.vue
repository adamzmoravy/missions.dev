<template>
<div class="container mt-2 mt-md-4">

    <div class="row">
        <div class="col-12 col-md-8 offset-md-2">
            <h2 class="x-large">Create new platform</h2>
            <p class="medium">Here you may create a new platform for testing purposes. If you want to use a Missions.dev beyond testing, please contact us.</p>

            <ui-panel class="bg-white">
                <platform-create/>
            </ui-panel>

        </div>

    </div>


    </div>
</template>

<style scoped>
/* Add your component styles here */
</style>

<script>
import platformCreate from '../components/platform-create.vue';
export default {
    name: 'PlatformNewPage',
    components: {
        platformCreate
    },
}
</script>

