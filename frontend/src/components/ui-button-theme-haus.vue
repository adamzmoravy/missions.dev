<style scoped>
.btn-primary {
    background-color:#fbdd6b;
    color:#3b3b3b;
    border-style:none;
}

.btn-success {
    background-color:#73bf98;
    color:#3b3b3b;
    border-style:none;

}
.btn-danger {
    background-color:#f7bcb6;
    color:#3b3b3b;
    border-style:none;

}
.btn-secondary {
    background-color:#ffffff;
    color:#3b3b3b;
    border-color:#dbdbdb;
}

.btn-sm .lds-ellipsis {
    width:40px;
    height:10px;
}
</style>

<template>

    <button :disabled="$parent.disabled" type="buttom" :class="classes" class="btn">
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
        name: 'ui-button-theme-haus',
        computed: {
            classes: function () {
                let classes = [
                    this.typeToClass[this.$parent.buttonType],
                    `btn-${this.$parent.size}`
                ]
                return classes
            }
        },
        data() {
            return {
                typeToClass: {
                    action: 'btn-primary',
                    new: 'btn-success',
                    cancel: 'btn-secondary',
                    remove: 'btn-danger',
                    primary: 'btn-primary',
                    default: 'btn-secondary',
                    
                }
            }
        }
    }
</script>
