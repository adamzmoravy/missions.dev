<template>

    <div v-if="!$config.platform.groups.default" class="container pt-5">
        <span class="large">No default group has been set.</span>
    </div>
    <happening ref="happening" v-else :happening-id="$config.platform.groups.default" />
    
    
    </template>
    
    <script>
    
    export default {
    
        name: 'HomePage',
        watch:{
            $route (to, from){
                if (to.name == 'home') {
                    // do this after 2 seconds
                    setTimeout(() => {
                        this.$refs.happening.setup()
                    }, 1000);
                }
            }
        }
    
    }
    
    </script>