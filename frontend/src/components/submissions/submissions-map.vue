<script setup>
  import { ref, computed } from 'vue'
  import SubmissionPreview from './submission-preview.vue';
  import { MapboxMap, MapboxImage, MapboxCluster } from '@studiometa/vue-mapbox-gl';
  import 'mapbox-gl/dist/mapbox-gl.css'

  import { useSubmissionsStore } from '../../stores/submissions'

  const submissionsStore = useSubmissionsStore()

  const props = defineProps({
    stages: {
      type: Array,
      required: true
    },
    token: {
      type: String,
      required: true
    }
    });
  
  const { stages } = props;

  const locations = computed(() => {

    return submissionsStore.getLocations().map(location => ({
      type: 'Feature',
      properties: {
        formatted_address: location.formatted_address,
        vicinity: location.vicinity,
        location: location.location
      },
      geometry: {
        type: 'Point',
        coordinates: [location.location.lng, location.location.lat]
      }
    }))
  })

  const submissionsInSelectedLocation = computed(() => {

    if (showSubmissionsInLocation.value) {
      return submissionsStore.getEntities([
        {
          sorter: 'created',
          direction: 'asc'
        }
      ],
      [{
        selection: 'stages',
        value: stages
      },
      {
        selection: 'address',
        value: showSubmissionsInLocation.value.formatted_address
      }])
    }

  })


  const mapCenter = ref([0,60]);
  const showSubmissionsInLocation = ref(null)

  let locationSelected = function (feature, event) {
    showSubmissionsInLocation.value = feature.properties
  }

</script>


<style scoped>
.transparent-map :deep(.mapboxgl-canvas) {
  opacity: 0; /* or any value between 0-1 */
}
/* Or to make the container background transparent */
.transparent-map :deep(.mapboxgl-map) {
  background: transparent !important;
}
</style>

<template>


  <div v-if="showSubmissionsInLocation">
      <ui-modal :title="showSubmissionsInLocation.formatted_address" size="tall" :open="true" v-if="showSubmissionsInLocation" @close="showSubmissionsInLocation = null">
          <template #body>

            <div>
              <div v-for="submission in submissionsInSelectedLocation" :key="submission.id" class="row col-md-12 d-flex justify-content-center">
    
              <submission-preview :experiment="submission.data" class="w-100 col-md-10" />

            </div>
            </div>

          </template>


        </ui-modal>

  </div>

  <MapboxMap
    class="transparent-map"
    style="height:800px;"
    :access-token="token.split('').reverse().join('')"
    map-style=""
    :center="mapCenter"
    :zoom="1">
   
    <MapboxImage
      id="organisation"
      src="/img/lena/Location_pin.png">
      <MapboxCluster
        @mb-feature-click="locationSelected"
        :data="{type: 'FeatureCollection', features: locations}"
        unclustered-point-layer-type="symbol"
        :unclustered-point-layout="{
          'icon-image': 'organisation',
          'icon-size': 0.1,
        }"
        :unclustered-point-paint="null" />
    </MapboxImage>

  </MapboxMap>


</template>

