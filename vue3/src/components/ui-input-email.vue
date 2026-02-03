<template>
    <div class="email-input-container">
        <input 
            type="email" 
            v-model="value" 
            @input="updateValue"
            @blur="handleBlur"
            @keydown.enter="$emit('keydown-enter')"
            @keyup.esc="$emit('keyup-esc')"
            :placeholder="placeholder"
            :class="{'invalid': invalid, 'valid': valid}"
            class="form-control"
            :autofocus="autofocus"
        >
        <span v-if="showError && errorMessage" class="error-message">
            {{ errorMessage }}
        </span>
    </div>
</template>

<style scoped>
    .email-input-container {
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
        position: absolute;
        left: 0;
        bottom: -20px;
        font-size: 12px;
        color: red;
    }
</style>

<script>
export default {
    name: 'ui-input-email',
    data() {
        return {
            showError: false
        }
    },
    methods: {
        updateValue(event) {
            this.$emit('update:modelValue', event.target.value)
        },
        handleBlur() {
            this.showError = true
            this.$emit('blur')
        },
        validateEmail(email) {
            if (!email || email.trim() === '') {
                return { valid: false, error: '' }
            }

            // Basic format check
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
            if (!emailRegex.test(email)) {
                return { valid: false, error: 'Invalid email format' }
            }

            // More strict validation
            const strictRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/
            if (!strictRegex.test(email)) {
                return { valid: false, error: 'Invalid email format' }
            }

            // Check for common typos
            const domain = email.split('@')[1]
            if (domain) {
                // Check for spaces
                if (domain.includes(' ')) {
                    return { valid: false, error: 'Email cannot contain spaces' }
                }

                // Check for double dots
                if (domain.includes('..')) {
                    return { valid: false, error: 'Invalid domain format' }
                }

                // Check for valid TLD (at least 2 characters)
                const tld = domain.split('.').pop()
                if (tld.length < 2) {
                    return { valid: false, error: 'Invalid domain extension' }
                }
            }

            // Check local part (before @)
            const localPart = email.split('@')[0]
            if (localPart.length > 64) {
                return { valid: false, error: 'Email address is too long' }
            }

            // Check total length
            if (email.length > 254) {
                return { valid: false, error: 'Email address is too long' }
            }

            return { valid: true, error: '' }
        }
    },
    computed: {
        value: {
            get() {
                return this.modelValue || ''
            },
            set(value) {
                this.$emit('update:modelValue', value)
            }
        },
        validation() {
            return this.validateEmail(this.value)
        },
        invalid() {
            if (!this.value || this.value.trim() === '') {
                return false
            }
            return !this.validation.valid
        },
        valid() {
            if (!this.value || this.value.trim() === '') {
                return false
            }
            return this.validation.valid
        },
        errorMessage() {
            return this.validation.error
        }
    },
    watch: {
        modelValue() {
            // Reset error display when user starts typing again
            if (this.showError && this.value) {
                this.showError = false
            }
        }
    },
    props: {
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
            default: 'Email address',
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