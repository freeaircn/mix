<template>
  <div>
    <a-form-model ref="queryForm" layout="inline" :model="query" @submit.native.prevent>
      <a-form-model-item>
        <a-month-picker v-model="query.date" valueFormat="YYYY-MM-DD" />
      </a-form-model-item>

      <a-form-model-item>
        <a-select v-model="query.generator_id" placeholder="机组" allowClear style="width: 75px">
          <a-select-option value="1">1G</a-select-option>
          <a-select-option value="2">2G</a-select-option>
          <a-select-option value="3">3G</a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item>
        <a-button type="primary" @click="onClickQuery">查询</a-button>
      </a-form-model-item>

      <a-form-model-item>
        <a-button @click="onClickExportExcel">导出</a-button>
      </a-form-model-item>
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
      <span slot="generator_id" slot-scope="text">
        {{ text | generatorIdFilter }}
      </span>
      <span slot="event" slot-scope="text">
        {{ text | eventFilter }}
      </span>
      <span slot="cause" slot-scope="text">
        {{ text | causeFilter }}
      </span>
      <span slot="action" slot-scope="text, record">
        <template>
          <a @click="onClickUpdate(record)">修改</a>
          <a-divider type="vertical" />
          <a @click="onClickDel(record)">删除</a>
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
    title: '机组',
    dataIndex: 'generator_id',
    scopedSlots: { customRender: 'generator_id' }
  },
  {
    title: '事件',
    dataIndex: 'event',
    scopedSlots: { customRender: 'event' }
  },
  {
    title: '原因',
    dataIndex: 'cause',
    scopedSlots: { customRender: 'cause' }
  },
  {
    title: '时间',
    dataIndex: 'event_at'
  },
  {
    title: '记录人',
    dataIndex: 'creator'
  },
  {
    title: '说明',
    dataIndex: 'description'
  },
  {
    title: '操作',
    dataIndex: 'action',
    width: '150px',
    scopedSlots: { customRender: 'action' }
  }
]

const generatorIdMap = {
  1: { text: '1G' },
  2: { text: '2G' },
  3: { text: '3G' },
  4: { text: '4G' }
}

const eventMap = {
  1: { text: '停机' },
  2: { text: '开机' }
}

const causeMap = {
  0: { text: ' - ' },
  1: { text: '调度许可' },
  2: { text: '检修试验' },
  3: { text: '电气故障' },
  4: { text: '水系统故障' },
  5: { text: '油系统故障' },
  6: { text: '气系统故障' },
  7: { text: '线路保护动作' },
  8: { text: '母线保护动作' },
  9: { text: '主变保护动作' },
  10: { text: '发电机保护动作' },
  11: { text: '安稳动作' },
  12: { text: '误操作' }
}

export default {
  name: 'RecordList',
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    date: {
      type: String,
      default: ''
    },
    genId: {
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
      default: 5
    },
    total: {
      type: Number,
      default: 0
    }
  },
  data () {
    this.columns = columns
    return {
      query: {
        // moment YYYY-MM-DD
        date: null
      },

      // 事件列表显示区
      pagination: {
        current: 1,
        pageSize: 5,
        total: 0
      }
    }
  },
  filters: {
    generatorIdFilter (type) {
      return generatorIdMap[type].text
    },
    eventFilter (type) {
      return eventMap[type].text
    },
    causeFilter (type) {
      return causeMap[type].text
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
      let gid = 0
      if (this.query.generator_id !== undefined) {
        gid = this.query.generator_id
      }

      // 向父组件发消息，更新数据区
      const query = {
        limit: this.pagination.pageSize,
        offset: this.pagination.current,
        generator_id: gid
      }
      this.$emit('update:current', value.current)
      this.$emit('paginationChange', query)
    },

    // 点击查询
    onClickQuery () {
      let gid = '0'
      if (this.query.date == null) {
        this.query.date = ''
      }
      if (this.query.generator_id !== undefined) {
        gid = this.query.generator_id
      }
      this.$emit('update:date', this.query.date)
      this.$emit('update:genId', gid)
      this.$emit('query', this.query.date, gid)
    },

    // 导出excel
    onClickExportExcel () {
      this.$confirm({
        title: '执行导出吗',
        content: h => <div style="color:rgba(0, 0, 0, 0.65);">导出Excel文件，请点确定按钮</div>,
        onOk: () => {
          if (this.query.date == null) {
            this.query.date = moment().format('YYYY-MM-DD')
          }
          this.$emit('export', this.query.date)
        },
        onCancel () {
        }
      })
    },

    onClickUpdate (record) {
      let gid = '0'
      if (this.query.date == null) {
        this.query.date = ''
      }
      if (this.query.generator_id !== undefined) {
        gid = this.query.generator_id
      }
      this.$emit('update:date', this.query.date)
      this.$emit('update:genId', gid)

      this.$emit('update', record)
    },

    onClickDel (record) {
      const param = {
        id: record.id,
        station_id: record.station_id,
        generator_id: record.generator_id,
        event: record.event
      }

      let title = ''
      if (record.event === '1') {
        title = '删除记录吗? ' + record.event_at + ' ' + record.generator_id + 'G ' + ' 停机'
      }
      if (record.event === '2') {
        title = '删除记录吗? ' + record.event_at + ' ' + record.generator_id + 'G ' + ' 开机'
      }
      this.$confirm({
        title: title,
        // content: '删除 ' + record.username,
        onOk: () => {
          let gid = '0'
          if (this.query.date == null) {
            this.query.date = ''
          }
          if (this.query.generator_id !== undefined) {
            gid = this.query.generator_id
          }
          this.$emit('update:date', this.query.date)
          this.$emit('update:genId', gid)

          this.$emit('delete', param)
        }
      })
    }
  }
}
</script>
