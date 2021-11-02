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
        <a-button type="primary" @click="handleQuery">查询</a-button>
      </a-form-model-item>

      <a-form-model-item>
        <a-button @click="handleExportExcel">导出</a-button>
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
          <a @click="handleEdit(record)">修改</a>
          <a-divider type="vertical" />
          <a @click="handleDel(record)">删除</a>
        </template>
      </span>
    </a-table>

    <a-modal
      title="修改"
      v-model="editModalVisible"
      :width="500"
      :centered="true"
      @ok="handleEditOk"
    >
      <a-form-model
        ref="editForm"
        :model="curRecord"
        :rules="rules"
        :label-col="labelCol"
        :wrapper-col="wrapperCol"
      >
        <a-form-model-item label="机组" prop="generator_id">
          <a-select v-model="curRecord.generator_id" disabled>
            <a-select-option value="1">1G</a-select-option>
            <a-select-option value="2">2G</a-select-option>
            <a-select-option value="3">3G</a-select-option>
          </a-select>
        </a-form-model-item>

        <a-form-model-item label="事件" prop="event">
          <a-select v-model="curRecord.event" disabled>
            <a-select-option value="1">停机</a-select-option>
            <a-select-option value="2">开机</a-select-option>
          </a-select>
        </a-form-model-item>

        <a-form-model-item label="原因" prop="cause">
          <a-select v-model="curRecord.cause" placeholder="请选择">
            <a-select-option value="1">调度许可</a-select-option>
            <a-select-option value="2">检修试验</a-select-option>
            <a-select-option value="3">电气故障</a-select-option>
            <a-select-option value="4">水系统故障</a-select-option>
            <a-select-option value="5">油系统故障</a-select-option>
            <a-select-option value="6">气系统故障</a-select-option>
            <a-select-option value="7">线路保护动作</a-select-option>
            <a-select-option value="8">母线保护动作</a-select-option>
            <a-select-option value="9">主变保护动作</a-select-option>
            <a-select-option value="10">发电机保护动作</a-select-option>
            <a-select-option value="11">安稳动作</a-select-option>
            <a-select-option value="12">误操作</a-select-option>
          </a-select>
        </a-form-model-item>

        <a-form-model-item label="日期/时间" prop="event_at">
          <a-date-picker v-model="curRecord.event_at" valueFormat="YYYY-MM-DD HH:mm:ss" show-time placeholder="请选择" />
        </a-form-model-item>

        <a-form-model-item label="说明" prop="description">
          <a-textarea v-model="curRecord.description"></a-textarea>
        </a-form-model-item>
      </a-form-model>
    </a-modal>
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
  name: 'GeneratorEventList',
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
      },

      // 修改表单对话框
      labelCol: {
        lg: { span: 7 }, sm: { span: 7 }
      },
      wrapperCol: {
        lg: { span: 10 }, sm: { span: 17 }
      },
      editModalVisible: false,
      curRecord: {},
      rules: {
        event_at: [{ required: true, message: '请选择日期和时间', trigger: ['change', 'blur'] }]
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
    handleQuery () {
      let gid = 0
      if (this.query.date == null) {
        this.query.date = moment().format('YYYY-MM-DD')
      }
      if (this.query.generator_id !== undefined) {
        gid = this.query.generator_id
      }
      this.$emit('update:date', this.query.date)
      this.$emit('query', this.query.date, gid)
    },

    // 导出excel
    handleExportExcel () {
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

    handleEdit (record) {
      this.curRecord = { ...record }
      this.editModalVisible = true
    },

    handleEditOk () {
      this.editModalVisible = false
      const param = { ...this.curRecord }
      this.$emit('edit', param)
    },

    // 删除请求
    handleDel (record) {
      const param = {
        id: record.id,
        station_id: record.station_id,
        generator_id: record.generator_id,
        event: record.event
      }
      this.$confirm({
        title: '确定删除吗?',
        // content: '删除 ' + record.username,
        onOk: () => {
          this.$emit('delete', param)
        }
      })
    }
  }
}
</script>
