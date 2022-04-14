<template>
  <page-header-wrapper :title="false">

    <a-card :bordered="false" title="隐患 缺陷" :body-style="{marginBottom: '8px'}">

      <div class="table-page-search-wrapper">
        <a-form layout="inline" :label-col="labelCol" :wrapper-col="wrapperCol">
          <a-row :gutter="48">
            <a-col :md="8" :sm="24">
              <a-form-model-item label="站点" >
                <a-select v-model="queryParam.station_id" placeholder="请选择" >
                  <a-select-option v-for="d in stationItems" :key="d.id" :value="d.id">
                    {{ d.name }}
                  </a-select-option>
                </a-select>
              </a-form-model-item>
            </a-col>
            <a-col :md="8" :sm="24">
              <a-form-item label="类型">
                <a-select v-model="queryParam.type" placeholder="请选择" default-value="0">
                  <a-select-option value="0">全部</a-select-option>
                  <a-select-option value="1">隐患</a-select-option>
                  <a-select-option value="2">缺陷</a-select-option>
                </a-select>
              </a-form-item>
            </a-col>
            <template v-if="advanced">
              <a-col :md="8" :sm="24">
                <a-form-item label="影响等级">
                  <a-select v-model="queryParam.level" placeholder="请选择" default-value="0">
                    <a-select-option value="0">全部</a-select-option>
                    <a-select-option value="1">紧急</a-select-option>
                    <a-select-option value="2">严重</a-select-option>
                    <a-select-option value="3">一般</a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>
              <a-col :md="8" :sm="24">
                <a-form-item label="单号">
                  <a-input v-model="queryParam.dts_id" placeholder=""/>
                </a-form-item>
              </a-col>
              <a-col :md="8" :sm="24">
                <a-form-item label="创建人">
                  <a-input v-model="queryParam.creator" placeholder=""/>
                </a-form-item>
              </a-col>
              <a-col :md="8" :sm="24">
                <a-form-model-item label="进度" >
                  <a-select v-model="queryParam.place_at" placeholder="请选择" >
                    <a-select-option v-for="d in workflowItems" :key="d.id" :value="d.alias">
                      {{ d.name }}
                    </a-select-option>
                  </a-select>
                </a-form-model-item>
              </a-col>
            </template>
            <a-col :md="!advanced && 8 || 24" :sm="24">
              <span class="table-page-search-submitButtons" :style="advanced && { float: 'right', overflow: 'hidden' } || {} ">
                <a-button type="primary" @click="onQuery(queryParam)">查询</a-button>
                <a-button style="margin-left: 8px" @click="resetQueryParam">重置</a-button>
                <a @click="toggleAdvanced" style="margin-left: 8px">
                  {{ advanced ? '收起' : '展开' }}
                  <a-icon :type="advanced ? 'up' : 'down'"/>
                </a>
              </span>
            </a-col>
          </a-row>
        </a-form>
      </div>

      <div class="table-operator">
        <router-link slot="extra" to="/dashboard/dts/new"><a-button type="primary">新建</a-button></router-link>
      </div>

      <a-table
        ref="table"
        rowKey="id"
        :columns="columns"
        :data-source="listData"
        :pagination="pagination"
        :loading="loading"
      >
        <span slot="dts_id" slot-scope="text, record">
          <template>
            <a @click="onQueryDetails(record)">{{ text }}</a>
          </template>
        </span>
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

    <a-card title="统计" :bordered="false" :body-style="{marginBottom: '8px'}">
      xxxx
    </a-card>

  </page-header-wrapper>
</template>

<script>
import { mapGetters } from 'vuex'
import { baseMixin } from '@/store/app-mixin'
import { getQueryParams, getList } from '@/api/mix/dts'

const typeMap = {
  '1': { text: '隐患' },
  '2': { text: '缺陷' }
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
      queryParam: {
        station_id: '0',
        type: '0',
        level: '0',
        dts_id: '',
        creator: '',
        place_at: 'all'
      },
      stationItems: [{ id: '0', name: '全部' }],
      workflowItems: [{ alias: 'all', name: '全部' }],
      labelCol: {
        lg: { span: 7 }, sm: { span: 7 }
      },
      wrapperCol: {
        lg: { span: 10 }, sm: { span: 17 }
      },

      columns: [
        {
          title: '单号',
          dataIndex: 'dts_id',
          scopedSlots: { customRender: 'dts_id' }
        },
        {
          title: '站点',
          dataIndex: 'station'
        },
        {
          title: '标题',
          dataIndex: 'title'
        },
        {
          title: '类型',
          dataIndex: 'type',
          scopedSlots: { customRender: 'type' }
        },
        {
          title: '影响等级',
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
  beforeMount () {
    this.beforeQuery()
  },
  mounted () {
    this.queryParam.station_id = this.userInfo.allowDefaultDeptId
    this.onQuery(this.queryParam)
  },
  methods: {
    toggleAdvanced () {
      this.advanced = !this.advanced
    },

    // 查询
    beforeQuery () {
      getQueryParams()
        .then(res => {
          res.station.forEach(element => {
            this.stationItems.push(element)
          })
          res.workflow.forEach(element => {
            this.workflowItems.push(element)
          })
        })
        .catch(() => {
          //
        })
    },

    resetQueryParam () {
      this.queryParam.station_id = this.userInfo.allowDefaultDeptId
      this.queryParam.type = '0'
      this.queryParam.level = '0'
      this.queryParam.dts_id = ''
      this.queryParam.creator = ''
      this.queryParam.place_at = 'all'
    },

    onQuery (queryParam) {
      const query = Object.assign(queryParam, {
        limit: this.pagination.pageSize,
        offset: 1
      })
      this.pagination.current = 1
      this.loading = true
      getList(query)
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
      if (record.dts_id) {
        const id = record.dts_id
        this.$router.push({ path: `/dashboard/dts/details/${id}` })
      }
    }

  }
}
</script>
