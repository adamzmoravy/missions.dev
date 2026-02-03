<template>
    <div class="url-input-container">
        <input 
            type="url" 
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
    .url-input-container {
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
    name: 'ui-input-url',
    data() {
        return {
            showError: false
        }
    },
    emits: ['update:modelValue', 'blur', 'keydown-enter', 'keyup-esc', 'valid-state'],
    methods: {
        updateValue(event) {
            this.$emit('update:modelValue', event.target.value)
        },
        handleBlur() {
            this.showError = true
            this.$emit('blur')
        },
        validateUrl(url) {
            if (!url || url.trim() === '') {
                return { valid: false, error: '' }
            }

            // Try to parse the URL
            let parsedUrl
            try {
                parsedUrl = new URL(url)
            } catch (e) {
                return { valid: false, error: 'Invalid URL format' }
            }

            // Check if protocol is in the allowed list
            const protocol = parsedUrl.protocol.replace(':', '')
            if (!this.allowedProtocols.includes(protocol)) {
                return { 
                    valid: false, 
                    error: `Protocol must be one of: ${this.allowedProtocols.join(', ')}`
                }
            }
            // Check for spaces that are not at the end of the string
            const trimmedUrl = url.trimEnd()
            if (trimmedUrl.includes(' ')) {
                return { valid: false, error: 'URL cannot contain spaces' }
            }

            // Check for hostname
            if (!parsedUrl.hostname) {
                return { valid: false, error: 'URL must have a hostname' }
            }

            // Check for valid hostname format
            const hostnameRegex = /^[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/
            if (!hostnameRegex.test(parsedUrl.hostname)) {
                return { valid: false, error: 'Invalid hostname format' }
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
            return this.validateUrl(this.value)
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
        valid(oldVal, newVal) {
            this.$emit('valid-state', this.valid)
        },
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
            default: 'Enter URL',
            type: String
        },
        allowedProtocols: {
            required: false,
            default: () => ['https', 'http'],
            type: Array
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
