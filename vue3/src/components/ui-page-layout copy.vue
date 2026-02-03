<template>

    <div>

        <ui-page-header>
            <slot name="page-header">
        
            </slot>
        </ui-page-header>
        <slot name="page-body">

            

        </slot>


    </div>

</template>

<script>

export default {
    name: 'ui-page-layout'
}

</script>