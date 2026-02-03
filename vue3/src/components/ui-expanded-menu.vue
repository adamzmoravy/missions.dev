<template>

    <div class="expanded-menu-container" :class="direction">

        <slot name="menu-items">


        </slot>

    </div>

</template>

<style scoped>

.right {
    right:0;
}

.left {
    left:0;
}

.expanded-menu-container {
    padding-top:0.5em;
    z-index:99999999;
    min-width:250px;
    max-width:350px;
    top:70px;
    background: white;
    position: absolute;
    max-height: 80vh;
    overflow-y: scroll;
    border-radius: 0.2em;
    overflow-x: hidden;
    filter:drop-shadow(0px 1px 4px rgba(0, 0, 0, 0.07));

}
@media only screen and (max-width: 800px) {
        .expanded-menu-container {
            width: 100%;
            height:100vh;
            max-height: 100vh;
        }
    }   

</style>

<script>

export default {

    name: 'ui-expanded-menu',
    props: {
        direction: {
            type: String,
            default: 'left'
        }
    },
    components: {
        
    },
    methods: {
        closeMenu() {
            this.$emit('close')
        }
    },
    isUserAdmin: function() {
        if (this.isUserAuthenticated && this.$stores.auth.user_type == 'admin') {
            return true
        }
        return false
    },
    watch: {
        $latestEvent: function() {
            if (this.$latestEvent.eventName === 'new-page-navigation') {
                this.$emit('close')
            }
        }
    },
    mounted() {
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.$emit('close')
            }
        })
    }    
}    

</script>