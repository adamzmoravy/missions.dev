<template>
    <div>
        <div class="container-fluid">

                <div v-if="newUserFieldsDisplayed" class="mt-3 d-flex flex-column mb-4 col-md-3 col-s-6">
                    <input v-model="firstName" class="mb-2"
                           placeholder="First name" type="text">
                    <input v-model="lastName" class="mb-2"
                           placeholder="Last name" type="text">
                   <input v-model="email" class="mb-2"
                           placeholder="Email" type="text">
                    <input v-model="password" class="mb-2"
                           placeholder="Password" type="text">
                    <div class="">
                        <ui-button @click="createUser(email, firstName, lastName, password)"
                        size="sm" button-type="new" class="mr-1"
                               >
                            {{ $trans('Create') }}
                        </ui-button>
                        <ui-button @click="emptyInputFields(), newUserFieldsDisplayed=false"
                        size="sm" button-type="danger"

                                >
                            {{ $trans('Cancel') }}
                        </ui-button>
                    </div>

                </div>
        </div>
        <ui-modal :title="'Show users in groups'" :open="filterByGroupModalDisplayed" @close="filterByGroupModalDisplayed = false">
            <template #body>
                <entities @selected="filterByGroup" class="w-100" :multi-select="true" :multi-select-selected="filterByGroups" :include-link="false" :title="$trans('Groups')" :entities="allGroups" />                    
            </template>
        </ui-modal>
        <div class="container-fluid">

            <admin-panel-table class="pt-3" 
                @remove-record="disableUser"
                :offer-new-record="false"
                :offer-remove-record="true"
                @save-field="saveField"
                record-type="users"
                :id-field="'user_id'"
                :table-data="tableData" 
                :fields-meta="fieldsMeta"
            >
                <template v-slot:action-buttons>
                    <ui-button class="mr-2" button-type="new" size="md" @click="renderNewUserFields" name="newUser">+ {{ $trans('New user') }}</ui-button>
                </template>
                <template v-slot:filter-buttons>
                    <ui-button class="mr-2" button-type="default" size="md" @click="filterByGroupModalDisplayed = true">{{ $trans('Filter by group') }} ({{ filterByGroups.length }})</ui-button>
                </template>
            </admin-panel-table>

            </div>
        </div>
</template>

<script>
import fieldsMeta from '../../../../config/config_fields_users.json'
import backend from '../../backend.js'
import adminPanelTable from './admin-panel-table.vue'

export default {
    name: 'AdminUsers',
    props: {
        apiUrl: {
            required: false,
            type: String,
            default: function() {
                return ''
            }
        }
    },
    components: {
        adminPanelTable
    },
    mounted() {
        this.fetchUsers()
    },
    computed: {
        allGroups() {
            return this.$stores.groups.getEntities()
        },
        allFilteredGroups() {
            return this.allGroups.filter((group) => this.filterByGroups.includes(group.id))
        },
        allUsersInFilteredGroups () {
            // Get all users in the groups that are selected in the filterByGroups array getting the values in the data.registrations array
            let usersInFilteredGroups = []
            this.allFilteredGroups.forEach((group) => {
                usersInFilteredGroups = usersInFilteredGroups.concat(group.data.registrations)
            })
            return usersInFilteredGroups
        },
        metaDefinitions() {
            if (this.$config.platform.platform_meta_definitions) {
                return this.$config.platform.platform_meta_definitions
            }
            return {}
        },
        tableData() {
            let users = this.users

            // If there are groups selected in the filterByGroups array
            if (this.filterByGroups.length > 0) {
                // remove users if user.user_id is not in the usersInFilteredGroups array
                users = users.filter((user) => {
                    return this.allUsersInFilteredGroups.includes(parseInt(user.user_id))
                })

            }

            // Go through the meta fields object and add the meta fields to the users object
            users.forEach((user) => {
                if (user.meta) {
                    for (var key in user.meta) {
                        user['meta_' + key] = user.meta[key]
                    }
                }
            })

            return users
        },
        fieldsMeta() {
            let myFieldsMeta = fieldsMeta
            
            let metaDefinitionsArray = Object.keys(this.metaDefinitions)

            metaDefinitionsArray.forEach((metaDefinition) => {
                myFieldsMeta['meta_' + metaDefinition] = {
                    label: this.metaDefinitions[metaDefinition].label,
                    type: "string",
                    editable: true,
                    required: false,
                    description: this.metaDefinitions[metaDefinition].label
                }
            })

            return myFieldsMeta
        }
    },
    methods: {
        filterByGroup(data) {
            if (!this.filterByGroups.includes(data.happening_id)) {
                this.filterByGroups.push(data.happening_id)
            } else {
                this.filterByGroups = this.filterByGroups.filter((group) => group !== data.happening_id)
            }
        },
        saveField(event) {
            console.log(event)
            return true
            let userId = this.users[event.record].user_id
            this.patchUser(userId, {
                [event.field]: event.value
            })
        },
        fetchUsers() {
            backend.get('/users/preview/').then((response) => {
                this.users = response.data.previews
            })
        },
        disableUser(userRecord) {

            backend.delete('/users/' + userRecord.user_id + '/').then(response => {
                this.fetchUsers()
            })

        },
        // TODO: Create enableUser
        createUser(newUserEmail, newUserFirstName, newUserLastName, newUserPassword) {
            var authHeader = this.getAuthorizationHeaderMixin()
            return this.$http.post(this.apiUrl + '/users/', {
                email: newUserEmail,
                first_name: newUserFirstName,
                last_name: newUserLastName,
                password: newUserPassword
            }, {
                headers: {
                    'Authorization': authHeader
                }
            }).then((response) => {
                this.fetchUsers()
                this.emptyInputFields()
            },
            (error) => {
                alert(error.response.request.statusText)
            })
        },
        renderNewUserFields() {
            this.newUserFieldsDisplayed = !this.newUserFieldsDisplayed
        },
        emptyInputFields() {
            this.email = ''
            this.firstName = ''
            this.lastName = ''
            this.password = ''
        }
    },
    data() {
        return {
            users: [],
            filterByGroupModalDisplayed: false,
            filterByGroups: [],
            newUserFieldsDisplayed: false,
            email: '',
            firstName: '',
            lastName: '',
            password: ''
        }
    }
}
</script>

<style lang="css" scoped>
</style>
