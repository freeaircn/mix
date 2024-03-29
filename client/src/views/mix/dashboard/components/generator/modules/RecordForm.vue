<template>
  <div>
    <a-form-model ref="form" :model="record" :rules="rules" :label-col="labelCol" :wrapper-col="wrapperCol">
      <a-form-model-item label="站点">
        <a-select v-model="record.station_id">
          <a-select-option v-for="d in stationItems" :key="d.id" :value="d.id">
            {{ d.name }}
          </a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item label="机组" prop="generator_id">
        <a-select v-model="record.generator_id" disabled>
          <a-select-option value="1">1G</a-select-option>
          <a-select-option value="2">2G</a-select-option>
          <a-select-option value="3">3G</a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item label="事件" prop="event">
        <a-select v-model="record.event" placeholder="请选择" :disabled="isUpdate" @select="onSelectEvent">
          <a-select-option value="1">停机</a-select-option>
          <a-select-option value="2">开机</a-select-option>
          <a-select-option value="3">检修开始</a-select-option>
          <a-select-option value="4">检修结束</a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item label="分类" prop="cause">
        <a-select v-model="record.cause" placeholder="请选择">
          <a-select-option v-for="d in eventCauseList" :key="d.value" :value="d.value">{{ d.label }}</a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item label="日期" prop="date_at">
        <a-date-picker v-model="record.date_at" valueFormat="YYYY-MM-DD" style="width: 100%;" />
      </a-form-model-item>

      <a-form-model-item label="时间" prop="time_at" >
        <a-time-picker v-model="record.time_at" format="HH:mm:ss" valueFormat="HH:mm:ss" style="width: 100%;" />
      </a-form-model-item>

      <a-form-model-item label="补充说明" prop="description">
        <a-textarea v-model="record.description"></a-textarea>
      </a-form-model-item>

      <a-form-model-item :wrapper-col="{ lg: { span: 14, offset: 4 }, sm: { span: 14 } }">
        <a-button type="primary" block @click="onClickSubmit" :disabled="disableBtn">提交</a-button>
      </a-form-model-item>
    </a-form-model>
  </div>
</template>

<script>
import moment from 'moment'
import { apiSaveEvent } from '@/api/mix/generator'

export default {
  name: 'RecordForm',
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    stationId: {
      type: String,
      default: ''
    },
    stationItems: {
      type: Array,
      default: () => []
    },
    genId: {
      type: String,
      default: ''
    },
    creator: {
      type: String,
      default: ''
    },
    update: {
      type: Boolean,
      default: false
    },
    recordInfo: {
      type: Object,
      default: () => {}
    }
  },
  data () {
    return {
      record: {
        station_id: '',
        generator_id: '',
        event: '',
        cause: '',
        date_at: '',
        time_at: '',
        description: '无'
      },
      isUpdate: false,
      labelCol: {
        lg: { span: 4 }, sm: { span: 4 }
      },
      wrapperCol: {
        lg: { span: 14 }, sm: { span: 14 }
      },
      rules: {
        generator_id: [{ required: true, message: '请选择机组', trigger: 'change' }],
        event: [{ required: true, message: '请选择事件名称', trigger: 'change' }],
        cause: [{ required: true, message: '请选择事件分类', trigger: 'change' }],
        date_at: [{ required: true, message: '请选择日期', trigger: ['change'] }],
        time_at: [{ required: true, message: '请选择时间', trigger: ['change'] }]
      },
      disableBtn: false,
      //
      eventCauseList: null,
      eventCauseOptions: {
        c1: [{ label: '调度许可', value: '1' }, { label: '设备故障', value: '21' }, { label: '保护动作', value: '22' }, { label: '稳控动作', value: '23' }, { label: '试验', value: '11' }],
        c2: [{ label: '调度许可', value: '1' }, { label: '空转', value: '12' }, { label: '试验', value: '11' }],
        c3: [{ label: 'A级检修', value: '31' }, { label: 'B级检修', value: '32' }, { label: 'C级检修', value: '33' }, { label: 'D级检修', value: '34' }, { label: '其他', value: '35' }]
      }
    }
  },
  mounted () {
    this.record.station_id = this.stationId
    this.record.generator_id = this.genId
    // this.record.cause = '1'
    this.record.date_at = moment().format('YYYY-MM-DD')
  },
  watch: {
    update: {
      handler: function (val) {
        if (this.update === true) {
          this.isUpdate = true
          setTimeout(() => {
            this.onSelectEvent(this.recordInfo.event)
            this.record.station_id = this.recordInfo.station_id
            this.record.generator_id = this.recordInfo.generator_id
            this.record.event = this.recordInfo.event
            this.record.cause = this.recordInfo.cause
            this.record.date_at = this.recordInfo.event_at.substr(0, 10)
            this.record.time_at = this.recordInfo.event_at.substr(11, 8)
            this.record.description = this.recordInfo.description
          }, 100)
        }
      },
      immediate: true
    }
  },
  methods: {
    onClickSubmit () {
      if (this.isUpdate) {
        this.handleUpdateRecord()
      } else {
        this.handleNewRecord()
      }
    },

    handleNewRecord () {
      this.$refs.form.validate(valid => {
        if (valid) {
          const data = {
            station_id: this.record.station_id,
            generator_id: this.record.generator_id,
            event: this.record.event,
            cause: this.record.cause,
            event_at: this.record.date_at + ' ' + this.record.time_at,
            creator: this.creator,
            description: this.record.description
          }
          if (data.description === '') {
            data.description = '无'
          }

          let title = ''
          if (data.event === '1') {
            title = '录入 ' + data.event_at + '  ' + data.generator_id + 'G 停机'
          }
          if (data.event === '2') {
            title = '录入 ' + data.event_at + '  ' + data.generator_id + 'G 开机'
          }
          if (data.event === '3') {
            title = '录入 ' + data.event_at + '  ' + data.generator_id + 'G 检修开始'
          }
          if (data.event === '4') {
            title = '录入 ' + data.event_at + '  ' + data.generator_id + 'G 检修结束'
          }

          this.$confirm({
            title: title,
            // content: h => <div style="color:rgba(0, 0, 0, 0.65);">{{ }}</div>,
            onOk: () => {
              this.disableBtn = true
              apiSaveEvent(data)
                .then(() => {
                  this.$emit('submitSuccess', 'post')
                })
                //  网络异常，清空页面数据显示，防止错误的操作
                .catch((err) => {
                  setTimeout(() => { this.disableBtn = false }, 3000)
                  if (err.response) { }
                })
            },
            onCancel: () => {
            }
          })
        } else {
          return false
        }
      })
    },

    handleUpdateRecord () {
      this.$refs.form.validate(valid => {
        if (valid) {
          const data = {
            id: this.recordInfo.id,
            station_id: this.record.station_id,
            generator_id: this.record.generator_id,
            event: this.record.event,
            cause: this.record.cause,
            event_at: this.record.date_at + ' ' + this.record.time_at,
            creator: this.creator,
            description: this.record.description
          }
          if (data.description === '') {
            data.description = '无'
          }

          let title = ''
          if (data.event === '1') {
            title = '修改 ' + data.event_at + '  ' + data.generator_id + 'G 停机'
          }
          if (data.event === '2') {
            title = '修改 ' + data.event_at + '  ' + data.generator_id + 'G 开机'
          }
          if (data.event === '3') {
            title = '修改 ' + data.event_at + '  ' + data.generator_id + 'G 检修开始'
          }
          if (data.event === '4') {
            title = '修改 ' + data.event_at + '  ' + data.generator_id + 'G 检修结束'
          }

          this.$confirm({
            title: title,
            // content: h => <div style="color:rgba(0, 0, 0, 0.65);">{{ }}</div>,
            onOk: () => {
              this.disableBtn = true
              apiSaveEvent(data)
                .then(() => {
                  this.$emit('submitSuccess', 'put')
                })
                //  网络异常，清空页面数据显示，防止错误的操作
                .catch((err) => {
                  setTimeout(() => { this.disableBtn = false }, 3000)
                  if (err.response) { }
                })
            },
            onCancel: () => {
            }
          })
        } else {
          return false
        }
      })
    },

    onSelectEvent (value) {
      if (value === '1') {
        this.eventCauseList = this.eventCauseOptions.c1
        this.record.cause = ''
      } else if (value === '2') {
        this.eventCauseList = this.eventCauseOptions.c2
        this.record.cause = ''
      } else if (value === '3') {
        this.eventCauseList = this.eventCauseOptions.c3
        this.record.cause = ''
      } else if (value === '4') {
        this.eventCauseList = this.eventCauseOptions.c3
        this.record.cause = ''
      }
    }
  }
}
</script>
