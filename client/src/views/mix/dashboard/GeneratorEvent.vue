<template>
  <page-header-wrapper>

    <a-card :loading="loading" :bordered="false" :body-style="{padding: '0', marginBottom: '8px'}">
      <div class="current-year-card">
        <div class="current-year-card-header">
          {{ currentYear }}年 - {{ userInfo.belongToDeptName }}
        </div>
        <div class="current-year-card-content">
          <a-row :gutter="8">
            <a-col :sm="24" :md="12" :xl="6" :style="{ marginBottom: '8px' }">
              <chart-card2 :loading="loading" title="开机次数">
                <div>
                  <mini-horizontal-bar :data="singleData" color="#5ab1ef"/>
                </div>
              </chart-card2>
            </a-col>

            <a-col :sm="24" :md="12" :xl="6" :style="{ marginBottom: '8px' }">
              <chart-card2 :loading="loading" title="运行时长">
                <div>
                  <mini-horizontal-bar :data="singleData" />
                </div>
              </chart-card2>
            </a-col>

            <a-col :sm="24" :md="12" :xl="6" :style="{ marginBottom: '8px' }">
              <chart-card2 :loading="loading" title="检修次数">
                <div>
                  <mini-horizontal-bar :data="singleData" color="#ffb980"/>
                </div>
              </chart-card2>
            </a-col>

            <a-col :sm="24" :md="12" :xl="6" :style="{ marginBottom: '8px' }">
              <chart-card2 :loading="loading" title="检修时长">
                <div>
                  <mini-horizontal-bar :data="singleData" color="#f5994e"/>
                </div>
              </chart-card2>
            </a-col>
          </a-row>
        </div>
      </div>
    </a-card>

    <div class="antd-pro-pages-dashboard-analysis-twoColLayout" :class="!isMobile && 'desktop'">
      <a-row :gutter="8" type="flex" :style="{ marginBottom: '8px' }">
        <a-col :xl="8" :lg="24" :md="24" :sm="24" :xs="24">
          <a-card :loading="loading" :bordered="false" title="事件录入" :style="{height: '100%'}">
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
                  <a-select-option value="3">检修开始</a-select-option>
                  <a-select-option value="4">检修结束</a-select-option>
                </a-select>
              </a-form-model-item>

              <a-form-model-item label="日期/时间" prop="timestamp">
                <a-date-picker valueFormat="YYYY-MM-DD HH:mm:ss" show-time placeholder="请选择" @change="handleEventTimestampChange" @ok="handleClickEventTimestamp" />
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
          <a-card class="antd-pro-pages-dashboard-analysis-salesCard" :loading="loading" :bordered="false" title="事件查询" :style="{ height: '100%' }">
            <div >
              <a-range-picker :style="{width: '256px'}" />
              <span style="margin: 0 18px"><a-button type="primary" @click="handleQueryHisEvent">查询</a-button></span>
            </div>

            <div>
              <!-- style="width: calc(100% - 240px);" -->
              <div>

              </div>

            </div>
          </a-card>
        </a-col>
      </a-row>
    </div>

    <a-card :loading="loading" title="历史数据" :bordered="false" :body-style="{padding: '0', marginBottom: '8px'}">
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
import { mapGetters } from 'vuex'
import { saveGeneratorEvent } from '@/api/service'
import { baseMixin } from '@/store/app-mixin'

const singleData = [
  { name: '1G', value: 10 },
  { name: '2G', value: 20 },
  { name: '3G', value: 15 }
]

const availableYearRange = []
for (let i = 2019; i <= moment().year(); i++) {
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
    MiniHorizontalBar
  },
  data () {
    return {
      availableYearRange,
      loading: true,
      //
      currentYear: moment().year(),

      //
      singleData,
      objEvent: {
        station_id: null,
        timestamp: null
      },
      labelCol: {
        lg: { span: 7 }, sm: { span: 7 }
      },
      wrapperCol: {
        lg: { span: 10 }, sm: { span: 17 }
      },
      //
      rules: {
        generator_id: [{ required: true, message: '请选择机组', trigger: 'change' }],
        event: [{ required: true, message: '请选择事件名称', trigger: 'change' }],
        timestamp: [{ required: true, message: '请选择日期和时间', trigger: 'change' }]
      }

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
  },
  methods: {
    handleEventTimestampChange (value, dateString) {
      if (dateString) {
        this.objEvent.timestamp = moment(dateString).unix()
      }
    },

    handleClickEventTimestamp (value) {
      if (value) {
        this.objEvent.timestamp = moment(value).unix()
      }
    },

    handleNewEvent () {
      this.$refs.eventForm.validate(valid => {
        if (valid) {
          const data = { ...this.objEvent }
          data.station_id = this.userInfo.belongToDeptId
          data.creator = this.userInfo.username
          saveGeneratorEvent(data)
            .then(() => {

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

    handleQueryHisEvent () {
      console.log('QueryHisEvent')
    },

    handleQueryHisStatistic () {
      console.log('QueryHisStatistic')
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

  .current-year-card-header {
    font-size: 16px;
    line-height: 22px;
    border-bottom: 1px solid #e8e8e8;
    padding: 8px;
    margin-bottom: 8px;
  }

</style>
