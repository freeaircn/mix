<template>
  <page-header-wrapper>

    <a-card :title="userInfo.belongToDeptName" :bordered="false" :headStyle="{marginBottom: '8px'}">
    </a-card>

    <div class="antd-pro-pages-dashboard-analysis-twoColLayout" :class="!isMobile && 'desktop'">
      <a-row :gutter="8" type="flex" :style="{ marginBottom: '8px' }">
        <a-col :xl="8" :lg="24" :md="24" :sm="24" :xs="24" >
          <a-card :bordered="false" title="电表读数" :style="{height: '100%'}">
            <a-form-model
              ref="metersForm"
              :model="metersForm"
              :rules="rules"
              :label-col="labelCol"
              :wrapper-col="wrapperCol"
            >
              <a-form-model-item :wrapper-col="{ lg: { span: 14, offset: 4 }, sm: { span: 14 } }">
                <div style="font-size: 16px; font-weight:bold">{{ logMeterSteps[logMeterStepIndex].title }}</div>
              </a-form-model-item>
              <a-form-model-item label="日期" prop="log_date">
                <a-date-picker v-model="metersForm.log_date" valueFormat="YYYY-MM-DD" placeholder="请选择" />
              </a-form-model-item>

              <a-form-model-item label="时间" prop="log_time">
                <a-radio-group v-model="metersForm.log_time">
                  <a-radio :value="'20:00:00'">
                    20:00
                  </a-radio>
                  <a-radio :value="'23:59:00'">
                    23:59
                  </a-radio>
                </a-radio-group>
              </a-form-model-item>

              <div v-for="(item, i) in metersForm.meter" :key="item.prop+'_'+i" v-show="logMeterStepIndex == i" >
                <a-form-model-item label="正向有功">
                  <a-input-number v-model="metersForm.meter[i].fak" :min="0" :style="{width: '100%'}" />
                </a-form-model-item>
                <a-form-model-item label="反向有功" v-show="i < 2 || i > 4">
                  <a-input-number v-model="metersForm.meter[i].bak" :min="0" :style="{width: '100%'}" />
                </a-form-model-item>
                <a-form-model-item label="正向无功">
                  <a-input-number v-model="metersForm.meter[i].frk" :min="0" :style="{width: '100%'}" />
                </a-form-model-item>
                <a-form-model-item label="反向无功">
                  <a-input-number v-model="metersForm.meter[i].brk" :min="0" :style="{width: '100%'}" />
                </a-form-model-item>

                <a-form-model-item label="高峰" v-show="i > 1 && i < 5">
                  <a-input-number v-model="metersForm.meter[i].peak" :min="0" :style="{width: '100%'}" />
                </a-form-model-item>
                <a-form-model-item label="低谷" v-show="i > 1 && i < 5">
                  <a-input-number v-model="metersForm.meter[i].valley" :min="0" :style="{width: '100%'}" />
                </a-form-model-item>
              </div>

              <a-form-model-item :wrapper-col="{ lg: { span: 14, offset: 4 }, sm: { span: 14 } }">
                <div>
                  <a-button v-if="logMeterStepIndex < logMeterSteps.length - 1" type="primary" block @click="handleLogMeterStepNext">下一步</a-button>
                  <a-button v-if="logMeterStepIndex == logMeterSteps.length - 1" type="primary" block @click="handleLogMeters">提交</a-button>
                </div>
                <div>
                  <a-button v-if="logMeterStepIndex > 0" style="margin-top: 8px" block @click="handleLogMeterStepPrev">上一步</a-button>
                </div>
              </a-form-model-item>
            </a-form-model>
          </a-card>
        </a-col>

        <a-col :xl="8" :lg="24" :md="24" :sm="24" :xs="24">
          <a-card class="antd-pro-pages-dashboard-analysis-salesCard" :loading="listLoading" :bordered="false" title="电表记录" :style="{ height: '100%' }">
            <a-form-model ref="hisLogsForm" layout="inline" :model="hisLogsParam" @submit.native.prevent>
              <a-form-model-item>
                <a-month-picker v-model="hisLogsParam.date" valueFormat="YYYY-MM-DD" />
              </a-form-model-item>

              <a-form-model-item>
                <a-button type="primary" @click="handleQueryHisLogs">查询</a-button>
              </a-form-model-item>

              <!-- <a-form-model-item>
                <a-button @click="handleExportHisEvent">导出</a-button>
              </a-form-model-item> -->
            </a-form-model>

            <div>
              <!-- style="width: calc(100% - 240px);" -->
              <MetersLogList
                :loading="listLoading"
                :listData="listLogData"
                :current.sync="logListPageIndex"
                :total="totalLogs"
                @paginationChange="onReqEventLog"
                @report="onReqDailyReport"
                @reqEdit="onReqEditEventLog"
                @reqDelete="onReqDelEventLog"
              >
              </MetersLogList>
            </div>
          </a-card>
        </a-col>

        <a-col :xl="8" :lg="24" :md="24" :sm="24" :xs="24">
          <a-card :bordered="false" :title="currentYear + '年 计划&成交电量'" :style="{height: '100%'}">
            <a-button type="primary">修改</a-button>
            <PlanKWhList
              :loading="listLoading"
              :listData="planKWhData"
              :current.sync="logListPageIndex"
              @reqData="onReqEventLog"
              @reqEdit="onReqEditEventLog"
              @reqDelete="onReqDelEventLog"
            >
            </PlanKWhList>
          </a-card>
        </a-col>
      </a-row>
    </div>

    <RealKWhChart
      :loading="false"
      :year="'2021'"
    >
    </RealKWhChart>

    <a-card :loading="loading" title="历史统计" :bordered="false" :body-style="{padding: '0', marginBottom: '8px'}">
      <div style="padding: 8px">
        <a-select style="width: 100px" placeholder="起始" >
          <a-select-option v-for="d in availableYearRange" :key="d.value" :value="d.value" >
            {{ d.name }}
          </a-select-option>
        </a-select>
        <span style="margin: 0 18px">至</span>
        <a-select style="width: 100px" placeholder="结束" >
          <a-select-option v-for="d in availableYearRange" :key="d.value" :value="d.value" >
            {{ d.name }}
          </a-select-option>
        </a-select>
        <span style="margin: 0 18px"><a-button type="primary" @click="handleQueryHisStatistic">查询</a-button></span>
      </div>
    </a-card>

  </page-header-wrapper>
</template>

<script>
import moment from 'moment'
// import { deepMerge } from '@/utils/util'
import { MetersLogList, PlanKWhList, RealKWhChart } from './components/meter'
import { mapGetters } from 'vuex'
import { getMeterLogs, saveMeterLogs, getMetersDailyReport, getGeneratorEventStatistic, delGeneratorEvent, getExportGeneratorEvent } from '@/api/service'
import { baseMixin } from '@/store/app-mixin'

const availableYearRange = []
for (let i = 2018; i <= moment().year(); i++) {
  availableYearRange.push({
    name: i + '年',
    value: i
  })
}

export default {
  name: 'StationMeters',
  mixins: [baseMixin],
  components: {
    MetersLogList,
    PlanKWhList,
    RealKWhChart
  },
  data () {
    return {
      loading: true,
      // 电表读数记录区
      logMeterStepIndex: 0,
      logMeterSteps: [
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

      metersForm: {
        log_date: '',
        log_time: '',
        meter: this.makeupMeterDataStructure()
      },

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

      // 列表显示区
      hisLogsParam: {
        date: ''
      },

      hisEvent: {
        startAt: '',
        endAt: ''
        // generatorId: null
      },

      listLoading: false,
      logListPageIndex: 1,
      pageSize: 6,
      listLogData: [],
      totalLogs: 0,

      editEventModalVisible: false,
      editEventRecord: {},

      // 年计划显示区
      planKWhData: [
        { id: 1, month: '1月', planning: 1000, deal: 1000 },
        { id: 2, month: '2月', planning: 2000, deal: 1000 },
        { id: 3, month: '3月', planning: 3000, deal: 1000 },
        { id: 4, month: '4月', planning: 4000, deal: 1000 },
        { id: 5, month: '5月', planning: 5000, deal: 1000 },
        { id: 6, month: '6月', planning: 6000, deal: 1000 },
        { id: 7, month: '7月', planning: 7000, deal: 1000 },
        { id: 8, month: '8月', planning: 8000, deal: 1000 },
        { id: 9, month: '9月', planning: 9000, deal: 1000 },
        { id: 10, month: '10月', planning: 10000, deal: 1000 },
        { id: 11, month: '11月', planning: 11000, deal: 1000 },
        { id: 12, month: '12月', planning: 12000, deal: 1000 }
      ],

      // 统计Bar显示区
      barLoading: false,
      currentYear: moment().year(),
      barStatisticRunNumber: [],
      barStatisticRunTotalTime: [],
      barStatisticLatestTime: [],

      // 历史统计显示区
      availableYearRange

    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  created () {
    this.hisLogsParam.date = moment().format('YYYY-MM-DD')
    //
    setTimeout(() => {
      this.loading = !this.loading
    }, 1000)

    // log列表显示区
    this.handleQueryHisLogs()

    // 图标显示区

    // 历史统计显示区
  },
  methods: {

    // 录入表单区域
    handleLogMeterStepNext () {
      if (this.hasNullInMeterData(this.metersForm.meter[this.logMeterStepIndex])) {
        this.$message.warning('请输入数字，例如：0，12，12.3，0.123')
        return true
      }
      this.logMeterStepIndex++
    },

    handleLogMeterStepPrev () {
      this.logMeterStepIndex--
    },

    handleLogMeters () {
      if (this.hasNullInMeterData(this.metersForm.meter[this.logMeterStepIndex])) {
        this.$message.warning('请输入数字，例如：0，12，12.3，0.123')
        return true
      }
      this.$refs.metersForm.validate(valid => {
        if (valid) {
          const data = { ...this.metersForm }
          const meter = JSON.parse(JSON.stringify(this.metersForm.meter))
          data.meter = this.floorMeterValue(meter)
          data.station_id = this.userInfo.belongToDeptId
          data.creator = this.userInfo.username

          saveMeterLogs(data)
            .then(() => {
              // this.handleQueryHisLogs()
              // this.queryThisYearStatistic()
            })
            //  网络异常，清空页面数据显示，防止错误的操作
            .catch((err) => {
              if (err.response) { }
            })
        } else {
          return false
        }
      })
    },

    // 事件列表显示区
    handleQueryHisLogs () {
      if (this.hisLogsParam.date == null) {
        this.hisLogsParam.date = moment().format('YYYY-MM-DD')
      }
      const query = {
        station_id: this.userInfo.belongToDeptId,
        date: this.hisLogsParam.date,
        type: 'month',
        limit: this.pageSize,
        offset: 1
      }
      this.logListPageIndex = 1
      this.listLoading = true
      getMeterLogs(query)
        .then(res => {
          this.listLoading = false
          //
          this.totalLogs = res.total
          this.listLogData = res.data
        })
        .catch((err) => {
          this.listLoading = false
          this.listLogData.splice(0, this.listLogData.length)
          if (err.response) {
          }
        })
    },

    // 查看电量单日简报
    onReqDailyReport (param) {
      console.log('report', param)
      const query = {
        station_id: param.station_id,
        log_date: param.log_date,
        log_time: param.log_time
      }
      getMetersDailyReport(query)
        .then(res => {

        })
        .catch((err) => {
          if (err.response) {
          }
        })
    },

    // 导出excel文件
    handleExportHisEvent () {
      // 检查输入日期
      const format = 'YYYY-MM-DD'
      const start = this.hisEvent.startAt ? this.hisEvent.startAt : moment().format('YYYY') + '-01-01'
      const end = this.hisEvent.endAt ? this.hisEvent.endAt : moment().format(format)
      const diffDays = moment(moment(end, format)).diff(moment(moment(start, format)), 'days')
      if (diffDays < 0 || diffDays > 366) {
        this.$notification.warning({
          message: '错误',
          description: '检查起始、结束时间（365天以内）'
        })
        return
      }

      const query = {
        station_id: this.userInfo.belongToDeptId,
        start: start,
        end: end
      }

      getExportGeneratorEvent(query)
        .then(res => {
          const { data, headers } = res

          // 下载excel文件
          const blob = new Blob([data], { type: headers['content-type'] })
          const dom = document.createElement('a')
          const url = window.URL.createObjectURL(blob)
          const filename = this.userInfo.belongToDeptName + '_开停机记录_' + moment().format('YYYY-MM-DD') + '.xlsx'
          dom.href = url
          dom.download = decodeURI(filename)
          dom.style.display = 'none'
          document.body.appendChild(dom)
          dom.click()
          dom.parentNode.removeChild(dom)
          window.URL.revokeObjectURL(url)

          this.$message.info('已导出文件')
        })
        .catch((err) => {
          if (err.response) {
          }
        })
    },

    onReqEventLog (param) {
      const query = { ...param }

      // 检查输入日期
      const format = 'YYYY-MM-DD'
      const start = this.hisEvent.startAt ? this.hisEvent.startAt : '2011-01-01'
      const end = this.hisEvent.endAt ? this.hisEvent.endAt : moment().format(format)
      const gid = this.hisEvent.generatorId ? this.hisEvent.generatorId : 9
      if (moment(moment(end, format)).diff(moment(moment(start, format)), 'days') < 0) {
        this.$notification.warning({
          message: '错误',
          description: '请检查起始时间和结束时间'
        })
        return
      }

      query.station_id = this.userInfo.belongToDeptId
      query.generator_id = gid
      query.start = start
      query.end = end

      this.listLoading = true
      getMeterLogs(query)
        .then(res => {
          this.listLoading = false
          //
          this.totalLogs = res.total
          this.listLogData = res.data
        })
        .catch((err) => {
          this.listLoading = false
          this.listLogData.splice(0, this.listLogData.length)
          if (err.response) {
          }
        })
    },

    onReqDelEventLog (param) {
      delGeneratorEvent(param)
        .then(() => {
            this.handleQueryHisEvent()
            this.queryThisYearStatistic()
          })
          //  网络异常，清空页面数据显示，防止错误的操作
          .catch((err) => {
            if (err.response) { }
          })
    },

    onReqEditEventLog (param) {
      param.creator = this.userInfo.username
      saveMeterLogs(param)
        .then(() => {
            this.handleQueryHisEvent()
            this.queryThisYearStatistic()
          })
          //  网络异常，清空页面数据显示，防止错误的操作
          .catch((err) => {
            if (err.response) { }
          })
    },

    // bar 统计显示区
    queryThisYearStatistic () {
      const query2 = {
        year: this.currentYear,
        station_id: this.userInfo.belongToDeptId
      }
      this.barLoading = true
      getGeneratorEventStatistic(query2)
        .then(res => {
          this.filterBarStatisticData(res.data)
          this.barLoading = false
        })
        .catch((err) => {
          this.barLoading = false
          this.barStatisticRunNumber.splice(0, this.barStatisticRunNumber.length)
          this.barStatisticRunTotalTime.splice(0, this.barStatisticRunTotalTime.length)
          this.barStatisticLatestTime.splice(0, this.barStatisticLatestTime.length)
          if (err.response) {
          }
        })
    },

    filterBarStatisticData (data) {
      this.barStatisticRunNumber.splice(0, this.barStatisticRunNumber.length)
      this.barStatisticRunTotalTime.splice(0, this.barStatisticRunTotalTime.length)
      this.barStatisticLatestTime.splice(0, this.barStatisticLatestTime.length)

      data.forEach(element => {
        const tempRunNumber = {
          name: element.generator_id + 'G',
          value: Number(element.run_num)
        }
        const tempRunTotalTime = {
          name: element.generator_id + 'G',
          value: element.run_total_time / 3600
        }

        this.barStatisticRunNumber.push(tempRunNumber)
        this.barStatisticRunTotalTime.push(tempRunTotalTime)
        this.barStatisticLatestTime.push(element.latest_time)
      })
    },

    // 历史统计 显示区
    handleQueryHisStatistic () {
      console.log('QueryHisStatistic')
      this.$message.warning('暂不支持该功能')
    },

    makeupMeterDataStructure () {
      var data = new Array(9)
      for (let i = 0; i < data.length; i++) {
        data[i] = {
          fak: '0',
          bak: '0',
          frk: '0',
          brk: '0',
          peak: '0',
          valley: '0'
        }
      }
      return data
    },

    hasNullInMeterData (data) {
      for (const x in data) {
        if (data[x] === null) {
          return true
        }
      }
      return false
    },

    floorMeterValue (data) {
      for (let i = 0; i < data.length; i++) {
        for (const x in data[i]) {
          if (i < 2) {
            data[i][x] = Math.floor(data[i][x] * 10000)
          } else {
            data[i][x] = Math.floor(data[i][x] * 100)
          }
        }
      }
      return data
    }
  }
}
</script>

<style lang="less" scoped>
  .extra-wrapper {
    // line-height: 55px;
    padding-right: 8px;

    .extra-item {
      display: inline-block;
      margin-right: 8px;

      a {
        margin-left: 8px;
      }
    }
  }

  .antd-pro-pages-dashboard-analysis-twoColLayout {
    position: relative;
    display: flex;
    display: block;
    flex-flow: row wrap;
  }

  .antd-pro-pages-dashboard-analysis-salesCard {
    height: calc(100% - 24px);
    /deep/ .ant-card-head {
      position: relative;
    }
  }

  .dashboard-analysis-iconGroup {
    i {
      margin-left: 16px;
      color: rgba(0,0,0,.45);
      cursor: pointer;
      transition: color .32s;
      color: black;
    }
  }
  .analysis-salesTypeRadio {
    position: absolute;
    right: 54px;
    bottom: 12px;
  }

</style>
