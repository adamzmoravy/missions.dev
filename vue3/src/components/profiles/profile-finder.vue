<template>

    <template v-if="layout=='standard'">

        <template v-if="sectionsShown.includes('country')">

            <h4 class="medium-2 mb-2 mt-2">{{ countryLabel }}</h4>
                <div class="filter-panel">

                <div :key="'all-countries'">

                    <label class="filter-section">

                        <input type="radio" :value="''" v-model="countryFilter">
                        <span>All</span>

                    </label>

                </div>

                <div v-for="country in profileCountries" :key="country.code">

                    <label class="filter-section">

                        <input type="radio" :value="country.code" v-model="countryFilter">
                        <ui-country-flag :country-code="country.code" :show-name="true" />
                        <span>  &nbsp;({{ country.frequency }})</span>

                    </label>

                </div>
            </div>


        </template>

        <template v-for="tagType in allTypes" :key="tagType.type" v-if="sectionsShown.includes('tags')">


            <h4 class="medium-2 mb-2 mt-2">{{ $trans(getTagTypeLabel(tagType.type)) }}</h4>

            <div class="filter-panel">


                <template v-for="tag in tagType.tags" :key="tag.tag_id" >
                    <div v-if="$stores.tags.getTagById(tag.tag_id)">

                        <label class="filter-section">
                            <input type="checkbox" :value="tag.tag_id" v-model="filters">
                            <span v-if="!tag.label">{{$stores.tags.getTagById(tag.tag_id)?.label}} ({{ tag.frequency }})</span>
                            <span v-else>{{ tag.label }} ({{ tag.frequency }})</span>

                        </label>

                    </div>
                </template>


            </div>
        </template>

        <template v-if="sectionsShown.includes('submissions')">

            <template v-for="submissionType in filterBySubmissionsType">

                <h4 class="medium-2 mb-2 mt-2">{{ $trans($ui.stageHandles[submissionType]).charAt(0).toUpperCase() + $trans($ui.stageHandles[submissionType]).slice(1) }}</h4>
                    <div class="filter-panel">

                    <div v-for="submission in getSubmissionByStage(submissionType)" :key="submission.experiment_id">

                        <label class="filter-section">

                            <input type="checkbox" :value="submission.experiment_id" v-model="submissionsFilter">
                            <span> {{ submission.title }}</span>

                        </label>

                    </div>
                </div>
            </template>

        </template>



    </template>

    <div v-if="layout=='minimalist'" class="minimalist-layout">
        
        <div v-if="sectionsShown.includes('country')" class="minimalist-type-section">
            <div class="minimalist-type-header" @click="toggleType('country')">
                <h4 class="minimalist-type-title">
                    {{ countryLabel }}
                    <span class="toggle-icon">{{ expandedTypes['country'] ? '▼' : '▶' }}</span>
                </h4>
                <div v-if="!expandedTypes['country'] && countryFilter" class="selected-values">
                    {{ profileCountries.find(c => c.code === countryFilter)?.name }}
                </div>
            </div>
            
            <div v-if="expandedTypes['country']" class="filter-panel">
                <div :key="'all-countries'">
                    <label class="filter-section">
                        <input type="radio" :value="''" v-model="countryFilter">
                        <span>All</span>
                    </label>
                </div>
                <div v-for="country in profileCountries" :key="country.code">
                    <label class="filter-section">
                        <input type="radio" :value="country.code" v-model="countryFilter">
                        <ui-country-flag :country-code="country.code" :show-name="true" />
                        <span>&nbsp;({{ country.frequency }})</span>
                    </label>
                </div>
            </div>
        </div>

        <div v-for="tagType in allTypes" :key="tagType.type" class="minimalist-type-section" v-if="sectionsShown.includes('tags')">
            <div class="minimalist-type-header" @click="toggleType(tagType.type)">
                <h4 class="minimalist-type-title">
                    {{ $trans(getTagTypeLabel(tagType.type)) }}
                    <span class="toggle-icon">{{ expandedTypes[tagType.type] ? '▼' : '▶' }}</span>
                </h4>
                <div v-if="!expandedTypes[tagType.type] && getSelectedTagsForType(tagType.type).length > 0" class="selected-values">
                    {{ getSelectedTagsForType(tagType.type).map(id => $stores.tags.getTagById(id)?.label).join(', ') }}
                </div>
            </div>
            
            <div v-if="expandedTypes[tagType.type]" class="filter-panel">
                <template v-for="tag in tagType.tags" :key="tag.tag_id">
                    <div v-if="$stores.tags.getTagById(tag.tag_id)">
                        <label class="filter-section">
                            <input type="checkbox" :value="tag.tag_id" v-model="filters">
                            {{$stores.tags.getTagById(tag.tag_id)?.label}} ({{ tag.frequency }})
                        </label>
                    </div>
                </template>

            </div>
        </div>

        <template v-if="sectionsShown.includes('submissions')">
            <div v-for="submissionType in filterBySubmissionsType" :key="submissionType" class="minimalist-type-section">
                <div class="minimalist-type-header" @click="toggleType('submissions-' + submissionType)">
                    <h4 class="minimalist-type-title">
                        {{ $trans($ui.stageHandles[submissionType]).charAt(0).toUpperCase() + $trans($ui.stageHandles[submissionType]).slice(1) }}
                        <span class="toggle-icon">{{ expandedTypes['submissions-' + submissionType] ? '▼' : '▶' }}</span>
                    </h4>
                    <div v-if="!expandedTypes['submissions-' + submissionType] && getSelectedSubmissionsForType(submissionType).length > 0" class="selected-values">
                        {{ getSelectedSubmissionsForType(submissionType).map(id => getSubmissionByStage(submissionType).find(s => s.experiment_id === id)?.title).join(', ') }}
                    </div>
                </div>
                
                <div v-if="expandedTypes['submissions-' + submissionType]" class="filter-panel">
                    <div v-for="submission in getSubmissionByStage(submissionType)" :key="submission.experiment_id">
                        <label class="filter-section">
                            <input type="checkbox" :value="submission.experiment_id" v-model="submissionsFilter">
                            {{ submission.title }}
                        </label>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <div v-if="layout=='minimalist-horizontal'" class="horizontal-layout">
        
        <div v-if="sectionsShown.includes('country')" class="horizontal-type-section">
            <div class="horizontal-type-header" @click="toggleType('country')">
                <div class="horizontal-type-title">
                    {{ countryLabel }}
                    <span class="toggle-icon">{{ expandedTypes['country'] ? '▼' : '▶' }}</span>
                </div>
                <div v-if="!expandedTypes['country'] && countryFilter" class="horizontal-selected-values">
                    {{ profileCountries.find(c => c.code === countryFilter)?.name }}
                </div>
            </div>
            
            <div v-if="expandedTypes['country']" class="horizontal-filter-panel">
                <div :key="'all-countries'">
                    <label class="filter-section">
                        <input type="radio" :value="''" v-model="countryFilter">
                        <span>All</span>
                    </label>
                </div>
                <div v-for="country in profileCountries" :key="country.code">
                    <label class="filter-section">
                        <input type="radio" :value="country.code" v-model="countryFilter">
                        <ui-country-flag :country-code="country.code" :show-name="true" />
                        <span> ({{ country.frequency }})</span>
                    </label>
                </div>
            </div>
        </div>

        <div v-for="tagType in allTypes" :key="tagType.type" class="horizontal-type-section" v-if="sectionsShown.includes('tags')">
            <div class="horizontal-type-header" @click="toggleType(tagType.type)">
                <div class="horizontal-type-title">
                    {{ $trans(getTagTypeLabel(tagType.type)) }}
                    <span class="toggle-icon">{{ expandedTypes[tagType.type] ? '▼' : '▶' }}</span>
                </div>
                <div v-if="!expandedTypes[tagType.type] && getSelectedTagsForType(tagType.type).length > 0" class="horizontal-selected-values">
                    {{ getSelectedTagsForType(tagType.type).map(id => $stores.tags.getTagById(id)?.label).join(', ') }}
                </div>
            </div>
            
            <div v-if="expandedTypes[tagType.type]" class="horizontal-filter-panel">
                <template v-for="tag in tagType.tags" :key="tag.tag_id">
                    <div v-if="$stores.tags.getTagById(tag.tag_id)">
                        <label class="filter-section">
                            <input type="checkbox" :value="tag.tag_id" v-model="filters">
                            {{$stores.tags.getTagById(tag.tag_id)?.label}} ({{ tag.frequency }})
                        </label>
                    </div>
                </template>
            </div>
        </div>

        <template v-if="sectionsShown.includes('submissions')">
            <div v-for="submissionType in filterBySubmissionsType" :key="submissionType" class="horizontal-type-section">
                <div class="horizontal-type-header" @click="toggleType('submissions-' + submissionType)">
                    <div class="horizontal-type-title">
                        {{ $trans($ui.stageHandles[submissionType]).charAt(0).toUpperCase() + $trans($ui.stageHandles[submissionType]).slice(1) }}
                        <span class="toggle-icon">{{ expandedTypes['submissions-' + submissionType] ? '▼' : '▶' }}</span>
                    </div>
                    <div v-if="!expandedTypes['submissions-' + submissionType] && getSelectedSubmissionsForType(submissionType).length > 0" class="horizontal-selected-values">
                        {{ getSelectedSubmissionsForType(submissionType).map(id => getSubmissionByStage(submissionType).find(s => s.experiment_id === id)?.title).join(', ') }}
                    </div>
                </div>
                
                <div v-if="expandedTypes['submissions-' + submissionType]" class="horizontal-filter-panel">
                    <template v-for="submission in getSubmissionByStage(submissionType)" :key="submission.experiment_id">
                        <label class="filter-section">
                            <input type="checkbox" :value="submission.experiment_id" v-model="submissionsFilter">
                            {{ submission.title }}
                        </label>
                    </template>
                </div>
            </div>
        </template>
    </div>

</template>


<style scoped>
    .filter-panel {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px;
        max-height: 200px;
        overflow-y: auto;
        margin-bottom:2em;
    }
    .filter-section {
        cursor:pointer;
        display: flex; align-items: center; margin-bottom: 5px;        
    }

    .filter-section input[type="checkbox"] {
        margin-right: 4px;
        margin-top:2px;
    }
    .filter-section input[type="radio"] {
        margin-right: 4px;
        margin-top:2px;
    }

    /* Minimalist Layout Styles */
    .minimalist-layout {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .minimalist-type-section {
        border: 1px solid #ddd;
        border-radius: 4px;
        overflow: hidden;
    }

    .minimalist-type-header {
        padding: 10px 15px;
        cursor: pointer;
        background-color: #f8f9fa;
        transition: background-color 0.2s;
    }

    .minimalist-type-header:hover {
        background-color: #e9ecef;
    }

    .minimalist-type-title {
        margin: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 1rem;
    }

    .toggle-icon {
        font-size: 0.8rem;
        color: #666;
    }

    .selected-values {
        margin-top: 5px;
        font-size: 0.9rem;
        color: #666;
        font-style: italic;
    }

    .minimalist-layout .filter-panel {
        border: none;
        border-top: 1px solid #ddd;
        border-radius: 0;
        margin-bottom: 0;
    }

    /* Horizontal Layout Styles */
    .horizontal-layout {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: stretch;
    }

    .horizontal-type-section {
        border: 1px solid #ddd;
        border-radius: 4px;
        overflow: visible;
        position: relative;
        min-width: 49%;
        max-width: 250px;
        flex: 0 1 auto;
        display: flex;
        flex-direction: column;
    }

    .horizontal-type-header {
        padding: 8px 12px;
        cursor: pointer;
        /* background-color: #f8f9fa; */
        transition: background-color 0.2s;
    }

    .horizontal-type-header:hover {
        background-color: #e9ecef;
    }

    .horizontal-type-title {
        font-size: 0.9rem;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2px;
    }

    .horizontal-selected-values {
        font-size: 0.8rem;
        color: #666;
        font-style: italic;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-top: 4px;
    }

    .horizontal-filter-panel {
        border-top: 1px solid #ddd;
        padding: 8px;
        max-height: 180px;
        overflow-y: auto;
        background-color: white;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 10;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .horizontal-filter-panel .filter-section {
        margin-bottom: 4px;
        font-size: 0.85rem;
    }

</style>

<script>


export default {
    name: 'profile-finder',
    data() {
        return {
            filters: [],
            countryFilter: '',
            submissionsFilter: [],
            expandedTypes: {} // Track which types are expanded
        }
    },
    emits: ['update', 'update-country', 'update-submissions'],
    mounted() {
        if (this.layout === 'minimalist-horizontal') {
            document.addEventListener('click', this.handleClickOutside)
        }
    },
    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside)
    },
    watch: {
        filters: function(newFilters) {
            this.$emit('update', newFilters)
        },
        countryFilter: function(newCountry) {
            this.$emit('update-country', newCountry)
        },
        submissionsFilter: function(newSubmissions) {
            this.$emit('update-submissions', newSubmissions)
        }
    },
    props: {
        layout: {
            type: String,
            required: false,
            default: 'standard' // or 'minimalist'
        },
        profiles: {
            type: Array,
            required: false,
            default: () => []
        },
        sectionsShown: {
            type: Array,
            required: false,
            default: () => ['tags', 'country', 'submissions']
        },
        includeTagTypes: {
            type: Array,
            required: false,
            default: () => []
        },
        orderOfTagTypes: {
            type: Array,
            required: false,
            default: () => []
        },
        countryLabel: {
            type: String,
            required: false,
            default: 'Country'
        },
        filterBySubmissionsType: {
            type: Array,
            required: false,
            default: () => []
        }
    },
    computed: {
/*        
        includeTagTypes() {
            return []
            return [
                {
                    "type": "populations",
                    "label": "Populations da workings"
                },
                {
                    "type": "country",
                    "label": "Country da workings"
                },

            ]
        },
        orderOfTagTypes() {
            return [
                "populations", "roles", "degrees"
            ]
        },
        */
        getTypes: function () {
            const typeMap = {}
            
            // Go through all profiles and their tags_with_type
            this.profiles.forEach(profile => {
                if (!profile.tags_with_type || !Array.isArray(profile.tags_with_type)) {
                    return
                }
                
                profile.tags_with_type.forEach(tagItem => {
                    if (!tagItem.types || !Array.isArray(tagItem.types)) {
                        return
                    }
                    
                    // Process each type in the types array
                    tagItem.types.forEach(type => {
                        // Initialize type if it doesn't exist
                        if (!typeMap[type]) {
                            typeMap[type] = {
                                type: type,
                                frequency: 0,
                                tags: {}
                            }
                        }
                        
                        // Increment type frequency
                        typeMap[type].frequency++
                        
                        // Track tag_id frequency for this type
                        if (!typeMap[type].tags[tagItem.tag_id]) {
                            typeMap[type].tags[tagItem.tag_id] = 0
                        }
                        typeMap[type].tags[tagItem.tag_id]++
                    })
                })
            })
            
            // Convert to array and format tags as array of objects
            return Object.values(typeMap).map(typeData => ({
                type: typeData.type,
                frequency: typeData.frequency,
                tags: Object.entries(typeData.tags).map(([tag_id, frequency]) => ({
                    tag_id: parseInt(tag_id),
                    frequency: frequency
                })).sort((a, b) => b.frequency - a.frequency) // Sort by frequency descending
            })).sort((a, b) => b.frequency - a.frequency) // Sort types by frequency descending

        },
        allTypes: function () {
            let allTypes = this.getTypes

            if (this.includeTagTypes && this.includeTagTypes.length > 0) {
                allTypes = allTypes.filter(type => 
                    this.includeTagTypes.some(included => included.type === type.type)
                )

                // Add any missing tag types from includeTagTypes that aren't in allTypes
                this.includeTagTypes.forEach(includedType => {
                    if (!allTypes.find(t => t.type === includedType.type)) {
                        allTypes.push({
                            type: includedType.type,
                            frequency: 0,
                            tags: []
                        })
                    }
                })
            }

            /*
            // Populate country, if exists
            const countryType = allTypes.find(t => t.type === 'country')
            if (countryType) {
                countryType.tags = this.profileCountries.map(country => ({
                    tag_id: country.code,
                    frequency: country.frequency,
                    label: country.name
                }))
                countryType.frequency = countryType.tags.reduce((sum, tag) => sum + tag.frequency, 0)
            }
            */            

            if (this.orderOfTagTypes && this.orderOfTagTypes.length > 0) {
                // Sort based on predefined order
                allTypes = allTypes.sort((a, b) => {
                    const indexA = this.orderOfTagTypes.indexOf(a.type)
                    const indexB = this.orderOfTagTypes.indexOf(b.type)
                    return indexA - indexB
                })
            }


            return allTypes

        },
        profileCountries() {
            return this.$stores.profiles.getProfilesCountries
        }    
    },
    methods: {
        getSubmissionByStage (stage) {
            return this.$stores.submissions.getFilteredSubmissions([{selection: 'stages', value: [stage]}])
        },
        getTagTypeLabel(tagType) {

            // Check if tag type is in includeTagTypes and return that label
            const includedType = this.includeTagTypes.find(tt => tt.type === tagType)
            if (includedType) {
                return includedType.label
            }

            return this.$ui.profileTagTypes.find(tt => tt.type === tagType)?.label || tagType
            
        },
        toggleType(type) {
            const wasExpanded = this.expandedTypes[type]
            
            if (!wasExpanded) {
                // If expanding, close all others first
                this.expandedTypes = { [type]: true }
            } else {
                // If collapsing, close this one
                this.expandedTypes = {}
            }
        },
        getSelectedTagsForType(type) {
            // Get all tag_ids for this type
            const typeData = this.allTypes.find(t => t.type === type)
            if (!typeData) return []
            
            const typeTagIds = typeData.tags.map(t => t.tag_id)
            // Return only the selected tags that belong to this type
            return this.filters.filter(tagId => typeTagIds.includes(tagId))
        },
        getSelectedSubmissionsForType(submissionType) {
            // Get all submission IDs for this stage
            const submissions = this.getSubmissionByStage(submissionType)
            const submissionIds = submissions.map(s => s.experiment_id)
            // Return only the selected submissions that belong to this stage
            return this.submissionsFilter.filter(id => submissionIds.includes(id))
        },
        handleClickOutside(event) {
            // Only handle for minimalist-horizontal layout
            if (this.layout !== 'minimalist-horizontal') return
            
            // Check if click is outside all horizontal-type-section elements
            const clickedInside = event.target.closest('.horizontal-type-section')
            
            if (!clickedInside) {
                // Close all expanded types
                this.expandedTypes = {}
            }
        }
    }
}


</script>