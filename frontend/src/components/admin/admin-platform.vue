<template>
    <div class="container-fluid">
        <div class="admin-panel-data-container col-md-12">
            <!--
                // Change from {{ $trans('Save') }} to {{ $trans('Save') }}

            -->

                <div class="mt-5 col-8">
                    <table class="table-hover">
                        <thead>
                            <th></th>
                            <th></th>
                            <th></th>
                        </thead>
                        <tr>
                            <td class="border p-2">{{ $trans('Site title') }}:</td>
                            <td class="border p-1">
                                <input class="'edit-tag-input' form-control" :readonly="'siteTitle' != platformComponentBeingEdited"
                                       type="text" v-model="platformConfig.site_title">
                            </td>


                            <td class="px-2">
                                <button v-if="platformComponentBeingEdited=='siteTitle'"
                                    @click="updatePlatformConfig"
                                    class="btn btn-success btn-block" type="submit" name="button">
                                    {{ $trans('Save') }}
                                </button>
                                <button
                                    v-else
                                    @click="makePlatformComponentEditable('siteTitle')"
                                    class="btn btn-info btn-block" type="submit" name="button">
                                    {{ $trans('Edit') }}
                                </button>

                            </td>

                        </tr>
                        <tr>
                            <td class="border p-2">{{ $trans('Site label') }}:</td>
                            <td class="border p-1">

                                <input class="'edit-tag-input' form-control" :readonly="'siteLabel'!=platformComponentBeingEdited"
                                       type="text" v-model="platformConfig.site_label">
                            </td>
                            <td class="px-2">
                                <button v-if="platformComponentBeingEdited=='siteLabel'"
                                    @click="updatePlatformConfig"
                                    class="btn btn-success btn-block" type="submit" name="button">
                                    {{ $trans('Save') }}
                                </button>
                                <button
                                    v-else
                                    @click="makePlatformComponentEditable('siteLabel')"
                                    class="btn btn-info btn-block" type="submit" name="button">
                                    {{ $trans('Edit') }}
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td class="border p-2">{{ $trans('Mission statement') }}:</td>
                            <td class="border p-1">

                                <input class="'edit-tag-input' form-control" :readonly="'missionStatement'!=platformComponentBeingEdited"
                                       type="text" v-model="platformConfig.mission_statement">
                            </td>
                            <td class="px-2">
                                <button v-if="platformComponentBeingEdited=='missionStatement'"
                                    @click="updatePlatformConfig"
                                    class="btn btn-success btn-block" type="submit" name="button">
                                    {{ $trans('Save') }}
                                </button>
                                <button
                                    v-else
                                    @click="makePlatformComponentEditable('missionStatement')"
                                    class="btn btn-info btn-block" type="submit" name="button">
                                    {{ $trans('Edit') }}
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td class="border p-2">{{ $trans('Site logo') }}:</td>
                            <td class="border p-1">

                                <input class="'edit-tag-input' form-control" :readonly="'platformLogo'!=platformComponentBeingEdited"
                                       type="text" v-model="platformConfig.platform_logo">
                            </td>
                            <td class="px-2">
                                <button v-if="platformComponentBeingEdited=='platformLogo'"
                                    @click="updatePlatformConfig"
                                    class="btn btn-success btn-block" type="submit" name="button">
                                    {{ $trans('Save') }}
                                </button>
                                <button
                                    v-else
                                    @click="makePlatformComponentEditable('platformLogo')"
                                    class="btn btn-info btn-block" type="submit" name="button">
                                    {{ $trans('Edit') }}
                                </button>
                            </td>
                        </tr>
 
                        <tr>
                            <td class="border p-2 align-top">{{ $trans('Header elements') }}:</td>
                            <td class="border p-2">
                                <table>
                                    <tr v-for="element in ['home', 'missions', 'groups', 'meetings', 'activity']">
                                        <td>{{element}}</td>
                                        <td>
                                            <input :value="element" class="ml-2" :disabled="'headerElements' != platformComponentBeingEdited"
                                                   :id="element" type="checkbox" v-model="platformConfig.header_elements">
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td class="align-top px-2">
                                <button v-if="platformComponentBeingEdited=='headerElements'"
                                        @click="updatePlatformConfig"
                                        class="btn btn-success btn-block" type="submit" name="button">
                                    {{ $trans('Save') }}
                                </button>
                                <button
                                        v-else
                                        @click="makePlatformComponentEditable('headerElements')"
                                        class="btn btn-info btn-block" type="submit" name="button">
                                     {{ $trans('Edit') }}
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td class="border p-2 align-top">{{ $trans('Secret access link') }}:</td>
                            <td class="border p-2 w-100">
                                <div class="w-100">
                                    <input :disabled="'secretAccess' != platformComponentBeingEdited" value="secret_access_code_active" class="ml-2"
                                    id="secret_access_code_active" type="checkbox" v-model="platformConfig.secret_access_code_active">
                                    <label for="secret_access_code_active"> Enable access link</label>
                                </div>
                                <div class="w-100">
                                    <input :disabled="!platformConfig.secret_access_code_active" type="text" :value="secretAccessLink" readonly :size="secretAccessLink.length" style="max-width:60%;">
                                    <button @click="copySecretAccessLink">Copy link</button>                                        
                                </div>
                                <p class="mt-2 small">When the platform is set to private mode, anyone with this link will be able to register and gain access. Only share with those who are supposed to have access to the platform.</p>

                            </td>
                            <td class="align-top px-2">
                                <button v-if="platformComponentBeingEdited=='secretAccess'"
                                        @click="updatePlatformConfig"
                                        class="btn btn-success btn-block" type="submit" name="button">
                                    {{ $trans('Save') }}
                                </button>
                                <button
                                        v-else
                                        @click="makePlatformComponentEditable('secretAccess')"
                                        class="btn btn-info btn-block" type="submit" name="button">
                                    {{ $trans('Edit') }}
                                </button>
                            </td>
                        </tr>
                    </table>
                </div>
        </div>
    </div>
</template>

<script>

import backend from '../../backend.js'

export default {
    props: {
        apiUrl: {
            required: false,
            type: String,
            default: function () {
                return ''
            }
        }
    },
    mounted() {
        this.fetchPlatformConfig()
    },
    computed: {
        language: function() {
            return this.$lang
        },
        secretAccessLink: function() {
            return window.location.origin + '/' + this.language + '/access/' + this.platformConfig.secret_access_code + '/'
        }
    },
    methods: {
        copySecretAccessLink () {
            // copy this.secretAccessLink to clipboard
            navigator.clipboard.writeText(this.secretAccessLink)
            alert('Link has been copied to clipboard')
        },
        fetchPlatformConfig() {

            backend.get('/platform/admin/').then((response) => {
                this.platformConfig = response.data.platform

            })

        },
        createPlatformConfig(newSiteTitle, newSiteLabel, newSiteLangs) {
            this.$http.patch(`${this.apiUrl}/platform/`, {
                site_title: newSiteTitle,
                site_label: newSiteLabel,
                site_languages: newSiteLangs
            }, {
                headers: {
                    'Authorization': this.getAuthorizationHeaderMixin()
                }
            })
        },
        makePlatformComponentEditable(componentBeingEdited) {
            this.platformComponentBeingEdited = componentBeingEdited
        },
        updatePlatformConfig() {

            backend.patch('/platform/', this.platformConfig).then((response) => {
                this.fetchPlatformConfig()
            })

            this.platformComponentBeingEdited = ''
        }
    },
    data() {
        return {
            platformConfig: [],
            platformBeingEdited: false,
            platformComponentBeingEdited: '',
            siteTitle: '',
            siteLabel: '',
            siteLanguages: []
        }
    },
}
</script>

<style lang="css" scoped>
</style>
