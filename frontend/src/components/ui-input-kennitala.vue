<template>

    <input type="text" autofocus @blur="$emit('blur')" @keydown.enter="$emit('keydown-enter')" @keyup.esc="$emit('keyup-esc')" v-model="value" @input="updateValue" :placeholder="placeholder" :class="{'invalid': invalid, 'valid': valid}" class="form-control">

</template>

<style scoped>

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

    name: 'ui-input-kennitala',
    methods: {
        updateValue(event) {
            this.$emit('update:modelValue', event.target.value)
        },
        kennitalaCheck: function (value) {
            if (value && value !== null) {
                return new Kennitala(this.value)                
            }
            return new Kennitala('')
        }
    },
    computed: {
        value: {
            get() {
                return this.modelValue
            },
            set(value) {
                this.$emit('update:modelValue', value)
            }
        },
        invalid: function () {
            if (this.value == '') {
                return false
            }
            return !this.kennitalaCheck(this.value).valid
        },
        valid: function () {
            return this.kennitalaCheck(this.value).valid
        },
    },
    watch: {
        modelValue: function (value) {

            // if value contains anything but numbers, remove it
            let cleanValue = value.replace(/\D/g,'')
            this.$emit('update:modelValue', cleanValue)

        }
    },
    props: {
        autofocus: {
            required: false,
            default: false,
            type: Boolean
        },
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