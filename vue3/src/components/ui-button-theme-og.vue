<style scoped>
    .button-proceed {
        font-family:permanent-marker, sans-serif;
        font-size:15px;
        background-size:contain;
        background-repeat: no-repeat;
        min-width: 119px;
        height: 63px;
        margin-bottom:-3px;
    }

    .button-new {
        background-image:url('/img/buttons/button-round-green.svg');
    }

    .button-action {
        background-image:url('/img/buttons/button-round-yellow.svg');
    }


    .button-cancel {
        background-image:url('/img/buttons/button-round-pink.svg');
    }

    .btn-md {
        font-family:permanent-marker, sans-serif;
        font-size:18px;
        border-radius: 255px 15px 225px 15px/15px 225px 15px 255px;
        margin:5% auto;
        padding:1em;
        line-height:1.5em;
        background:hsla(67, 95%, 95%, 1);
        border-radius: 20px;
        border:solid 2px rgb(0, 0, 0);
    }


    .btn-sm {
        background-size: 80px;
        height:42px;
        min-width:80px;
        font-size:14px;
    }
    .btn-sm .lds-ellipsis { 
        width:20px;
    }
</style>

<template>

    <button @click="$emit('click')" type="buttom" :class="classes" class="button btn second secondary">
        <span v-if="!$parent.working">
            <slot></slot>{{$parent.label}}
        </span>

        <span v-if="$parent.working" class="lds-ellipsis align-center">
            <div></div><div></div><div v-if="$parent.size != 'sm'"></div><div v-if="$parent.size != 'sm'"></div>
        </span>

    </button>

</template>

<script>
    export default {
        name: 'ui-button-missions',
        data() {
            return {
                typeToClass: {
                    action: 'button-action',
                    new: 'button-new',
                    cancel: 'button-cancel'
                }
            }
        },
        computed: {
            classes: function () {
                let classes = [
                    this.typeToClass[this.$parent.buttonType],
                    `btn-${this.$parent.size}`
                ]
                return classes
            }
        },        
        mixins: []
    }
</script>
