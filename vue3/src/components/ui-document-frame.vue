<template>
    <div @click="$emit('clicked')">
        <div class="document-container">
            <slot></slot>
            <div class="name elipsis">
                {{ documentName }}
            </div>
        </div>

    </div>

</template>

<style scoped>

    .elipsis {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .name {
        font-size: 0.8rem;
        position:absolute;
        width:100%;
        text-align: center;
        background-color:white;
        border-top-style:solid;
        border-top-width:1px;
        border-top-color:lightgrey;
        bottom:0;
    }

    .document-container:hover {
        transform:translateY(-2px);
        filter: drop-shadow(0 0 0.5rem rgb(214, 213, 213));
    }
    .document-container {
        overflow: hidden;
        background-color: white;
        cursor: pointer;
        position:relative;
        width: 123px;
        aspect-ratio: 1 / 1.41;
        border-width:1px;
        border-style:solid;
        border-color:lightgrey;
    }

</style>

<script>

export default {
    name: 'ui-document-frame',
    props: {
        documentName: {
            required: false,
            default: 'Document',
            type: String
        }
     },
    data() {
        return {
         }
    },
    mounted() {
    }
}

</script>