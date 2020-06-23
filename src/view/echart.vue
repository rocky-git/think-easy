<template>
    <el-card shadow="hover">
        <div slot="header">
            <div style="display: flex;justify-content: space-between;align-items:center;">
                <span>折线图</span>
                <el-button-group>

                    <el-button v-if="params.date_type == 'yesterday'"type="primary" @click="requestData('yesterday')">昨天</el-button>
                    <el-button v-else plain @click="requestData('yesterday')">昨天</el-button>

                    <el-button v-if="params.date_type == 'today'"type="primary" @click="requestData('today')">今天</el-button>
                    <el-button v-else plain @click="requestData('today')">今天</el-button>

                    <el-button v-if="params.date_type == 'week'"type="primary" @click="requestData('week')">本周</el-button>
                    <el-button v-else plain @click="requestData('week')">本周</el-button>

                    <el-button v-if="params.date_type == 'month'"type="primary" @click="requestData('month')">本月</el-button>
                    <el-button v-else plain @click="requestData('month')">本月</el-button>

                    <el-button v-if="params.date_type == 'year'"type="primary" @click="requestData('year')">全年</el-button>
                    <el-button v-else plain @click="requestData('year')">全年</el-button>

                    <el-date-picker
                            v-model="rangeDate"
                            type="daterange"
                            value-format="yyyy-MM-dd"
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期"
                    >
                    </el-date-picker>
                </el-button-group>
            </div>
        </div>
        <component v-loading="loading" :is="component"></component>
    </el-card>
</template>

<script>
    export default {
        data(){
            return {
                rangeDate:[],
                component:null,
                loading:false,
                params:{
                    date_type:'today'
                }
            }
        },
        watch:{
            rangeDate(val){
                if(val == null){
                    this.requestData('today')
                }else{
                    this.params.start_date = val[0]
                    this.params.end_date = val[1]
                    this.requestData('range')
                }

            }
        },
        created(){
            this.component = () => new Promise(resolve => {
                resolve(this.$splitCode(decodeURIComponent("{$html|raw}")))
            })
        },
        methods:{
            requestData(type){
                this.loading = true
                this.params.date_type = type
                this.params.ajax = true
                this.$request({
                    url: '{$url|default=""}',
                    params:this.params
                }).then(res=>{
                    this.component = () => new Promise(resolve => {
                        this.loading = false
                        resolve(this.$splitCode(res.data))
                    })
                }).catch(res=>{
                    this.loading = false
                })
            }
        }
    }
</script>

<style scoped>

</style>