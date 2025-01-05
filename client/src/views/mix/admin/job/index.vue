<template>
  <page-header-wrapper :title="false">
    <a-card :bordered="false">

      <div class="table-page-search-wrapper">
        <a-form layout="inline">
          <a-row :gutter="48">
            <a-col :md="8" :sm="24">
              <a-form-item label="岗位名称">
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
        <a-form layout="inline">
          <a-row :gutter="48">
            <a-col :md="5" :sm="24">
              <a-form-model-item label="日期" prop="date_at">
                <a-date-picker v-model="tempUUID.date_at" valueFormat="YYYY-MM-DD" style="width: 100%;" />
              </a-form-model-item>
            </a-col>
            <a-col :md="5" :sm="24">
              <a-form-model-item label="时间" prop="time_at" >
                <a-time-picker v-model="tempUUID.time_at" format="HH:mm:ss" valueFormat="HH:mm:ss" style="width: 100%;" />
              </a-form-model-item>
            </a-col>
            <a-col :md="5" :sm="24">
              <a-form-item label="序号">
                <a-input v-model="tempUUID.index" placeholder=""/>
              </a-form-item>
            </a-col>

            <a-col :md="5" :sm="24">
              <a-form-item label="UUID">
                <a-input v-model="tempUUID.uuid" placeholder=""/>
              </a-form-item>
            </a-col>

            <a-col :md="4" :sm="24">
              <span class="table-page-search-submitButtons" >
                <a-button type="primary" @click="createTempUUID()">生成UUID</a-button>
                <a-button style="margin-left: 8px" type="primary" @click="parseTempUUID()">解析UUID</a-button>
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
        :data-source="listData"
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
      </a-table>

      <job-form :visible.sync="visibleForm" :record="tempRecord" @res="handleSave">
      </job-form>

    </a-card>
  </page-header-wrapper>
</template>

<script>
import JobForm from './modules/JobForm'
import { getJobTbl, saveJob, delJob } from '@/api/manage'
import { newTimestamp } from '@/utils/util'

const columns = [
  {
    title: '#',
    scopedSlots: { customRender: 'serial' }
  },
  {
    title: '岗位名称',
    dataIndex: 'name'
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
  name: 'JobList',
  components: {
    JobForm
  },
  data () {
    this.columns = columns
    return {
      listData: [],
      // 查询参数
      queryParam: {},
      pagination: {},
      loading: false,
      visibleForm: false,
      tempRecord: {},
      tempUUID: {
        date_at: '',
        time_at: '',
        index: '',
        uuid: ''
      }
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
      getJobTbl(requestParameters)
        .then(res => {
          this.listData = res.data
        })
        //  网络异常，清空页面数据显示，防止错误的操作
       .catch((err) => {
         if (err.response) {
           this.listData.splice(0, this.listData.length)
         }
       })
    },

    handleAdd () {
      this.tempRecord = Object.assign({}, { status: '1' })
      this.visibleForm = true
    },
    handleEdit (record) {
      this.tempRecord = Object.assign({}, record)
      this.visibleForm = true
    },

    handleSave (record) {
      saveJob(record)
       .then((res) => {
          // 新建结果同步至table
          if (res && res.id) {
            var now = newTimestamp()
            const temp = Object.assign({ id: res.id, updated_at: now }, record)
            this.listData.unshift(temp)
          }
          // 修改结果同步至table
          if (record && record.id) {
            this.listData.forEach((element) => {
              if (element.id === record.id) {
                Object.assign(element, record)
                element.updated_at = newTimestamp()
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
    },

    handleDel (record) {
      this.$confirm({
        title: '确定删除吗?',
        content: '删除 ' + record.name,
        onOk: () => {
          delJob(record.id)
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
    },

    createTempUUID () {
      const requestParameters = Object.assign({ type: 'create' }, this.tempUUID)
      getJobTbl(requestParameters)
        .then(res => {
          console.log(res.uuid)
          this.tempUUID.uuid = res.uuid
        })
        //  网络异常，清空页面数据显示，防止错误的操作
       .catch((err) => {
         if (err.response) {
           this.listData.splice(0, this.listData.length)
         }
       })
    },

    parseTempUUID () {
      const requestParameters = Object.assign({ type: 'parse' }, this.tempUUID)
      getJobTbl(requestParameters)
        .then(res => {
          console.log(res)
          this.tempUUID.date_at = res.date_at
          this.tempUUID.time_at = res.time_at
          this.tempUUID.index = res.index
        })
        //  网络异常，清空页面数据显示，防止错误的操作
       .catch((err) => {
         if (err.response) {
           this.listData.splice(0, this.listData.length)
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
