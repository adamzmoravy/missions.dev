<template>

    <div :style="{width: size + 'px', height: size + 'px', fontSize: fontSize}" :class="{'icon-control-selected': selected}" class="icon-container icon-control circle-border">
        <slot>
        </slot>
    </div>

</template>


<style scoped>
.circle-border {
    border-radius:90%;border-style:solid;border-width:1px;
    aspect-ratio: 1/1;
}
.icon-container {
    display:flex;
    justify-content:center;
    align-items:center;
    aspect-ratio: 1/1;
}


.icon-control {
    z-index:0;
    cursor:pointer;
}


.icon-control-selected {
    background-color:var(--very-dark);
    color:white;
}
</style>

<style>

.icon-control-selected img {
    filter:invert(1);
}
</style>


<script>

export default {
    name: 'ui-icon-control',
    props: {
        size: {
            type: Number,
            default: 40
        },
        selected: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        fontSize: function() {
            return (this.size / 40) + 'rem'
        }
    },
}

</script>