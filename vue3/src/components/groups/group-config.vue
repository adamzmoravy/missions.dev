<template>
<div class="px-3">

    <div class="row">

        <table class="table">
            <thead class="thead-light">
                <tr>
                    <th colspan="10">Name</th>
                </tr>
            </thead>
            <tr>
                <td colspan="10">
                    <input type="text" class="w-100" id="name" v-model="editedConfig.name">
                </td>
                <td>
                </td>
            </tr>
            <thead class="thead-light">
                <tr>
                    <th colspan="10">Access</th>
                </tr>
            </thead>
            <tr>
                <td class="">
                    <input type="radio" id="access_public" value="public" name="access" v-model="editedConfig.access">
                    <label for="access_public">Public</label>
                </td>
                <td>
                </td>
            </tr>
            <tr>
                <td class="">
                    <input type="radio" id="access_private" value="private" name="access" v-model="editedConfig.access">
                    <label for="access_private">Private</label>
                </td>
                <td>
                </td>

            </tr>
            <thead class="thead-light">
                <tr>
                    <th colspan="10">New registrations</th>
                </tr>
            </thead>
            <tr>
                <td class="">
                    <input type="radio" id="registrations_open" value="open" name="registrations" v-model="editedConfig.registrations">
                    <label for="registrations_open">Open</label>
                </td>
                <td>
                    Anyone can register
                </td>
            </tr>
            <tr>
                <td class="">
                    <input type="radio" id="registrations_closed" value="closed" name="registrations" v-model="editedConfig.registrations">
                    <label for="registrations_closed">Closed</label>
                </td>
                <td>
                    Only admin can add new participants
                </td>
            </tr>
            <tr>
                <td class="">
                    <input type="radio" id="registrations_applications" value="applications" name="registrations" v-model="editedConfig.registrations">
                    <label for="registrations_applications">Applications</label>
                </td>
                <td>
                    Accepting applications
                </td>
            </tr>
            <thead class="thead-light">
                <tr>
                    <th colspan="10">Notify of registrations</th>
                </tr>
            </thead>
            <tr>
                <td colspan="10">
                    <p class="mb-1">Selected users will get an e-mail notification when someone enters or leaves the group</p>
                    <entities :entities="notifyOnRegistrationProfiles"></entities>
                    <ui-button class="mt-3" size="md" @click="localUi.selectUsersToNotify = true">Select</ui-button>

                    

                    <entities 
                        @close="localUi.selectUsersToNotify = false" 
                        v-if="localUi.selectUsersToNotify" 
                        :multi-select="true" 
                        :modal-only="true"                                     
                        :show-search-bar="true" 
                        @selected="selectedNotifyUsers($event)" 
                        @unselected="unSelectedNotifyUsers($event)" 
                        :multi-select-selected="editedConfig.notify_on_registrations" 
                        :include-link="false" 
                        :sub-title="'Select users who should be in group. Unselect to remove them.'"
                        :title="$trans('Group participants')" 
                        :entities="$stores.profiles.getEntities()" 
                        :limit="5000" 
                        class="w-100" />


                </td>
                <td>
                </td>
            </tr>            
            <thead class="thead-light">
                <tr>
                    <th colspan="10">Registration survey</th>
                </tr>
                <tr>
                    <td colspan="2" class="w-100 px-0">
                        
                        <textarea  v-model="editedConfig.survey"></textarea>
                        <!--
                        <v-jsoneditor :options="jsonEditorOptions" v-model="editedConfig.survey" :plus="true" height="400px" @error="console.log('error')" />

                        -->

                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="w-100">
                        <input type="radio" value="tree" name="json-options" v-model="jsonEditorOptions.mode"> Tree
                        <input type="radio" value="code" name="json-options" v-model="jsonEditorOptions.mode"> Code

                        <div class="pull-right">CTR-i to format</div>
                    </td>
                </tr>

            </thead>

        </table>

        <ui-button class="mt-3" @click="updateConfig()">Update</ui-button>

    </div>

</div>

</template>


<script>

export default {
    
        name: 'group-config',
        data() {
            return {
                localUi: {
                    userPopupId: null,
                    selectUsersToNotify: false,
                },
                jsonEditorOptions: {mainMenuBar: false, mode: 'tree'},
                editedConfig: {},
                newConfigString: '',
            }
        },
        props: {
            happening: {
                required: true,
                type: Object
            }
        },
        computed: {
            notifyOnRegistrationProfiles: function () {
                if (this.editedConfig.notify_on_registrations === undefined)
                    return []
                else {
                    return this.$stores.profiles.getEntities([], [{
                        selection: 'ids',
                        value: this.editedConfig.notify_on_registrations
                    }])

                }
            },
            config: function () {
                return this.happening.config
            }
        },
        methods: {
            selectedNotifyUsers: function (selected) {
                let id = selected.user_id                
                this.editedConfig.notify_on_registrations.push(id)
            },
            unSelectedNotifyUsers: function (unselected) {
                let unselectedId = unselected.user_id
                this.editedConfig.notify_on_registrations = this.editedConfig.notify_on_registrations.filter(id => id !== unselectedId)
            },
            updateConfig: function () {
                this.$emit('update-config', this.editedConfig)
            }
        },
        mounted() {
            this.editedConfig = this.config
        }
}

</script>