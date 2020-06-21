<template>
        <el-main ref="ruleForm" style="background: #fff;border-radius: 4px;">
            <!--{notempty name="title"}-->
            <div >{$title}</div>
            <el-divider></el-divider>
            <!--{/notempty}-->
            <el-form ref="form" @submit.native.prevent :model="form" {$attrStr|raw}>
                {$formItem|raw}
                <el-form-item style="margin-top: 15px;">
                    <el-button type="primary" :disabled="disabledSubmit" native-type="submit" @click="onSubmit('form')">{$submitText|default='保存数据'}</el-button>
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
            let _self = this
            return {
                disabledSubmit:false,
                auto:'',
                manyIndex:0,
                form:{$formData|raw},
                validates:{$formValidate|raw},
                formItemTags:{$formItemTags|raw},
                {$formScriptVar|raw}
            }
        },
        created(){
            this.init()
        },
        methods:{
            //单选框切换事件
            radioChange(val,tag,manyIndex){
                {$radioJs|raw|default=''}
            },
            init(){
                {$script|raw}
            },
            //数组寻找并删除
            deleteArr(arr,value){
                for(var i = arr.length ; i > 0 ; i--){
                    if(arr[i-1]== value){
                        arr.splice(i-1,1);
                    }
                }
            },
            // 一对多上移
            handleUp (relation,index) {
                const len = this.form[relation][index - 1]
                this.$set(this.form[relation], index - 1, this.form[relation][index])
                this.$set(this.form[relation], index, len)
            },
            // 一对多下移
            handleDown (relation,index) {
                const len = this.form[relation][index + 1]
                this.$set(this.form[relation], index + 1, this.form[relation][index])
                this.$set(this.form[relation], index, len)
            },
            //一对多添加元素
            addManyData(relation,manyData){
                this.form[relation].push(JSON.parse(decodeURIComponent(manyData)))
                this.init()
            },
            //一对多移除元素
            removeManyData(relation,index){
                this.form[relation].splice(index, 1)
                this.init()
            },
            //移除错误
            clearValidate(formName) {
                this.$refs[formName].clearValidate();
                this.validates[formName+'ErrorMsg'] = ''
            },
            //一对多移除错误
            clearValidateArr(formName,index){
                this.$refs[formName][index].clearValidate();
                this.validates[formName+'ErrorMsg'] = ''
            },
            handleCheckChange(data){
                let field = this.$refs.tree.$attrs.field
                this.form[field] = this.$refs.tree.getCheckedNodes();
            },
            //提交
            onSubmit(formName){
                let url,method
                let urlArr = this.$route.path.split('/')
                //url = urlArr[1]+'/'+ urlArr[2]
                url = '{$submitUrl}'
                if(this.form.id == undefined){
                    url = url+'.rest'
                    method = 'post'
                }else{
                    url = url +'/'+this.form.id+'.rest'
                    method = 'put'
                }
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.disabledSubmit = true
                        this.$request({
                            url: url,
                            method: method,
                            data:this.form
                        }).then(response=>{
                            this.disabledSubmit = false
                            if(response.code == 200){
                                this.$notify({
                                    title: '操作完成',
                                    message: response.message,
                                    type: 'success',
                                    duration: 1500
                                })
                                this.$emit('update:dialogVisible', false)
                            }else if(response.code == 422){
                                for(field in response.data){
                                    val = response.data[field]
                                    field = field.replace('.','_')
                                    this.validates[field+'ErrorShow'] = true
                                    this.validates[field+'ErrorMsg'] = val
                                }
                            }
                        }).catch(res=>{
                            this.disabledSubmit = false
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
