<template>

    <ui-modal :title="'Add session'" :open="open" :size="'tall'" @close="close">

        <template #body>
            
            <calendar-event-new ref="calendarEventNew" class="w-100 pb-4" @times="times"
                :duplicate="duplicate"
                :show="['price', 'max-participants']"
            />

        </template>

        <template #footer>

            <div class="d-flex justify-content-center">

                <ui-button :disabled="!eventIsValid" @click="saveHappening()" :button-type="'new'" :label="$trans('Add')" class="mr-2" />
                <ui-button @click="$emit('close')" :button-type="'cancel'" :label="$trans('Cancel')" />

            </div>

        </template>

    </ui-modal>


</template>


<script>

import backend from '../../backend.js'
import calendarEventNew from './calendar-event-new.vue'
export default {
    name: 'sesstion-event-new',
    data: function() {
        return {
            eventIsValid: false,
            eventData: {}
        }
    },
    components: {
        calendarEventNew
    },
    props: ['open', 'duplicate'],
    methods: {
        saveHappening() {
            this.$refs.calendarEventNew.emitEvent()
            backend.post('/calendar/activites/events/', this.eventData)
            .then((response) => {

                alert('Event added')
                this.$emit('close')
                this.$emit('saved')

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