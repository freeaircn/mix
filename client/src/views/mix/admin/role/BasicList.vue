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
          </template>
        </span>
        <span slot="action2" slot-scope="text, record">
          <template>
            <a @click="handleAccessAuth(record)">访问</a>
            <a-divider type="vertical" />
            <a @click="handleWorkflowAuth(record)">流程</a>
          </template>
        </span>
      </a-table>

      <blank-form :visible.sync="visibleRoleForm" :record="tempRecord" @res="handleSubmitRole">
      </blank-form>

      <permission-tree
        :visible.sync="visiblePermissionTree"
        :role="tempRole"
        :treeData="treeData"
        :allCheckableId="allCheckableMenuId"
        :checkedKeys="checkedKeys"
        @submitPermission="handleSubmitAccessAuth">
      </permission-tree>

      <WorkflowTree
        :visible.sync="visibleWorkflowTree"
        :role="tempRole2"
        :treeData="treeData2"
        :allCheckableId="allCheckableMenuId2"
        :checkedKeys="checkedKeys2"
        @submit="handleSubmitWorkflowAuth">
      </WorkflowTree>

    </a-card>
  </page-header-wrapper>
</template>

<script>
import BlankForm from './modules/BlankForm'
import PermissionTree from './modules/PermissionTree'
import WorkflowTree from './modules/WorkflowTree'
import { getRoleTbl, saveRole, delRole, getMenu, getRoleMenu, saveRoleMenu, getWorkflowAuthority, getRoleWorkflowAuthority, saveRoleWorkflowAuthority } from '@/api/manage'
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
  },
  {
    title: '定义权限',
    dataIndex: 'action2',
    width: '150px',
    scopedSlots: { customRender: 'action2' }
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
    PermissionTree,
    WorkflowTree
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
      allCheckableMenuId: [],
      treeData: [],
      checkedKeys: [],
      //
      visibleWorkflowTree: false,
      tempRole2: {},
      allCheckableMenuId2: [],
      treeData2: [],
      checkedKeys2: []
    }
  },
  filters: {
    statusFilter (type) {
      return statusMap[type].text
    }
  },
  created () {
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

    handleAccessAuth (role) {
      this.tempRole = Object.assign({}, role)
      this.checkedKeys = []
      Promise.all([getMenu(), getRoleMenu({ role_id: role.id })])
        .then(function (res) {
          this.treeData.splice(0)
          const menuList = this.filterTreeData(res[0].menu.slice(0))
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

    handleSubmitAccessAuth (permission) {
      saveRoleMenu(permission)
       .then(() => {

       })
       .catch((err) => {
          if (err.response) {
          }
        })
    },

    handleWorkflowAuth (record) {
      this.visibleWorkflowTree = false
      //
      this.tempRole2 = Object.assign({}, record)
      this.checkedKeys = []
      Promise.all([getWorkflowAuthority(), getRoleWorkflowAuthority({ role_id: record.id })])
        .then((res) => {
          this.treeData2.splice(0)
          const temp = this.filterTreeData2(res[0].slice(0))
          listToTree(temp, this.treeData2)
          //
          res[1].forEach(element => {
            this.checkedKeys2.push(element.workflow_authority_id)
          })

          this.$nextTick(() => {
            this.visibleWorkflowTree = true
          })
        })
        .catch((err) => {
          this.visibleWorkflowTree = false
          if (err.response) {
          }
        })
    },

    handleSubmitWorkflowAuth (result) {
      saveRoleWorkflowAuthority(result)
        .then(() => {

        })
        .catch((err) => {
          if (err.response) {
          }
        })
    },

    filterTreeData (menu) {
      this.allCheckableMenuId.splice(0)
      menu.forEach(element => {
        if (element.type && element.type === '0') {
          element.disableCheckbox = true
          element.checkable = false
        }
        if (element.type && element.type === '1') {
          element.slots = { icon: 'file-image' }
          this.allCheckableMenuId.push(element['id'])
        }
        if (element.type && element.type === '2') {
          element.slots = { icon: 'form' }
          this.allCheckableMenuId.push(element['id'])
        }
      })
      return menu
    },

    filterTreeData2 (tree) {
      this.allCheckableMenuId2.splice(0)
      tree.forEach(element => {
        if (element.alias && element.alias === 'null') {
          element.disableCheckbox = true
          element.checkable = false
        }
        if (element.alias && element.type !== 'null') {
          this.allCheckableMenuId2.push(element['id'])
        }
      })
      return tree
    }
  }
}
</script>
