<template>
    {$html|raw}
</template>

<script>
    export default {
        data(){
            return {
                {$scriptVar|raw}
            }
        },
        methods:{
            handleCheckChange(data, checked, indeterminate){
                this.$emit('update:tableData', this.checkedData)
            }
        }

    }
</script>

<style scoped>
    {$styleHorizontal|raw|default=''}
</style>