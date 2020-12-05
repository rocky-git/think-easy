<template>
    <div>
        <!--{notempty name="title"}-->
        <div class="container-header">
            <span class="title">{$title}</span>
            <eadmin-breadcrumb style="margin-left: auto"/>
        </div>
        <!--{/notempty}-->
       <div>
           {$html|raw}
       </div>
    </div>
</template>

<script>
    export default {
        name: "eadminContent",
        data(){
            return {
                tableData:[],
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
    .container-header{
        display: flex;
        align-items: center;
        background: #fff;
    }
    .container-header .title{
        font-size: 20px;
        font-weight: 400;
        padding: 10px;
        color: #2c2c2c;
    }
</style>
