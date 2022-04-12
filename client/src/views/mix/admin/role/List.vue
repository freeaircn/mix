<template>
  <page-header-wrapper :title="false">
    <a-card :bordered="false">

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
            <a @click="assignMenu(record)">前端页面</a>
            <a-divider type="vertical" />
            <a @click="assignApi(record)">API</a>
            <a-divider type="vertical" />
            <a @click="assignDept(record)">部门</a>
            <a-divider type="vertical" />
            <a @click="handleDeptSetting(record)">部门设置</a>
            <a-divider type="vertical" />
            <a @click="assignWorkflow(record)">工作流程</a>
          </template>
        </span>
      </a-table>

      <blank-form :visible.sync="visibleRoleForm" :record="tempRecord" @res="handleSubmitRole">
      </blank-form>

      <modal-tree
        :visible.sync="visibleAssignMenuDiag"
        title="权限：前端页面"
        :objId="objId"
        :treeData="treeData"
        :allCheckableId="allCheckableId"
        :checkedKeys="checkedId"
        @submit="submitAllowMenu">
      </modal-tree>

      <modal-tree
        :visible.sync="visibleAssignApiDiag"
        title="权限：API"
        :objId="objId"
        :treeData="treeData"
        :allCheckableId="allCheckableId"
        :checkedKeys="checkedId"
        @submit="submitAllowApi">
      </modal-tree>

      <modal-tree
        :visible.sync="visibleAssignDeptDiag"
        title="权限：部门"
        :objId="objId"
        :treeData="treeData"
        :allCheckableId="allCheckableId"
        :checkedKeys="checkedId"
        @submit="submitAllowDept">
      </modal-tree>

      <modal-tree
        :visible.sync="visibleAssignWorkflowDiag"
        title="权限：工作流程"
        :objId="objId"
        :treeData="treeData"
        :allCheckableId="allCheckableId"
        :checkedKeys="checkedId"
        @submit="submitAllowWorkflow">
      </modal-tree>

    </a-card>
  </page-header-wrapper>
</template>

<script>
import BlankForm from './modules/BlankForm'
import ModalTree from './modules/ModalTree'
import { getRoleTbl, saveRole, delRole, getMenu, getRoleMenu, saveRoleMenu, getApi, getRoleApi, saveRoleApi, getDept, getRoleDept, saveRoleDept, getWorkflow, getRoleWorkflow, saveRoleWorkflow } from '@/api/manage'
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
    title: '分配权限',
    dataIndex: 'action2',
    width: '380px',
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
  name: 'List',
  components: {
    BlankForm,
    ModalTree
  },
  data () {
    this.columns = columns
    return {
      data: [],
      queryParam: {},
      status: 'all',
      pagination: {},
      loading: false,
      visibleRoleForm: false,
      tempRecord: {},
      //
      objId: '0',
      allCheckableId: [],
      treeData: [],
      checkedId: [],
      //
      visibleAssignMenuDiag: false,
      visibleAssignApiDiag: false,
      visibleAssignDeptDiag: false,
      //
      visibleAssignWorkflowDiag: false
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

    assignMenu (role) {
      this.objId = role.id
      this.checkedId.splice(0)
      Promise.all([getMenu({ any: 'any' }), getRoleMenu({ role_id: role.id })])
        .then(function (res) {
          const allMenu = res[0].data.slice(0)
          const allowMenu = res[1].data.slice(0)

          this.allCheckableId.splice(0)
          allMenu.forEach(element => {
            if (element['pid'] !== '0') {
              this.allCheckableId.push(element['id'])
            }
          })
          listToTree(allMenu, this.treeData, '1')
          //
          allowMenu.forEach(element => {
            this.checkedId.push(element.menu_id)
          })
          //
          this.$nextTick(() => {
            this.visibleAssignMenuDiag = true
          })
        }.bind(this))
    },

    submitAllowMenu (data) {
      var temp = {
        role_id: data.objId,
        menus: data.selected
      }
      saveRoleMenu(temp)
       .then(() => {

       })
       .catch((err) => {
          if (err.response) {
          }
        })
    },

    assignApi (role) {
      this.objId = role.id
      this.checkedId.splice(0)
      Promise.all([getApi(), getRoleApi({ role_id: role.id })])
        .then(function (res) {
          const allApi = res[0].data.slice(0)
          const allowApi = res[1].data.slice(0)

          this.allCheckableId.splice(0)
          allApi.forEach(element => {
            this.allCheckableId.push(element['id'])
          })
          listToTree(allApi, this.treeData, '1')
          //
          allowApi.forEach(element => {
            this.checkedId.push(element.api_id)
          })
          //
          this.$nextTick(() => {
            this.visibleAssignApiDiag = true
          })
        }.bind(this))
    },

    submitAllowApi (data) {
      var temp = {
        role_id: data.objId,
        api: data.selected
      }
      saveRoleApi(temp)
       .then(() => {

       })
       .catch((err) => {
          if (err.response) {
          }
        })
    },

    assignDept (role) {
      this.objId = role.id
      this.checkedId.splice(0)
      Promise.all([getDept(), getRoleDept({ role_id: role.id })])
        .then(function (res) {
          const allDept = res[0].data.slice(0)
          const allowDept = res[1].data.slice(0)

          var dept = []
          this.allCheckableId.splice(0)
          allDept.forEach(element => {
            if (element['dataMask'] === '1') {
              element['title'] = element['name']
              dept.push(element)
              this.allCheckableId.push(element['id'])
            }
          })
          listToTree(dept, this.treeData, '1')
          //
          allowDept.forEach(element => {
            this.checkedId.push(element.dept_id)
          })
          //
          this.$nextTick(() => {
            this.visibleAssignDeptDiag = true
          })
        }.bind(this))
    },

    submitAllowDept (data) {
      var temp = {
        role_id: data.objId,
        dept: data.selected
      }
      saveRoleDept(temp)
       .then(() => {

       })
       .catch((err) => {
          if (err.response) {
          }
        })
    },

    handleDeptSetting (record) {
      const roleId = record.id
      this.$router.push({ path: `/app/role/dept/${roleId}` })
    },

    saveRoleDeptSetting () {

    },

    assignWorkflow (role) {
      this.objId = role.id
      this.checkedId.splice(0)
      Promise.all([getWorkflow(), getRoleWorkflow({ role_id: role.id })])
        .then(function (res) {
          const allWorkflow = res[0].data.slice(0)
          const allowWorkflow = res[1].data.slice(0)

          this.allCheckableId.splice(0)
          allWorkflow.forEach(element => {
            element['title'] = element['name']
            this.allCheckableId.push(element['id'])
          })
          listToTree(allWorkflow, this.treeData, '1')
          //
          allowWorkflow.forEach(element => {
            this.checkedId.push(element.wf_id)
          })
          //
          this.$nextTick(() => {
            this.visibleAssignWorkflowDiag = true
          })
        }.bind(this))
    },

    submitAllowWorkflow (data) {
      var temp = {
        role_id: data.objId,
        workflow: data.selected
      }
      saveRoleWorkflow(temp)
       .then(() => {

       })
       .catch((err) => {
          if (err.response) {
          }
        })
    }
  }
}
</script>
