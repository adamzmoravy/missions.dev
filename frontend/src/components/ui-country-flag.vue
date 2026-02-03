<template>
  <span class="country-flag">
    <span class="flag-emoji">{{ flagEmoji }}</span>
    <span v-if="showName" class="country-name">{{ countryName }}</span>
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  countryCode: {
    type: String,
    required: true
  },
  showName: {
    type: Boolean,
    default: false
  }
})

const flagEmoji = computed(() => {
  if (!props.countryCode || props.countryCode.length !== 2) {
    return '🏳️'
  }
  
  const code = props.countryCode.toUpperCase()
  const codePoints = [...code].map(char => 127397 + char.charCodeAt(0))
  return String.fromCodePoint(...codePoints)
})

const countryName = computed(() => {
  if (!props.countryCode) return ''
  
  try {
    const regionNames = new Intl.DisplayNames(['en'], { type: 'region' })
    return regionNames.of(props.countryCode.toUpperCase())
  } catch (e) {
    return props.countryCode.toUpperCase()
  }
})
</script>

<style scoped>
.country-flag {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.flag-emoji {
  font-size: 1.2em;
  line-height: 1;
}


</style>