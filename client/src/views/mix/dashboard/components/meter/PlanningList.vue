<template>
  <div>
    <a-form-model ref="queryForm" layout="inline" :model="query" @submit.native.prevent>
      <a-form-model-item>
        <a-date-picker
          v-model="query.date"
          mode="year"
          format="YYYY年"
          @panelChange="handleDatePickerPanelChange"
          placeholder="选择年份"
        />
      </a-form-model-item>

      <a-form-model-item>
        <a-button type="primary" @click="handleQuery">查询</a-button>
      </a-form-model-item>

      <!-- <a-form-model-item>
        <a-button @click="handleExportHisEvent">导出</a-button>
      </a-form-model-item> -->
    </a-form-model>

    <div style="margin: 12px 0px">
      <a-row :gutter="16" >
        <a-col :span="12">
          <a-statistic :title="year + '年计划'" :value="sumPlanning">
            <template #suffix>
              <span> / 万kWh</span>
            </template>
          </a-statistic>
        </a-col>
        <a-col :span="12">
          <a-statistic title="累计成交" :value="sumDeal">
            <template #suffix>
              <span> / 万kWh</span>
            </template>
          </a-statistic>
        </a-col>
      </a-row>
    </div>

    <a-table
      ref="table"
      rowKey="id"
      :columns="columns"
      :data-source="listData"
      :pagination="pagination"
      :loading="loading"
      :customRow="onRowClick"
      @change="handleTableChange"
    >
      <!-- <span slot="serial" slot-scope="text, record, index">
        {{ index + 1 }}
      </span> -->
      <template slot="footer">
        注：双击某一行，可修改
      </template>
    </a-table>

    <a-modal
      :title="year + '年' + curRecord.month"
      v-model="editModalVisible"
      :width="450"
      :centered="true"
      okText="修改"
      @ok="handleEditOk"
    >
      <a-form-model
        ref="editForm"
        :model="curRecord"
        :label-col="labelCol"
        :wrapper-col="wrapperCol"
      >
        <a-form-model-item label="计划 /万kWh">
          <a-input-number v-model="curRecord.planning" :min="0" :style="{width: '100%'}"/>
        </a-form-model-item>

        <a-form-model-item label="成交 /万kWh">
          <a-input-number v-model="curRecord.deal" :min="0" :style="{width: '100%'}"/>
        </a-form-model-item>

        <a-form-model-item label="记录">
          <a-input v-model="curRecord.creator" disabled/>
        </a-form-model-item>
      </a-form-model>

    </a-modal>

  </div>
</template>

<script>
import moment from 'moment'

export default {
  name: 'PlanningKWhList',
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
    sumPlanning: {
      type: String,
      default: '0'
    },
    sumDeal: {
      type: String,
      default: '0'
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
      default: 12
    }
  },
  data () {
    return {
      // 列表显示区
      columns: [
        // {
        //   title: '#',
        //   scopedSlots: { customRender: 'serial' }
        // },
        {
          title: '年',
          dataIndex: 'month'
        },
        {
          title: '计划',
          dataIndex: 'planning'
        },
        {
          title: '成交',
          dataIndex: 'deal'
        }
      ],
      pagination: {
        current: 1,
        pageSize: 6,
        total: 12
      },

      // 查询表单
      query: {
        // moment Obj
        date: null
      },
      year: '',

      // 修改表单对话框
      labelCol: {
        lg: { span: 6 }, sm: { span: 6 }
      },
      wrapperCol: {
        lg: { span: 12 }, sm: { span: 12 }
      },
      editModalVisible: false,
      curRecord: {}
    }
  },
  mounted () {
  },
  watch: {
    date: {
      handler: function (val) {
        const temp = moment(val, 'YYYY-MM-DD')
        this.columns[0].title = temp.format('YYYY') + '年'
        this.year = temp.format('YYYY')
        this.query.date = temp
      },
      immediate: true
    },
    current: {
      handler: function (val) {
        this.pagination.current = val
      },
      immediate: true
    }
  },
  methods: {

    // 点击分页
    handleTableChange (value) {
      this.pagination.current = value.current
      // this.$emit('update:current', value.current)
    },

    // 查询日期，moment Obj
    handleDatePickerPanelChange (value) {
      this.query.date = value
    },

    // 点击查询
    handleQuery () {
      if (this.query.date == null) {
        // moment Obj
        this.query.date = moment()
      }
      this.$emit('query', this.query.date.format('YYYY-MM-DD'))
      this.pagination.current = 1
    },

    // 双击行，弹出修改对话框
    onRowClick (record) {
      return {
        on: {
          dblclick: () => {
            this.curRecord = { ...record }
            this.editModalVisible = true
          }
        }
      }
    },

    // 修改对话框，提交修改
    handleEditOk () {
      if (this.curRecord.planning === null || this.curRecord.deal === null) {
        this.$message.warning('请输入数字，例如：0，12，12.3，0.123')
      } else {
        this.editModalVisible = false
        const data = { ...this.curRecord }
        data.month = data.month.replace('月', '')
        data.year = this.year
        this.$emit('update', data)
      }
    }
  }
}
</script>
