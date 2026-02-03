<template>

    <div>
        <div class="json-editor" ref="editorHolder">

        </div>

    </div>

</template>

<style>

.level-1 .je-object__title {
    display: none!important;
}
.json-editor-btn-collapse {
    display: none!important;
}

.json-editor-btn-edit_properties {
    display: none!important;
}

</style>

<script>

    import { JSONEditor } from '@json-editor/json-editor/src/core.js'
    export default {
        name: 'ui-json-editor',
        data: function () {
            return {
                editor: null,
                outputJson: {}
            }
        },
        props: {
            value: {
            },
            schema: {
                type: Object,
                default: function () {
                    return {}
                }
            }
        },
        methods: {
            initiateEditor() {
                const element = this.$refs.editorHolder;
                this.editor = new JSONEditor(element, {
                theme: 'bootstrap4',
                compact: false,
                disable_array_reorder: false,
                no_additional_properties: false,
                array_controls_top: true,
                disable_edit_json: true,
                startval: this.value,
                schema: this.schema,
              })
              this.editor.on('change', function() {
                  var json = this.editor.getValue()
                    this.$emit('value-update', json)
                  this.outputJson = json

              }.bind(this))

            }
        },
        mounted() {
            this.initiateEditor()
        },
    }

</script>