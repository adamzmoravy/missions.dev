
<template>

        <img :style="style" :src="src">

</template>

<style scoped>

</style>

<script>

export default {
    name: 'stage-icon',
    computed: {
        src: function () {
            return this.iconFiles.normal
        },
        handle: function () {
            return this.$ui.stageHandles[this.stage]
        },
        iconFiles: function () {
            if (!this.$ui.iconFiles['stage-' + this.handle]) {
                return this.$ui.iconFiles['stage-default']
            }
            return this.$ui.iconFiles['stage-' + this.handle]
        },
        style: function() {
            let style = {
                position: 'relative',
                width: this.size + 'px'
            }
            if (this.iconFiles.scale) {
                {
                    style.transform = 'scale(' + this.iconFiles.scale + ')'
                }
            }
            if (this.iconFiles.top) {
                {
                    style.top = this.size * this.iconFiles.top + 'px'
                }
            }
            return style
        }
    },
    props: {
        size: {
            type: Number,
            default: 24
        },
        stage: {
            required: true
        }
    }
}

</script>