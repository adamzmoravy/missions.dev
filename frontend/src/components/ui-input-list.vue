<template>
    <div class="input-list-container">
        <div class="list-items" v-if="items.length > 0">
            <span v-for="(item, index) in items" :key="index" class="list-item">
                {{ item }}
                <button type="button" @click="removeItem(index)" class="remove-btn">&times;</button>
            </span>
        </div>
        <input 
            type="text" 
            v-model="currentInput"
            @keydown.enter.prevent="addItem"
            @keyup.esc="$emit('keyup-esc')"
            @blur="$emit('blur')"
            :placeholder="placeholder" 
            :class="{'invalid': invalid, 'valid': valid}" 
            class="form-control list-input"
        >
    </div>
</template>

<style scoped>
    .input-list-container {
        position: relative;
        width: 100%;
    }

    .list-input {

    }
    
    .list-items {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 10px;
    }
    
    .list-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 8px;
        background-color: #e9ecef;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .remove-btn {
        background: none;
        border: none;
        color: #6c757d;
        font-size: 20px;
        line-height: 1;
        cursor: pointer;
        padding: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .remove-btn:hover {
        color: #dc3545;
    }
    
    .hint-text {
        font-size: 12px;
        color: #999;
        margin-top: 4px;
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

</style>

<script>
export default {
    name: 'ui-input-list',
    data() {
        return {
            currentInput: ''
        }
    },
    methods: {
        addItem() {
            const trimmed = this.currentInput.trim()
            if (trimmed) {
                const newItems = [...this.items, trimmed]
                this.$emit('update:modelValue', newItems)
                this.currentInput = ''
            }
           this.$emit('item-added')

        },
        removeItem(index) {
            const newItems = this.items.filter((_, i) => i !== index)
            this.$emit('update:modelValue', newItems)
            this.$emit('item-removed')
        }
    },
    computed: {
        items() {
            if (Array.isArray(this.modelValue)) {
                return this.modelValue
            }
            return []
        }
    },
    props: {
        autofocus: {
            required: false,
            default: false,
            type: Boolean
        },
        invalid: {
            type: Boolean,
            default: false
        },
        valid: {
            type: Boolean,
            default: false
        },
        modelValue: {
            required: false,
            default: () => []
        },
        placeholder: {
            required: false,
            default: 'Type and press Enter to add',
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
