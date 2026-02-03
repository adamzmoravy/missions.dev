<template>
    <node-view-wrapper class="entity-mention">
        <ui-a v-if="entity" @click="goToEntity">{{ entity.title }}</ui-a>
     </node-view-wrapper>
  </template>
  
  <script>
  import { nodeViewProps, NodeViewWrapper } from '@tiptap/vue-3'
  
  export default {
    components: {
      NodeViewWrapper,
    },
  
    props: nodeViewProps,
    computed: {
      entity() {
        let entityId = this.node.attrs.id
        let type = this.node.attrs.type

        let entity = this.$stores.entities.getFilteredEntities(
          [{
            selection: 'id',
            value: entityId
          },
          {
            selection: 'type',
            value: type
          }]);

          return entity[0]

      },
    },  
    methods: {
      goToEntity() {
          this.$router.push('/' + this.$lang + this.entity.link)
      },
    },
  }
  </script>
  
  <style lang="scss">


  .entity-mention {
    display: inline-block;

  }


  </style>
  