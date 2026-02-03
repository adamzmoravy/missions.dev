<template>

    <ui-modal :title="'Add event'" :open="open"  :size="'tall'"  @close="close">

        <template #body>

            <div class="row" v-if="groupId == null">

                <div class="col-md-12">
                    <h3 class="medium-2 pb-2">
                        {{$trans('Select a group to add an event to')}}
                    </h3>
                    <entities @selected="selected" :include-link="false" :title="$trans('Groups')" :entities="myGroups" />

               </div>
            </div>

            <div v-else>
                <div class="col-md-12">
                    <h3 class="medium-2 pb-0">
                        Add event to <ui-a class="underline" :href="'/group/' + selectedGroup.happening_id">{{ selectedGroup.name }}</ui-a>
                    </h3>

                </div>
                <calendar-event-new ref="calendarEventNew" class="col-md-12" @times="times" />
            </div>
        </template>

        <template #footer>

            <div class="d-flex justify-content-center" v-if="groupId != null">

                <ui-button :disabled="!eventIsValid" @click="saveHappening()" :button-type="'action'" :label="$trans('Add')" class="mr-2" />
                <ui-button @click="$emit('close')" :button-type="'cancel'" :label="$trans('Cancel')" />

            </div>

        </template>

    </ui-modal>


</template>


<script>

import calendarEventNew from '../calendar/calendar-event-new.vue'

import backend from '../../backend.js'
export default {
    name: 'group-event-new',
    data: function() {
        return {
            selectedGroupId: null,
            eventIsValid: false,
            eventData: {}
        }
    },
    components: {
        calendarEventNew
    },
    props: {
        open: {
            type: Boolean,
            default: false
        },
        preSelectedGroupId: {
            type: String,
            default: null
        }
    },
    computed: {
        postEventUrl: function () {
            if (this.groupId) {
                return '/happenings/' + this.groupId + '/calendar/events/'
            } 
            return null
        },
        groupId: function () {
            return this.selectedGroupId || this.preSelectedGroupId
        },
        selectedGroup: function () {
            return this.$stores.groups.getGroupPreview(this.groupId)
        },
        myGroups: function () {
            return this.$stores.groups.getEntities(
                [{
                    sorter: 'registrations_count',
                    direction: 'asc'
                }],
                [{
                selection: 'member',
                value: this.$stores.auth.userData.user_id
            }])
        }
    },
    methods: {
        selected: function (entity) {
            this.selectedGroupId = entity.happening_id
        },        
        saveHappening() {
            this.$refs.calendarEventNew.emitEvent()
            backend.post(this.postEventUrl, this.eventData)
            .then((response) => {

                alert('Calendar event added')
                this.$emit('event-added')
                this.$emit('close')

            }).catch((error) => {
                console.log(error)
            })

        },
        times: function (e) {
            this.eventIsValid = true,
            this.eventData = e
        },
        close: function() {
            this.$emit('close')
        }
    }

}

</script>