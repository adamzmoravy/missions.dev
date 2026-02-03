<template>

    <div id="nav-login"  class="d-inline mr-2">
        <OnClickOutside @trigger="showExpanded = false">

            <a ref="button" @click="showExpanded=!showExpanded">
                
                    <div class="d-flex align-items-center">
                        <div>
                            <profile-image-simple style="max-width:40px;" :profile="profile"></profile-image-simple>
                        </div>
                    </div>
            </a>

            <ui-expanded-menu @close="showExpanded = false" :direction="'right'" v-if="showExpanded">

                <template #menu-items>

                    <div class="pl-4 p-2"> 

                        <h3 class="medium-2 mb-0"><ui-a :href="'/profile/' + profile.user_id + '/'">{{ profile.first_name }} {{ profile.last_name }}</ui-a></h3>
                        <ui-a class="underline" :href="'/profile/' + profile.user_id + '/'">{{ $trans('View my profile') }}</ui-a>


                    </div>


                    <hr class="mt-2 mb-1" >

                    <div class="pl-3 p-2" > 

                        <div v-if="myGroups.length != 0">
                            <h3 class="medium">{{ $trans('My groups') }}</h3>
                            <entities class="entities mb-3" :title="$trans('My groups')" :entities="myGroups" :limit="999" />
                        </div>

                        <div v-if="mySubmissions.length != 0">
                            <h3 class="medium">{{ $trans('My submissions') }}</h3>
                            <entities  class="entities" :title="$trans('My submissions')" :entities="mySubmissions" :limit="999" />
                        </div>

                    </div>
                    
                </template>

            </ui-expanded-menu>
        </OnClickOutside>

    </div>

</template>
<style scoped>
 .entities {
    min-width:300px;
 }
</style>

<script>

import { OnClickOutside } from '@vueuse/components'

export default {
    name: 'nav-header-profile-menu',
    data() {
        return {
            showExpanded: false
        }
    },
    components: {
        OnClickOutside
    },
    computed: {
        profile: function () {
            return this.$stores.auth.userData
        },
        myGroups: function () {
            return this.$stores.groups.getEntitiesByMember(this.profile.user_id)
        },  
        mySubmissions: function () {
            if (this.profile.user_id) {
                return this.$stores.submissions.getEntities([{
                    sorter: 'updated',
                    direction: 'asc'
                }],
                [
                    {
                        selection: 'owner',
                        value: this.profile.user_id.toString()
                    }
                ])
            }
        }
    },
    props: ['menuItems'],
    methods: {
        closeExpanded() {
            this.showExpanded = false
        }
    }
}


</script>