<template>
  <page-header-wrapper :title="false">

    <a-card :title="userInfo.belongToDeptName" :bordered="false" :headStyle="{marginBottom: '8px'}">
    </a-card>

    <!-- <div class="antd-pro-pages-dashboard-analysis-twoColLayout" :class="!isMobile && 'desktop'"> -->
    <a-card :bordered="false" title="问题单" :body-style="{marginBottom: '8px'}">
      <!-- <a-button slot="extra" type="link" @click="handleNewDtsBlankForm">新建</a-button> -->
      <router-link slot="extra" to="/dashboard/dts/new">新建</router-link>

      <div class="table-page-search-wrapper">
        <a-form layout="inline">
          <a-row :gutter="48">
            <a-col :md="8" :sm="24">
              <a-form-item label="规则编号">
                <a-input v-model="queryParam.id" placeholder=""/>
              </a-form-item>
            </a-col>
            <a-col :md="8" :sm="24">
              <a-form-item label="使用状态">
                <a-select v-model="queryParam.status" placeholder="请选择" default-value="0">
                  <a-select-option value="0">全部</a-select-option>
                  <a-select-option value="1">关闭</a-select-option>
                  <a-select-option value="2">运行中</a-select-option>
                </a-select>
              </a-form-item>
            </a-col>
            <template v-if="advanced">
              <a-col :md="8" :sm="24">
                <a-form-item label="调用次数">
                  <a-input-number v-model="queryParam.callNo" style="width: 100%"/>
                </a-form-item>
              </a-col>
              <a-col :md="8" :sm="24">
                <a-form-item label="更新日期">
                  <a-date-picker v-model="queryParam.date" style="width: 100%" placeholder="请输入更新日期"/>
                </a-form-item>
              </a-col>
              <a-col :md="8" :sm="24">
                <a-form-item label="使用状态">
                  <a-select v-model="queryParam.useStatus" placeholder="请选择" default-value="0">
                    <a-select-option value="0">全部</a-select-option>
                    <a-select-option value="1">关闭</a-select-option>
                    <a-select-option value="2">运行中</a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>
              <a-col :md="8" :sm="24">
                <a-form-item label="使用状态">
                  <a-select placeholder="请选择" default-value="0">
                    <a-select-option value="0">全部</a-select-option>
                    <a-select-option value="1">关闭</a-select-option>
                    <a-select-option value="2">运行中</a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>
            </template>
            <a-col :md="!advanced && 8 || 24" :sm="24">
              <span class="table-page-search-submitButtons" :style="advanced && { float: 'right', overflow: 'hidden' } || {} ">
                <a-button type="primary" @click="$refs.table.refresh(true)">查询</a-button>
                <a-button style="margin-left: 8px" @click="() => this.queryParam = {}">重置</a-button>
                <a @click="toggleAdvanced" style="margin-left: 8px">
                  {{ advanced ? '收起' : '展开' }}
                  <a-icon :type="advanced ? 'up' : 'down'"/>
                </a>
              </span>
            </a-col>
          </a-row>
        </a-form>
      </div>

      <a-table
        ref="table"
        rowKey="id"
        :columns="columns"
        :data-source="listData"
        :pagination="pagination"
        :loading="loading"
      >
        <!-- :customRow="onRowClick"
          @change="handleTableChange" -->
        <span slot="type" slot-scope="text">
          {{ text | typeFilter }}
        </span>
        <span slot="level" slot-scope="text">
          {{ text | levelFilter }}
        </span>
        <span slot="action" slot-scope="text, record">
          <template>
            <a @click="onClickReport(record)">详情</a>
            <!-- <a-divider type="vertical" />
            <a @click="handleDel(record)">删除</a> -->
          </template>
        </span>
        <!-- <template slot="footer">
            注：双击某一行，查看记录的电表读数
          </template> -->
      </a-table>
    </a-card>
    <!-- </div> -->

    <a-card title="统计图表" :bordered="false" :body-style="{marginBottom: '8px'}">
      <!-- <div class="extra-wrapper" slot="extra">
        <div class="extra-item">
          <a-button type="link" @click="onQueryBasicStatistic('month')">刷新</a-button>
        </div>
      </div> -->
      xxxx
    </a-card>

  </page-header-wrapper>
</template>

<script>
// import moment from 'moment'
import { mapGetters } from 'vuex'
import { baseMixin } from '@/store/app-mixin'
// import { MetersLogList } from './components/meter'
// import { getMeterLogs } from '@/api/service'

const typeMap = {
  '1': { text: '隐患' },
  '2': { text: '故障' }
}

const levelMap = {
  '1': { text: '紧急' },
  '2': { text: '严重' },
  '3': { text: '一般' }
}

export default {
  name: 'DTS',
  mixins: [baseMixin],
  components: {

  },
  data () {
    return {
      advanced: false,
      queryParam: {},

      columns: [
        {
          title: '单号',
          dataIndex: 'ticket_id'
        },
        {
          title: '类型',
          dataIndex: 'type',
          scopedSlots: { customRender: 'type' }
        },
        {
          title: '问题描述',
          dataIndex: 'description'
        },
        {
          title: '影响程度',
          dataIndex: 'level',
          scopedSlots: { customRender: 'level' }
        },
        {
          title: '创建人',
          dataIndex: 'creator'
        },
        {
          title: '创建日期',
          dataIndex: 'created_at'
        },
        {
          title: '当前处理人',
          dataIndex: 'owner'
        },
        {
          title: '更新日期',
          dataIndex: 'updated_at'
        },
        {
          title: '状态',
          dataIndex: 'place_at'
        },
        {
          title: '操作',
          dataIndex: 'action',
          scopedSlots: { customRender: 'action' }
        }
      ],

      pagination: {
        current: 1,
        pageSize: 5,
        total: 0
      },

      loading: false,

      listData: [
        { id: '1', ticket_id: '20210923001', type: '2', description: 'xx导致xx故障', level: '1', place_at: 'posted', created_at: '2021-09-23 10:00:00', updated_at: '2021-09-23 10:00:00', creator: '小强', owner: 'XX' },
        { id: '2', ticket_id: '20210923002', type: '1', description: 'xx导致xx故障', level: '3', place_at: 'posted', created_at: '2021-09-23 10:00:00', updated_at: '2021-09-23 10:00:00', creator: '小强', owner: 'XX' }
      ]

    }
  },
  filters: {
    typeFilter (id) {
      return typeMap[id].text
    },
    levelFilter (id) {
      return levelMap[id].text
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  created () {

  },
  mounted () {

  },
  methods: {
    toggleAdvanced () {
      this.advanced = !this.advanced
    },

    // 新建
    handleNewDtsBlankForm () {
      // const uid = 0
      // this.$router.push({ path: `/app/user/save/${uid}` })
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
