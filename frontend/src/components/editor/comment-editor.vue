
<script>
import { Editor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import { BubbleMenu } from '@tiptap/vue-3'
import Mention from '@tiptap/extension-mention'
import { mergeAttributes } from '@tiptap/core'
import Link from '@tiptap/extension-link'
import Youtube from '@tiptap/extension-youtube'

import suggestion from './tiptap-suggestions.js'

import TiptapEntityMention from './tiptap-mention-entity-extension.js'

export default {
  components: {
    EditorContent,
    BubbleMenu
  },
  emits: ['update:modelValue'],
  props: {
    type: {
      type: String,
      default: 'comment',
    },
    editable: {
      type: Boolean,
      default: false,
    },
    modelValue: {
      type: Object,
      default: () => ({
        type: 'doc',
        content: [
          {
            type: 'paragraph',
          },
        ],
      })
    },
    placeholder: {
      type: String,
      default: 'Write something …',
    }
  },

  data() {
    return {
      editor: null
    }
  },
  watch: {
    modelValue(value) {
      // HTML
//      const isSame = this.editor.getHTML() === value

      // JSON
       const isSame = JSON.stringify(this.editor.getJSON()) === JSON.stringify(value)

      if (isSame) {
        return
      }

      this.editor.commands.setContent(value, false)
    },
  },
  mounted() {
    this.editor = new Editor({
        editable: this.editable,
        content: this.modelValue,
        autofocus: false,
        extensions: [
          StarterKit,
          TiptapEntityMention,
          Link.configure({
            openOnClick: false,
            defaultProtocol: 'https',
          },
          ),
          Youtube.configure({
            controls: false,
            nocookie: true,
          }),
        Mention.configure({
          renderHTML({ options, node }) {
              return [
                'a',
                mergeAttributes({ }, options.HTMLAttributes),
                `${node.attrs.label}`,
              ]
            },
          suggestion,
        })
    ],
      onUpdate: () => {
        // JSON
        this.$emit('update:modelValue', this.editor.getJSON())
      },
      onFocus: () => {
        this.$emit('focus')
      },
      onBlur: () => {
        this.$emit('blur')
      }

    })
  },
  computed: {
    entities: function () {
        // getSortedEntities
        return this.$stores.entities.entities
    }
  },
  methods: {
    addVideo() {
      const url = prompt('Enter YouTube URL')
      this.editor.commands.setYoutubeVideo({
        src: url,
        width: '100%'
      })
    },
    insertSomething() {
        this.editor
        .chain()
        .focus()
        .insertContent({
            type: 'entityMention',
            attrs: {
                id: 1,
                type: 'profile',
            },
        })
        .run();
    },
  },
  beforeDestroy() {
    this.editor.destroy()
  },
}
</script>



<template>
        <div :class="{ editable: editable, [type]: true }">
            <div v-if="editor" class="edit-container">


              <ui-context-menu class="text-box-menu" v-if="editable" :options="[
                    {label: $trans('+ Video'), action: 'video'}
                    ]" 
                    @video="addVideo"
                
                />

              <editor-content class="edit-content" :editor="editor"/>
            </div>
            <bubble-menu
                :editor="editor"
                :tippy-options="{ duration: 100 }"
                v-if="editor"
                >
                <div class="bubble-menu" v-if="editable">
                    <ui-button size="sm" class="text-formatting-button"  @click="editor.chain().focus().toggleBold().run()" :class="{ 'is-active': editor.isActive('bold') }">
                    <strong>B</strong>
                    </ui-button>
                    <ui-button size="sm" class="text-formatting-button" @click="editor.chain().focus().toggleItalic().run()" :class="{ 'is-active': editor.isActive('italic') }">
                    <i>I</i>
                    </ui-button>
                    <ui-button size="sm" class="text-formatting-button"  @click="editor.chain().focus().toggleStrike().run()" :class="{ 'is-active': editor.isActive('strike') }">
                    <s>S</s>
                    </ui-button>
                </div>
                </bubble-menu>
        </div>

    </template>
  
<style lang="scss">

.text-box-menu {

  position:absolute;
  right:25px;
  padding-top:5px;
}

.comment-reply .text-box-menu {

  position:absolute;
  right:25px;

}

.tiptap a {
  overflow-wrap: break-word;

}

.editor-container {

  position:relative;
}

[data-youtube-video] {
  position: relative;
  padding-bottom: 56.25%; /* 16:9 aspect ratio */
  height: 0;
  overflow: hidden;
  max-width: 100%;
  background: #000;
  margin-bottom: 1rem;
}

[data-youtube-video] iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.text-formatting-button {
  min-width:30px;
}
.editable {
  .tiptap p {
}
}

.editable .tiptap {
  border: 1px solid #d1d5db;
  border-radius: 0.25rem;
}

.editable.comment .tiptap {
  min-height:100px;
  padding: 1rem;
}

.editable.reply .tiptap {
  min-height:50px;
  padding: 0.7rem;

}


.tiptap p.is-editor-empty:first-child::before {
  
  color: #7e8388;
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}


.editable.comment p {

  font-size:1.25rem!important;
  line-height:1.5;
  font-weight: 400;

}

.editable a {
  font-weight:600;
}


.tiptap:focus {
  box-shadow:0 0 0 0.2rem rgba(0, 123, 255, 0.25);
  outline:0;
}


/* Bubble menu */
/* Basic editor styles */
.tiptap {
  :first-child {
    margin-top: 0;
  }

  /* List styles */
  ul,
  ol {
    padding: 0 1rem;
    margin: 1.25rem 1rem 1.25rem 0.4rem;

    li p {
      margin-top: 0.25em;
      margin-bottom: 0.25em;
    }
  }

  /* Heading styles */
  h1,
  h2,
  h3,
  h4,
  h5,
  h6 {
    line-height: 1.1;
    margin-top: 2.5rem;
    text-wrap: pretty;
  }

  h1,
  h2 {
    margin-top: 3.5rem;
    margin-bottom: 1.5rem;
  }

  h1 {
    font-size: 1.4rem;
  }

  h2 {
    font-size: 1.2rem;
  }

  h3 {
    font-size: 1.1rem;
  }

  h4,
  h5,
  h6 {
    font-size: 1rem;
  }

  /* Code and preformatted text styles */
  code {
    background-color: var(--purple-light);
    border-radius: 0.4rem;
    color: var(--black);
    font-size: 0.85rem;
    padding: 0.25em 0.3em;
  }

  pre {
    background: var(--black);
    border-radius: 0.5rem;
    color: var(--white);
    font-family: 'JetBrainsMono', monospace;
    margin: 1.5rem 0;
    padding: 0.75rem 1rem;

    code {
      background: none;
      color: inherit;
      font-size: 0.8rem;
      padding: 0;
    }
  }

  blockquote {
    border-left: 3px solid var(--gray-3);
    margin: 1.5rem 0;
    padding-left: 1rem;
  }

  hr {
    border: none;
    border-top: 1px solid var(--gray-2);
    margin: 2rem 0;
  }
}

/* Bubble menu */
.bubble-menu {
  border: 1px solid var(--gray-1);
  border-radius: 0.7rem;
  box-shadow: var(--shadow);
  display: flex;
  padding: 0.2rem;

  button {
    background-color: unset;

    &:hover {
      background-color:lightgrey;
    }

    &.is-active {
      background-color: lightgrey;

      &:hover {
        background-color: lightgrey;
      }
    }
  }

}
.mentioned {
      background-color:#ececec;
      border-radius: 0.4rem;
      padding-left: 0.4rem;
      padding-right: 0.4rem;
      padding-top: 0.1rem;
      padding-bottom: 0.1rem;
    }

</style>
