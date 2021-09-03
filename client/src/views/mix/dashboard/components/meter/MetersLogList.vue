<template>
  <div>
    <a-form-model ref="queryForm" layout="inline" :model="query" @submit.native.prevent>
      <a-form-model-item>
        <a-month-picker v-model="query.date" valueFormat="YYYY-MM-DD" />
      </a-form-model-item>

      <a-form-model-item>
        <a-button type="primary" @click="handleQuery">查询</a-button>
      </a-form-model-item>

      <!-- <a-form-model-item>
        <a-button @click="handleExportHisEvent">导出</a-button>
      </a-form-model-item> -->
    </a-form-model>

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
      <span slot="action" slot-scope="text, record">
        <template>
          <a @click="onClickReport(record)">简报</a>
          <a-divider type="vertical" />
          <a @click="handleDel(record)">删除</a>
        </template>
      </span>
    </a-table>

  </div>
</template>

<script>
import moment from 'moment'

const columns = [
  {
    title: '#',
    scopedSlots: { customRender: 'serial' }
  },
  {
    title: '日期',
    dataIndex: 'log_date'
  },
  {
    title: '时间',
    dataIndex: 'log_time'
  },
  {
    title: '记录人',
    dataIndex: 'creator'
  },
  {
    title: '操作',
    dataIndex: 'action',
    scopedSlots: { customRender: 'action' }
  }
]

export default {
  name: 'MetersLogList',
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    date: {
      type: String,
      default: ''
    },
    listData: {
      type: Array,
      default: () => []
    },
    current: {
      type: Number,
      default: 1
    },
    pageSize: {
      type: Number,
      default: 6
    },
    total: {
      type: Number,
      default: 0
    }
  },
  data () {
    this.columns = columns
    return {
      // 事件列表显示区
      pagination: {
        current: 1,
        pageSize: 6,
        total: 0
      },

      // 查询表单
      query: {
        // moment YYYY-MM-DD
        date: null
      }
    }
  },
  watch: {
    date: {
      handler: function (val) {
        this.query.date = val
      },
      immediate: true
    },
    current: {
      handler: function (val) {
        this.pagination.current = val
      },
      immediate: true
    },
    pageSize: {
      handler: function (val) {
        this.pagination.pageSize = val
      },
      immediate: true
    },
    total: {
      handler: function (val) {
        this.pagination.total = val
      },
      immediate: true
    }
  },
  methods: {
    // 点击分页
    handleTableChange (value) {
      this.pagination.current = value.current

      // 向父组件发消息，更新数据区
      const queryParam = {
        limit: this.pagination.pageSize,
        offset: this.pagination.current
      }
      this.$emit('update:current', value.current)
      this.$emit('paginationChange', queryParam)
    },

    // 点击查询
    handleQuery () {
      if (this.query.date == null) {
        this.query.date = moment().format('YYYY-MM-DD')
      }
      this.$emit('query', this.query.date)
    },

    // 查看简报，消息码report
    onClickReport (record) {
      this.$emit('report', record)
    },

    // 删除请求
    handleDel (record) {
      const param = { ...record }
      this.$confirm({
        title: '确定删除吗?',
        onOk: () => {
          this.$emit('delete', param)
        }
      })
    }
  }
}
</script>
