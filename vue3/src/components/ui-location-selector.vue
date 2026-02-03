

<template>


    <MapboxGeocoder :access-token="token.split('').reverse().join('')" ref="geocoder" @mb-result="result" @mb-clear="clear" />


</template>




<script setup>

    import { ref } from 'vue'
    import { MapboxMap, MapboxGeocoder } from '@studiometa/vue-mapbox-gl'
    import '@mapbox/mapbox-gl-geocoder/lib/mapbox-gl-geocoder.css'

    const emit = defineEmits(['location-selected'])

    const clear = () => {

        emit('location-selected', null)

    }

    defineProps({
        token: {
            type: String,
            required: true
            }
    })

    const result = (data) => {

        let geographicData = {
            location: {
                lat: data.result.geometry.coordinates[1],
                lng: data.result.geometry.coordinates[0]
            },
            data: {
                mapbox_place_id: data.result.properties.mapbox_id,
                wikidata: data.result.properties.wikidata,
            },
            vicinity: data.result.text,
            source: 'mapbox',
            formatted_address: data.result.place_name,
            country: {
                name: data.result.context ? data.result.context.find(c => c.id.startsWith('country'))?.text : null,
                code: data.result.context ? data.result.context.find(c => c.id.startsWith('country'))?.short_code.toUpperCase() + '' : null
            } 
        }

        emit('location-selected', geographicData)


    }


</script>