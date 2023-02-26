<!--
 * @Description:
 * @Author: freeair
 * @Date: 2023-02-26 08:58:37
 * @LastEditors: freeair
 * @LastEditTime: 2023-02-26 19:58:25
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
    </a-table>
  </div>
</template>

<script>
export default {
  name: 'DocTable',
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
          title: '标题',
          dataIndex: 'name',
          ellipsis: true
          // width: '80%'
          // scopedSlots: { customRender: 'title' }
        },
        {
          title: '日期',
          dataIndex: 'date',
          align: 'right'
        }
      ]
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
