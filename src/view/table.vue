<template>
    <div>
        <div class="container">
            <!--{notempty name="title"}-->
            <!--{if !isset($trashed) || $trashed===false}-->
            <div>{$title}</div>
            <el-divider></el-divider>
            <!--{/if}-->
            <!--{/notempty}-->
            <el-button type="primary" size="small" icon="el-icon-plus" @click="showDialog('添加',1)">添加</el-button>
            <!--{if !isset($hideDeletesButton)}-->
            <el-button plain size="small" icon="el-icon-delete" v-show="selectButtonShow" @click="DeleteSelect()">删除选中</el-button>
            <el-button plain type="primary" size="small" icon="el-icon-zoom-in" v-show="selectButtonShow && deleteColumnShow" @click="recoverySelect()">恢复选中</el-button>
            <el-button type="danger" size="small" icon="el-icon-delete" @click="deleteAll()">{{deleteButtonText}}</el-button>
            <!--{/if}-->
        </div>
        <!--{if isset($trashed) && $trashed===true}-->
        <el-tabs v-model="activeTabsName" class="container" @tab-click="handleTabsClick">
            <el-tab-pane label="{$title|default='数据列表'}" name="data">
                {$tableHtml|raw}
            </el-tab-pane>
            <el-tab-pane label="回收站" name="trashed">
                {$tableHtml|raw}
            </el-tab-pane>
        </el-tabs>
        <!--{else/}-->
        {$tableHtml|raw}
        <!--{/if}-->

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
                deleteButtonText:'清空数据',
                deleteColumnShow:false,
                showEditId:0,
                dialogVisible:false,
                isDialog :false,
                selectButtonShow:false,
                loading:false,
                plugDialog:null,
                activeTabsName:'data',
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
            if(sessionStorage.getItem('deleteColumnShow')){
                this.deleteColumnShow = true
                this.activeTabsName = 'trashed'
                this.requestPageData()
            }
        },
        inject:['reload'],
        watch:{
            deleteColumnShow(val){
                if(val){
                    this.deleteButtonText = '清空回收站'
                }else{
                    this.deleteButtonText = '清空数据'
                }
            },
            dialogVisible(val){
                if(!val){
                    this.showEditId=0
                    this.requestPageData()
                }
            },
            showEditId(val){
                if(val != 0){
                    this.showDialog('编辑',2)
                }
            },
        },
        methods: {
            handleTabsClick(tab, event){
                this.page = 1
                if(this.activeTabsName == 'data'){
                    sessionStorage.removeItem('deleteColumnShow')
                    this.deleteColumnShow = false
                }else{
                    sessionStorage.setItem('deleteColumnShow',1)
                    this.deleteColumnShow = true

                }
                this.requestPageData()
            },
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
            //对话框表单 type=1添加，type=2编辑
            showDialog(title,type){
                let url
                if(type == 1){
                    url = this.$route.path+'/create.rest'
                }else if(type == 2){
                    url = this.$route.path+'/'+this.showEditId+'/edit.rest'
                }
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
            //恢复选中
            recoverySelect(){
                let ids  =[]
                this.selectionData.forEach((item)=>{
                    ids.push(item.id)
                })
                this.recoveryRequest('此操作将恢复选中数据, 是否继续?',ids)
            },
            //清空全部
            deleteAll(){
                this.deleteRequest('此操作将删除清空所有数据, 是否继续?','true')
            },
            //递归寻找删除ID索引并删除
            deleteTreeData(arr,id){
                for(var i = arr.length ; i > 0 ; i--){
                    if(arr[i-1].id == id){
                        arr.splice(i-1,1);
                    }else{
                        if(arr[i-1].children){
                            this.deleteTreeData(arr[i-1].children,id)
                        }
                    }
                }
            },
            //恢复数据请求
            recoveryRequest(title,ids){
                this.$confirm(title, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'info'
                }).then(() => {
                    let url  = this.$route.path
                    this.$request({
                        url: url +'/batch.rest',
                        method: 'put',
                        data:{
                            ids:ids,
                            delete_time:null,
                        }
                    }).then(res=>{
                        ids.forEach((id)=>{
                            this.deleteTreeData(this.{$tableDataScriptVar},id)
                        })
                        this.$notify({
                            title: '操作完成',
                            message: '数据恢复成功',
                            type: 'success',
                            duration: 1500
                        })

                    })
                })
            },
            //删除请求
            deleteRequest(title,deleteIds){
                this.$confirm(title, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    let url  = this.$route.path
                    this.$request({
                        url: url+'/delete.rest',
                        method: 'delete',
                        data:{
                            ids:deleteIds,
                            trueDelete:this.deleteColumnShow
                        }
                    }).then(res=>{
                        if(deleteIds == 'true'){
                            this.{$tableDataScriptVar} = [];
                        }else{
                            deleteIds.forEach((delId)=>{
                                this.deleteTreeData(this.{$tableDataScriptVar},delId)
                            })
                        }
                        this.$notify({
                            title: '操作完成',
                            message: res.message,
                            type: 'success',
                            duration: 1500
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
                let requestParams = {
                    build_request_type:'page',
                    page:this.page,
                    size:this.size,
                }
                if(this.deleteColumnShow){
                    requestParams = Object.assign(requestParams,{'is_deleted':true})
                }
                this.$request({
                    url: url,
                    method: 'get',
                    params:requestParams
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