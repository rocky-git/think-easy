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
        data(){
            return {
                form:{},
                {$formScriptVar|raw}
            }
        },
        methods:{
            onSubmit(formName){
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        alert('submit!');
                    } else {
                        console.log('error submit!!');
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