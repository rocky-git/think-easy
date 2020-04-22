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
        },
        methods:{
            handleDelete(row, index) {
                this.$confirm('此操作将删除该数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    let url  = this.$route.path
                    this.$request({
                        url: url+'/'+row.id,
                        method: 'delete',
                    }).then(res=>{
                        this.tableData.splice(index, 1)
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
        },
    }
</script>
