<template>
  <div>
    <a-form-model ref="form" :model="record" :rules="rules" :label-col="labelCol" :wrapper-col="wrapperCol">
      <a-form-model-item :wrapper-col="{ lg: { span: 14, offset: 4 }, sm: { span: 14 } }">
        <div style="font-size: 16px; font-weight:bold">{{ STEPS[currentStep].title }}</div>
      </a-form-model-item>

      <div v-show="currentStep == 0" >
        <a-form-model-item label="日期" prop="log_date">
          <a-date-picker v-model="record.log_date" valueFormat="YYYY-MM-DD" placeholder="请选择" />
        </a-form-model-item>

        <a-form-model-item label="时间" prop="log_time">
          <a-radio-group v-model="record.log_time">
            <a-radio :value="'20:00:00'">
              20:00
            </a-radio>
            <a-radio :value="'23:59:00'">
              23:59
            </a-radio>
          </a-radio-group>
        </a-form-model-item>
      </div>

      <div v-for="(item, i) in record.meter" :key="item.prop+'_'+i" v-show="currentStep == (i+1)" >
        <a-form-model-item :wrapper-col="{ lg: { span: 14, offset: 4 }, sm: { span: 14 } }">
          <div>{{ record.log_date + ' ' + record.log_time }}</div>
        </a-form-model-item>
        <a-form-model-item label="正向有功">
          <a-input-number v-model="record.meter[i].fak" :min="0" :style="{width: '100%'}" />
        </a-form-model-item>
        <a-form-model-item label="反向有功" v-show="i < 2 || i > 4">
          <a-input-number v-model="record.meter[i].bak" :min="0" :style="{width: '100%'}" />
        </a-form-model-item>
        <a-form-model-item label="正向无功">
          <a-input-number v-model="record.meter[i].frk" :min="0" :style="{width: '100%'}" />
        </a-form-model-item>
        <a-form-model-item label="反向无功">
          <a-input-number v-model="record.meter[i].brk" :min="0" :style="{width: '100%'}" />
        </a-form-model-item>

        <a-form-model-item label="高峰" v-show="i > 1 && i < 5">
          <a-input-number v-model="record.meter[i].peak" :min="0" :style="{width: '100%'}" />
        </a-form-model-item>
        <a-form-model-item label="低谷" v-show="i > 1 && i < 5">
          <a-input-number v-model="record.meter[i].valley" :min="0" :style="{width: '100%'}" />
        </a-form-model-item>
      </div>

      <a-form-model-item :wrapper-col="{ lg: { span: 14, offset: 4 }, sm: { span: 14 } }">
        <div>
          <a-button v-if="currentStep < STEPS.length - 1" type="primary" :disabled="disableBtn" block @click="handleNextStep">下一步</a-button>
          <a-button v-if="(currentStep == STEPS.length - 1) && (isUpdate === false)" type="primary" :disabled="disableBtn" block @click="handleNewRecord">提交</a-button>
          <a-button v-if="(currentStep == STEPS.length - 1) && (isUpdate === true)" type="primary" :disabled="disableBtn" block @click="handleUpdateRecord">提交</a-button>
        </div>
        <div>
          <a-button v-if="currentStep > 0" style="margin-top: 8px" block @click="handlePrevStep">上一步</a-button>
        </div>
      </a-form-model-item>
    </a-form-model>
  </div>
</template>

<script>
// import moment from 'moment'
import { BigNumber } from 'bignumber.js'
import { getRecordDetail, newRecord, updateRecord } from '@/api/mix/meter'

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
      currentStep: 0,
      submitClicked: false,
      STEPS: [
        { title: '选择日期/时间' },
        { title: '线路主表' },
        { title: '线路副表' },
        { title: '1#G' },
        { title: '2#G' },
        { title: '3#G' },
        { title: '1#厂变' },
        { title: '2#厂变' },
        { title: '3#厂变' },
        { title: '隔离变' }
      ],
      STEPS_LEN: 9,
      record: {
        log_date: '',
        log_time: '',
        meter: this.setDataZero()
      },
      isUpdate: false,
      labelCol: {
        lg: { span: 4 }, sm: { span: 4 }
      },
      wrapperCol: {
        lg: { span: 14 }, sm: { span: 14 }
      },
      rules: {
        log_date: [{ required: true, message: '请选择日期', trigger: ['change'] }],
        log_time: [{ required: true, message: '请选择时间', trigger: ['change'] }]
      },
      disableBtn: false
    }
  },
  mounted () {
    this.currentStep = 0
    this.record.meter = this.setDataZero()
    // this.isUpdate = false
  },
  watch: {
    update: {
      handler: function (val) {
        if (this.update === true) {
          this.isUpdate = true

          this.record.log_date = this.recordInfo.log_date
          this.record.log_time = this.recordInfo.log_time
          const query = {
            station_id: this.recordInfo.station_id,
            log_date: this.recordInfo.log_date,
            log_time: this.recordInfo.log_time
          }
          this.disableBtn = true
          getRecordDetail(query)
            .then((res) => {
              if (res.size !== 0) {
                this.record.meter = res.record
              }
              //
              this.disableBtn = false
              this.currentStep++
            })
            //  网络异常，清空页面数据显示，防止错误的操作
            .catch((err) => {
              this.$message.warning('请稍后重试')
              setTimeout(() => { this.disableBtn = false }, 3000)
              this.$emit('failure')
              if (err.response) {}
            })
        }
      },
      immediate: true
    }
    // current: {
    //   handler: function (val) {
    //     this.pagination.current = val
    //   },
    //   immediate: true
    // }
  },
  methods: {

    handleNextStep () {
      if (this.currentStep === 0) {
        // if (this.submitClicked === false) {
        //   this.record.meter = this.setDataZero()
        // }

        this.$refs.form.validate(valid => {
          if (valid) {
            this.currentStep++
          }
        })

        return
      }

      if (this.currentStep > 0) {
        const index = this.currentStep - 1
        if (this.hasNullInData(this.record.meter[index])) {
          this.$message.warning('请输入数字，例如：0，12，12.3，0.1234')
          return true
        } else {
          this.currentStep++
        }
      }
    },

    handlePrevStep () {
      if (this.isUpdate) {
        if (this.currentStep > 1) {
          this.currentStep--
        }
      } else {
        this.currentStep--
      }
    },

    hasNullInData (data) {
      for (const x in data) {
        if (data[x] === null) {
          return true
        }
      }
      return false
    },

    // onClickSubmit () {
    //   if (this.isUpdate) {
    //     this.handleUpdateRecord()
    //   } else {
    //     this.handleNewRecord()
    //   }
    // },

    handleNewRecord () {
      const index = this.currentStep - 1
      if (this.hasNullInData(this.record.meter[index])) {
        this.$message.warning('请输入数字，例如：0，12，12.3，0.1234')
        return true
      }
      this.$refs.form.validate(valid => {
        if (valid) {
          const temp = JSON.parse(JSON.stringify(this.record.meter))
          const meter = this.transformValue(temp)

          const data = {
            station_id: this.stationId,
            creator: this.creator,
            log_date: this.record.log_date,
            log_time: this.record.log_time,
            meter: meter
          }
          this.submitClicked = true

          const title = '确认日期：' + this.record.log_date + ' ' + this.record.log_time
          this.$confirm({
            title: title,
            // content: h => <div style="color:rgba(0, 0, 0, 0.65);">{{ '电表读数日期：' +  }}</div>,
            onOk: () => {
              this.disableBtn = true
              newRecord(data)
                .then(() => {
                  // this.submitClicked = false
                  // this.disableBtn = false
                  // this.record.log_date = ''
                  // this.record.log_time = ''
                  // this.record.meter = this.setDataZero()
                  // this.currentStep = 0
                  //
                  this.$emit('submitSuccess', 'post')
                })
                //  网络异常，清空页面数据显示，防止错误的操作
                .catch((err) => {
                  setTimeout(() => { this.disableBtn = false }, 3000)
                  if (err.response) { }
                })
            },
            onCancel: () => {
              // this.currentStep = 0
            }
          })
        } else {
          return false
        }
      })
    },

    handleUpdateRecord () {
      const index = this.currentStep - 1
      if (this.hasNullInData(this.record.meter[index])) {
        this.$message.warning('请输入数字，例如：0，12，12.3，0.1234')
        return true
      }
      this.$refs.form.validate(valid => {
        if (valid) {
          const temp = JSON.parse(JSON.stringify(this.record.meter))
          const meter = this.transformValue(temp)

          const data = {
            station_id: this.stationId,
            creator: this.creator,
            log_date: this.record.log_date,
            log_time: this.record.log_time,
            meter: meter
          }
          this.submitClicked = true

          const title = '确认日期：' + this.record.log_date + ' ' + this.record.log_time
          this.$confirm({
            title: title,
            // content: h => <div style="color:rgba(0, 0, 0, 0.65);">{{ '电表读数日期：' +  }}</div>,
            onOk: () => {
              this.disableBtn = true
              updateRecord(data)
                .then(() => {
                  // this.submitClicked = false
                  // this.disableBtn = false
                  // this.record.log_date = ''
                  // this.record.log_time = ''
                  // this.record.meter = this.setDataZero()
                  // this.currentStep = 0
                  //
                  this.$emit('submitSuccess', 'put')
                })
                //  网络异常，清空页面数据显示，防止错误的操作
                .catch((err) => {
                  setTimeout(() => { this.disableBtn = false }, 3000)
                  if (err.response) { }
                })
            },
            onCancel: () => {
              // this.currentStep = 1
            }
          })
        } else {
          return false
        }
      })
    },

    setDataZero () {
      var data = new Array(this.STEPS_LEN)
      for (let i = 0; i < data.length; i++) {
        data[i] = {
          fak: 0,
          bak: 0,
          frk: 0,
          brk: 0,
          peak: 0,
          valley: 0
        }
      }
      return data
    },

    // 万kwh -> kwh
    // filterMeterValue (meter) {
    transformValue (meters) {
      const temp = meters
      for (let i = 0; i < temp.length; i++) {
        for (const x in temp[i]) {
          if (i < 2) {
            // 四位小数
            temp[i][x] = this.fractionToInteger(temp[i][x], 4)
          } else {
            // 两位小数
            temp[i][x] = this.fractionToInteger(temp[i][x], 2)
          }
        }
      }
      return temp
    },

    fractionToInteger (value, bits) {
      const str = new BigNumber(value).toFixed()
      // 截取小数部分 bits位
      const arr = str.split('.')
      let res = str
      if (arr.length === 2) {
        const frac = arr[1]
        res = arr[0] + '.' + frac.substr(0, bits)
      }
      // 乘以10的bits方
      const x = Math.pow(10, bits)
      const temp = new BigNumber(res).multipliedBy(x)

      return temp.toFixed()
    }
  }
}
</script>
