<template>
  <div class="entities-container position-relative" :class="{selected: isSelected || isSelectedMultiSelect, 'multi-select': multiSelect}">


    <div class="entity-container position-relative">

      <profile-image-simple :width="72" :height="72" class="entity-icon" v-if="entity.type == 'profile'" :profile="entity.data" />

      <stage-icon :stage="entity.data.stage" class="entity-icon stage-icon" :size="18" v-if="entity.type == 'submission'" />

      <img class="group-icon icon" :src="$ui.iconFiles['people'].normal" v-if="entity.type == 'group'">

      <img class="icon" :src="$ui.iconFiles['submissions'].normal" v-if="entity.type == 'space'">

      <div class="overlay-container">
        <slot name="overlay">


        </slot>

      </div>

    </div>

    <div v-if="entity.reactions" class="reactions-container d-flex pr-2">
      <reactions-icon v-for="reaction in entity.reactions" :reaction="reaction" :size="'25px'" :mode="'selected'" class="reactions-icon" />

    </div>

    <ui-a v-if='href' class="entity-link stretched-link" :href="href" v-tooltip="tooltip">{{ entity.title }}</ui-a>
    <a v-else @click="toggleSelected" class="entity-link stretched-link" v-tooltip="tooltip">{{ entity.title }}</a>

      <input v-if="multiSelect" type="checkbox" class="toggler" @click="toggleSelected" :checked="isChecked" :disabled="hasBeenChanged" />

  </div>
</template>

<style scoped>
.multi-select {
  padding-left:0.5em;
  padding-right:1em;
  cursor: pointer;
  border-color: var(--light);
  border-radius: 8px;
  border-style: solid;
  border-width: 1px;

}
.overlay-container {

  position:absolute;
  right:0;
  bottom:0;

}

.mention-dropdown-menu .entities-container {
  padding-left:1em;
  padding-right:1.5em;
}


.navbar-search-dropdown .entities-container {
  padding-left:1.5em;
  padding-right:1.5em;
}
.selected {
  background-color: var(--light);

}

.on-dotted-line .entity-container {
  background-color:#dddddd;

}

.entities-container:hover .entity-container {
  background-color:#dddddd;
}

.group-icon {
  margin:18%;
}
.icon {
  height:45%;
}

.toggler {
cursor:pointer;
width:20px;
height: 20px;
}

.entity-link {
  flex-grow: 2;
  display:inline-block;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size:15px;
}

.small-entity .entity-link {
  font-size:13px;
}

.stage-icon {
    width:18px;
}

.entity-icon {
    width:v-bind(iconSize);
}


.entity-container {
    flex-grow: 0;
    background-color:rgba(149, 149, 149, 0.15);
    justify-content: center;
    align-items: center;
    display:flex;
    height:v-bind(iconSize);
    margin-right: 0.65rem;
    border-radius:50%;
    aspect-ratio: 1 / 1;
  }
  .reactions-container {
    margin-left:-0.5rem;
    
  }
  .reactions-icon {
    margin-left:0.15rem;
  }

</style>

<script>

import stageIcon from './submissions/stage-icon.vue'
import reactionsIcon from './reactions-icon.vue'
export default {
  name: 'entity',
  data() {
    return {
      hasBeenChanged: false
    }
  },
  emits: ['selected', 'unselected'],
  computed: {
    iconSize: function() {
      if (this.size == 'small') {
        return '18px'
      } else if (this.size == 'medium') {
        return '36px'
      } else if (this.size == 'large') {
        return '60px'
      }
    },
    isChecked() {
      if (this.hasBeenChanged) {
        return !this.isSelectedMultiSelect
      }
      return this.isSelectedMultiSelect
    },
    tooltip() {
      // if this.entity.title is too long, show it in the tooltip
      if (this.entity.title.length > 20) {
        return this.entity.title
      }

    },
    href() {
      if (!this.includeLink) {
        return false
      }
      if (this.entity.link) {
        return this.entity.link
      }      
    }
  },
  components: {
    stageIcon,
    reactionsIcon
  },
  methods: {
    toggleSelected() {
      if (this.isSelectedMultiSelect) {
        this.unselected()
      } else {
        this.selected()
      }
    },
    selected(data) {
      if (!this.hasBeenChanged) {
        this.hasBeenChanged = true
        this.$emit('selected', this.entity.data)
      }
    },
    unselected(data) {
      if (!this.hasBeenChanged) {
        this.hasBeenChanged = true
        this.$emit('unselected', this.entity.data)
      }
    }
  },
  watch: {
    isSelectedMultiSelect() {
      this.hasBeenChanged = false
    }
  },
  props: {
    size: {
      type: String,
      required: false,
      default: 'medium'
    },
    multiSelect: {
      type: Boolean,
      required: false,
      default: false
    },
    isSelectedMultiSelect: {
      type: Boolean,
      required: false,
      default: false
    },
    isSelected: {
      type: Boolean,
      required: false,
      default: false
    },
    showMore: {
      type: Number,
      required: false,
      default: 0
    },
    includeLink: {
      type: Boolean,
      required: false,
      default: true
    },
    entity: {
      type: Object,
      required: false
    }
  }
}
</script>
