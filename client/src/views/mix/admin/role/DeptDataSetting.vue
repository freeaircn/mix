<template>
  <page-header-wrapper :title="false">
    <a-card :bordered="false">
      <div class="table-operator">
        <router-link to="/app/role/list"><a-button type="primary" icon="double-left" >返回</a-button></router-link>
      </div>

      <a-table
        ref="table"
        rowKey="dept_id"
        :columns="columns"
        :data-source="data"
        :pagination="false"
        :loading="loading"
      >
        <span slot="serial" slot-scope="text, record, index">
          {{ index + 1 }}
        </span>
        <span slot="data_writable" slot-scope="text">
          {{ text | textFilter }}
        </span>
        <span slot="is_default" slot-scope="text">
          {{ text | textFilter }}
        </span>
        <span slot="operation" slot-scope="text, record">
          <template>
            <a @click="handleEdit(record)">编辑</a>
          </template>
        </span>
      </a-table>

      <blank-form2 :visible.sync="visibleEditForm" :record="tempRecord" @res="handleSubmit">
      </blank-form2>

    </a-card>
  </page-header-wrapper>
</template>

<script>
import BlankForm2 from './modules/BlankForm2'
// import { getRoleDept } from '@/api/manage'
import { getRoleDept, saveRoleDept } from '@/api/manage'

const columns = [
  {
    title: '#',
    scopedSlots: { customRender: 'serial' }
  },
  {
    title: '部门',
    dataIndex: 'name'
  },
  {
    title: '数据写权限',
    dataIndex: 'data_writable',
    scopedSlots: { customRender: 'data_writable' }
  },
  {
    title: '默认部门（多部门）',
    dataIndex: 'is_default',
    scopedSlots: { customRender: 'is_default' }
  },
  {
    title: '操作',
    dataIndex: 'operation',
    width: '200px',
    scopedSlots: { customRender: 'operation' }
  }
]

const textMap = {
  0: {
    text: '否'
  },
  1: {
    text: '是'
  }
}

export default {
  name: 'DeptDataSetting',
  components: {
    BlankForm2
  },
  data () {
    this.columns = columns
    return {
      role_id: '',
      data: [],
      pagination: {},
      loading: false,
      visibleEditForm: false,
      tempRecord: {},
      objId: '0',
      visibleSetDeptDiag: false
    }
  },
  filters: {
    textFilter (val) {
      return textMap[val].text
    }
  },
  created () {
    this.role_id = (this.$route.params.roleId) ? this.$route.params.roleId : '0'
  },
  mounted () {
    this.loadData()
  },
  methods: {

    loadData () {
      const param = {
        role_id: this.role_id,
        method: 'set'
      }
      getRoleDept(param)
        .then(function (res) {
          this.data = res.data.slice(0)
        }.bind(this))
    },

    handleEdit (record) {
      this.tempRecord = Object.assign({}, record)
      this.visibleEditForm = true
    },

    handleSubmit (record) {
      var param = Object.assign({ method: 'set' }, record)
      saveRoleDept(param)
       .then(() => {
          // 修改结果同步至table
          if (record && record.dept_id) {
            this.data.forEach((element) => {
              if (element.dept_id === record.dept_id) {
                Object.assign(element, record)
              }
            })
          }
       })
    }
  }
}
</script>
