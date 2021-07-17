<template>
  <page-header-wrapper>
    <a-card
      style="margin-top: 24px"
      :bordered="false">

      <div class="table-page-search-wrapper">
        <a-form layout="inline">
          <a-row :gutter="48">
            <a-col :md="8" :sm="24">
              <a-form-item label="角色名称">
                <a-input v-model="queryParam.name" placeholder=""/>
              </a-form-item>
            </a-col>
            <a-col :md="8" :sm="24">
              <a-form-item label="状态">
                <a-select v-model="queryParam.status" placeholder="请选择">
                  <a-select-option value="0">已禁用</a-select-option>
                  <a-select-option value="1">已启用</a-select-option>
                </a-select>
              </a-form-item>
            </a-col>
            <a-col :md="8" :sm="24">
              <!-- <span class="table-page-search-submitButtons" :style="{ float: 'right', overflow: 'hidden' }"> -->
              <span class="table-page-search-submitButtons" >
                <a-button type="primary" @click="loadData()">查询</a-button>
                <a-button style="margin-left: 8px" @click="() => this.queryParam = {}">重置</a-button>
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
        :data-source="data"
        :pagination="pagination"
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
            <a @click="handleEdit(record)">编辑</a>
            <a-divider type="vertical" />
            <a @click="handleDel(record)">删除</a>
            <a-divider type="vertical" />
            <a @click="handleAssign(record)">配置</a>
          </template>
        </span>
      </a-table>

      <blank-form :visible.sync="visibleRoleForm" :record="tempRecord" @res="handleSubmitRole">
      </blank-form>

      <permission-tree :visible.sync="visiblePermissionTree" :role="tempRole" :treeData="treeData" :checkedKeys="checkedKeys" @submitPermission="handleSubmitPermission">
      </permission-tree>

    </a-card>
  </page-header-wrapper>
</template>

<script>
import BlankForm from './modules/BlankForm'
import PermissionTree from './modules/PermissionTree'
import { getRoleTbl, saveRole, delRole, getMenu, getRoleMenu, saveRoleMenu } from '@/api/manage'
import { listToTree, newTimestamp } from '@/utils/util'

const columns = [
  {
    title: '#',
    scopedSlots: { customRender: 'serial' }
  },
  {
    title: '角色名',
    dataIndex: 'name'
  },
  {
    title: '别名',
    dataIndex: 'alias'
    // scopedSlots: { customRender: 'description' }
  },
  {
    title: '描述',
    dataIndex: 'description',
    scopedSlots: { customRender: 'description' },
    ellipsis: false
  },
  {
    title: '状态',
    dataIndex: 'status',
    scopedSlots: { customRender: 'status' }
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
  name: 'StandardList',
  components: {
    BlankForm,
    PermissionTree
  },
  data () {
    this.columns = columns
    return {
      data: [],
      // 查询参数
      queryParam: {},
      status: 'all',
      pagination: {},
      loading: false,
      visibleRoleForm: false,
      tempRecord: {},
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
    this.loadData()
  },
  methods: {

    loadData () {
      const requestParameters = Object.assign({}, this.queryParam)
      getRoleTbl(requestParameters)
        .then(res => {
          this.data = res.data
        })
    },

    handleAdd () {
      this.tempRecord = Object.assign({}, { status: '1' })
      this.visibleRoleForm = true
    },
    handleEdit (record) {
      this.tempRecord = Object.assign({}, record)
      this.visibleRoleForm = true
    },

    handleSubmitRole (record) {
      saveRole(record)
       .then((res) => {
          // 新建结果同步至table
          if (res && res.id) {
            var now = newTimestamp()
            const temp = Object.assign({ id: res.id, updated_at: now }, record)
            this.data.unshift(temp)
          }
          // 修改结果同步至table
          if (record && record.id) {
            this.data.forEach((element) => {
              if (element.id === record.id) {
                Object.assign(element, record)
                element.updated_at = newTimestamp()
              }
            })
          }
       })
    },

    handleDel (record) {
      this.$confirm({
        title: '确定删除吗?',
        content: '删除 ' + record.name,
        onOk: () => {
          delRole(record.id)
            .then(() => {
              // 结果同步至table
              if (record.id) {
                this.data.forEach(function (element, index, array) {
                  if (element.id === record.id) {
                    array.splice(index, 1)
                  }
                })
              }
            })
        }
      })
    },

    handleAssign (role) {
      this.tempRole = Object.assign({}, role)
      this.checkedKeys = []
      Promise.all([getMenu(), getRoleMenu({ role_id: role.id })])
        .then(function (res) {
          this.treeData.splice(0)
          // this.treeData = res[0].menu.slice(0)
          const menuList = this.formatMenuData(res[0].menu.slice(0))
          listToTree(menuList, this.treeData)
          //
          res[1].menu.forEach(element => {
            this.checkedKeys.push(element.menu_id)
          })
          //
          this.$nextTick(() => {
            this.titlePermissionTree = role.name
            this.visiblePermissionTree = true
          })
        }.bind(this))
    },

    handleSubmitPermission (permission) {
      saveRoleMenu(permission)
       .then(() => {

       })
    },

    formatMenuData (menu) {
      menu.forEach(element => {
        if (element.type && element.type === '0') {
          element.disableCheckbox = true
          element.checkable = false
        }
        if (element.type && element.type === '1') {
          element.slots = { icon: 'file-image' }
        }
        if (element.type && element.type === '2') {
          element.slots = { icon: 'form' }
        }
      })
      return menu
    }
  }
}
</script>
