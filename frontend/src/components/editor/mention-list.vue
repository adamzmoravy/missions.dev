<template>
  <div class="mention-dropdown-menu">
    <template v-if="items.length">

        <entity v-for="(item, index) in items" 
        class="entity"
        :include-link="false" 
        :entity="item.entity" 
        :key="item.entity.link" 
        :class="{ 'is-selected': index === selectedIndex }"
        :is-selected="index === selectedIndex"
        @selected="selectItem(index)" />


    </template>
    <div class="entity px-3 pb-2 bg-white" v-else>
      No result
    </div>
  </div>
</template>

<script>
export default {
  name: 'MentionList',
  props: {
    items: {
      type: Array,
      required: true,
    },

    command: {
      type: Function,
      required: true,
    },
  },

  data() {
    return {
      selectedIndex: 0,
    }
  },
  watch: {
    items() {
      this.selectedIndex = 0
    },
  },

  methods: {
    onKeyDown({ event }) {
      if (event.key === 'ArrowUp') {
        this.upHandler()
        return true
      }

      if (event.key === 'ArrowDown') {
        this.downHandler()
        return true
      }

      if (event.key === 'Enter') {
        this.enterHandler()
        return true
      }

      return false
    },

    upHandler() {
      this.selectedIndex = ((this.selectedIndex + this.items.length) - 1) % this.items.length
    },

    downHandler() {
      this.selectedIndex = (this.selectedIndex + 1) % this.items.length
    },

    enterHandler() {
      this.selectItem(this.selectedIndex)
    },

    selectItem(index) {
      const item = this.items[index]

      if (item) {
        this.command({ id: item.id, label: item.label })
      }
    },
  },
}
</script>

<style lang="scss" scoped>


.is-selected {

}

.entity:first-of-type {
  padding-top: 0.5rem;
}

.mention-dropdown-menu {
  background-color:white;
  border-radius:6px;

  width:300px;
  filter: drop-shadow(0px 1px 4px rgba(0, 0, 0, 0.07));

}
/* Dropdown menu 
.mention-dropdown-menu {
  background: var(--white);
  border: 10px solid lightgrey;
  border-radius: 0.7rem;
  box-shadow: 10px 10px 5px 0px rgba(0,0,0,0.75);
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
  overflow: auto;
  padding: 0.4rem;
  position: relative;

  button {
    align-items: center;
    background-color: transparent;
    display: flex;
    gap: 0.25rem;
    text-align: left;
    width: 100%;

    &:hover,
    &:hover.is-selected {
      background-color: red;
    }

    &.is-selected {
      background-color: var(--gray-2);
    }
  }
}
  */
</style>
