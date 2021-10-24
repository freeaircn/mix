<template>
  <page-header-wrapper :title="false">
    <a-card :bordered="false">

      <div class="table-page-search-wrapper">
        <a-form layout="inline">
          <a-row :gutter="48">
            <a-col :md="6" :sm="24">
              <a-form-item label="姓名">
                <a-input v-model="queryParam.username" :allowClear="true" placeholder=""/>
              </a-form-item>
            </a-col>
            <a-col :md="6" :sm="24">
              <a-form-item label="状态">
                <a-select v-model="queryParam.status" :allowClear="true" placeholder="请选择">
                  <a-select-option value="0">已禁用</a-select-option>
                  <a-select-option value="1">已启用</a-select-option>
                </a-select>
              </a-form-item>
            </a-col>
            <a-col :md="6" :sm="24">
              <a-form-item label="部门">
                <a-cascader
                  :options="departmentOptions"
                  v-model="queryParam.department"
                  :allowClear="true"
                  expand-trigger="hover"
                  change-on-select
                  :fieldNames="{ label: 'name', value: 'id', children: 'children' }"
                  placeholder="请选择"
                />
              </a-form-item>
            </a-col>
            <a-col :md="6" :sm="24">
              <!-- <span class="table-page-search-submitButtons" :style="{ float: 'right', overflow: 'hidden' }"> -->
              <span class="table-page-search-submitButtons" >
                <a-button type="primary" @click="handleQuery">查询</a-button>
                <a-button style="margin-left: 8px" @click="() => this.queryParam = {}">重置</a-button>
              </span>
            </a-col>
          </a-row>
        </a-form>
      </div>

      <div class="table-operator">
        <a-button type="primary" icon="plus" @click="handleAdd">新建</a-button>
        <!-- <router-link to="/app/user/new"><a-button type="primary" icon="plus" >新建</a-button></router-link> -->
      </div>

      <a-table
        ref="table"
        rowKey="id"
        :columns="columns"
        :data-source="listData"
        :pagination="pagination"
        :loading="loading"
        @change="handleTableChange"
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

    </a-card>
  </page-header-wrapper>
</template>

<script>
import { getUserTbl, delUser, getDeptTbl } from '@/api/manage'
import { listToTree } from '@/utils/util'

const columns = [
  {
    title: '#',
    scopedSlots: { customRender: 'serial' }
  },
  {
    title: '工号',
    dataIndex: 'workID'
  },
  {
    title: '姓名',
    dataIndex: 'username'
  },
  {
    title: '性别',
    dataIndex: 'sex'
  },
  {
    title: '手机号',
    dataIndex: 'phone'
  },
  {
    title: '电子邮箱',
    dataIndex: 'email'
  },
  {
    title: '政治面貌',
    dataIndex: 'politicLabel'
  },
  {
    title: '部门',
    dataIndex: 'department'
  },
  {
    title: '岗位',
    dataIndex: 'jobLabel'
  },
  {
    title: '职称',
    dataIndex: 'titleLabel'
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
  name: 'UserList',
  data () {
    this.columns = columns
    return {
      listData: [],
      departmentOptions: [],
      // 查询参数
      queryParam: {},
      pagination: { pageSize: 5 },
      pageSize: 5,
      loading: false
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
    const columnName = ['id', 'name', 'pid', 'status']
    getDeptTbl({ columnName })
      .then(res => {
        const data = res.data
        data.forEach((elem, index) => {
          for (var key in elem) {
            if (key === 'status' && elem[key] === '0') {
              elem.disabled = true
            }
          }
        })
        listToTree(data, this.departmentOptions)
      })

    this.loadData()
  },
  methods: {

    loadData () {
      this.loading = true
      const requestParameters = Object.assign({}, this.queryParam, { limit: this.pageSize, offset: this.pagination.current })
      getUserTbl(requestParameters)
        .then(res => {
          const pagination = { ...this.pagination }
          pagination.total = res.total
          this.loading = false
          this.pagination = pagination

          this.listData = res.data
        })
        //  网络异常，清空页面数据显示，防止错误的操作
       .catch((err) => {
         if (err.response) {
           this.listData.splice(0, this.listData.length)
         }
       })
    },

    handleTableChange (pagination) {
      const pager = { ...this.pagination }
      pager.current = pagination.current
      this.pagination = pager

      this.loadData()
    },

    handleQuery () {
      this.pagination.current = 1
      this.loadData()
    },

    handleAdd () {
      const uid = 0
      this.$router.push({ path: `/app/user/save/${uid}` })
    },

    handleEdit (record) {
      this.$router.push({ path: `/app/user/save/${record.id}` })
    },

    // 删除请求
    handleDel (record) {
      this.$confirm({
        title: '确定删除吗?',
        content: '删除 ' + record.username,
        onOk: () => {
          delUser(record.id)
            .then(() => {
              // 结果同步至table
              if (record.id) {
                this.listData.forEach(function (element, index, array) {
                  if (element.id === record.id) {
                    array.splice(index, 1)
                  }
                })
              }
            })
            //  网络异常，清空页面数据显示，防止错误的操作
            .catch((err) => {
              if (err.response) {
                this.listData.splice(0, this.listData.length)
              }
            })
        }
      })
    }
  }
}
</script>
