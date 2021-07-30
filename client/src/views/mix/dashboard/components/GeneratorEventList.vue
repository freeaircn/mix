<template>
  <a-table
    ref="table"
    rowKey="id"
    :columns="columns"
    :data-source="listData"
    :pagination="pagination"
    :loading="loading"
    @change="handleTableChange"
  >
    <span slot="serial" slot-scope="text, record, index">
      {{ index + 1 }}
    </span>
    <span slot="generator_id" slot-scope="text">
      {{ text | generatorIdFilter }}
    </span>
    <span slot="event" slot-scope="text">
      {{ text | eventFilter }}
    </span>
    <span slot="timestamp" slot-scope="text">
      {{ text | timestampFilter }}
    </span>
    <span slot="action" slot-scope="text, record">
      <template>
        <a @click="handleEdit(record)">修改</a>
        <a-divider type="vertical" />
        <a @click="handleDel(record)">删除</a>
      </template>
    </span>
  </a-table>
</template>

<script>
import moment from 'moment'

const columns = [
  {
    title: '#',
    scopedSlots: { customRender: 'serial' }
  },
  {
    title: '机组',
    dataIndex: 'generator_id',
    scopedSlots: { customRender: 'generator_id' }
  },
  {
    title: '事件',
    dataIndex: 'event',
    scopedSlots: { customRender: 'event' }
  },
  {
    title: '时间',
    dataIndex: 'timestamp',
    scopedSlots: { customRender: 'timestamp' }
  },
  {
    title: '记录人',
    dataIndex: 'creator'
  },
  {
    title: '说明',
    dataIndex: 'description'
  },
  {
    title: '操作',
    dataIndex: 'action',
    width: '150px',
    scopedSlots: { customRender: 'action' }
  }
]

const generatorIdMap = {
  1: { text: '1G' },
  2: { text: '2G' },
  3: { text: '3G' },
  4: { text: '4G' }
}

const eventMap = {
  1: { text: '停机' },
  2: { text: '开机' },
  3: { text: '检修开始' },
  4: { text: '检修结束' }
}

export default {
  name: 'GeneratorEventList',
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    listData: {
      type: Array,
      default: () => []
    },
    current: {
      type: Number,
      default: 1
    },
    pageSize: {
      type: Number,
      default: 5
    },
    total: {
      type: Number,
      default: 0
    }
  },
  data () {
    this.columns = columns
    return {
      // 事件列表显示区
      pagination: {
        current: 1,
        pageSize: 5,
        total: 0
      }
    }
  },
  filters: {
    generatorIdFilter (type) {
      return generatorIdMap[type].text
    },
    eventFilter (type) {
      return eventMap[type].text
    },
    timestampFilter (value) {
      return moment.unix(value).format('YYYY-MM-DD HH:MM:ss')
    }
  },
  watch: {
    current: {
      handler: function (val) {
        this.pagination.current = val
      },
      immediate: true
    },
    pageSize: {
      handler: function (val) {
        this.pagination.pageSize = val
      },
      immediate: true
    },
    total: {
      handler: function (val) {
        this.pagination.total = val
      },
      immediate: true
    }
  },
  methods: {

    handleTableChange (value) {
      this.pagination.current = value.current

      // 向父组件发消息，更新数据区
      const queryParam = {
        limit: this.pagination.pageSize,
        offset: this.pagination.current
      }
      this.$emit('update:current', value.current)
      this.$emit('reqData', queryParam)
    },

    handleEdit (record) {
      console.log('edit', record)
    },

    // 删除请求
    handleDel (record) {
      console.log('del', record)
      // this.$confirm({
      //   title: '确定删除吗?',
      //   content: '删除 ' + record.username,
      //   onOk: () => {
      //     delUser(record.id)
      //       .then(() => {
      //         // 结果同步至table
      //         if (record.id) {
      //           this.listData.forEach(function (element, index, array) {
      //             if (element.id === record.id) {
      //               array.splice(index, 1)
      //             }
      //           })
      //         }
      //       })
      //       //  网络异常，清空页面数据显示，防止错误的操作
      //       .catch((err) => {
      //         if (err.response) {
      //           this.listData.splice(0, this.listData.length)
      //         }
      //       })
      //   }
      // })
    }
  }
}
</script>
