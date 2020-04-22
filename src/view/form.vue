<template>
        <el-main ref="ruleForm" style="background: #fff;">
            <!--{notempty name="title"}-->
            <div >{$title}</div>
            <el-divider></el-divider>
            <!--{/notempty}-->
            <el-form ref="form" :model="form" {$attrStr|raw}>
                {$formItem|raw}
                <el-form-item >
                    <el-button type="primary" @click="onSubmit('form')">提交保存</el-button>
                    <el-button @click="resetForm('form')">重置</el-button>
                </el-form-item>
            </el-form>

        </el-main>


</template>

<script>
    export default {
        props:{
            dialogVisible:Boolean
        },
        data(){
            return {
                form:{$formData|raw},
                {$formScriptVar|raw}
            }
        },
        methods:{
            onSubmit(formName){
                let url,method
                let urlArr = this.$route.path.split('/')
                url = urlArr[1]+'/'+ urlArr[2]
                if(this.form.id == undefined){
                    url = url +'/save'
                    method = 'post'
                }else{
                    url = url +'/'+this.form.id
                    method = 'put'
                }

                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.$request({
                            url: url,
                            method: method,
                            data:this.form
                        }).then(response=>{
                            this.$notify({
                                title: '操作完成',
                                message: response.message,
                                type: 'success',
                                duration: 2000
                            })
                            this.$emit('update:dialogVisible', false)
                        })
                    } else {
                        return false;
                    }
                });
            },
            resetForm(formName) {
                this.$refs[formName].resetFields();
            }
        }
    }
</script>

<style scoped>

</style>