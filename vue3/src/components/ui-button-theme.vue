<style scoped>
    .sketchy:hover {
        border-color:black;
        color:black;
        border-width:1px;
    }


    .sketchy:disabled {
        opacity:0.7;
    }
    .sketchy {
        margin-right:2px;
        box-sizing:border-box;
        display: inline-block;
        border-style:none;
        border-width:0px;
/*        border: 1px solid rgba(91, 91, 91, 0);*/
        font-weight:600;
        border-radius: 4px;
        background: #ffffff;
        position: relative;
        color:#3b3b3b;
        
        &::before {
            content: '';
            border: 0px solid rgb(91, 91, 91);
            display: block;
            width: 100%;
            height: 100%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate3d(-50%, -50%, 0) scale(1.045) rotate(0.5deg);
            border-radius: 4px;

        }
    }

    .btn-new-hand {
        background-color: #73BF98;

    } 
    .btn-danger-hand {
        background-color:#F7BCB6;        
    }
    .btn-primary-hand {
        background-color:white;
        border-width:1px!important;
        border-color:#bcbcbc;
    }

    .btn-borderless-hand {
        background-color:transparent;
        border-color:transparent!important;
        &::before {
            border-color:transparent!important;
        }        
    }

    .btn-borderless-hand:hover .button-label {
        text-decoration: underline;
    }

    .btn-action-hand {
        background-color: #FBDD6B;
    }


</style>

<template>

    <button :disabled="$parent.disabled" type="buttom" :class="classes" class="btn">
        <span v-if="!$parent.working">
            <div class="d-flex align-items-center justify-content-center">
                <slot></slot><span class="button-label">{{$parent.label}}</span>

            </div>
        </span>

        <span v-if="$parent.working" class="lds-ellipsis align-center">
            <div></div><div></div><div></div><div></div>
        </span>

    </button>

</template>

<script>
    export default {
        name: 'ui-button-theme',
        computed: {
            classes: function () {
                let buttonType = 'btn-primary-hand'
                if (this.typeToClass[this.$parent.buttonType]) {
                    buttonType = this.typeToClass[this.$parent.buttonType]
                }
                let classes = [
                    buttonType,
                    `btn-${this.$parent.size}`
                ]
                return classes
            }
        },
        data() {
            return {
                typeToClass: {
                    action: 'btn-action-hand',
                    new: 'btn-new-hand',
                    cancel: 'btn-primary-hand',
                    remove: 'btn-danger-hand',
                    primary: 'btn-primary-hand',
                    discreet: 'btn-borderless-hand',
                }
            }
        }
    }
</script>
