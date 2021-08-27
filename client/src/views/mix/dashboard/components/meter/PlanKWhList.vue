<template>
  <div>
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
// import moment from 'moment'

const columns = [
  {
    title: '#',
    scopedSlots: { customRender: 'serial' }
  },
  {
    title: '月份',
    dataIndex: 'month'
  },
  {
    title: '发电量(kWh)',
    dataIndex: 'value'
  }
]

export default {
  name: 'PlanKWhList',
  props: {
    loading: {
      type: Boolean,
      default: false
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
      default: 12
    }
  },
  data () {
    this.columns = columns
    return {
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
  watch: {
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

    handleTableChange (value) {
      this.pagination.current = value.current

      // 向父组件发消息，更新数据区
      const queryParam = {
        limit: this.pagination.pageSize,
        offset: this.pagination.current
      }
      this.$emit('update:current', value.current)
      this.$emit('reqData', queryParam)
    },

    handleEdit (record) {
      this.curRecord = { ...record }
      this.editModalVisible = true
    },

    handleEditOk () {
      this.editModalVisible = false
      const param = { ...this.curRecord }
      this.$emit('reqEdit', param)
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
          this.$emit('reqDelete', param)
        }
      })
    }
  }
}
</script>
