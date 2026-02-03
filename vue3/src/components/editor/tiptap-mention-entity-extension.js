import { mergeAttributes, Node } from '@tiptap/core'
import { VueNodeViewRenderer } from '@tiptap/vue-3'

import Component from './tiptap-entity-mention.vue'

export default Node.create({
  name: 'entityMention',
  group: 'inline',
  inline: true,
  atom: true,

  addAttributes() {
    return {
      id: {
        default: null,
      },
      type: {
        default: null,
      }
    }
  },

  parseHTML() {
    return [
      {
        tag: 'tiptap-entity-mention',
      },
    ]
  },

  renderHTML({ HTMLAttributes }) {
    return ['tiptap-entity-mention', mergeAttributes(HTMLAttributes)]
  },

  addNodeView() {
    return VueNodeViewRenderer(Component)
  },
})
