<template>
    <span class="d-inline-block">
        <span class="d-flex justify-content-center text-align-center h-100">
            <img v-if="size=='auto'" class="type-icon" :src="'/img/badge-' + $ui.stageHandles[stage] + '.svg'">
            <img v-else :style="{height:size/2.9 + 'px'}" class="type-icon" :src="'/img/badge-' + $ui.stageHandles[stage] + '_' + size +'.png'">
        </span>
        <span class="d-flex justify-content-center text-align-center">
            <p class="label" v-if="showLabel">{{label}}</p>
        </span>

    </span>
</template>

<style scoped>


.type-icon {
    height:100%;
}
</style>

<script>

export default {
    name: 'submission-type',
    props: {
        size: {
            required: false,
            default: '100'
        },
        stage: {
            required: true
        },
        showLabel: {
            type: Boolean,
            required: false,
            default: false
        }	
    },
    computed: {
        label: function() {
            return this.$trans(this.$ui.stageHandles[this.stage])
        }
    }
}

</script>

<style>

.label {
    text-transform:capitalize;
    margin-top:2px;
}

</style>