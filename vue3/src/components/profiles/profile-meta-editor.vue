<template>

    <div>
        <template v-if="meta" v-for="definition in metaDefininitionsAsArray">
            <div class="form-group" v-if="includeMetaKeys.length === 0 || includeMetaKeys.includes(definition.key)" :key="definition.key">
                <label class="mb-0"><strong>{{ definition.label }}</strong></label>
                    <p class="small-2 mb-0" style="white-space: pre-wrap;" v-if="definition.explainer">{{ definition.explainer }}</p>
                <div class="input-group mt-2" style="width:100%">
                    <textarea v-if="definition.type && definition.type === 'textarea'" class="form-control" v-model="meta[definition.key]" rows="4"></textarea>
                    <input v-else type="text" class="form-control" v-model="meta[definition.key]">
                </div>
            </div>
            <ui-button class="mb-3" v-if="!saveOnDestroy" @click="saveMeta()" :label="$trans(updateButtonLabel)"></ui-button>

        </template>
    </div>

</template>

<script>

import backend from '../../backend.js'
export default {
    name: 'profile-meta-editor',
    data() {
        return {
            meta: null
        }
    },
    beforeUnmount () {
        if (this.saveOnDestroy) {
            this.saveMeta()
        }
    },
    methods: {
        saveMeta: function () {
            backend.patch('/users/me/profile', {
                meta: this.meta,
            }).then(response => {
                this.$emit('saved')
            })
        }
    },
    computed: {
        metaDefininitionsAsArray: function () {
            let result = []
            for (let key in this.platformMetaDefinitions.default) {
                let definition = this.platformMetaDefinitions.default[key]
                definition.key = key
                result.push(definition)
            }
            // sort result array by order
            result.sort(function (a, b) {
                if (a.order < b.order) {
                    return -1
                }
                if (a.order > b.order) {
                    return 1
                }
                return 0
            })
            return result
        }
    },
    mounted() {
        if (!this.profile.meta) {
            this.meta = {}
        } else {
            this.meta = this.profile.meta
        }
        // add keys from platformMetaDefinitions that are not in meta
        for (let key in this.platformMetaDefinitions.default) {
            if (!this.meta[key]) {
                this.meta[key] = ''
            }
        }
    },
    props: {
        includeMetaKeys: {
            required: false,
            default: function () {
                return []
            }
        },
        updateButtonLabel: {
            required: false,
            default: 'Update'
        },
        saveOnDestroy: {
            required: false,
            default: false
        },        
        profile: {
            required: true
        },
        platformMetaDefinitions: {
            required: false,
            default: function () {
                return {}
            }
        }
    }

}

</script>