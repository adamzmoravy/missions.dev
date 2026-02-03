<template>

    <input @keydown.enter="$emit('keydown-enter')" ref="input" type="text" @blur="$emit('blur')"  v-model="value" :placeholder="placeholder" :class="{'invalid': invalid, 'valid': valid}" class="form-control">

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
import 'kennitalajs/kennitala'
export default {

    name: 'ui-input-text',
    methods: {
        updateValue(event) {

            let value = this.value
            // remove all non numeric characters
            let cleanValue = value.replace(/\D/g,'')
            if (value == cleanValue) {
                // no non numeric characters
                this.$emit('input', value)
                return
            }
            this.$refs.input.value = cleanValue

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
            return !this.kennitalaCheck.valid
        },
        valid: function () {
            return this.kennitalaCheck.valid
        },
        kennitalaCheck() {
            if (this.value && this.value !== null) {
                return new Kennitala(this.value)                
            }
            return new Kennitala('')
        }        
    },
    props: {
        placeholder: {
            required: false,
            default: '',
            type: String
        }
    }
}

</script>