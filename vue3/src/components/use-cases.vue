<template>

    <div class="row">
        
                    <ui-steps :stepsStyle="'yellow'" @finished="$emit('finished')" class="col-lg-12 pt-0" v-slot:default="slotProps" :stages="stages" @stage-changed="stageChanged">
                        <div class="mt-3 container-use-case" v-if="stage==0">

                            <header-menu-item 
                            id="events-icon"
                            class="menu-item"
                            :icon-files="$ui.iconFiles['events']"
                            :active="true"
                            :link="''" 
                            :name="'item.page'"
                            :icon="'events'" 
                            :label="'Events'"
                            :page-mode="'pageMode'"
                            />

                            <header-menu-item 
                            id="people-icon"
                            class="menu-item"
                            :icon-files="$ui.iconFiles['members']"
                            :active="true"
                            :link="''" 
                            :name="'item.page'"
                            :icon="'events'" 
                            :label="'Community'"
                            :page-mode="'pageMode'"
                            />

                            <header-menu-item 
                            id="portfolio-icon"
                            class="menu-item"
                            :icon-files="$ui.iconFiles['portfolio']"
                            :active="true"
                            :link="''" 
                            :name="'item.page'"
                            :icon="'events'" 
                            :label="'Portfolio'"
                            :page-mode="'pageMode'"
                            />


                            <span id="haus-label" class="mb-2">Bring together a creative space like <img src="/img/haus-logo.png" style="width:110px;position:relative;bottom:3px;"></span>

<!--                            <img id="haus-collage-image" src="https://d34m5sods6fnic.cloudfront.net/image/fetch/c_thumb,w_1500,h_900,e_pixelate:0/https://s3-eu-west-1.amazonaws.com/idea-village-image-host/sandbox/0c95aafd-b379-41dd-a4f3-b9e54f689a18.jpg" />-->
                            <img id="haus-collage-image" src="https://d34m5sods6fnic.cloudfront.net/image/fetch/c_thumb,w_1500,h_900,e_pixelate:0/https://s3-eu-west-1.amazonaws.com/idea-village-image-host/sandbox/3d136d1f-f0f6-40a6-b369-8d0ccccd03b3.jpg" />


                        </div>



                        <div class="mt-3 container-use-case" v-if="stage==1">

                            <span id="haus-label" class="mb-2">Take on societal challenges, like <strong>the city library</strong>.</span>
                            <!--
                            <ui-stage-process-explainer icon-type="hexagon" style="max-width:250px;" id="stage-explainer" />

                            -->
                            
                            <img id="library-collage-image" src="https://d34m5sods6fnic.cloudfront.net/image/fetch/c_thumb,w_1500,h_900,e_pixelate:0/https://s3-eu-west-1.amazonaws.com/idea-village-image-host/sandbox/b75d07f2-c63c-4de9-9b4e-e90f3995cda5.jpg" />

                           <header-menu-item 
                            id="ideation-icon"
                            class="menu-item"
                            :icon-files="$ui.iconFiles['bulp']"
                            :active="true"
                            :link="''" 
                            :name="'item.page'"
                            :icon="'events'" 
                            :label="'Ideation'"
                            :page-mode="'pageMode'"
                            />

                        <header-menu-item 
                            id="participate-icon"
                            class="menu-item"
                            :icon-files="$ui.iconFiles['join']"
                            :active="true"
                            :link="''" 
                            :name="'item.page'"
                            :icon="'events'" 
                            :label="'Participate'"
                            :page-mode="'pageMode'"
                            />

                        </div>


                        <hr/>
                    </ui-steps>

</div>

</template>

<style scoped>

    #events-icon {
        position:absolute;
        top:100px;
        rotate: -15deg;
        left:85px;
        z-index:200;
        zoom:200%;
    }
    #people-icon {
        position:absolute;
        top:50px;
        rotate: 5deg;
        right:190px;
        z-index:200;
        zoom:150%;
    }
    #portfolio-icon {
        position:absolute;
        top:190px;
        rotate: 5deg;
        right:180px;
        z-index:200;
        zoom:160%;
    }
    #ideation-icon {
        position:absolute;
        top:60px;
        rotate: -7deg;
        right:175px;
        z-index:200;
        zoom:170%;
    }


    #participate-icon {
        position:absolute;
        top:195px;
        rotate: 8deg;
        left:100px;
        z-index:200;
        zoom:180%;
    }

    .container-use-case {
        text-align:center;
        margin-top:20px;
        width: 1200px;
        height: 400px;
        display:block;

    }

    #haus-collage-image {
        width: 40%;
        top:20%;
        left:25%;
        position:absolute;
        rotate: -5deg;

    }

    #stage-explainer {
        position:absolute;
        top:30px;
        left:40px;
        width:500px;
        z-index:100;
        height:100px;
        rotate:-10deg;
        zoom:2;
    }

    #library-collage-image {
        width: 40%;
        top:20%;
        left:23.5%;
        position:absolute;
        rotate: 4deg;

    }

    #haus-label {
        z-index:100;
        font-size: 1.5em;
        position:absolute;
        bottom:60px;
        left:170px;
        padding-left:10px;
        padding-right:10px;

    }
</style>


<script>
    import headerMenuItem from './header/header-menu-item.vue';
    export default {
        name: 'use-cases',
        props: {
            stages: {
                type: Array,
                default: () => [
                    { name: 'haus', label: 'Haus' },
                    { name: 'platform_logo', label: 'Platform Logo' }

                ]
            }
        },
        components: {
            headerMenuItem
        },
        methods: {
            stageChanged(stage) {
                this.stage = stage
            }
        },
        data() {
            return {
                stage: 0
            }
        }
    }
</script>