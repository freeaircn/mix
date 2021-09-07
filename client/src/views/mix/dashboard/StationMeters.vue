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
          <a-card class="antd-pro-pages-dashboard-analysis-salesCard" :bordered="false" title="电表记录" :style="{ height: '100%' }">
            <div>
              <!-- style="width: calc(100% - 240px);" -->
              <MetersLogList
                :loading="logListLoading"
                :date="logListDate"
                :listData="logListData"
                :current.sync="logListPageIndex"
                :total="totalLogs"
                @paginationChange="onLogListPageChange"
                @query="onQueryMeterLog"
                @report="onReqDailyReport"
                @delete="onDeleteMeterLog"
              >
              </MetersLogList>
            </div>
          </a-card>
        </a-col>

        <a-col :xl="8" :lg="24" :md="24" :sm="24" :xs="24">
          <a-card :bordered="false" title="计划&成交电量" :style="{height: '100%'}">
            <PlanningKWhList
              :loading="planningKWhListLoading"
              :date="planningKWhListDate"
              :listData="planningKWhListData"
              @query="onQueryPlanningKWh"
              @update="onUpdatePlanningKWhRecord"
            >
            </PlanningKWhList>
          </a-card>
        </a-col>
      </a-row>
    </div>

    <a-card title="发电量统计" :bordered="false" :body-style="{marginBottom: '8px'}">
      <div class="extra-wrapper" slot="extra">
        <div class="extra-item">
          <a-button type="link" @click="onQueryBasicStatistic('month')">刷新</a-button>
        </div>
      </div>
      <KWhStatistic
        :loading="kWhStatisticLoading"
        :changed="kWhStatisticChanged"
        :date="kWhStatisticDate"
        :statisticListData="kWhStatisticListData"
        :thirtyDaysData="kWhStatistic30DaysData"
        :monthlyData="kWhStatisticMonthlyData"
        :quarterlyData="kWhStatisticQuarterlyData"
      >
      </KWhStatistic>
    </a-card>

    <a-card :loading="false" title="历史统计" :bordered="false" :body-style="{padding: '0', marginBottom: '8px'}">
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
import { MetersLogList, PlanningKWhList, KWhStatistic } from './components/meter'
import { mapGetters } from 'vuex'
import { getMeterLogs, saveMeterLogs, getMetersDailyReport, getMetersBasicStatistic, delMeterLogs, getPlanningKWh, updatePlanningKWhRecord } from '@/api/service'
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
    PlanningKWhList,
    KWhStatistic
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

      // 记录显示
      logListLoading: false,
      logListPageIndex: 1,
      pageSize: 6,
      totalLogs: 0,
      logListDate: '',
      logListData: [],

      // 年计划显示
      planningKWhListLoading: false,
      planningKWhListDate: '',
      // { id: 1, month: '1月', planning: 1000, deal: 1000 },
      planningKWhListData: [],

      // Basic统计显示
      kWhStatisticLoading: false,
      kWhStatisticChanged: false,
      kWhStatisticDate: '',
      kWhStatisticListData: [],
      kWhStatistic30DaysData: [],
      kWhStatisticMonthlyData: [],
      kWhStatisticQuarterlyData: [],

      // 历史统计显示
      availableYearRange

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
    this.planningKWhListDate = moment().format('YYYY-MM-DD')
  },
  mounted () {
    // 记录显示
    this.onQueryMeterLog(this.logListDate)
    this.onQueryPlanningKWh(this.planningKWhListDate)

    // 统计图表
    this.onQueryBasicStatistic()
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

          saveMeterLogs(data)
            .then(() => {
              // this.handleQueryHisLogs()
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

    // 查看电量单日简报
    onReqDailyReport (param) {
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

    onQueryBasicStatistic () {
      const query = {
        station_id: this.userInfo.belongToDeptId
      }
      this.kWhStatisticLoading = true
      getMetersBasicStatistic(query)
        .then(res => {
          this.kWhStatisticDate = res.date
          this.kWhStatisticListData = res.statisticList
          this.kWhStatistic30DaysData = res.thirtyDaysData
          this.kWhStatisticMonthlyData = res.monthlyData
          this.kWhStatisticQuarterlyData = res.quarterlyData
          //
          this.kWhStatisticLoading = false
          this.kWhStatisticChanged = !this.kWhStatisticChanged
        })
        .catch((err) => {
          this.kWhStatisticLoading = false
          if (err.response) {
          }
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
    onQueryPlanningKWh (date) {
      const query = {
        station_id: this.userInfo.belongToDeptId,
        date: date
      }
      this.planningKWhListLoading = true
      getPlanningKWh(query)
        .then(res => {
          this.planningKWhListLoading = false
          //
          this.planningKWhListDate = date
          this.planningKWhListData = this.transformPlanningKWhListData(res.data)
        })
        .catch((err) => {
          this.planningKWhListLoading = false
          this.planningKWhListData.splice(0, this.planningKWhListData.length)
          if (err.response) {
          }
        })
    },

    // 修改计划
    onUpdatePlanningKWhRecord (record) {
      const data = { ...record }
      data.station_id = this.userInfo.belongToDeptId
      // 单位换算
      data.planning = Math.floor(data.planning * 10000)
      data.deal = Math.floor(data.deal * 10000)
      updatePlanningKWhRecord(data)
        .then(() => {
          this.onQueryPlanningKWh(this.planningKWhListDate)
        })
        .catch((err) => {
          if (err.response) {
          }
        })
    },

    // 历史统计 显示区
    handleQueryHisStatistic () {
      this.$message.warning('暂不支持')
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
    transformPlanningKWhListData (data) {
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
