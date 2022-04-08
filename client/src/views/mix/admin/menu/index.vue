<template>
  <page-header-wrapper :title="false">
    <a-card :bordered="false" title="前端菜单" :body-style="{marginBottom: '8px'}">
      <div class="table-page-search-wrapper">
        <a-form layout="inline">
          <a-row :gutter="48">
            <a-col :md="8" :sm="24">
              <a-form-item label="名称">
                <a-input v-model="query.name" placeholder=""/>
              </a-form-item>
            </a-col>
            <a-col :md="16" :sm="24">
              <span class="table-page-search-submitButtons" >
                <a-button type="primary" @click="handleQuery()">查询</a-button>
                <a-button style="margin-left: 8px" @click="() => this.query = {}">重置</a-button>
              </span>
            </a-col>
          </a-row>
        </a-form>
      </div>

      <div class="table-operator">
        <a-button type="primary" icon="plus" @click="handleAdd">新建</a-button>
      </div>

      <a-table
        ref="table"
        rowKey="id"
        :columns="columns"
        :data-source="tableData"
        :defaultExpandedRowKeys="['1']"
        :pagination="false"
        :loading="loading"
      >
        <span slot="serial" slot-scope="text, record, index">
          {{ index + 1 }}
        </span>
        <span slot="status" slot-scope="text">
          {{ text | statusFilter }}
        </span>
        <span slot="action" slot-scope="text, record">
          <template>
            <a @click="handleEdit(record)">修改</a>
            <a-divider type="vertical" />
            <a @click="handleDel(record)">删除</a>
          </template>
        </span>
      </a-table>

      <dept-form :visible.sync="visibleRoleForm" :record="tempRecord" :deptTreeData="tempDeptData" @res="handleSave">
      </dept-form>

    </a-card>
  </page-header-wrapper>
</template>

<script>
import DeptForm from './modules/DeptForm'
import { getDeptTbl, saveDept, delDept } from '@/api/manage'
import { listToTree, newTimestamp } from '@/utils/util'

const columns = [
  {
    title: '#',
    scopedSlots: { customRender: 'serial' }
  },
  {
    title: '标题',
    dataIndex: 'title'
  },
  {
    title: '组件',
    dataIndex: 'component'
  },
  {
    title: '路由',
    dataIndex: 'path'
  },
  {
    title: '重定向',
    dataIndex: 'redirect'
  },
  {
    title: '更新时间',
    dataIndex: 'updated_at'
  },
  {
    title: '操作',
    dataIndex: 'action',
    width: '150px',
    scopedSlots: { customRender: 'action' }
  }
]

const statusMap = {
  0: {
    status: 'disable',
    text: '已禁用'
  },
  1: {
    status: 'active',
    text: '已启用'
  }
}

export default {
  name: 'DeptList',
  components: {
    DeptForm
  },
  data () {
    this.columns = columns
    return {
      // 部门数据
      deptData: [],
      // 部门的树结构数据
      tableData: [],
      // 查询参数
      query: {},
      loading: false,
      visibleRoleForm: false,
      tempRecord: {},
      tempDeptData: [],
      //
      visiblePermissionTree: false,
      tempRole: {},
      treeData: [],
      checkedKeys: []
    }
  },
  filters: {
    statusFilter (type) {
      return statusMap[type].text
    }
  },
  created () {
    // const { pageNo } = this.$route.params
    // const localPageNum = this.pageURI && (pageNo && parseInt(pageNo)) || this.pageNum
    // this.localPagination = ['auto', true].includes(this.showPagination) && Object.assign({}, this.localPagination, {
    //   current: localPageNum,
    //   pageSize: this.pageSize,
    //   showSizeChanger: this.showSizeChanger
    // }) || false
    // this.needTotalList = this.initTotalList(this.columns)
    this.handleQuery()
  },
  watch: {
    deptData: {
      handler: function (val) {
        listToTree(val, this.tableData)
      },
      immediate: true
    }
  },
  methods: {

    handleQuery () {
      const requestParameters = Object.assign({}, this.query)
      getDeptTbl(requestParameters)
        .then(res => {
          this.deptData = res.data
        })
        //  网络异常，清空页面数据显示，防止错误的操作
       .catch((err) => {
         if (err.response) {
           this.deptData.splice(0, this.deptData.length)
         }
       })
    },

    handleAdd () {
      this.tempDeptData = this.tableData.slice(0)
      //
      this.tempRecord = Object.assign({}, { status: '1' })
      this.visibleRoleForm = true
    },
    handleEdit (record) {
      this.tempDeptData = this.tableData.slice(0)
      //
      this.tempRecord = Object.assign({}, record)
      this.visibleRoleForm = true
    },

    handleSave (record) {
      console.log('handleSave', record)
      saveDept(record)
       .then((res) => {
          // 新建结果同步至table
          if (res && res.id) {
            var now = newTimestamp()
            const temp = Object.assign({ id: res.id, updated_at: now }, record)
            this.deptData.unshift(temp)
          }
          // 修改结果同步至table
          if (record && record.id) {
            const temp = this.deptData.slice(0)
            temp.forEach((element) => {
              if (element.id === record.id) {
                Object.assign(element, record)
                element.updated_at = newTimestamp()
              }
            })
            this.deptData = temp.slice(0)
          }
       })
      //  网络异常，清空页面数据显示，防止错误的操作
       .catch((err) => {
         if (err.response) {
           this.deptData.splice(0, this.deptData.length)
         }
       })
    },

    handleDel (record) {
      this.$confirm({
        title: '确定删除吗?',
        content: '删除 ' + record.name,
        onOk: () => {
          delDept(record.id)
            .then(() => {
              // 结果同步至table
              if (record.id) {
                this.deptData.forEach(function (element, index, array) {
                  if (element.id === record.id) {
                    array.splice(index, 1)
                  }
                })
              }
            })
            //  网络异常，清空页面数据显示，防止错误的操作
            .catch((err) => {
              if (err.response) {
                this.deptData.splice(0, this.deptData.length)
              }
            })
        }
      })
    }
  }
}
</script>

<style lang="less" scoped>
.ant-avatar-lg {
    width: 48px;
    height: 48px;
    line-height: 48px;
}

.list-content-item {
    color: rgba(0, 0, 0, .45);
    display: inline-block;
    vertical-align: middle;
    font-size: 14px;
    margin-left: 40px;
    span {
        line-height: 20px;
    }
    p {
        margin-top: 4px;
        margin-bottom: 0;
        line-height: 22px;
    }
}
</style>
