<template>
  <page-header-wrapper :title="false">

    <a-card :title="userInfo.belongToDeptName" :bordered="false" :headStyle="{marginBottom: '8px'}">
    </a-card>

    <div class="antd-pro-pages-dashboard-analysis-twoColLayout" :class="!isMobile && 'desktop'">
      <a-row :gutter="[8,8]" type="flex" :style="{ marginBottom: '8px' }">
        <a-col :xl="8" :lg="24" :md="24" :sm="24" :xs="24" >
          <a-card :bordered="false" title="录入电表数" :style="{height: '100%'}">
            <a-form-model
              ref="metersForm"
              :model="metersForm"
              :rules="rules"
              :label-col="labelCol"
              :wrapper-col="wrapperCol"
            >
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

              <a-form-model-item :wrapper-col="{ lg: { span: 14, offset: 4 }, sm: { span: 14 } }">
                <div style="font-size: 16px; font-weight:bold">{{ logMeterSteps[logMeterStepIndex].title }}</div>
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
                  <a-button v-if="logMeterStepIndex == logMeterSteps.length - 1" type="primary" :disabled="disableBtn" block @click="handleLogMeters">提交</a-button>
                </div>
                <div>
                  <a-button v-if="logMeterStepIndex > 0" style="margin-top: 8px" block @click="handleLogMeterStepPrev">上一步</a-button>
                </div>
              </a-form-model-item>
            </a-form-model>
          </a-card>
        </a-col>
        <a-col :xl="8" :lg="24" :md="24" :sm="24" :xs="24">
          <a-card :bordered="false" title="历史记录" :style="{ height: '100%' }">
            <div>
              <!-- style="width: calc(100% - 240px);" -->
              <LogList
                :loading="logListLoading"
                :date="logListDate"
                :listData="logListData"
                :current.sync="logListPageIndex"
                :total="totalLogs"
                @paginationChange="onLogListPageChange"
                @query="onQueryMeterLog"
                @queryDetail="onQueryMetersLogDetail"
                @report="onReqDailyReport"
                @delete="onDeleteMeterLog"
              >
              </LogList>
            </div>
          </a-card>
        </a-col>

        <a-col :xl="8" :lg="24" :md="24" :sm="24" :xs="24">
          <a-card :bordered="false" title="计划&成交电量" :style="{height: '100%'}">
            <PlanningList
              :loading="planningListLoading"
              :date="planningListDate"
              :listData="planningListData"
              @query="onQueryPlanning"
              @update="onUpdatePlanningRecord"
            >
            </PlanningList>
          </a-card>
        </a-col>
      </a-row>
    </div>

    <a-modal
      :title="logDetailDiagTitle"
      v-model="logDetailDiagVisible"
    >
      <template slot="footer">
        <a-button key="submit" type="primary" @click="() => {this.logDetailDiagVisible = false}">
          关闭
        </a-button>
      </template>
      <LogDetail
        :tab1Data="logDetailTab1Data"
        :tab2Data="logDetailTab2Data"
        :tab3Data="logDetailTab3Data"
        :tab4Data="logDetailTab4Data"
      >
      </LogDetail>
    </a-modal>

    <a-modal v-model="reportDiagVisible" title="简报" >
      <template slot="footer">
        <a-button key="submit" type="primary" @click="() => {this.reportDiagVisible = false}">
          关闭
        </a-button>
      </template>
      <a-card size="small" title="今日1" style="margin-bottom: 8px">
        <a-button slot="extra" type="link" @click="handleCopyReportContent('daily1')">复制</a-button>
        <p>{{ reportContent.daily1 }}</p>
      </a-card>
      <a-card size="small" title="今日2" style="margin-bottom: 8px">
        <a-button slot="extra" type="link" @click="handleCopyReportContent('daily2')">复制</a-button>
        <p>{{ reportContent.daily2 }}</p>
      </a-card>
      <a-card size="small" title="一周">
        <a-button slot="extra" type="link" @click="handleCopyReportContent('weekly')">复制</a-button>
        <p>{{ reportContent.weekly }}</p>
      </a-card>
    </a-modal>

    <a-modal v-model="reportOf20ClockDiagVisible" title="简报" >
      <template slot="footer">
        <a-button key="submit" type="primary" @click="() => {this.reportOf20ClockDiagVisible = false}">
          关闭
        </a-button>
      </template>
      <a-statistic title="20:00发电量" :value="dailyOf20Clock">
        <template #suffix>
          <span> / 万kWh</span>
        </template>
      </a-statistic>
    </a-modal>

    <div v-if="!isMobile">
      <a-card title="统计图表" :bordered="false" :body-style="{marginBottom: '8px'}">
        <div class="extra-wrapper" slot="extra">
          <div class="extra-item">
            <a-button type="link" @click="onQueryBasicStatistic('month')">刷新</a-button>
          </div>
        </div>
        <BasicStatistic
          :loading="basicStatLoading"
          :changed="basicStatChanged"
          :date="basicStatDate"
          :statisticListData="basicStatListData"
          :thirtyDaysData="basicStat30DaysData"
          :monthlyData="basicStatMonthlyData"
          :quarterlyData="basicStatQuarterlyData"
        >
        </BasicStatistic>
      </a-card>

      <a-card :loading="false" title="全景" :bordered="false" :body-style="{marginBottom: '8px'}">
        <div class="extra-wrapper" slot="extra">
          <div class="extra-item">
            <a-button type="link" @click="onQueryOverallStatistic">刷新</a-button>
          </div>
        </div>

        <OverallStatistic
          :loading="overallStatLoading"
          :changed="overallStatChanged"
          :total="overallStatTotal"
          :yearData="overallStatYearData"
          :monthData="overallStatMonthData"
        >
        </OverallStatistic>

      </a-card>
    </div>

  </page-header-wrapper>
</template>

<script>
import moment from 'moment'
// import { deepMerge } from '@/utils/util'
import { LogList, PlanningList, BasicStatistic, OverallStatistic, LogDetail } from './components/meter'
import { mapGetters } from 'vuex'
import { getMeterLogs, getMetersLogDetail, saveMeterLogs, getMetersDailyReport, getMetersBasicStatistic, delMeterLogs, getPlanningKWh, updatePlanningKWhRecord, getMetersOverallStatistic } from '@/api/service'
import { baseMixin } from '@/store/app-mixin'

export default {
  name: 'Meters',
  mixins: [baseMixin],
  components: {
    LogList,
    PlanningList,
    BasicStatistic,
    OverallStatistic,
    LogDetail
  },
  data () {
    return {

      // 输入记录
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
      disableBtn: false,

      // 记录显示
      logListLoading: false,
      logListPageIndex: 1,
      pageSize: 6,
      totalLogs: 0,
      logListDate: '',
      logListData: [],
      reportDiagVisible: false,
      reportContent: {
        daily1: '',
        daily2: '',
        weekly: ''
      },

      reportOf20ClockDiagVisible: false,
      dailyOf20Clock: '0',

      logDetailDiagTitle: '',
      logDetailDiagVisible: false,
      logDetailTab1Data: [],
      logDetailTab2Data: [],
      logDetailTab3Data: [],
      logDetailTab4Data: [],

      // 年计划显示
      planningListLoading: false,
      planningListDate: '',
      planningListData: [],

      // Basic统计显示
      basicStatLoading: false,
      basicStatChanged: false,
      basicStatDate: '',
      basicStatListData: [],
      basicStat30DaysData: [],
      basicStatMonthlyData: [],
      basicStatQuarterlyData: [],

      // 全景统计显示
      overallStatLoading: false,
      overallStatChanged: false,
      overallStatTotal: {
        onGridEnergy: 0,
        genEnergy: 0
      },
      overallStatYearData: [],
      overallStatMonthData: []
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  created () {
    // 初值
    this.logListDate = moment().format('YYYY-MM-DD')
    this.planningListDate = moment().format('YYYY-MM-DD')
  },
  mounted () {
    // 记录显示
    this.onQueryMeterLog(this.logListDate)
    this.onQueryPlanning(this.planningListDate)

    if (!this.isMobile) {
      // 统计图表
    this.onQueryBasicStatistic()

    // 全景统计
    this.onQueryOverallStatistic()
    }
  },
  methods: {

    // 录入表单
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

          this.disableBtn = true
          saveMeterLogs(data)
            .then(() => {
              this.logMeterStepIndex = 0
              this.disableBtn = false
              //
              this.metersForm.log_date = ''
              this.metersForm.log_time = ''
              this.metersForm.meter = this.makeupMeterDataStructure()
              //
              this.onQueryMeterLog(this.logListDate)
            })
            //  网络异常，清空页面数据显示，防止错误的操作
            .catch((err) => {
              setTimeout(() => { this.disableBtn = false }, 3000)
              if (err.response) { }
            })
        } else {
          return false
        }
      })
    },

    // 记录列表显示
    onQueryMeterLog (date) {
      const query = {
        station_id: this.userInfo.belongToDeptId,
        date: date,
        type: 'month',
        limit: this.pageSize,
        offset: 1
      }
      this.logListPageIndex = 1
      this.logListLoading = true
      getMeterLogs(query)
        .then(res => {
          this.logListLoading = false
          //
          this.logListDate = date
          this.totalLogs = res.total
          this.logListData = res.data
        })
        .catch((err) => {
          this.logListLoading = false
          this.logListData.splice(0, this.logListData.length)
          if (err.response) {
          }
        })
    },

    onLogListPageChange (param) {
      const query = { ...param }
      query.station_id = this.userInfo.belongToDeptId
      query.date = this.logListDate
      query.type = 'month'

      this.logListLoading = true
      getMeterLogs(query)
        .then(res => {
          this.logListLoading = false
          //
          this.totalLogs = res.total
          this.logListData = res.data
        })
        .catch((err) => {
          this.logListLoading = false
          this.logListData.splice(0, this.logListData.length)
          if (err.response) {
          }
        })
    },

    onQueryMetersLogDetail (param) {
      this.logDetailTab1Data.splice(0, this.logDetailTab1Data.length)
      this.logDetailTab2Data.splice(0, this.logDetailTab2Data.length)
      this.logDetailTab3Data.splice(0, this.logDetailTab3Data.length)
      this.logDetailTab4Data.splice(0, this.logDetailTab4Data.length)
      //
      this.logDetailDiagTitle = param.log_date + ' ' + param.log_time
      this.logDetailDiagVisible = false
      getMetersLogDetail(param)
        .then((data) => {
          this.logDetailTab1Data = data.tab1Data
          this.logDetailTab2Data = data.tab2Data
          this.logDetailTab3Data = data.tab3Data
          this.logDetailTab4Data = data.tab4Data
          //
          this.logDetailDiagVisible = true
        })
        .catch((err) => {
          this.logDetailTab1Data.splice(0, this.logDetailTab1Data.length)
          this.logDetailTab2Data.splice(0, this.logDetailTab2Data.length)
          this.logDetailTab3Data.splice(0, this.logDetailTab3Data.length)
          this.logDetailTab4Data.splice(0, this.logDetailTab4Data.length)
          //
          this.logDetailDiagVisible = false
          if (err.response) {
          }
        })
    },

    // 查看电量单日简报
    onReqDailyReport (param) {
      const query = {
        station_id: param.station_id,
        log_date: param.log_date,
        log_time: param.log_time
      }
      this.reportDiagVisible = false
      this.reportContent.daily1 = ''
      this.reportContent.daily2 = ''
      this.reportContent.weekly = ''
      //
      this.reportOf20ClockDiagVisible = false
      this.dailyOf20Clock = '0'
      getMetersDailyReport(query)
        .then(res => {
          const data = res.data
          const type = res.type
          //
          if (type === '23') {
            this.reportDiagVisible = true
            this.reportContent.daily1 = data.daily1
            this.reportContent.daily2 = data.daily2
            this.reportContent.weekly = data.weekly
          }
          //
          if (type === '20') {
            this.reportOf20ClockDiagVisible = true
            this.dailyOf20Clock = data
          }
        })
        .catch((err) => {
          this.reportDiagVisible = false
          this.reportContent.daily1 = ''
          this.reportContent.daily2 = ''
          this.reportContent.weekly = ''
          //
          this.reportOf20ClockDiagVisible = false
          this.dailyOf20Clock = '0'
          if (err.response) {
          }
        })
    },

    handleCopyReportContent (type) {
      let text = ''
      if (type === 'daily1') {
        text = this.reportContent.daily1
      } else if (type === 'daily2') {
        text = this.reportContent.daily2
      } else if (type === 'weekly') {
        text = this.reportContent.weekly
      }
      this.$copyText(text).then(
        () => {
          this.$message.info('复制成功')
        },
        () => {
          this.$message.info('复制失败')
        })
    },

    onDeleteMeterLog (param) {
      delMeterLogs(param)
        .then(() => {
            this.onQueryMeterLog(this.logListDate)
          })
          //  网络异常，清空页面数据显示，防止错误的操作
          .catch((err) => {
            if (err.response) {
              this.logListData.splice(0, this.logListData.length)
            }
          })
    },

    // 查询计划
    onQueryPlanning (date) {
      const query = {
        station_id: this.userInfo.belongToDeptId,
        date: date
      }
      this.planningListLoading = true
      getPlanningKWh(query)
        .then(res => {
          this.planningListLoading = false
          //
          this.planningListDate = date
          this.planningListData = this.transformPlanningListData(res.data)
        })
        .catch((err) => {
          this.planningListLoading = false
          this.planningListData.splice(0, this.planningListData.length)
          if (err.response) {
          }
        })
    },

    // 修改计划
    onUpdatePlanningRecord (record) {
      const data = { ...record }
      data.station_id = this.userInfo.belongToDeptId
      // 单位换算
      data.planning = Math.floor(data.planning * 10000)
      data.deal = Math.floor(data.deal * 10000)
      updatePlanningKWhRecord(data)
        .then(() => {
          this.onQueryPlanning(this.planningListDate)
        })
        .catch((err) => {
          if (err.response) {
          }
        })
    },

    // 基本统计
    onQueryBasicStatistic () {
      const query = {
        station_id: this.userInfo.belongToDeptId
      }
      this.basicStatLoading = true
      getMetersBasicStatistic(query)
        .then(res => {
          this.basicStatDate = res.date
          this.basicStatListData = res.statisticList
          this.basicStat30DaysData = res.thirtyDaysData
          this.basicStatMonthlyData = res.monthData
          this.basicStatQuarterlyData = res.quarterData
          //
          this.basicStatLoading = false
          this.basicStatChanged = !this.basicStatChanged
        })
        .catch((err) => {
          this.basicStatLoading = false
          if (err.response) {
          }
        })
    },

    // 全景
    onQueryOverallStatistic () {
      const query = {
        station_id: this.userInfo.belongToDeptId
      }
      this.overallStatLoading = true
      getMetersOverallStatistic(query)
        .then((res) => {
          this.overallStatTotal = { ...res.total }
          this.overallStatYearData = res.yearData
          this.overallStatMonthData = res.monthData
          //
          this.overallStatLoading = false
          this.overallStatChanged = !this.overallStatChanged
          })
          .catch((err) => {
            this.overallStatLoading = false
            if (err.response) {
            }
          })
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

    // 单位换算
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
    },

    // 单位换算
    transformPlanningListData (data) {
      for (let i = 0; i < data.length; i++) {
        data[i].month = data[i].month + '月'
        data[i].planning = data[i].planning / 10000
        data[i].deal = data[i].deal / 10000
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

  // .antd-pro-pages-dashboard-analysis-twoColLayout {
  //   position: relative;
  //   display: flex;
  //   display: block;
  //   flex-flow: row wrap;
  // }

  // .antd-pro-pages-dashboard-analysis-salesCard {
  //   height: calc(100% - 24px);
  //   /deep/ .ant-card-head {
  //     position: relative;
  //   }
  // }

</style>
