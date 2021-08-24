<template>
  <page-header-wrapper>

    <a-card :title="userInfo.belongToDeptName" :bordered="false" :headStyle="{marginBottom: '8px'}">
    </a-card>

    <div class="antd-pro-pages-dashboard-analysis-twoColLayout" :class="!isMobile && 'desktop'">
      <a-row :gutter="8" type="flex" :style="{ marginBottom: '8px' }">
        <a-col :xl="8" :lg="24" :md="24" :sm="24" :xs="24" >
          <a-card :bordered="false" title="事件录入" :style="{height: '100%'}">
            <a-form-model
              ref="eventForm"
              :model="objEvent"
              :rules="rules"
              :label-col="labelCol"
              :wrapper-col="wrapperCol"
              @submit="handleNewEvent"
              @submit.native.prevent
            >
              <a-form-model-item label="机组" prop="generator_id">
                <a-select v-model="objEvent.generator_id" placeholder="请选择">
                  <a-select-option value="1">1G</a-select-option>
                  <a-select-option value="2">2G</a-select-option>
                  <a-select-option value="3">3G</a-select-option>
                </a-select>
              </a-form-model-item>

              <a-form-model-item label="事件" prop="event">
                <a-select v-model="objEvent.event" placeholder="请选择">
                  <a-select-option value="1">停机</a-select-option>
                  <a-select-option value="2">开机</a-select-option>
                </a-select>
              </a-form-model-item>

              <a-form-model-item label="日期/时间" prop="event_at">
                <a-date-picker v-model="objEvent.event_at" valueFormat="YYYY-MM-DD HH:mm:ss" show-time placeholder="请选择" />
              </a-form-model-item>

              <a-form-model-item label="说明" prop="description">
                <a-textarea v-model="objEvent.description"></a-textarea>
              </a-form-model-item>

              <a-form-model-item :wrapperCol="{ span: 24 }" style="text-align: center">
                <a-button type="primary" html-type="submit">提交</a-button>
              </a-form-model-item>
            </a-form-model>
          </a-card>
        </a-col>

        <a-col :xl="16" :lg="24" :md="24" :sm="24" :xs="24">
          <a-card class="antd-pro-pages-dashboard-analysis-salesCard" :loading="listLoading" :bordered="false" title="事件查询" :style="{ height: '100%' }">
            <a-form-model ref="hisEventForm" layout="inline" :model="hisEvent" @submit.native.prevent>
              <a-form-model-item>
                <a-date-picker v-model="hisEvent.startAt" valueFormat="YYYY-MM-DD" placeholder="开始日期"/>
              </a-form-model-item>

              <a-form-model-item>
                <a-date-picker v-model="hisEvent.endAt" valueFormat="YYYY-MM-DD" placeholder="结束日期"/>
              </a-form-model-item>

              <a-form-model-item>
                <a-select v-model="hisEvent.generatorId" placeholder="机组" allowClear style="width: 75px">
                  <a-select-option value="1">1G</a-select-option>
                  <a-select-option value="2">2G</a-select-option>
                  <a-select-option value="3">3G</a-select-option>
                </a-select>
              </a-form-model-item>

              <a-form-model-item>
                <a-button type="primary" @click="handleQueryHisEvent">查询</a-button>
              </a-form-model-item>

              <a-form-model-item>
                <a-button @click="handleExportHisEvent">导出</a-button>
              </a-form-model-item>
            </a-form-model>

            <div>
              <!-- style="width: calc(100% - 240px);" -->
              <GeneratorEventList
                :loading="listLoading"
                :listData="eventLogData"
                :current.sync="eventLogListPageIndex"
                :total="totalEventLog"
                @reqData="onReqEventLog"
                @reqEdit="onReqEditEventLog"
                @reqDelete="onReqDelEventLog"
              >
              </GeneratorEventList>
            </div>
          </a-card>
        </a-col>
      </a-row>
    </div>

    <a-card :title="currentYear + '年'" :loading="barLoading" :bordered="false" :body-style="{marginBottom: '8px'}">
      <a-button slot="extra" type="link" @click="queryThisYearStatistic">刷新</a-button>
      <div class="current-year-card">
        <div class="current-year-card-content">
          <a-row :gutter="8">
            <a-col :sm="24" :md="12" :xl="12" :style="{ marginBottom: '8px' }">
              <chart-card2 :loading="barLoading" title="运行时长（小时）">
                <a-tooltip slot="action">
                  <template slot="title">
                    截至日期：
                    <div>1G：{{ barStatisticLatestTime[0] }}</div>
                    <div>2G：{{ barStatisticLatestTime[1] }}</div>
                    <div>3G：{{ barStatisticLatestTime[2] }}</div>
                  </template>
                  <a-icon type="info-circle-o" />
                </a-tooltip>
                <div>
                  <mini-horizontal-bar :data="barStatisticRunTotalTime" scaleAlias="小时"/>
                </div>
              </chart-card2>
            </a-col>
            <a-col :sm="24" :md="12" :xl="12" :style="{ marginBottom: '8px' }">
              <chart-card2 :loading="barLoading" title="开机次数">
                <div>
                  <mini-horizontal-bar :data="barStatisticRunNumber" color="#5ab1ef" scaleAlias="次数"/>
                </div>
              </chart-card2>
            </a-col>
          </a-row>
        </div>
      </div>
    </a-card>

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
import { ChartCard2, MiniHorizontalBar } from '@/components'
import { GeneratorEventList } from './components'
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
  name: 'GeneratorEvent',
  mixins: [baseMixin],
  components: {
    ChartCard2,
    MiniHorizontalBar,
    GeneratorEventList
  },
  data () {
    return {
      loading: true,
      // 录入事件 表单区域
      objEvent: {
        station_id: null,
        event_at: ''
      },
      labelCol: {
        lg: { span: 7 }, sm: { span: 7 }
      },
      wrapperCol: {
        lg: { span: 10 }, sm: { span: 17 }
      },
      rules: {
        generator_id: [{ required: true, message: '请选择机组', trigger: 'change' }],
        event: [{ required: true, message: '请选择事件名称', trigger: 'change' }],
        event_at: [{ required: true, message: '请选择日期和时间', trigger: ['change', 'blur'] }]
      },
      // 事件 列表显示区
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
    line-height: 55px;
    padding-right: 24px;

    .extra-item {
      display: inline-block;
      margin-right: 24px;

      a {
        margin-left: 24px;
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
