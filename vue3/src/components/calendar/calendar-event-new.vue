<template>

    <div class="event-container">
        <div class="w-100" style="max-height:100%;" v-if="show.includes('image')">
            <ui-img-dynamic v-if="eventData.image" :img-style="{maxHeight: '300px', width: '100%', objectFit: 'contain'}"  :heightRatio="null" :src="eventData.image"/>
        </div>

        <div class="d-flex justify-content-end my-2" v-if="show.includes('image')">
            <ui-image-upload @uploaded="addImage" :button-label="$trans('Upload image')" size="sm" />
            <ui-button v-if="eventData.image" @click="eventData.image = null" size="sm" label="Remove" button-type="cancel" class="ml-2"></ui-button>
        </div>

        <ui-input-text v-model="eventData.name" :placeholder="$trans('Event name')" class="mb-3 mt-2" />

        <ui-textarea v-if="show.includes('description')" v-model="eventData.description" rows="3" :placeholder="$trans('Event description')" class="mb-3" />

        <ui-input-text v-model="eventData.location" :placeholder="$trans('Location')" class="mb-3" />

        <ui-timespan-selector v-if="duplicate" @times="setTimes" :default-start="duplicate.start" :default-end="duplicate.end" />
        <ui-timespan-selector v-else @times="setTimes" />

        <div class="more-container">

            <div v-if="show.includes('price')">
                <label for="price">Price</label>
                <ui-input-text id="price" class="price-input" v-model="eventData.price"></ui-input-text>
            </div>

            <div v-if="show.includes('max-participants')">
                <label for="price">Max participants</label>
                <ui-input-text id="max-participants" type="number" class="number-input" v-model="eventData.max_participants"></ui-input-text>
            </div>
            
        </div>


    </div>

</template>

<style scoped>

    .event-container {
    }
    .more-container {
        margin-bottom:4em;
        display: inline-flex;
        flex-wrap: wrap;
        gap: 12px;
    }
    .price-input {
        max-width: 7rem;
    }

    .number-input {
        max-width: 7rem;
    }

</style>


<script>

export default {
    name: 'calendar-event-new',
    data() {
        return {
            eventData: {
                name: '',
                description: '',
                location: '',
                start: '',
                end: '',
                price: null,
                image: null,
                max_participants: null
            }
        }
    },
    watch: {
        eventIsValid: function() {
            this.emitEvent()
        }
    },
    computed: {
        eventIsValid: function() {
            return this.eventData.name != '' && this.eventData.start != '' && this.eventData.end != ''
        }
    },
    props: {
        duplicate: {
            required: false,
            type: Object,
            default: null
        },
        show: {
            required: false,
            type: Array,
            default: function () {
                return ["image", "price", "max-participants", "description"]
            }
        }
    },
    methods: {
        convertDuplicate: function() {
            let eventData = this.duplicate
            if (this.duplicate.price) {
                eventData.price = this.duplicate.price.amount
            }
            this.eventData = eventData

        },
        addImage: function (image) {
            this.eventData.image = image.image.filename
        },
        emitEvent: function () {
            this.$emit('times', this.eventData)
        },
        setTimes: function(e) {
            this.eventData.start = e.start
            this.eventData.end = e.end
        }
    },
    mounted: function() {
        if (this.duplicate) {
            this.convertDuplicate()
        }
    }
}

</script>