<template>
  <page-header-wrapper :title="false">

    <a-card :title="userInfo.belongToDeptName" :bordered="false" :headStyle="{marginBottom: '8px'}">
    </a-card>

    <a-card :bordered="false" title="问题单" :body-style="{marginBottom: '8px'}">
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
                <a-button type="primary" @click="onQuery">查询</a-button>
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
        <span slot="type" slot-scope="text">
          {{ text | typeFilter }}
        </span>
        <span slot="level" slot-scope="text">
          {{ text | levelFilter }}
        </span>
        <span slot="action" slot-scope="text, record">
          <template>
            <a @click="onQueryDetails(record)">详情</a>
            <!-- <a-divider type="vertical" />
            <a @click="handleDel(record)">删除</a> -->
          </template>
        </span>

      </a-table>
    </a-card>

    <a-card title="统计分析" :bordered="false" :body-style="{marginBottom: '8px'}">
      xxxx
    </a-card>

  </page-header-wrapper>
</template>

<script>
import { mapGetters } from 'vuex'
import { baseMixin } from '@/store/app-mixin'
import { getDtsList } from '@/api/mix/dts'

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
  // components: {

  // },
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
          title: '标题',
          dataIndex: 'title'
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
          title: '处理人',
          dataIndex: 'handler'
        },
        {
          title: '更新日期',
          dataIndex: 'updated_at'
        },
        {
          title: '进度',
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
        pageSize: 8,
        total: 0
      },

      loading: false,
      listData: [ ]
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
  // created () {

  // },
  mounted () {
    this.onQuery()
  },
  methods: {
    toggleAdvanced () {
      this.advanced = !this.advanced
    },

    // 查询
    onQuery () {
      const query = {
        station_id: this.userInfo.belongToDeptId,
        limit: this.pagination.pageSize,
        offset: 1
      }
      this.pagination.current = 1
      this.loading = true
      getDtsList(query)
        .then(res => {
          this.loading = false
          //
          this.pagination.total = res.total
          this.listData = res.data
        })
        .catch((err) => {
          this.loading = false
          this.listData.splice(0, this.listData.length)
          if (err.response) {
          }
        })
    },

    onQueryDetails (record) {
      if (record.ticket_id) {
        const ticketId = record.ticket_id
        this.$router.push({ path: `/dashboard/dts/details/${ticketId}` })
      }
    }

  }
}
</script>
