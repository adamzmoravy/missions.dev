<template>

    <div v-for="(link, index) in profileLinks">
        <div class="d-flex align-items-center justify-content-between mb-2 link-item">
            <div class="d-flex align-items-center">
                <div class="favicon-container mr-2">
                    <img :src="'https://d34m5sods6fnic.cloudfront.net/image/fetch/c_thumb/' + link.metadata.favicon" v-if="link.metadata.favicon" alt="Link image" @error="$event.target.src='/img/external-link.svg'" />
                    <img v-else src="/img/external-link.svg" alt="External link" />
                </div>
                <div>
                    <div><a :href="link.url" target="_blank" class="stretched-link" rel="noopener">{{ link.title }}</a></div>
                </div>
            </div>
        </div>
    </div>

</template>

<style scoped>

.favicon-container {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 38px;
    border-radius: 4px;
}
.favicon-container img {
    max-height: 50%;
}

.link-item {
    position:relative;
}

</style>

<script>

export default {
    name: 'profile-links',
    props: {
        profile: {
            type: Object,
            required: true
        }
    },
    computed: {
        profileLinks () {
            return this.profile.links
        }
    }
}

</script>