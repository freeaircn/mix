<template>
  <page-header-wrapper>
    <a-card
      style="margin-top: 24px"
      :bordered="false">

      <div class="table-page-search-wrapper">
        <a-form layout="inline">
          <a-row :gutter="48">
            <a-col :md="8" :sm="24">
              <a-form-item label="设备单元">
                <a-input v-model="query.unit" placeholder=""/>
              </a-form-item>
            </a-col>
            <a-col :md="16" :sm="24">
              <span class="table-page-search-submitButtons" >
                <a-button type="primary" @click="onQuery()">查询</a-button>
                <a-button style="margin-left: 8px" @click="() => this.query = {}">重置</a-button>
              </span>
            </a-col>
          </a-row>
        </a-form>
      </div>

      <div class="table-operator">
        <a-button type="primary" icon="plus" @click="handleNew">新建</a-button>
      </div>

      <a-table
        ref="table"
        rowKey="id"
        :columns="columns"
        :data-source="treeData"
        :defaultExpandedRowKeys="['1']"
        :pagination="false"
        :loading="loading"
      >
        <span slot="serial" slot-scope="text, record, index">
          {{ index + 1 }}
        </span>
        <span slot="action" slot-scope="text, record">
          <template>
            <a @click="handleEdit(record)">修改</a>
            <a-divider type="vertical" />
            <a @click="handleDel(record)">删除</a>
          </template>
        </span>
      </a-table>

      <DialogForm :visible.sync="visibleDialog" :record="recordCache" :treeData="treeDataCache" @submit="onSubmit">
      </DialogForm>

    </a-card>
  </page-header-wrapper>
</template>

<script>
import DialogForm from './modules/DialogForm'
import { getEquipmentUnit, saveEquipmentUnit, deleteEquipmentUnit } from '@/api/manage'
import { listToTree } from '@/utils/util'

const columns = [
  {
    title: '#',
    scopedSlots: { customRender: 'serial' }
  },
  {
    title: '设备单元',
    dataIndex: 'name'
  },
  {
    title: '描述',
    dataIndex: 'description'
  },
  {
    title: '更新时间',
    dataIndex: 'updated_at',
    sorter: true
  },
  {
    title: '操作',
    dataIndex: 'action',
    width: '150px',
    scopedSlots: { customRender: 'action' }
  }
]

export default {
  name: 'EquipmentUnitList',
  components: {
    DialogForm
  },
  data () {
    this.columns = columns
    return {
      // Table 树结构数据
      treeData: [],
      loading: false,

      // 设备单元数据
      // dbData: [],
      deptData: [],

      // 查询参数
      query: {},

      //
      visibleDialog: false,
      treeDataCache: [],
      recordCache: {},

      // ---
      visibleRoleForm: false,
      tempRecord: {},
      tempDeptData: []
    }
  },
  created () {
    this.onQuery()
  },
  methods: {

    onQuery () {
      const params = { ...this.query }
      getEquipmentUnit(params)
        .then(data => {
          listToTree(data, this.treeData, '1')
        })
        //  网络异常，清空页面数据显示，防止错误的操作
       .catch((err) => {
         this.treeData.splice(0, this.treeData.length)
         if (err.response) {
         }
       })
    },

    handleNew () {
      const params = { select: 1 }
      getEquipmentUnit(params)
        .then(data => {
          listToTree(data, this.treeDataCache)
          this.recordCache = {}
          this.visibleDialog = true
        })
        //  网络异常，清空页面数据显示，防止错误的操作
       .catch((err) => {
         this.treeDataCache.splice(0, this.treeDataCache.length)
         if (err.response) {
         }
       })
    },

    handleEdit (record) {
      const params = { select: 1 }
      getEquipmentUnit(params)
        .then(data => {
          listToTree(data, this.treeDataCache)
          this.recordCache = { ...record }
          this.visibleDialog = true
        })
        //  网络异常，清空页面数据显示，防止错误的操作
       .catch((err) => {
         this.treeDataCache.splice(0, this.treeDataCache.length)
         if (err.response) {
         }
       })
    },

    handleDel (record) {
      this.$confirm({
        title: '确定删除吗?',
        content: '删除 ' + record.name,
        onOk: () => {
          deleteEquipmentUnit(record.id)
            .then(() => {
              this.onQuery()
            })
            //  网络异常，清空页面数据显示，防止错误的操作
            .catch((err) => {
              this.treeData.splice(0, this.treeData.length)
              if (err.response) {
              }
            })
        }
      })
    },

    onSubmit (record) {
      saveEquipmentUnit(record)
       .then(() => {
         this.onQuery()
       })
      //  网络异常，清空页面数据显示，防止错误的操作
       .catch((err) => {
         if (err.response) {
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
