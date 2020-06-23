<template>
    <div>
        {$html|raw}
    </div>
</template>

<script>
    export default {
        name: "eadminContent",
        data(){
            return {
                {$scriptVar|raw|default=''}
            }
        }
    }
</script>

<style scoped>

</style>
