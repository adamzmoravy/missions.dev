<template>
    <div>
        <vue-horizontal>
            <div v-for="tab in tabs" class="tab-wrapper">
                <a :class="{'tab-link-selected': currentSelection === tab.value}" class="tab-link" @click="$emit('update:section', tab.value)">
                    <span v-if="false">
                    <img v-if="currentSelection === tab.value" src="./img/checkbox-yellow.svg">
                        <img v-if="currentSelection !== tab.value" src="./img/checkbox.svg">
                    </span>
                    {{tab.display}}
                </a>
            </div>
        </vue-horizontal>
    </div>
</template>

<script>
    export default {
        name: 'ui-tabs',
        data() {
            return {
            }
        },
        props: {
            tabs: {
                required: true
            },
            currentSelection: {
                required: true
            }
        },
        mixins: [
        ],
        methods: {
        },
        mounted: function() {
        }
    }
</script>
