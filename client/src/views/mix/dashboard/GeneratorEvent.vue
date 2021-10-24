<template>
  <page-header-wrapper :title="false">

    <a-card :title="userInfo.belongToDeptName" :bordered="false" :headStyle="{marginBottom: '8px'}">
    </a-card>

    <div class="antd-pro-pages-dashboard-analysis-twoColLayout" :class="!isMobile && 'desktop'">
      <a-row :gutter="[8,8]" type="flex" :style="{ marginBottom: '8px' }">
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

              <a-form-model-item label="补充说明" prop="description">
                <a-textarea v-model="objEvent.description"></a-textarea>
              </a-form-model-item>

              <a-form-model-item :wrapperCol="{ span: 24 }" style="text-align: center">
                <a-button type="primary" html-type="submit" :disabled="disableBtn">提交</a-button>
              </a-form-model-item>
            </a-form-model>
          </a-card>
        </a-col>

        <a-col :xl="16" :lg="24" :md="24" :sm="24" :xs="24">
          <a-card class="antd-pro-pages-dashboard-analysis-salesCard" :bordered="false" title="事件记录" :style="{ height: '100%' }">
            <!-- style="width: calc(100% - 240px);" -->
            <GeneratorEventList
              :loading="listLoading"
              :date.sync="logListDate"
              :listData="eventLogData"
              :current.sync="eventLogListPageIndex"
              :total="totalEventLog"
              @paginationChange="onLogListPageChange"
              @query="onQueryEventLog"
              @edit="onEditEventLog"
              @delete="onDelEventLog"
              @export="onExportEventFile"
            >
            </GeneratorEventList>
          </a-card>
        </a-col>
      </a-row>
    </div>

    <div v-if="!isMobile">
      <a-card :title=" '统计图表-' + genEventBasicStatDate " :bordered="false" :body-style="{marginBottom: '8px'}">
        <a-button slot="extra" type="link" @click="onQueryBasicStatistic">刷新</a-button>
        <GenEventBasicStatistic
          :loading="genEventBasicStatLoading"
          :changed="genEventBasicStatChanged"
          :statisticData="genEventBasicStatData"
        >
        </GenEventBasicStatistic>
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
    </div>

  </page-header-wrapper>
</template>

<script>
import moment from 'moment'
import { GeneratorEventList, GenEventBasicStatistic } from './components/generator'
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
    GeneratorEventList,
    GenEventBasicStatistic
  },
  data () {
    return {
      // 录入事件 表单
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
      disableBtn: false,

      // 记录 列表显示
      listLoading: false,
      eventLogListPageIndex: 1,
      pageSize: 5,
      eventLogData: [],
      totalEventLog: 0,
      logListDate: '',

      editEventModalVisible: false,
      editEventRecord: {},

      // 统计显示
      genEventBasicStatLoading: false,
      genEventBasicStatChanged: false,
      genEventBasicStatDate: '',
      genEventBasicStatData: [],

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
    this.genEventBasicStatDate = moment().format('YYYY')
  },
  mounted () {
    // 记录显示
    this.onQueryEventLog(this.logListDate, 0)

    // 统计图表
    if (!this.isMobile) {
      this.onQueryBasicStatistic()
    }
  },
  methods: {

    // 录入事件 表单
    handleNewEvent () {
      this.$refs.eventForm.validate(valid => {
        if (valid) {
          const data = { ...this.objEvent }
          data.station_id = this.userInfo.belongToDeptId
          data.creator = this.userInfo.username

          this.disableBtn = true
          saveGeneratorEvent(data)
            .then(() => {
              this.$refs.eventForm.resetFields()
              this.disableBtn = false
              this.onQueryEventLog(this.logListDate, data.generator_id)
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
    onQueryEventLog (date, gid) {
      const query = {
        station_id: this.userInfo.belongToDeptId,
        generator_id: gid,
        date: date,
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

    // 点击分页
    onLogListPageChange (param) {
      const query = { ...param }
      query.station_id = this.userInfo.belongToDeptId
      query.date = this.logListDate

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

    onDelEventLog (param) {
      delGeneratorEvent(param)
        .then(() => {
            this.onQueryEventLog(this.logListDate, param.generator_id)
          })
          //  网络异常，清空页面数据显示，防止错误的操作
          .catch((err) => {
            if (err.response) { }
          })
    },

    onEditEventLog (param) {
      param.creator = this.userInfo.username
      saveGeneratorEvent(param)
        .then(() => {
            this.onQueryEventLog(this.logListDate, param.generator_id)
          })
          //  网络异常，清空页面数据显示，防止错误的操作
          .catch((err) => {
            if (err.response) { }
          })
    },

    // 导出excel文件
    onExportEventFile (date) {
      const query = {
        station_id: this.userInfo.belongToDeptId,
        date: date
      }

      getExportGeneratorEvent(query)
        .then(res => {
          const { data, headers } = res

          const str = headers['content-type']
          if (str.indexOf('json') !== -1) {
            this.$message.warning('没有权限')
          } else {
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
          }
        })
        .catch((err) => {
          this.$message.info('导出文件失败')
          if (err.response) {
          }
        })
    },

    // 统计图表
    onQueryBasicStatistic () {
      const query = {
        year: this.genEventBasicStatDate,
        station_id: this.userInfo.belongToDeptId
      }
      this.genEventBasicStatLoading = true
      getGeneratorEventStatistic(query)
        .then(res => {
          this.genEventBasicStatData = res
          this.genEventBasicStatLoading = false
          this.genEventBasicStatChanged = !this.genEventBasicStatChanged
        })
        .catch((err) => {
          this.genEventBasicStatLoading = false
          this.genEventBasicStatData.splice(0, this.genEventBasicStatData.length)
          if (err.response) {
          }
        })
    },

    // 历史统计 显示区
    handleQueryHisStatistic () {
      this.$message.warning('暂不支持')
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
