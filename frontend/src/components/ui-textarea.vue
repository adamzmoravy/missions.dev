<template>
    <div class="input-text-container">
        <textarea type="text" v-model="value" :rows="rows" :placeholder="placeholder" @input="updateValue" :maxlength="maxLength" class="form-control"></textarea>
        <span v-if="charsLeft <= 50" class="chars-left">
            {{value.length }}/{{ maxLength }}
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
</style>

<script>

export default {

    name: 'ui-textarea',
    data() {
        return {
            textareaValue: ''
        }
    },
    computed: {
        charsLeft() {
            return this.maxLength - this.value.length
        },
        value: {
            get() {
                return this.modelValue
            },
            set(value) {
                this.$emit('update:modelValue', value)
            }
        }        
    },
    watch: {
        textareaValue: function(newValue) {
            this.$emit('input', newValue)
        }
    },
    methods: {
        updateValue(event) {
            this.$emit('update:modelValue', event.target.value)
        }
    },
    props: {
        rows: {
            required: false,
            default: 4,
            type: Number
        },
        maxLength: {
            required: false,
            default: 10000,
            type: Number
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
    }
}

</script>