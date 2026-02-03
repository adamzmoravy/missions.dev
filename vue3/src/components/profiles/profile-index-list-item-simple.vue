<template>
    <div class="list-container-simple clickable-container" :class="{'item-selected': selected}">

        <div class="profile-container-simple" :style="{width: size + 'px'}">
          <ui-a :href="'/profile/' + source.user_id">
              <profile-image-simple :width="size + 10" :height="size + 10" :profile="source" :alt="source.first_name + ' ' + source.last_name">
                <template #icon>
                  <reactions-icon class="reaction-icon-component" v-if="reaction" :mode="'selected'" :reaction="reaction" :size="'25px'" />
                </template>
              </profile-image-simple>
          </ui-a>

        </div>

        <div class="profile-text">
            <ui-a class="small-2" :href="'/profile/' + source.user_id">{{ source.first_name + ' ' + source.last_name }}</ui-a>
        </div>


    </div>
</template>


<style scoped>

.profile-container-simple {
        margin-right:0.4em;
        flex-grow: 0;
        position:relative;
    }

.item-selected {
    background-color: #19b177;
}
.reaction-icon-component {
    position: absolute;
    bottom: 0;
    right: 0;
    z-index: 1;

}

</style>

<script>
  export default {
    name: 'profile-index-list-item-simple',
    props: {
      selected: {
        type: Boolean,
        default: false
      },
      size: {
        type: Number,
        default: 30
      },
      index: { // index of current item
        type: Number
      },
      reaction: {
        default: null
      },
      source: { // here is: {uid: 'unique_1', text: 'abc'}
        type: Object,
        default () {
          return {}
        }
      }
    }
  }
</script>
