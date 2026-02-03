<template>

        <div class="d-flex flex-wrap align-content-center">
        
            <div @mouseenter="$emit('hover-over-space-preview')"  :style="meshPreviewStyle" class="mesh-preview-window grow-mesh-preview-window d-flex flex-wrap align-content-center">
                
                <experiment-mesh :mesh-start-margin="2300" :mesh-start-zoom="0.2" :show-control-bar="false" :experiments="submissions" :active-board="space.key" />
                <p @click="$router.push('/' + $lang + '/mesh/' + space.key + '/')" @touchend="$router.push('/' + $lang + '/mesh/' + space.key + '/')"  class="medium space-title"><ui-language-field :language="$lang" :fields="space.language" name="title" /></p>

                <div @click="$router.push('/' + $lang + '/mesh/' + space.key + '/')" @touchend="$router.push('/' + $lang + '/mesh/' + space.key + '/')" style="position:absolute;height:100%;width:100%;z-index:1000;">
                </div>

            </div>

        </div>
</template>

<style scoped>

.mesh-preview-window  * {
    cursor:pointer!important;
}

.mesh-preview-window {
    height:150px;
    width:150px;
    position:relative;
    cursor:pointer;
    border-radius:50%;
    overflow: hidden;
    border-style:none;
    border-style:solid;
    border-width:1px;
    border-color: rgba(0, 0, 0, 0);
    position:relative;
    top:0px;

}


.mesh-preview-window {
    z-index:999;
    height:150px;
    width:150px;
    transition: 0.4s;
    filter: drop-shadow(10px 10px 4px rgba(0, 0, 0, 0.14));
}

.grow-mesh-preview-window:hover {
    transition: 1s;
    transition-delay: 0.5s;
    transform:scale(1.1);
    z-index:10000000;
    filter: drop-shadow(30px 30px 4px rgba(0, 0, 0, 0.14));

}


.mesh-preview-window .mesh-container {
    opacity:0.7;
}
.mesh-preview-window:hover .mesh-container {
    opacity:1;
}
.mesh-preview-window:hover .space-title {

}

.space-selected {
    border-color:black;
    border-style:dot-dot-dash;
    border-width:1px;    
}

.mesh-preview-window:hover {
    border-color:black;
    border-style:dot-dot-dash;
    border-width:1px;
}

.spaces-container {
    background-color:red;
    z-index:-100000;
    text-align:center;
}

.spaces-grid {
    display:grid;
    grid-column-gap: 10px;
    grid-template-columns: auto auto auto;
    cursor:pointer;
}

.space-preview {
    position:relative;
    height:250px;
    cursor:pointer;
    filter: drop-shadow(15px 20px 4px rgba(0, 0, 0, 0.14));

}
.space-title {
    text-align:center;
    width:100%;
    padding:3px;
    background-color: rgba(255, 255, 255, 0.77);
    position:relative;
    z-index:9999;
}

</style>

<script>

import ExperimentMesh from './experiment-mesh.vue'

export default {
    name: 'space-preview',
    data () {
        return {
//            currentSize: 150
        }
    },
    components: {
        ExperimentMesh
    },  
    props: {
        space: {
            type: Object,
            required: true
        },
        selected: {
                required: false,
                default: false
            },
        size: {
            required: false,
            default: 150
        }
    },
    computed: {
            submissions: function () {
                return this.$stores.submissions.submissions
            },
            currentSize: function () {
                return this.size
            },
            meshPreviewStyle: function () {
                let style = {}
                style.height = this.currentSize + 'px'
                style.width = this.currentSize + 'px'
                style.marginLeft = (this.currentSize * 0.07) + 'px'
                style.marginRight = (this.currentSize * 0.07) + 'px'
                return style
            }
        }    
}
</script>


