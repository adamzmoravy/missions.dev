<script setup>
import { ref, computed } from 'vue'
import { MapboxMap, MapboxImage, MapboxCluster } from '@studiometa/vue-mapbox-gl'
import 'mapbox-gl/dist/mapbox-gl.css'

import { useProfilesStore } from '../../stores/profiles'

const profilesStore = useProfilesStore()

const props = defineProps({
  profiles: {
    type: Array,
    required: true
  },
  token: {
    type: String,
    required: true
  }
})

const locations = computed(() => {
  const locationMap = new Map()
  
  // Group profiles by location
  props.profiles.forEach(profile => {
    if (profile.geographic_location) {
      const key = `${profile.geographic_location.lat},${profile.geographic_location.lng}`
      
      if (!locationMap.has(key)) {
        locationMap.set(key, {
          location: profile.geographic_location,
          country_code: profile.geographic_location.country.code,
          country_name: profile.geographic_location.country.name,
          profiles: []
        })
      }
      
      locationMap.get(key).profiles.push(profile)
    }
  })
  
  // Convert to GeoJSON features
  return Array.from(locationMap.values()).map(locationData => ({
    type: 'Feature',
    properties: {
      country_code: locationData.country_code,
      country_name: locationData.country_name,
      profiles: locationData.profiles
    },
    geometry: {
      type: 'Point',
      coordinates: [locationData.location.lng, locationData.location.lat]
    }
  }))
})

const mapCenter = ref([0, 60])
const showProfilesInLocation = ref(null)

const locationSelected = function (feature, event) {
  showProfilesInLocation.value = feature.properties
}
</script>

<style scoped>
.transparent-map :deep(.mapboxgl-canvas) {
  opacity: 0;
}

.transparent-map :deep(.mapboxgl-map) {
  background: transparent !important;
}
</style>

<template>
  <div v-if="showProfilesInLocation">
    <ui-modal 
      :title="showProfilesInLocation.country_name" 
      size="tall" 
      :open="true" 
      @close="showProfilesInLocation = null">
      <template #body>
        <div class="w-100">
            <div v-for="profile in JSON.parse(showProfilesInLocation.profiles)" :key="profile.user_id">

                                <div class="bg-white row users position-relative mt-3">
                                    <div class="col-md-3">
                                    <ui-a class="user-preview-link stretched-link" :href="'/profile/' + profile.user_id">                                        
                                        <profile-image-simple class="mx-auto" style="width:100%;" :profile="profile" />
                                    </ui-a>
                                    </div>
                                    <div class="pb-3 mb-3 col-md-9"> 

                                        <div class="text-center">

                                            <div class="user-name text-center pt-2">
                                                <h3><ui-a class="user-preview-link stretched-link" :href="'/profile/' + profile.user_id">{{ profile.first_name + ' ' + profile.last_name }}</ui-a></h3>
                                            </div>
                                            <div class="small-2  text-center d-flex justify-content-center mb-3 mt-2" v-if="profile.geographic_location && profile.geographic_location.vicinity">
                                                    <span class="d-flex small text-center"><img style="height:1.2em;" :src="'/img/location.svg'">{{$truncate(profile.geographic_location.vicinity, 18) + ', ' + profile.geographic_location.country.name }}</span>
                                            </div>
                                            <div class="small-2">
                                                {{ $truncate(profile.short_description, 150) }}
                                            </div>
                                            <div v-show="profile.skills">
                                                <span v-for="(skill, index) in profile.skills"><i>{{ skill }}</i> <span v-if="index !== profile.skills.length - 1">&nbsp;•&nbsp;&nbsp;</span>
                                                </span>    
                                            </div>    

                                        </div>

                                    </div>

                                    </div>
                                    <hr/>


            </div>
        </div>
      </template>
    </ui-modal>
  </div>

  <MapboxMap
    class="transparent-map mt-0 pt-0"
    style="height:800px;"
    :access-token="token.split('').reverse().join('')"
    map-style=""
    :center="mapCenter"
    :zoom="1">
   
    <MapboxImage
      id="profile-marker"
      src="/img/lena/Location_pin.png">
      <MapboxCluster
        @mb-feature-click="locationSelected"
        :data="{type: 'FeatureCollection', features: locations}"
        unclustered-point-layer-type="symbol"
        :unclustered-point-layout="{
          'icon-image': 'profile-marker',
          'icon-size': 0.1,
        }"
        :unclustered-point-paint="null" />
    </MapboxImage>
  </MapboxMap>
</template>