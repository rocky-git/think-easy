<template><span>{$cell|raw}</span></template>
<script>
    export default {
        props:{
            data:{
                type: Object,
                required: true
            },
            index:{
                type: Number,
                required: true
            },
            tableData: Array,
            showEditId:Number,
            page:Number,
        },
        data(){
          return {
            form:{
                switch:'{$switchValue|default="0"}',
            },
          }
        },
        methods:{
            //排序置顶
            sortTop(index){
                if(this.page ==1){
                    const oldValue = this.tableData[0]
                    const newValue = this.tableData[index]
                    this.$set(this.tableData,index,oldValue)
                    this.$set(this.tableData,0,newValue)
                }else{
                    this.tableData.splice(index,1)
                }
                this.$emit('update:tableData', this.tableData)
            },
            //排序置底
            sortBottom(index){
                if(this.page ==1){
                    const oldValue = this.tableData[0]
                    const newValue = this.tableData[index]
                    this.tableData[newIndex] = oldValue
                    this.tableData[oldIndex] = newValue
                }
            },
            handleEdit(row,index){
                this.$emit('update:showEditId', row.id)
            },
            handleDelete(row, index) {
                this.$confirm('此操作将删除该数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    let url  = this.$route.path
                    this.$request({
                        url: url+'/'+row.id+'.rest',
                        method: 'delete',
                    }).then(res=>{
                        this.deleteTreeData(this.tableData,row.id)
                        this.$emit('update:tableData', this.tableData)
                        this.$notify({
                            title: '操作完成',
                            message: res.message,
                            type: 'success',
                            duration: 2000
                        })

                    })
                })
            },
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
            }
        },
    }
</script>
