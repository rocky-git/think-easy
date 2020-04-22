<template>
    <div>
        <div class="container">
            <!--{notempty name="title"}-->
            <div>{$title}</div>
            <el-divider></el-divider>
            <!--{/notempty}-->

            <el-button type="primary" size="small" icon="el-icon-plus" @click="showAdd('添加')">添加</el-button>
            <!--{if !isset($hideDeletesButton)}-->
            <el-button size="small" icon="el-icon-delete" v-if="selectButtonShow" @click="DeleteSelect()">删除选中</el-button>
            <el-button type="danger" size="small" icon="el-icon-delete" @click="deleteAll()">清空数据</el-button>
            <!--{/if}-->
        </div>
        {$tableHtml|raw}
        <el-pagination class="container"
                       v-if="!pageHide"
                       @size-change="handleSizeChange"
                       @current-change="handleCurrentChange"
                       :page-sizes="pagesize"
                       :page-size="size"
                       :current-page="page"
                       background
                       :total="total"
                       layout="total, sizes, prev, pager, next, jumper">
        </el-pagination>
        {$dialog|raw|default=''}
    </div>
</template>
<script>

    export default {
        data(){
            return {
                dialogVisible:false,
                isDialog :false,
                selectButtonShow:false,
                loading:false,
                plugDialog:null,
                cellComponent:{$cellComponent|raw|default='[]'},
                pageHide:{$pageHide|default='true'},
                page:1,
                pagesize:[],
                total:{$pageTotal|default=0},
                size:{$pageSize|default=20},
                selectionData:[],
                {$tableScriptVar|raw}
            }
        },
        created(){
            /*{if isset($dialogVar)}*/

            this.isDialog = true
            /*{/if}*/

            let i = 10
            if(this.size < 10){
                i=this.size
            }
            for(i;i<=200;i+=10){
                this.pagesize.push(i)
            }
            if(this.$route.query.page != undefined){
                this.page = this.$route.query.page
            }
            if(this.$route.query.size != undefined){
                this.size = this.$route.query.size
            }
            this.cellComponent.forEach((cmponent,index)=>{
                this.cellComponent[index] = () => new Promise(resolve => {
                    resolve(this.splitCode(cmponent))
                })
            })
        },
        methods: {
            splitCode  (codeStr)  {
                const script = this.getSource(codeStr, 'script').replace(/export default/, 'return ')
                const css = this.getSource(codeStr, 'style')
                const template = this.getSource(codeStr, 'template')
                if (css) {
                    const style = document.createElement('style')
                    style.type = 'text/css'
                    // style.id = this.id;
                    style.innerHTML = css
                    document.getElementsByTagName('head')[0].appendChild(style)
                }
                return {
                    ...new Function(script)(), template
                }
            },
            getSource (source, type){
                const regex = new RegExp(`<${type}[^>]*>`)
                let openingTag = source.match(regex)

                if (!openingTag) {
                    return ''
                } else {
                    openingTag = openingTag[0]
                }

                return source.slice(source.indexOf(openingTag) + openingTag.length, source.lastIndexOf(`</${type}>`))
            },
            //
            showAdd(title){
                let url = this.$route.path+'/create'
                if(this.isDialog){

                    this.$request({
                        url: url,
                        method: 'get',
                        params: {
                            build_dialog:true
                        }
                    }).then(response=>{

                        this.{$dialogTitleVar|default='isDialog'} = title

                        let cmponent = response.data
                        this.plugDialog = () => new Promise(resolve => {
                            resolve(this.splitCode(cmponent))
                        })
                        this.dialogVisible = true

                    })

                }else{

                    this.$router.push(url)
                }

            },
            //删除选中
            DeleteSelect(){
                let ids  =[]
                this.selectionData.forEach((item)=>{
                    ids.push(item.id)
                })
                this.deleteRequest('此操作将删除选中数据, 是否继续?',ids)
            },
            //清空全部
            deleteAll(){
                this.deleteRequest('此操作将删除清空所有数据, 是否继续?','true')
            },
            deleteRequest(title,deleteIds){
                this.$confirm(title, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    let url  = this.$route.path
                    this.$request({
                        url: url+'/delete',
                        method: 'delete',
                        data:{
                            ids:deleteIds
                        }
                    }).then(res=>{
                        if(deleteIds == 'true'){
                            this.{$tableDataScriptVar} = [];
                        }else{
                            let idsArr = deleteIds;
                            let data = [];
                            this.{$tableDataScriptVar}.forEach((item)=>{
                                if(idsArr.indexOf(item.id) == -1){
                                    data.push(item)
                                }
                            })
                            this.{$tableDataScriptVar} = data
                        }
                        this.$notify({
                            title: '操作完成',
                            message: res.message,
                            type: 'success',
                            duration: 2000
                        })

                    })
                })
            },
            //当用户手动勾选数据行的 Checkbox 时触发的事件
            handleSelect(selection){
                this.selectionData = selection
                if(selection.length > 0){
                    this.selectButtonShow=true
                }else{
                    this.selectButtonShow=false
                }
            },
            //分页大小改变
            handleSizeChange(val) {
                this.size = val
                this.requestPageData()

            },
            //分页改变
            handleCurrentChange(val) {
                this.page = val
                this.requestPageData()
            },
            requestPageData(){
                this.loading = true
                let url  = this.$route.path
                this.$request({
                    url: url,
                    method: 'get',
                    params:{
                        build_request_type:'page',
                        page:this.page,
                        size:this.size,
                    }
                }).then(res=>{
                    this.loading = false
                    this.{$tableDataScriptVar} = res.data.data
                    this.total = res.data.total
                    res.data.cellComponent.forEach((cmponent,index)=>{
                        this.cellComponent[index] = () => new Promise(resolve => {
                            resolve(this.splitCode(cmponent))
                        })
                    })

                }).catch(res=>{
                    this.loading = false
                })

            }
        }
    }
</script>

<style scoped>

    .container {
        background: #fff;
        padding: 20px 16px;
    }
</style>