<template>
    <div class="icon" :class="{'active': active, 'highlighter': highlighterStyle}">
        <div class="px-md-2 px-1 position-relative text-align-center">
            <div class="d-flex justify-content-center">
                <span class="icon-container">
                    <img class="icon-png" :src="iconFile" :style="style">
                </span>
            </div>

            <span class="small header-item-label d-flex justify-content-center">
                <a v-if="pageMode=='multiPage'" :href="'/' + $lang + '/' + link" class="stretched-link text-align-center">
                    {{$trans(label)}}
                </a>
                <a v-else @click="$emit('selected', name)" class="stretched-link text-align-center">
                    {{$trans(label)}}
                </a>
            </span>
        </div>
    </div>


</template>

<style scoped>
    .transparent {
        opacity:0;
    }
    .icon-container {

    }
    .icon-container.active {
        position:absolute;
        left:auto;
/*        left:auto;*/
    }
    .icon-png {
        height:26px;
        margin-bottom:5px;
    }
    .header-item-label {
        line-height:1;
    }

    .material-symbols-outlined {
        font-size:30px!important;
        color:var(--very-dark)
    }

    .active.highlighter {
        background-image: url('/img/highlighter-yellow.svg');
        background-size: 50px;
        background-repeat: no-repeat;
        background-position: bottom;
    }

</style>

<script>

export default {
    name: 'header-menu-item',
    data() {
        return {
//            iconImg: iconImg
        }
    },
    computed: {
        iconFile() {
            if (this.active) {
                return this.iconFiles.active
            } else {
                return this.iconFiles.normal
            }
        },
        highlighterStyle: function () {
            return this.$ui.mainMenuHighlighter
        },
        style: function() {
            if (this.iconFiles.scale) {
                {
                    return {
                        transform: 'scale(' + this.iconFiles.scale + ')'
                    }                                
                }
            }
        }
    },
    props: {
        iconFiles: {
        },
        pageMode: {
            required: false,
            default: 'multiPage'
        },
        name: {
            required: false,
            default: 'name'
        },
        active: {
            required: false,
            default: false
        },
        label: {
            required: false,
            default: 'Label'
        },
        icon: {
            required: false,
            default: 'talk'
        },
        link: {
            required: false,
            default: null
        }
    } 
}
</script>