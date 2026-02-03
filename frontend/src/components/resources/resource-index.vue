<template>

    <div>

            <div class="col-md-4 mb-4" v-for="resource in resources">
                <div class="preview-wrapper">
                    <div class="preview-container position-relative">
                        <div class="top-head  px-3">
                            <h2 class="medium-2">
                                <ui-a class="stretched-link" :href="'/resource/' + resource.resource_id  + '/'">{{resource.name}}</ui-a>
                            </h2>
                        </div>
                        <div class="body">
                            <ui-img-dynamic :height-ratio="0.5625" class="experiment-image" v-if="resource.image" :src="resource.image" />
                            <p class="pt-3">
                                <div class="d-flex align-items-center medium-2">
                                    <span class="ml-3 mr-1 material-symbols-outlined md-20">sell</span> {{resource.price}} / hour
                                </div>
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
</template>

<style scoped>


.preview-container:hover {
transform:translateY(-2px);
filter: drop-shadow(0 0 1.2rem rgb(214, 213, 213));
}


.top-head {
padding-right:1rem;
border-bottom-style:none;
border-width:1px;
border-color:lightgrey;
padding-left:0.2em;
height:5rem;
display:flex;
align-items:center;
}
.top-head h2 {
font-weight:200!important;
margin-top:0px;
margin-bottom:0px;
}
.top-head a {
font-weight:300;
text-decoration:none;
}


.body {
flex:auto;
min-height:12rem;
}
.body p {
padding:1rem;
padding-top:0rem;
padding-bottom:2rem;
}

.preview-wrapper {
display: flex;
flex-direction: column;
}
.preview-container {
flex:auto;
box-sizing:border-box;
transition-property: all;
transition-duration:.2s;
border-color:lightgrey;
border-style:solid;
border-width:1px;
border-radius:6px;
background-color:white;
display: flex;
flex-direction: column;
cursor:pointer;

}

</style>

<script>

export default {
    name: 'resource-index',
    data: function() {
        return {
            resources: []
        }
    },
    props: {
        resourceType: {
            type: String,
            required: false,
            default: 'all'
        }
    },
    methods: {
        setup: function() {
            this.fetchResources()
        },
        fetchResources() {
            this.backend.get('/resources/type/' + this.resourceType + '/').then((response) => {
                this.resources = response.data.resources
            })
        }
    },
    mounted () {
        this.setup()
    }
}

</script>