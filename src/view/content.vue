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
        },
        methods:{
            linkComponent(url,name){
                this.$request({
                    url: url,
                }).then(res=>{
                    this[name] = () => new Promise(resolve => {
                        resolve(this.$splitCode(res.data))
                    })
                })
            }
        }
    }
</script>

<style scoped>

</style>
