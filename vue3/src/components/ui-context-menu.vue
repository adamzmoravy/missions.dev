<template>
    <OnClickOutside @trigger="isOpen = false" class="ui-context-menu">
        <div>
            <div @click.stop="toggleMenu()" class="menu-button">
                <svg width="20" height="6" viewBox="0 0 24 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="5" cy="2" r="2" fill="currentColor"/>
                    <circle cx="12" cy="2" r="2" fill="currentColor"/>
                    <circle cx="19" cy="2" r="2" fill="currentColor"/>
                </svg>
            </div>
            <div v-if="isOpen && !showModal" class="menu-options">
                <div 
                    v-for="(option, index) in options" 
                    :key="index" 
                    @click="selectOption(option)"
                    class="menu-option"
                >
                    {{ option.label }}
                </div>
            </div>
        </div>

            <ui-modal @close="isOpen = false" :open="isOpen && showModal" class="menu-options-modal">
                <template #header>
                    <p class="large">Options</p>
                </template>
                <template #body>
                    <div class="w-100 pt-2">
                        <div 
                            v-for="(option, index) in options" 
                            :key="index" 
                            @click="selectOption(option)"
                            class="underline menu-option medium-2"
                        >
                            {{ option.label }}
                        </div>

                    </div>
                </template>
            </ui-modal>


    </OnClickOutside>

</template>



<script>
import { OnClickOutside } from '@vueuse/components'
import { useBreakpoints } from '@vueuse/core'

export default {
    name: 'UiContextMenu',
    props: {
        options: {
            type: Array,
            required: true,
            default: () => []
        }
    },
    data() {
        return {
            isOpen: false
        };
    },
    components: {
        OnClickOutside
    },
    computed: {
        showModal() {
            return this.$breakpoints.active().value === 'mobile'
        }
    },
    methods: {
        toggleMenu() {
            this.isOpen = !this.isOpen
        },
        selectOption(option) {
            this.$emit(option.action)
            this.isOpen = false
        }
    },
    mounted() {
    }
};
</script>

<style scoped>

.menu-options-modal {
    z-index:10000;
}

.ui-context-menu {
    z-index:100;
}

.menu-button {
    padding-left:3px;
    padding-right:3px;
    padding-top:4px;
    padding-bottom:4px;
    line-height: 0.4;
    background: none;
    border: none;
    cursor: pointer;
}

.menu-options {
    min-width:80px;
    font-size:14px;
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    border: 1px solid #ccc;
    border-radius: 2px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
}

.menu-option {
    padding: 10px;
    padding-top:5px;
    padding-bottom:5px;
    cursor: pointer;
}
.menu-option {
    padding: 10px;
    padding-top:5px;
    padding-bottom:5px;
    cursor: pointer;
}


.menu-option:hover {
    background: #f0f0f0;
}
</style>