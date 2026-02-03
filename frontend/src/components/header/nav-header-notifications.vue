<template>

        <OnClickOutside @trigger="notificationsPanel = false">

            <a @click="notificationsPanel = !notificationsPanel" ref="activityBell">
                <ui-icon-control :selected="notificationsPanel" class="position-relative">
                    <span class="nav-header-icon"><img src="./img/notification-outline.svg" style="height:85%;"></span>
                    <activity ref="activity" style="position:absolute;right:1px;top:0px;"></activity>
                </ui-icon-control>
            </a>

            
            <ui-expanded-menu class="notifications-expanded" 
                @close="notificationsPanel = false" 
                :direction="'right'" 
                v-if="notificationsPanel"
            >

                <template #menu-items>

                    <events :open="notificationsPanel" @last-activity-fetch="checkActivityIcon" />

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

import activity from './activity.vue'
import events from '../events/events.vue'
export default {
    name: 'nav-header-notifications',
    data() {
        return {
            notificationsPanel: false
        }
    },
    watch: {
        $latestEvent: function() {
            if (this.$latestEvent.eventName === 'new-page-navigation') {
                this.closeExpanded()
            }
        }
    },
    components: {
        OnClickOutside,
        activity,
        events
    },
    methods: {
        toggleNotificationsPanel() {
            if (this.notificationsPanel == false) {
                this.notificationsPanel = true
                return
            }
        },
        checkActivityIcon() {
            this.$refs.activity.fetchUnseenEvents()
        },
        closeExpanded() {
            this.notificationsPanel = false
        }
    }
}

</script>