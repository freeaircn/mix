<template>
  <page-header-wrapper :title="false">
    <!-- <a-card :bordered="false" :body-style="{marginBottom: '8px'}">
      <router-link to="/dashboard/dts/new" ><a-button type="primary" style="margin-right: 8px">新建</a-button></router-link>
    </a-card> -->
    <a-card :bordered="false" title="" :body-style="{marginBottom: '8px'}">

      <div class="table-page-search-wrapper">
        <a-form layout="inline" :label-col="labelCol" :wrapper-col="wrapperCol">
          <a-row :gutter="24">
            <a-col :md="6" :sm="24">
              <a-form-model-item label="站点" >
                <a-select v-model="searchParams.station_id" placeholder="请选择" >
                  <a-select-option v-for="d in stationItems" :key="d.id" :value="d.id">
                    {{ d.name }}
                  </a-select-option>
                </a-select>
              </a-form-model-item>
            </a-col>
            <a-col :md="6" :sm="24">
              <a-form-item label="类型">
                <a-select v-model="searchParams.type" placeholder="请选择">
                  <a-select-option v-for="d in typeItems" :key="d.id" :value="d.id">
                    {{ d.name }}
                  </a-select-option>
                </a-select>
              </a-form-item>
            </a-col>
            <a-col :md="6" :sm="24">
              <a-form-model-item label="进度" >
                <a-select mode="multiple" v-model="searchParams.place_at" placeholder="可多选" >
                  <a-select-option v-for="d in workflowItems" :key="d.alias" :value="d.alias">
                    {{ d.name }}
                  </a-select-option>
                </a-select>
              </a-form-model-item>
            </a-col>
            <template v-if="advanced">
              <a-col :md="6" :sm="24">
                <a-form-item label="影响">
                  <a-select v-model="searchParams.level" placeholder="请选择">
                    <a-select-option v-for="d in levelItems" :key="d.id" :value="d.id">
                      {{ d.name }}
                    </a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>
              <a-col :md="6" :sm="24">
                <a-form-item label="设备">
                  <a-select v-model="searchParams.device" placeholder="请选择">
                    <a-select-option v-for="d in deviceItems" :key="d.id" :value="d.id">
                      {{ d.name }}
                    </a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>
              <a-col :md="6" :sm="24">
                <a-form-item label="创建">
                  <a-range-picker v-model="range_select1" @change="onCreatedRangeChange" />
                </a-form-item>
              </a-col>
              <a-col :md="6" :sm="24">
                <a-form-item label="更新">
                  <a-range-picker v-model="range_select2" @change="onUpdatedRangeChange" />
                </a-form-item>
              </a-col>
              <a-col :md="6" :sm="24">
                <a-form-item label="解决">
                  <a-range-picker v-model="range_select3" @change="onResolvedRangeChange" />
                </a-form-item>
              </a-col>

              <a-col :md="6" :sm="24">
                <a-form-item label="原因">
                  <a-select v-model="searchParams.cause" placeholder="请选择">
                    <a-select-option v-for="d in causeItems" :key="d.id" :value="d.id">
                      {{ d.name }}
                    </a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>
              <a-col :md="6" :sm="24">
                <a-form-item label="单号">
                  <a-input v-model="searchParams.dts_id" placeholder=""/>
                </a-form-item>
              </a-col>
              <a-col :md="6" :sm="24">
                <a-form-item label="标题">
                  <a-input v-model="searchParams.title" placeholder=""/>
                </a-form-item>
              </a-col>
              <a-col :md="6" :sm="24">
                <a-form-item label="创建人">
                  <a-input v-model="searchParams.creator" placeholder=""/>
                </a-form-item>
              </a-col>
              <a-col :md="6" :sm="24">
                <a-form-item label="评分">
                  <a-select v-model="searchParams.score" placeholder="请选择">
                    <a-select-option v-for="d in scoreItems" :key="d.id" :value="d.id">
                      {{ d.name }}
                    </a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>
            </template>
            <a-col :md="!advanced && 6 || 24" :sm="24">
              <span class="table-page-search-submitButtons" :style="advanced && { float: 'right', overflow: 'hidden' } || {} ">
                <a-button type="primary" @click="handleSearchDts(searchParams)">查询</a-button>
                <a-button style="margin-left: 8px" @click="resetSearchParams">重置</a-button>
                <!-- <router-link slot="extra" to="/dashboard/dts/new"><a-button type="primary" style="margin-left: 8px;">新建</a-button></router-link> -->
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
        @change="handleTableChange"
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
            <a-divider type="vertical" />
            <a @click="onEdit(record)">编辑</a>
            <a-divider type="vertical" />
            <a @click="handleDel(record)">删除</a>
          </template>
        </span>

      </a-table>
    </a-card>
  </page-header-wrapper>
</template>

<script>
import moment from 'moment'
import store from '@/store'
import { mapGetters } from 'vuex'
import { baseMixin } from '@/store/app-mixin'
import { queryDts, delDts } from '@/api/mix/dts'

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
  data () {
    return {
      labelCol: {
        lg: { span: 7 }, sm: { span: 7 }
      },
      wrapperCol: {
        lg: { span: 10 }, sm: { span: 17 }
      },
      //
      advanced: false,
      searchParams: {
        station_id: '0',
        type: '0',
        // place_at: ['suspend', 'working'],
        place_at: [],
        level: '0',
        //
        dts_id: '',
        title: '',
        device: '0',
        creator: '',
        //
        cause: '0',
        score: '0',
        //
        created_range: ['', ''],
        updated_range: ['', ''],
        resolved_range: ['', '']
      },
      range_select1: null,
      range_select2: null,
      range_select3: null,
      //
      stationItems: [],
      workflowItems: [],
      typeItems: [],
      levelItems: [],
      causeItems: [],
      deviceItems: [],
      scoreItems: [],
      //
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
        total: 0,
        showTotal: (total) => { return '结果' + total }
      },

      loading: false,
      listData: []
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
      'userInfo', 'dtsListSearchParam'
    ])
  },
  beforeMount () {
    this.prepareSearchFunc()
  },
  mounted () {
    this.searchParams.station_id = this.userInfo.allowDefaultDeptId
    if (this.dtsListSearchParam) {
      this.advanced = this.dtsListSearchParam.advanced
      this.pagination.current = this.dtsListSearchParam.pageId
      this.searchParams = { ...this.searchParams, ...this.dtsListSearchParam.params }
      var temp = []
      this.searchParams.created_range.forEach((item) => {
        if (item !== '') {
          temp.push(moment(item))
        }
      })
      if (temp.length === 2) {
        this.range_select1 = temp
      }
      //
      temp = []
      this.searchParams.updated_range.forEach((item) => {
        if (item !== '') {
          temp.push(moment(item))
        }
      })
      if (temp.length === 2) {
        this.range_select2 = temp
      }
      //
      temp = []
      this.searchParams.resolved_range.forEach((item) => {
        if (item !== '') {
          temp.push(moment(item))
        }
      })
      if (temp.length === 2) {
        this.range_select3 = temp
      }
      //
      this.sendSearchReq(this.searchParams)
    }
    // this.sendSearchReq(this.searchParams)
  },
  beforeRouteLeave (to, from, next) {
    if (to.name === 'DtsDetails') {
      var temp1 = { advanced: this.advanced, pageId: this.pagination.current, params: this.searchParams }
      store.dispatch('setDtsListSearchParam', temp1)
    } else if (to.name === 'DtsEdit') {
      var temp2 = { advanced: this.advanced, pageId: this.pagination.current, params: this.searchParams }
      store.dispatch('setDtsListSearchParam', temp2)
    } else {
      store.dispatch('setDtsListSearchParam', null)
    }
    next()
  },
  methods: {
    toggleAdvanced () {
      this.advanced = !this.advanced
    },

    // 查询
    prepareSearchFunc () {
      const params = { resource: 'search_params' }
      queryDts(params)
        .then(res => {
          this.stationItems = res.stationItems
          this.workflowItems = res.workflowItems
          this.typeItems = res.typeItems
          this.levelItems = res.levelItems
          this.causeItems = res.causeItems
          this.deviceItems = res.deviceItems
          this.scoreItems = res.scoreItems
        })
        .catch(() => {
        })
    },

    resetSearchParams () {
      this.range_select1 = null
      this.range_select2 = null
      this.range_select3 = null
      //
      this.searchParams.station_id = this.userInfo.allowDefaultDeptId
      this.searchParams.type = '0'
      // this.searchParams.place_at = ['suspend', 'working']
      this.searchParams.place_at = []
      this.searchParams.level = '0'
      //
      this.searchParams.dts_id = ''
      this.searchParams.title = ''
      this.searchParams.device = '0'
      this.searchParams.creator = ''
      //
      this.searchParams.cause = '0'
      this.searchParams.score = '0'
      this.searchParams.created_range = ['', '']
      this.searchParams.updated_range = ['', '']
      this.searchParams.resolved_range = ['', '']
    },

    onCreatedRangeChange (date, dateString) {
      this.searchParams.created_range = dateString
    },

    onUpdatedRangeChange (date, dateString) {
      this.searchParams.updated_range = dateString
    },

    onResolvedRangeChange (date, dateString) {
      this.searchParams.resolved_range = dateString
    },

    sendSearchReq (searchParams) {
      var params = Object.assign(searchParams, {
        resource: 'list',
        limit: this.pagination.pageSize,
        offset: this.pagination.current
      })
      params.title = searchParams.title.trim()
      params.dts_id = searchParams.dts_id.trim()
      params.creator = searchParams.creator.trim()
      this.loading = true
      queryDts(params)
        .then(res => {
          const pagination = { ...this.pagination }
          pagination.total = res.total
          this.pagination = pagination
          this.listData = res.data
          //
          this.loading = false
        })
        .catch((err) => {
          this.loading = false
          this.listData.splice(0, this.listData.length)
          if (err.response) {
          }
        })
    },

    handleSearchDts () {
      this.pagination.current = 1
      this.sendSearchReq(this.searchParams)
    },

    handleTableChange (pagination) {
      const pager = { ...this.pagination }
      pager.current = pagination.current
      this.pagination = pager

      this.sendSearchReq(this.searchParams)
    },

    onQueryDetails (record) {
      if (record.dts_id) {
        const id = record.dts_id
        this.$router.push({ path: `/dashboard/dts/details/${id}` })
      }
    },

    onEdit (record) {
      if (record.dts_id) {
        const id = record.dts_id
        this.$router.push({ path: `/dashboard/dts/edit/${id}` })
      }
    },

    handleDel (record) {
      if (record.dts_id) {
        this.$confirm({
          title: '确定删除吗?',
          content: record.title,
          onOk: () => {
            const param = { dts_id: record.dts_id }
            delDts(param)
              .then(() => {
                this.handleSearchDts()
              })
              .catch(() => {
              })
          }
        })
      }
    }

  }
}
</script>
