<template>
    <div class="hat" :class="color" style="height: 70%;"></div>
</template>

<script>
    export default {
        name: 'hat',
        data() {
            return {
            }
        },
        props: {
            color: {
                required: false,
                type: String,
                default: 'black'
            }
        },
        mixins: [
        ],
        notifications: {
        },
        methods: {
        },
        beforeDestroy () {
        },
        created: function() {

        },
        mounted: function() {
        }
    }
</script>

<style scoped>
.black {
    background-color: black;
}
.white {
    background-color: lightgrey;
}
.red {
    background-color:rgb(255, 102, 102);
}
.green {
    background-color: rgb(102, 204, 153);
}
.blue {
    background-color: rgb(102, 102, 204);
}

.yellow {
    background-color: rgb(255, 204, 102);
}


</style>
