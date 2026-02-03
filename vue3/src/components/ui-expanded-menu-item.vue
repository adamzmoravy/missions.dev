<template>

    <div class="expanded-menu menu-item-container" @click="$emit('selected')">

        <div class="menu-item-icon">
            <slot name="icon">

            </slot>

        </div>

        <div class="menu-item-label">
            <slot name="label">

            </slot>

        </div>

    </div>

</template>

<style>

</style>

<style scoped>
.menu-item-label {
    display:flex;
    align-items: center;

}
.menu-item-icon {
    width:40px;
    margin-right:10px;
    display:flex;
    align-items: center;
    justify-content:center;

}

.menu-item-container {
    display:flex;
    width:100%;
    padding-top:0.5em;
    padding-bottom:0.5em;
    padding-left:1em;
    padding-right:1em;
    cursor:pointer;
}

.menu-item-container:hover {
    background-color:var(--light);
}

@media (max-width:800px) {

    .menu-item-icon img {
        max-height:2em;    
    }
    .menu-item-label a {
        font-size:120%;
    }
   .menu-item-container {
        padding-top:1em;
        padding-bottom:1em;
        max-width:300px;
        margin-left:auto;
        margin-right:auto;
   }
   .menu-item-container-fullwidth {
        max-width:100%;
        width:100%;
   }
}

</style>

<script>

export default {

    name: 'ui-expanded-menu-item'
}

</script>