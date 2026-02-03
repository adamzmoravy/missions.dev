<template>
    <div>
        
        <ui-page-header>
            <div>
                <div class="pt-0 pb-0 w-100">
                    <div>
                        <h1 class="x-large mt-2">{{ $trans('Join') }}</h1>
                        <h2 class="large mt-2">
                            {{$trans('join_page_subheading')}}
                        </h2>

                    </div>

                </div>
                <ui-tabs class="mt-3" v-on:update:section="ui.showSection = $event" :tabs="[{display: 'Invitations', value: 'invitations'}]" :current-selection="'invitations'">
                    <div>
                        <div v-for="tab in tabs" class="tab-wrapper mr-3">
                            <a :class="{'tab-link-selected': currentSelection === tab.value}" class="tab-link mr-2" @click="$emit('update:section', tab.value)">

                                <span v-if="false">
                                <img v-if="currentSelection === tab.value" :src="'/img/checkbox-yellow.svg'">
                                    <img v-if="currentSelection !== tab.value" :src="'/img/checkbox.svg'">
                                </span>
                                {{tab.display}}</a>
                        </div>
                    </div>

                </ui-tabs>

            </div>
        </ui-page-header>

        <ui-page-body>

            <div class="row">

                <div class="col-md-12">
                    <template v-if="$config.platform.join.ongoing_groups && $config.platform.join.ongoing_groups.length > 0"> 
                        <group-invitation v-for="groupId in $config.platform.join.ongoing_groups" :happening-id="groupId" :key="groupId" />
                    </template>
                    <div class="d-flex">

                <session-event-new :duplicate="duplicate" @close="ui.addSomething = null" @saved="getEvents" :open="ui.addSomething == 'session-event'"/>

                <ui-button @click="ui.addSomething = 'session-event'" button-type="new" :size="'md'" :label="'Add session'"></ui-button>
                </div>

                    <session-signup @duplicate="duplicateEvent" ref="sessionSignup"></session-signup>

                </div>

            </div>

        </ui-page-body>

        <div class="container mt-3">
            <div class="row">
                <div class="col-md-10 px-4 col-12">

                </div>


            </div>

        </div>
    </div>


</template>

<script>

import { toRaw } from 'vue'

import { useGroup } from '../components/groups/group.js'
import groupInvitation from '../components/groups/group-invitation.vue'
import sessionEventNew from '../components/calendar/session-event-new.vue'
import sessionSignup from '../components/calendar/session-signup.vue'
import calendarEventNew from '../components/calendar/calendar-event-new.vue'

const group = useGroup()

export default {
    name: 'JoinPage',
    data () {
        return {
            ui: {
                addSomething: null,
            },
            duplicate: null
        }
    },
    computed: {
        group: function () {
            return group.data
        }
    },
    components: {
        groupInvitation,
        sessionEventNew,
        sessionSignup,
        calendarEventNew
    },
    methods: {
        duplicateEvent: function (event) {
            this.duplicate = structuredClone(toRaw(event))

            this.ui.addSomething = 'session-event'
        },
        getEvents: function () {
            this.$refs.sessionSignup.getEvents()
        }
    },
    watch:{
        $route (to, from){
            if (to.name == 'join') {
                this.getEvents()
            }
            this.limit = 24
        }
    },    
}

</script>