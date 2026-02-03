<template>
    <div>

    <ui-panel class="bg-white preview-container h-100 position-relative d-flex group-container">
        <div>
            <h3 class="medium-2"><ui-a :href="'/group/' + happening.happening_id" class="stretched-link">{{ happening.name }}</ui-a></h3>
        </div>

        
            <h4 class="small-2" v-if="happening.registrations.includes($currentUser.user_id)">Joined</h4>
            <h4 class="small-2" v-else></h4>
            
            <div class="d-flex flex-wrap align-content-center profiles-container w-100" style="pointer-events: none;">


                                <profile-index-row
                                    class="w-100"
                                    :profiles="profiles"
                                    :profile-size="30"
                                    :max-show-num-profiles="8"
                                    :show-name="false"
                                    @show-all-profiles="$emit('show-all-profiles')"
                                    @profiles-fit=""
                                />

            </div>


        </ui-panel>
    </div>


</template>

<style scoped>
.group-container {
    flex-direction:column;
    justify-content:space-between;
    min-height:120px;
}


</style>
<script>

    export default {
        name: 'group-preview',
        props: {
            happening: {
                required: true
            }
        },
        computed: {
            profiles: function () {
                return this.$stores.profiles.getProfilePreviewsByIds(this.happening.registrations)
            },
        }
    }

</script>