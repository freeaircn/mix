<template>
  <div>
    <a-tabs default-active-key="1" tabPosition="left" >
      <a-tab-pane key="1" tab="线路">
        <a-table
          ref="tab1Table"
          rowKey="id"
          :columns="tab1Columns"
          :data-source="tab1Data"
          :pagination="false"
        >
        </a-table>
      </a-tab-pane>
      <a-tab-pane key="2" tab="机组">
        <a-table
          ref="tab2Table"
          rowKey="id"
          :columns="tab2Columns"
          :data-source="tab2Data"
          :pagination="false"
        >
        </a-table>
      </a-tab-pane>
      <a-tab-pane key="3" tab="厂变">
        <a-table
          ref="tab3Table"
          rowKey="id"
          :columns="tab3Columns"
          :data-source="tab3Data"
          :pagination="false"
        >
        </a-table>
      </a-tab-pane>
      <a-tab-pane key="4" tab="隔离">
        <a-table
          ref="tab4Table"
          rowKey="id"
          :columns="tab4Columns"
          :data-source="tab4Data"
          :pagination="false"
        >
        </a-table>
      </a-tab-pane>
    </a-tabs>
  </div>
</template>

<script>

export default {
  name: 'MetersLogDetail',
  props: {
    tab1Data: {
      type: Array,
      default: () => []
    },
    tab2Data: {
      type: Array,
      default: () => []
    },
    tab3Data: {
      type: Array,
      default: () => []
    },
    tab4Data: {
      type: Array,
      default: () => []
    }
  },
  data () {
    return {
      //
      tab1Columns: [
        {
          title: '#',
          dataIndex: 'rowHeader'
        },
        {
          title: '线路主表',
          dataIndex: 'value1'
        },
        {
          title: '线路副表',
          dataIndex: 'value2'
        }
      ],

      tab2Columns: [
        {
          title: '#',
          dataIndex: 'rowHeader'
        },
        {
          title: '1G',
          dataIndex: 'value1'
        },
        {
          title: '2G',
          dataIndex: 'value2'
        },
        {
          title: '3G',
          dataIndex: 'value3'
        }
      ],

      tab3Columns: [
        {
          title: '#',
          dataIndex: 'rowHeader'
        },
        {
          title: '厂变1',
          dataIndex: 'value1'
        },
        {
          title: '厂变2',
          dataIndex: 'value2'
        },
        {
          title: '厂变3',
          dataIndex: 'value3'
        }
      ],

      tab4Columns: [
        {
          title: '#',
          dataIndex: 'rowHeader'
        },
        {
          title: '隔离变',
          dataIndex: 'value1'
        }
      ]

      // tab1Data: [
      //   { id: 1, rowHeader: '正向有功', value1: '1000000', value2: '1200000' },
      //   { id: 2, rowHeader: '反向有功', value1: '10000', value2: '12000' },
      //   { id: 3, rowHeader: '正向无功', value1: '100000', value2: '120000' },
      //   { id: 4, rowHeader: '反向无功', value1: '1000', value2: '1200' },
      //   { id: 5, rowHeader: '高峰', value1: '/', value2: '/' },
      //   { id: 6, rowHeader: '低谷', value1: '/', value2: '/' }
      // ],

      // tab2Data: [
      //   { id: 1, rowHeader: '正向有功', value1: '1000000', value2: '1200000', value3: '3200000' },
      //   { id: 2, rowHeader: '反向有功', value1: '/', value2: '/', value3: '/' },
      //   { id: 3, rowHeader: '正向无功', value1: '100000', value2: '120000', value3: '3200000' },
      //   { id: 4, rowHeader: '反向无功', value1: '1000', value2: '1200', value3: '3200000' },
      //   { id: 5, rowHeader: '高峰', value1: '1200000', value2: '2200000', value3: '3200000' },
      //   { id: 6, rowHeader: '低谷', value1: '1200000', value2: '2200000', value3: '3200000' }
      // ]
    }
  }
}
</script>
