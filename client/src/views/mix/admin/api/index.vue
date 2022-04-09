<template>
  <page-header-wrapper :title="false">
    <a-card :bordered="false" title="API管理" :body-style="{marginBottom: '8px'}">
      <div class="table-page-search-wrapper">
        <a-form layout="inline">
          <a-row :gutter="48">
            <a-col :md="8" :sm="24">
              <a-form-item label="标题">
                <a-input v-model="query.title" placeholder=""/>
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

      <my-form :visible.sync="visibleForm" :record="recordTemp" :treeData="treeDataTemp" @res="handleSave">
      </my-form>

    </a-card>
  </page-header-wrapper>
</template>

<script>
import MyForm from './modules/Form'
import { getApi, saveApi, delApi } from '@/api/manage'
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
    title: '类型',
    dataIndex: 'type'
  },
  {
    title: 'API',
    dataIndex: 'api'
  },
  {
    title: '方法',
    dataIndex: 'method'
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

export default {
  name: 'ApiList',
  components: {
    MyForm
  },
  data () {
    this.columns = columns
    return {
      // 数据
      respData: [],
      // 树结构数据
      tableData: [],
      // 查询参数
      query: {},
      loading: false,
      visibleForm: false,
      recordTemp: {},
      treeDataTemp: []
      //
    }
  },
  created () {
    this.handleQuery()
  },
  watch: {
    respData: {
      handler: function (val) {
        listToTree(val, this.tableData)
      },
      immediate: true
    }
  },
  methods: {

    handleQuery () {
      const param = Object.assign({}, this.query)
      getApi(param)
        .then(res => {
          this.respData = res.data
        })
        //  网络异常，清空页面数据显示，防止错误的操作
       .catch((err) => {
         if (err.response) {
           this.respData.splice(0, this.respData.length)
         }
       })
    },

    handleAdd () {
      this.treeDataTemp = this.tableData.slice(0)
      //
      this.recordTemp = {}
      this.visibleForm = true
    },
    handleEdit (record) {
      this.treeDataTemp = this.tableData.slice(0)
      //
      this.recordTemp = Object.assign({}, record)
      this.visibleForm = true
    },

    handleSave (record) {
      console.log('handleSave', record)
      saveApi(record)
       .then((res) => {
          // 新建结果同步至table
          if (res && res.id) {
            var now = newTimestamp()
            const temp = Object.assign({ id: res.id, updated_at: now }, record)
            this.respData.unshift(temp)
          }
          // 修改结果同步至table
          if (record && record.id) {
            const temp = this.respData.slice(0)
            temp.forEach((element) => {
              if (element.id === record.id) {
                Object.assign(element, record)
                element.updated_at = newTimestamp()
              }
            })
            this.respData = temp.slice(0)
          }
       })
      //  网络异常，清空页面数据显示，防止错误的操作
       .catch((err) => {
         if (err.response) {
           this.respData.splice(0, this.respData.length)
         }
       })
    },

    handleDel (record) {
      this.$confirm({
        title: '确定删除吗?',
        content: '删除 ' + record.title,
        onOk: () => {
          delApi(record.id)
            .then(() => {
              // 结果同步至table
              if (record.id) {
                this.respData.forEach(function (element, index, array) {
                  if (element.id === record.id) {
                    array.splice(index, 1)
                  }
                })
              }
            })
            //  网络异常，清空页面数据显示，防止错误的操作
            .catch((err) => {
              if (err.response) {
                this.respData.splice(0, this.respData.length)
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
