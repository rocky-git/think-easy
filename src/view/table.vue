<template>
    <div>
        <!--{notempty name="$filter"}-->
        <div class="filter" style="margin-bottom: 5px">

            <el-form :inline="true" size="small" ref="form" @submit.native.prevent :model="form">
                {$filter|raw|default=''}
                <el-button size="small" class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
                    搜索
                </el-button>
                <el-button size="small"  class="filter-item" icon="el-icon-refresh" @click="filterReset">
                    重置
                </el-button>
            </el-form>
        </div>
        <!--{/notempty}-->
        <!--{if isset($grid)}-->
        <div class="container">
            <!--{notempty name="title"}-->
                <!--{if !isset($trashed) || $trashed===false}-->
                <div>{$title}</div>
            <el-divider></el-divider>
                <!--{/if}-->
            <!--{/notempty}-->

            <el-row style="margin-top: 5px">
                <el-col :span="24">
                <!--{if !isset($hideAddButton)}-->
                <el-button type="primary" size="small" icon="el-icon-plus" @click="showDialog('添加',1)">添加</el-button>
                <!--{/if}-->
                <!--{if isset($exportOpen)}-->
                    <el-dropdown trigger="click" style="margin-left: 10px;">
                        <el-button type="primary" size="small" icon="el-icon-download">
                            导出<i class="el-icon-arrow-down el-icon--right"></i>
                        </el-button>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item @click.native="exportData(1)">导出当前页</el-dropdown-item>
                            <el-dropdown-item @click.native="exportData(2)" v-show="this.selectionData.length > 0">导出选中行</el-dropdown-item>
                            <el-dropdown-item @click.native="exportData(0)">导出全部</el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                <!--{/if}-->
                <!--{if isset($toolbar)}-->
                {$toolbar|raw}
                <!--{/if}-->
                <!--{if !isset($hideDeletesButton)}-->
                <el-button plain size="small" icon="el-icon-delete" v-show="selectButtonShow" @click="DeleteSelect()">删除选中</el-button>
                <el-button plain type="primary" size="small" icon="el-icon-zoom-in" v-show="selectButtonShow && deleteColumnShow" @click="recoverySelect()">恢复选中</el-button>
                <el-button type="danger" size="small" icon="el-icon-delete" @click="deleteAll()">{{deleteButtonText}}</el-button>
                <!--{/if}-->

                </el-col>
             </el-row>
        </div>
        <!--{/if}-->
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
                sortableParams:{},
                sortable:null,
                deleteButtonText:'清空数据',
                deleteColumnShow:false,
                showEditId:0,
                showDetailId:0,
                dialogVisible:false,
                isDialog :false,
                selectButtonShow:false,
                loading:false,
                tableData:[],
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
                    resolve(this.$splitCode(cmponent))
                })
            })
            if(sessionStorage.getItem('deleteColumnShow')){
                this.deleteColumnShow = true
                this.activeTabsName = 'trashed'
                this.requestPageData()
            }
            this.$nextTick(() => {
                this.setSort()
            })
            this.tableData = this.{$tableDataScriptVar}

        },
        inject:['reload'],
        watch:{
            tableData(val){
                this.{$tableDataScriptVar} = val
            },
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
                    this.showDetailId = 0
                    this.requestPageData()
                }
            },
            showEditId(val){
                if(val != 0){
                    this.showDialog('编辑',2)
                }
            },
            showDetailId(val){
                if(val != 0){
                    this.showDialog('详情',3)
                }
            },
        },
        methods: {
            //导出
            exportData(type){
                if(type == 0){
                    location.href = "{$exportUrl|default=''}&build_request_type=export&export_type=all"
                }else if(type == 1){
                    location.href = "{$exportUrl|default=''}&build_request_type=export&export_type=page&page=" + this.page + "&size=" + this.size
                }else if(type == 2){
                    let ids  =[]
                    this.selectionData.forEach((item)=>{
                        ids.push(item.id)
                    })
                    location.href = "{$exportUrl|default=''}&build_request_type=export&export_type=select&ids=" + ids.join(',')
                }
            },
            //合计
            columnSumHandel(param) {
                const {columns, data} = param;
                const sums = [];
                columns.forEach((column, index) => {
                    data.map(item => {
                        if(item[column.property + 'isTotalRow']){
                            if(sums[index] == undefined){
                                sums[index] = 0
                            }
                            sums[index] += Number(item[column.property])
                        }
                    })
                    if(sums[index]){
                        sums[index] += data[0][column.property + 'totalText']
                    }
                 })
                return sums;
            },
            //排序
            sortHandel({ column, prop, order }){
                if(order == null){
                    this.sortableParams = {}
                }else{
                    if(order === 'descending'){
                        order = 'desc'
                    }else if(order === 'ascending'){
                        order = 'asc'
                    }
                    this.sortableParams = {
                        sort_field:prop,
                        sort_by:order
                    }
                }
                this.requestPageData()
            },
            //拖拽排序
            setSort(){
                const el = this.$refs.dragTable.$el.querySelectorAll('.el-table__body-wrapper > table > tbody')[0]
                this.sortable = this.$sortable.create(el, {
                    handle:'.sortHandel',
                    ghostClass: 'sortable-ghost', // Class name for the drop placeholder,
                    onEnd: evt => {
                        var newIndex = evt.newIndex;
                        var oldIndex = evt.oldIndex;
                        var oldItem = this.tableData[oldIndex]
                        var startPage = (this.page-1) * this.size
                        const targetRow = this.tableData.splice(evt.oldIndex, 1)[0]
                        this.tableData.splice(evt.newIndex, 0, targetRow)
                        if(evt.newIndex != evt.oldIndex){
                            this.$request({
                                url: this.$route.path +'/batch.rest',
                                method: 'put',
                                data:{
                                    action:'buldview_drag_sort',
                                    sortable_data:{
                                        id:oldItem.id,
                                        sort: startPage +newIndex
                                    }
                                }
                            }).then(res=>{
                                this.$notify({
                                    title: '操作完成',
                                    message: '排序完成',
                                    type: 'success',
                                    duration: 1500
                                })
                            }).catch(res=>{
                                const targetRow = this.tableData.splice(evt.newIndex, 1)[0]
                                this.tableData.splice(evt.oldIndex, 0, targetRow)
                            })
                        }
                    }
                })
            },

            //重置筛选表单
            filterReset(){
                this.$refs['form'].resetFields();
            },
            clearValidate(formName) {

            },
            //查询过滤
            handleFilter(){
                this.page = 1
                this.requestPageData()
            },
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
            //对话框表单 type=1添加，type=2编辑 ,type=3详情
            showDialog(title,type){
                let url
                if(type == 1){
                    url = this.$route.path+'/create.rest'
                }else if(type == 2){
                    url = this.$route.path+'/'+this.showEditId+'/edit.rest'
                }else if(type == 3){
                    url = this.$route.path+'/'+this.showDetailId+'.rest'
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
                            resolve(this.$splitCode(cmponent))
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
                            this.deleteTreeData(this.tableData,id)
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
                            this.tableData= [];
                        }else{
                            deleteIds.forEach((delId)=>{
                                this.deleteTreeData(this.tableData,delId)
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
                requestParams = Object.assign(requestParams,this.form)
                requestParams = Object.assign(requestParams,this.sortableParams)
                requestParams = Object.assign(requestParams,this.$route.query)
                this.$request({
                    url: url,
                    method: 'get',
                    params:requestParams
                }).then(res=>{
                    this.loading = false
                    this.tableData = res.data.data
                    this.total = res.data.total
                    res.data.cellComponent.forEach((cmponent,index)=>{
                        this.cellComponent[index] = () => new Promise(resolve => {
                            resolve(this.$splitCode(cmponent))
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
    .sortable-selecte{
        background-color: #EBEEF5 !important;

    }
    .sortable-ghost{
        opacity: .8;
        color: #fff!important;
        background: #2d8cf0!important;
    }
    .container {
        background: #fff;
        position: relative;
        padding: 20px 16px;
        border-radius: 4px;
    }
    .filter {
        background: #fff;
        position: relative;
        padding: 20px 16px 0px;
        border-radius: 4px;
    }
</style>
