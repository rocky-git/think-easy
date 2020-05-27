<template>
    <div>
        <!--{notempty name="title"}-->
        <el-card class="box-card" :body-style="{padding: '0px 0px' }">
            <div slot="header" class="clearfix" >
                <span>{$title}</span>
            </div>
            <el-row >
                {$html|raw}
            </el-row>
        </el-card>
        <!-- {else/}-->
        <el-row :gutter="10">
            {$html|raw}
        </el-row>
        <!--{/notempty}-->



    </div>
</template>

<script>
    export default {
        name: "detail",
        data(){
            return {
                data:{$data|raw},
                cellComponent:{$cellComponent|raw|default='[]'},
                {$scriptVar|raw}
            }
        },
        created() {
            this.cellComponent.forEach((cmponent,index)=>{
                this.cellComponent[index] = () => new Promise(resolve => {
                    resolve(this.$splitCode(cmponent))
                })
            })
        }
    }
</script>

<style scoped>

</style>
