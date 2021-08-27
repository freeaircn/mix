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
              @submit="handleNewEvent"
              @submit.native.prevent
            >
              <a-form-model-item :wrapper-col="{ lg: { span: 14, offset: 4 }, sm: { span: 14 } }">
                <div style="font-size: 16px; font-weight:bold">{{ logMeterSteps[logMeterStepIndex].title }}</div>
              </a-form-model-item>
              <a-form-model-item label="日期" prop="log_at">
                <a-date-picker v-model="metersForm.log_at" valueFormat="YYYY-MM-DD" placeholder="请选择" />
              </a-form-model-item>

              <a-form-model-item v-show="logMeterStepIndex == 0" label="正向有功" prop="FAK">
                <a-input v-model="metersForm.lineMainFAK" placeholder="线路主表" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 0" label="反向有功" prop="BAK">
                <a-input v-model="metersForm.lineMainBAK" placeholder="线路主表" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 0" label="正向无功" prop="FRK">
                <a-input v-model="metersForm.lineMainFRK" placeholder="线路主表" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 0" label="反向无功" prop="BRK">
                <a-input v-model="metersForm.lineMainBRK" placeholder="线路主表" />
              </a-form-model-item>

              <a-form-model-item v-show="logMeterStepIndex == 1" label="正向有功" prop="FAK">
                <a-input v-model="metersForm.lineSubFAK" placeholder="线路副表" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 1" label="反向有功" prop="BAK">
                <a-input v-model="metersForm.lineSubBAK" placeholder="线路副表" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 1" label="正向无功" prop="FRK">
                <a-input v-model="metersForm.lineSubFRK" placeholder="线路副表" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 1" label="反向无功" prop="BRK">
                <a-input v-model="metersForm.lineSubBRK" placeholder="线路副表" />
              </a-form-model-item>

              <a-form-model-item v-show="logMeterStepIndex == 2" label="正向有功" prop="FAK">
                <a-input v-model="metersForm.firstGenFAK" placeholder="1G" />
              </a-form-model-item>
              <!-- <a-form-model-item v-show="logMeterStepIndex == 2" label="反向有功" prop="BAK">
                    <a-input v-model="metersForm.firstGenBAK" placeholder="1G" />
                  </a-form-model-item> -->
              <a-form-model-item v-show="logMeterStepIndex == 2" label="正向无功" prop="FRK">
                <a-input v-model="metersForm.firstGenFRK" placeholder="1G" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 2" label="反向无功" prop="BRK">
                <a-input v-model="metersForm.firstGenBRK" placeholder="1G" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 2" label="高峰" prop="Peak">
                <a-input v-model="metersForm.firstGenPeak" placeholder="1G" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 2" label="低谷" prop="Valley">
                <a-input v-model="metersForm.firstGenValley" placeholder="1G" />
              </a-form-model-item>

              <a-form-model-item v-show="logMeterStepIndex == 3" label="正向有功" prop="FAK">
                <a-input v-model="metersForm.secondGenFAK" placeholder="2G" />
              </a-form-model-item>
              <!-- <a-form-model-item v-show="logMeterStepIndex == 3" label="反向有功" prop="BAK">
                    <a-input v-model="metersForm.secondGenBAK" placeholder="2G" />
                  </a-form-model-item> -->
              <a-form-model-item v-show="logMeterStepIndex == 3" label="正向无功" prop="FRK">
                <a-input v-model="metersForm.secondGenFRK" placeholder="2G" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 3" label="反向无功" prop="BRK">
                <a-input v-model="metersForm.secondGenBRK" placeholder="2G" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 3" label="高峰" prop="Peak">
                <a-input v-model="metersForm.secondGenPeak" placeholder="2G" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 3" label="低谷" prop="Valley">
                <a-input v-model="metersForm.secondGenValley" placeholder="2G" />
              </a-form-model-item>

              <a-form-model-item v-show="logMeterStepIndex == 4" label="正向有功" prop="FAK">
                <a-input v-model="metersForm.thirdGenFAK" placeholder="3G" />
              </a-form-model-item>
              <!-- <a-form-model-item v-show="logMeterStepIndex == 4" label="反向有功" prop="BAK">
                    <a-input v-model="metersForm.thirdGenBAK" placeholder="3G" />
                  </a-form-model-item> -->
              <a-form-model-item v-show="logMeterStepIndex == 4" label="正向无功" prop="FRK">
                <a-input v-model="metersForm.thirdGenFRK" placeholder="3G" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 4" label="反向无功" prop="BRK">
                <a-input v-model="metersForm.thirdGenBRK" placeholder="3G" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 4" label="高峰" prop="Peak">
                <a-input v-model="metersForm.thirdGenPeak" placeholder="3G" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 4" label="低谷" prop="Valley">
                <a-input v-model="metersForm.thirdGenValley" placeholder="3G" />
              </a-form-model-item>

              <a-form-model-item v-show="logMeterStepIndex == 5" label="正向有功" prop="FAK">
                <a-input v-model="metersForm.firstTransformerFAK" placeholder="1#厂变" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 5" label="反向有功" prop="BAK">
                <a-input v-model="metersForm.firstTransformerBAK" placeholder="1#厂变" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 5" label="正向无功" prop="FRK">
                <a-input v-model="metersForm.firstTransformerFRK" placeholder="1#厂变" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 5" label="反向无功" prop="BRK">
                <a-input v-model="metersForm.firstTransformerBRK" placeholder="1#厂变" />
              </a-form-model-item>

              <a-form-model-item v-show="logMeterStepIndex == 6" label="正向有功" prop="FAK">
                <a-input v-model="metersForm.secondTransformerFAK" placeholder="2#厂变" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 6" label="反向有功" prop="BAK">
                <a-input v-model="metersForm.secondTransformerBAK" placeholder="2#厂变" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 6" label="正向无功" prop="FRK">
                <a-input v-model="metersForm.secondTransformerFRK" placeholder="2#厂变" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 6" label="反向无功" prop="BRK">
                <a-input v-model="metersForm.secondTransformerBRK" placeholder="2#厂变" />
              </a-form-model-item>

              <a-form-model-item v-show="logMeterStepIndex == 7" label="正向有功" prop="FAK">
                <a-input v-model="metersForm.thirdTransformerFAK" placeholder="3#厂变" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 7" label="反向有功" prop="BAK">
                <a-input v-model="metersForm.thirdTransformerBAK" placeholder="3#厂变" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 7" label="正向无功" prop="FRK">
                <a-input v-model="metersForm.thirdTransformerFRK" placeholder="3#厂变" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 7" label="反向无功" prop="BRK">
                <a-input v-model="metersForm.thirdTransformerBRK" placeholder="3#厂变" />
              </a-form-model-item>

              <a-form-model-item v-show="logMeterStepIndex == 8" label="正向有功" prop="FAK">
                <a-input v-model="metersForm.fourthTransformerFAK" placeholder="隔离变" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 8" label="反向有功" prop="BAK">
                <a-input v-model="metersForm.fourthTransformerBAK" placeholder="隔离变" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 8" label="正向无功" prop="FRK">
                <a-input v-model="metersForm.fourthTransformerFRK" placeholder="隔离变" />
              </a-form-model-item>
              <a-form-model-item v-show="logMeterStepIndex == 8" label="反向无功" prop="BRK">
                <a-input v-model="metersForm.fourthTransformerBRK" placeholder="隔离变" />
              </a-form-model-item>

              <a-form-model-item :wrapper-col="{ lg: { span: 14, offset: 4 }, sm: { span: 14 } }">
                <div>
                  <a-button v-if="logMeterStepIndex < logMeterSteps.length - 1" type="primary" block @click="handleLogMeterStepNext">下一步</a-button>
                  <a-button v-if="logMeterStepIndex == logMeterSteps.length - 1" type="primary" block @click="$message.success('Processing complete!')">提交</a-button>
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
            <a-form-model ref="hisEventForm" layout="inline" :model="hisEvent" @submit.native.prevent>
              <a-form-model-item>
                <a-date-picker v-model="hisEvent.startAt" valueFormat="YYYY-MM-DD" placeholder="开始日期"/>
              </a-form-model-item>

              <a-form-model-item>
                <a-date-picker v-model="hisEvent.endAt" valueFormat="YYYY-MM-DD" placeholder="结束日期"/>
              </a-form-model-item>

              <a-form-model-item>
                <a-button type="primary" @click="handleQueryHisEvent">查询</a-button>
              </a-form-model-item>

              <!-- <a-form-model-item>
                <a-button @click="handleExportHisEvent">导出</a-button>
              </a-form-model-item> -->
            </a-form-model>

            <div>
              <!-- style="width: calc(100% - 240px);" -->
              <MetersLogList
                :loading="listLoading"
                :listData="eventLogData"
                :current.sync="eventLogListPageIndex"
                :total="totalEventLog"
                @reqData="onReqEventLog"
                @reqEdit="onReqEditEventLog"
                @reqDelete="onReqDelEventLog"
              >
              </MetersLogList>
            </div>
          </a-card>
        </a-col>

        <a-col :xl="8" :lg="24" :md="24" :sm="24" :xs="24">
          <a-card :bordered="false" :title="currentYear + '年计划'" :style="{height: '100%'}">
            <!-- <a slot="extra" href="#">修改</a> -->
            <a-button type="primary">修改</a-button>
            <PlanKWhList
              :loading="listLoading"
              :listData="planKWHList"
              :current.sync="eventLogListPageIndex"
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
import { MetersLogList, PlanKWhList, RealKWhChart } from './components/meter'
import { mapGetters } from 'vuex'
import { getGeneratorEvent, saveGeneratorEvent, getGeneratorEventStatistic, delGeneratorEvent, getExportGeneratorEvent } from '@/api/service'
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
        station_id: null,
        log_at: ''
      },
      labelCol: {
        lg: { span: 4 }, sm: { span: 4 }
      },
      wrapperCol: {
        lg: { span: 14 }, sm: { span: 14 }
      },
      rules: {
        generator_id: [{ required: true, message: '请选择机组', trigger: 'change' }],
        event: [{ required: true, message: '请选择事件名称', trigger: 'change' }],
        event_at: [{ required: true, message: '请选择日期和时间', trigger: ['change', 'blur'] }]
      },

      // 列表显示区
      hisEvent: {
        startAt: '',
        endAt: ''
        // generatorId: null
      },

      listLoading: false,
      eventLogListPageIndex: 1,
      pageSize: 5,
      eventLogData: [],
      totalEventLog: 0,

      editEventModalVisible: false,
      editEventRecord: {},

      // 年计划显示区
      planKWHList: [
        { id: 1, month: '1月', value: 1000 },
        { id: 2, month: '2月', value: 2000 },
        { id: 3, month: '3月', value: 3000 },
        { id: 4, month: '4月', value: 4000 },
        { id: 5, month: '5月', value: 5000 },
        { id: 6, month: '6月', value: 6000 },
        { id: 7, month: '7月', value: 7000 },
        { id: 8, month: '8月', value: 8000 },
        { id: 9, month: '9月', value: 9000 },
        { id: 10, month: '10月', value: 10000 },
        { id: 11, month: '11月', value: 11000 },
        { id: 12, month: '12月', value: 12000 }
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
    setTimeout(() => {
      this.loading = !this.loading
    }, 1000)

    // 事件 列表显示区
    const start = '2011-01-01'
    const end = moment().format('YYYY-MM-DD')
    const query = {
      station_id: this.userInfo.belongToDeptId,
      generator_id: 9,
      start: start,
      end: end,
      limit: this.pageSize,
      offset: 1
    }
    this.listLoading = true
    getGeneratorEvent(query)
      .then(res => {
        this.listLoading = false
        //
        this.totalEventLog = res.total
        this.eventLogData = res.data
      })
      .catch((err) => {
        this.listLoading = false
        this.eventLogData.splice(0, this.eventLogData.length)
        if (err.response) {
        }
      })

    // 统计 Bar显示区
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

    // 历史统计显示区
  },
  methods: {

    // 录入事件 表单区域

    handleLogMeterStepNext () {
      this.logMeterStepIndex++
    },

    handleLogMeterStepPrev () {
      this.logMeterStepIndex--
    },

    handleNewEvent () {
      this.$refs.eventForm.validate(valid => {
        if (valid) {
          const data = { ...this.objEvent }
          data.station_id = this.userInfo.belongToDeptId
          data.creator = this.userInfo.username

          saveGeneratorEvent(data)
            .then(() => {
              this.handleQueryHisEvent()
              this.queryThisYearStatistic()
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
    handleQueryHisEvent () {
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

      const query = {
        station_id: this.userInfo.belongToDeptId,
        generator_id: gid,
        start: start,
        end: end,
        limit: this.pageSize,
        offset: 1
      }

      this.eventLogListPageIndex = 1
      this.listLoading = true
      getGeneratorEvent(query)
        .then(res => {
          this.listLoading = false
          //
          this.totalEventLog = res.total
          this.eventLogData = res.data
        })
        .catch((err) => {
          this.listLoading = false
          this.eventLogData.splice(0, this.eventLogData.length)
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
      getGeneratorEvent(query)
        .then(res => {
          this.listLoading = false
          //
          this.totalEventLog = res.total
          this.eventLogData = res.data
        })
        .catch((err) => {
          this.listLoading = false
          this.eventLogData.splice(0, this.eventLogData.length)
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
      saveGeneratorEvent(param)
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
