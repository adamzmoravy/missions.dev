import tippy  from 'tippy.js'
import { VueRenderer } from '@tiptap/vue-3'
import { useEntitiesStore } from '../../stores/entities'


import mention_list from './mention-list.vue'


let convertEntitiesToMentions = function (entities) {
  return entities.map(entity => ({
    id: entity.type + '-' + entity.id,
    label: entity.title,
    entity: entity
  }))
}


export default {
  items: ({ query }) => {
    return convertEntitiesToMentions(useEntitiesStore().entities).filter(entity => entity.label.toLowerCase().startsWith(query.toLowerCase())).slice(0, 7)
  },

  render: () => {
    let component
    let popup

    return {
      onStart: props => {
        component = new VueRenderer(mention_list, {
          props,
          editor: props.editor
        })

        if (!props.clientRect) {
          return
        }

        popup = tippy('body', {
          getReferenceClientRect: props.clientRect,
          appendTo: () => document.body,
          content: component.element,
          showOnCreate: true,
          interactive: true,
          trigger: 'manual',
          placement: 'bottom-start',
        })
      },

      onUpdate(props) {
        component.updateProps(props)

        if (!props.clientRect) {
          return
        }

        popup[0].setProps({
          getReferenceClientRect: props.clientRect,
        })
      },

      onKeyDown(props) {
        if (props.event.key === 'Escape') {
          popup[0].hide()

          return true
        }

        return component.ref?.onKeyDown(props)
      },

      onExit() {
        popup[0].destroy()
        component.destroy()
      },
    }
  },
}
