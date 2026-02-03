<template>
    <div class="input-text-container">
        <div style="position: relative;"></div>
        <input :list="list" :type="type" autofocus @blur="$emit('blur')" @keydown.enter="$emit('keydown-enter')" @keyup.esc="$emit('keyup-esc')" v-model="value" @input="updateValue" :placeholder="placeholder" :class="{'invalid': invalid, 'valid': valid}" class="form-control" :maxlength="maxLength">
        <span v-if="charsLeft <= 50" class="chars-left">
            {{ value.length }}/{{ maxLength }}
        </span>
    </div>
</template>

<style scoped>
    .input-text-container {
        position: relative;
        width: 100%;
    }
    .chars-left {
        position: absolute;
        right: 0px;
        bottom: -20px;
        font-size: 14px;
        color: #999;
    }
    .invalid {
        border-color:red;
    }

    .valid {
        background-color:  var(--valid-background);
    }

    .valid:focus {
        background-color: var(--valid-background);
    }


</style>

<script>

export default {

    name: 'ui-input-text',
    methods: {
        updateValue(event) {
            this.$emit('update:modelValue', event.target.value)
        }
    },
    computed: {
        charsLeft() {
            return this.maxLength - this.value.length
        },
        value: {
            get() {
                if (this.modelValue)  {
                    return this.modelValue
                }
                return '' 
            },
            set(value) {
                this.$emit('update:modelValue', value)
            }
        }
    },
    props: {
        list: {
            required: false,
            default: '',
            type: String
        },
        type: {
            required: false,
            default: 'text',
            type: String
        },
        maxLength: {
            required: false,
            default: 500,
            type: Number
        },
        autofocus: {
            required: false,
            default: false,
            type: Boolean
        },
        invalid: false,
        valid: false,
        modelValue : {
            required: false,
            default: ''
        },
        placeholder: {
            required: false,
            default: '',
            type: String
        }
    },
    mounted() {
        if (this.autofocus) {
            this.$nextTick(() => {
                this.$el.focus()
            })
        }
    }
}

</script>