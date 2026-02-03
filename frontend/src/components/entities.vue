<template>
  <div class="w-100">
      <div class="w-100">

        <div v-if="!modalOnly" class="w-100 entitites-container">

            <ui-input-text v-if="entities.length > 2 && showSearchBar" v-model="search" :placeholder="$trans('Search')" :autofocus="true" class="mb-2" />

              <div class="container-scroller w-100">
                <RecycleScroller
                
                  class="scroller w-100"
                  :items="searchedEntities"
                  :item-size="itemSize"
                  key-field="link"
                  v-slot="{ item }"
                >
                  <entity 
                    :include-link="includeLink" 
                    
                    @selected="selected"
                    @unselected="unselected"
                    :is-selected-multi-select="multiSelectSelected.includes(item.id)" 
                    :entity="item" 
                    :multi-select="multiSelect" />


                </RecycleScroller>
              </div>
        <!-- :is-selected="key == selectedResult"  -->

        <div class="entities-container position-relative" v-if="needToShowMore">
            <div class="entity-container more-profiles">
                +{{ showMore }}
            </div>
            <ui-a @click="showAll = true" class="entity-link stretched-link">Show all</ui-a>
        </div>

        <div class="entities-container position-relative" v-if="edit">
            <div class="entity-container more-profiles">
              ⋮
            </div>
            <ui-a @click="$emit('edit')" class="entity-link stretched-link">{{ $trans('Edit') }}</ui-a>
        </div>
      </div>

        <ui-modal :title="title" size="tall" :sub-title="subTitle" :open="showAll || modalOnly" @close="close()">

              <template #header>
                <p class="large"> {{ title }}</p>
                <hr class="mb-3">

                <div class="px-3 pb-3" v-if="subTitle">
                  <p class="medium mb-2"> {{ subTitle }}</p>
                   <template v-for="button in buttons" v-if="buttons.length > 0">
                      <ui-button 
                        size="md"
                        :button-type="button.type"
                        @click="$emit(button.action)"
                        class="mb-3 mr-2"
                      >
                        {{ button.label }}
                      </ui-button>
                    </template>

                  <ui-input-text v-if="entities.length > 10" v-model="search" :placeholder="$trans('Search')" :autofocus="true" />

                </div>
              </template>
               <template #body>
                <div class="w-100">

                  <RecycleScroller
                class="scroller"
                :items="searchedEntitiesAll"
                :item-size="itemSize"
                key-field="link"
                v-slot="{ item }"
              >
                <entity 
                  :include-link="includeLink" 
                  
                  @selected="selected"
                  @unselected="unselected"
                  :is-selected-multi-select="multiSelectSelected.includes(item.id)" 
                  :entity="item" 
                  :multi-select="multiSelect" />


              </RecycleScroller>
                </div>

                </template>
        </ui-modal>
      </div>
    </div>
</template>


<style scoped>


.entity-container {
  display:flex;
  flex-direction: column;
}
.container-scroller {
  overflow: auto;
  flex-grow: 1;
}
.on-dotted-line .entity-container {
  background-color:#dddddd;

}
.more-profiles {
    font-size:12px;
    font-weight:400;
}
.entity-link {
  flex-grow: 2;
  display:inline-block;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size:15px;
}

.entity-icon {
    width:36px;
}

</style>

<script>
import VueVirtualScroller from 'vue-virtual-scroller'
import 'vue-virtual-scroller/dist/vue-virtual-scroller.css'

export default {
  props: {
    buttons: {
      type: Array,
      required: false,
      default: () => []
    },
    searchProp: {
      type: String,
      required: false,
      default: ''
    },
    maxSelect: {
      type: Number,
      required: false,
      default: null
    },
    modalOnly: {
      type: Boolean,
      required: false,
      default: false
    },
    showSearchBar: {
      type: Boolean,
      required: false,
      default: false
    },
    multiSelect: {
      type: Boolean,
      required: false,
      default: false
    },
    multiSelectSelected: {
      type: Array,
      required: false,
      default: () => []
    },
    selectedResult: {
      type: Number,
      required: false,
      default: null
    },
    edit: {
      type: Boolean,
      required: false,
      default: false
    },
    title: {
      type: String,
      required: false,
      default: 'Show all'
    },
    subTitle: {
      type: String,
      required: false,
      default: null
    },
    entities: {
      type: Array,
      required: true
    },
    limit: {
      type: Number,
      default: 1000,
    },
    includeLink: {
      type: Boolean,
      default: true,
    },
  },
  components: {
    VueVirtualScroller,
  },
  emits: ['selected', 'unselected', 'edit', 'close'],
  data() {
    return {
        search: '',
        showAll: false,
        currentPage: 1,
    };
  },
  computed: {
    itemSize() {
      if (this.multiSelect) {
        return 60
      }
      return 50
    },
    list() {
      return []
    },
    searchedEntitiesAll() {
      let search = this.search.toLowerCase()

      if (this.searchProp !== '') {
        search = this.searchProp.toLowerCase()
      }
      
      return this.entities.filter((entity) => {
        return entity.title.toLowerCase().includes(search)
      })
    },
    searchedEntities() {

      let search = this.search.toLowerCase()

      if (this.searchProp !== '') {
        search = this.searchProp.toLowerCase()
      }
      
      return this.displayedEntities.filter((entity) => {
        return entity.title.toLowerCase().includes(search)
      })
    },
    showMore () {
        return this.entities.length - this.limit
    },
    needToShowMore () {
        return this.entities.length > this.limit
    },
    totalPages() {
      return Math.ceil(this.entities.length / this.limit);
    },
    displayedEntities() {

      const start = (this.currentPage - 1) * this.limit;
      const end = start + this.limit;
      return this.entities.slice(start, end);
    },
  },
  methods: {
    close () {
      this.showAll = false
      this.$emit('close')
    },
    selected(data) {
      if (this.maxSelect && this.multiSelectSelected.length >= this.maxSelect) {
        console.log(this.maxSelect)
        console.log(this.multiSelectSelected)
        this.unselected({ experiment_id: this.multiSelectSelected[0] })
      }
      this.$emit('selected', data);
    },
    unselected(data) {
      this.$emit('unselected', data);
    },
    previousPage() {
      this.currentPage -= 1;
    },
    nextPage() {
      this.currentPage += 1;
    },
  },
};
</script>
