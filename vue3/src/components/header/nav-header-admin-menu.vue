<template>

    <OnClickOutside @trigger="adminPanel = false">

        <a @click="adminPanel = !adminPanel">
            <ui-icon-control v-tooltip="$trans('Admin')" :selected="adminPanel" class="mr-2">
                <img src="./img/admin-icon.svg" style="width:65%;height:65%;">
            </ui-icon-control>
        </a>

        
        <ui-expanded-menu class="admin-expanded pt-0 pb-1" 
            @close="adminPanel = false" 
            :direction="'right'" 
            v-if="adminPanel"
        >

            <template #menu-items>

                <template v-for="section in $ui.adminSections">
                    <ui-expanded-menu-item 
                        style="padding-left:0.5em;padding-right:0.5em;"
                        @selected="$router.push('/' + $lang + '/admin/' + section + '/')" 
                        class="position-relative">

                        <template #icon>

                            <img src="./img/admin-icon.svg" class="entity-icon" style="width:70%;height:70%;">


                        </template>

                        <template #label>

                            {{$trans('admin_' + section)}}

                        </template>

                    </ui-expanded-menu-item>
                </template>

            </template>

        </ui-expanded-menu>        

    </OnClickOutside>

</template>

<style scoped>
.icon-control {
cursor:pointer;
height:40px;
width:40px;
}
.notifications-expanded {
width:350px;    
}

</style>

<script>
import { OnClickOutside } from '@vueuse/components'


export default {
name: 'nav-header-admin',
data() {
    return {
        adminPanel: false
    }
},
watch: {

},
props: {
},
components: {
    OnClickOutside
},
methods: {
    toggleAdminPanel() {
        if (this.adminPanel == false) {
            this.adminPanel = true
            return
        }
    },
    closeExpanded() {
        this.adminPanel = false
    }
}
}

</script>