<template>
    <span class="navbar-brand">
        <a v-if="$config.platform.platform_logo"><img :src="$config.platform.platform_logo" style="height:50px;"></a>
        <a v-if="!$config.platform.platform_logo" class="d-flex align-items-center justify-content-center align-content-center">
            <h2 class="community-heading"><ui-text-fitter base-length="9" :text="$config.platform.site_label"><span v-bind:style="css">{{text}}</span></ui-text-fitter></h2>
        </a>

    </span>

</template>

<script>

export default {
    data() {
        return {
        }
    },
    name: 'platform-identifier',
    props: {
        margin: {
            type: Boolean,
            default: true
        }
    }
}

</script>