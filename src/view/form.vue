<template>
        <el-main ref="ruleForm" style="background: #fff;">
            <!--{notempty name="title"}-->
            <div >{$title}</div>
            <el-divider></el-divider>
            <!--{/notempty}-->
            <el-form ref="form" @submit.native.prevent :model="form" {$attrStr|raw}>
                {$formItem|raw}
                <el-form-item style="margin-top: 15px;">
                    <el-button type="primary" native-type="submit" @click="onSubmit('form')">{$submitText|default='保存数据'}</el-button>
                    <!--{if !isset($hideResetButton)}-->
                    <el-button @click="resetForm('form')">重置</el-button>
                    <!--{/if}-->
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
                validates:{$formValidate|raw},
                {$formScriptVar|raw}
            }
        },
        methods:{
            clearValidate(formName) {
                this.$refs[formName].clearValidate();
                this.validates[formName+'ErrorMsg'] = ''
            },
            handleCheckChange(data){
                let field = this.$refs.tree.$attrs.field
                this.form[field] = this.$refs.tree.getCheckedNodes();
            },
            onSubmit(formName){
                let url,method
                let urlArr = this.$route.path.split('/')
                url = urlArr[1]+'/'+ urlArr[2]
                if(this.form.id == undefined){
                    url = url+'.rest'
                    method = 'post'
                }else{
                    url = url +'/'+this.form.id+'.rest'
                    method = 'put'
                }
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.$request({
                            url: url,
                            method: method,
                            data:this.form
                        }).then(response=>{
                            if(response.code == 200){
                                this.$notify({
                                    title: '操作完成',
                                    message: response.message,
                                    type: 'success',
                                    duration: 2000
                                })
                                this.$emit('update:dialogVisible', false)
                            }else if(response.code == 422){
                                for(field in response.data){
                                    this.validates[field+'ErrorShow'] = true
                                    this.validates[field+'ErrorMsg'] = response.data[field]
                                }

                            }
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
    {$styleHorizontal|raw|default=''}
</style>