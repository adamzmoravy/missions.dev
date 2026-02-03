<template>


        <ui-modal :title="title" size="tall" :open="showAll" @close="showAll = false">
              <template #header>
                <p class="large bolder"> {{ title }} </p>
                <ui-input-text v-if="entities.length > 10" v-model="search" :placeholder="$trans('Search')" :autofocus="true" />
              </template>
               <template #body>
                <div>

                    <RecycleScroller
                        class="scroller"
                        :items="displayedEntities"
                        :item-size="50"
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


</template>


<style scoped>

</style>

<script>

export default {
  name: 'entities-modal',
  data() {
    return {
      selected: [],
      show: false,
    }
  },
  methods: {
    open() {
      this.show = true
    },
    close() {
      this.show = false
    },
    selected(data) {
      this.selected.push(data)
    },
    unselected(data) {
      this.selected = this.selected.filter(d => d !== data)
    },
    submit() {
      this.$emit('submit', this.selected)
      this.close()
    }
  },
  props: {
    entities: {
      type: Array,
      required: true
    }
  }
}

</script>