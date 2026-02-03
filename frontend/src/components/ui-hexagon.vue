<template>
    <div>
        <div class="hexagon" :class="'stage-'+stage">
        <slot></slot>

    </div>

    </div>

</template>

<style scoped>
.hexagon {
    background-color:#a6a6a6;
    rotate: 90deg;
    width: 100%;
    height: 100%;
    clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
}

.stage-0 {
    background-color: #F7BCB6;
}
.stage-1 {

    background-color: #f8dd6c;
}
.stage-2 {
    background-color: #73BF98;
}

</style>

<script>

export default {
    name: 'ui-hexagon',
    props: {
        stage: {
            required: false,
            default: '1'
        }
    }
}

</script>