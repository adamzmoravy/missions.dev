<template>
    <div class="input-email-list-container">
        <input 
            type="text" 
            :autofocus="autofocus" 
            @blur="$emit('blur')" 
            @keydown.enter="$emit('keydown-enter')" 
            @keyup.esc="$emit('keyup-esc')" 
            v-model="value" 
            @input="updateValue" 
            :placeholder="placeholder" 
            :class="{'invalid': invalid, 'valid': valid}" 
            class="form-control">
        <div v-if="invalid && value && showErrorMessage" class="error-message">
            Invalid email address(es). Please check the format.
        </div>
    </div>
</template>

<style scoped>
    .input-email-list-container {
        position: relative;
        width: 100%;
    }

    .invalid {
        border-color: red;
    }

    .valid {
        background-color:  var(--valid-background);
    }

    .valid:focus {
        background-color: var(--valid-background);
    }

    .error-message {
        color: red;
        font-size: 12px;
        margin-top: 4px;
    }
</style>

<script>

export default {

    name: 'ui-input-email-list',
    methods: {
        updateValue(event) {
            this.$emit('update:modelValue', event.target.value)
        },
        validateEmail(email) {
            // RFC 5322 compliant email regex (simplified)
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
            return emailRegex.test(email.trim())
        },
        validateEmailList(value) {
            if (!value || value.trim() === '') {
                return true // Empty is valid (no validation)
            }

            // Split by comma and validate each email
            const emails = value.split(',').map(email => email.trim()).filter(email => email !== '')
            
            if (emails.length === 0) {
                return true
            }

            // Check if all emails are valid
            return emails.every(email => this.validateEmail(email))
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
            if (!this.value || this.value === '') {
                return false
            }
            return !this.validateEmailList(this.value)
        },
        valid: function () {
            if (!this.value || this.value === '') {
                return false
            }
            return this.validateEmailList(this.value)
        }
    },
    props: {
        showErrorMessage: {
            required: false,
            default: false,
            type: Boolean
        },
        autofocus: {
            required: false,
            default: false,
            type: Boolean
        },
        modelValue: {
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
                this.$el.querySelector('input').focus()
            })
        }
    }
}

</script>
