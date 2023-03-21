<!--
 * @Description:
 * @Author: freeair
 * @Date: 2023-02-26 08:58:37
 * @LastEditors: freeair
 * @LastEditTime: 2023-03-21 23:56:01
-->
<template>
  <div class="wrapper">
    <a-table
      ref="table"
      rowKey="id"
      size="small"
      :showHeader="false"
      :bordered="false"
      :pagination="false"
      :columns="columns"
      :data-source="listData"
      :loading="loading"
    >
      <!-- <span slot="file_org_name" slot-scope="text, record">
          <template>
            <a @click="handleQueryDetails(record)">{{ text }}</a>
          </template>
        </span> -->
      <span slot="action" slot-scope="text, record">
        <template>
          <a @click="handlePreviewFile(record)">预览</a>
          <a-divider type="vertical" />
          <a @click="handleDownloadFile(record)">下载</a>
          <a-divider type="vertical" />
          <a @click="handleDeleteFile(record)">删除</a>
        </template>
      </span>
    </a-table>
  </div>
</template>

<script>
export default {
  name: 'FilesTable',
  props: {
    listData: {
      type: Array,
      default: null
    }
  },
  data () {
    return {
      loading: false,
      columns: [
        {
          title: '名称',
          dataIndex: 'file_org_name',
          ellipsis: true
          // width: '80%'
          // scopedSlots: { customRender: 'title' }
        },
        // {
        //   title: '日期',
        //   dataIndex: 'date',
        //   align: 'right'
        // },
        {
          title: '操作',
          dataIndex: 'action',
          // align: 'right',
          scopedSlots: { customRender: 'action' }
        }
      ]
    }
  },
  methods: {
    handlePreviewFile (record) {
      console.log(record)
    },

    handleDownloadFile (record) {
      console.log(record)
    },

    handleDeleteFile (record) {
      console.log(record)
    }
  }
}
</script>

<style lang="less" scoped>
  .wrapper {
    /deep/ .ant-table-small {
      border-top-width: 0;
      border-right-width: 0;
      border-bottom-width: 0;
      border-left-width: 0;
    }

    /deep/ .ant-table-tbody > tr > td {
      border-bottom-width: 0;
    }
  }
</style>
