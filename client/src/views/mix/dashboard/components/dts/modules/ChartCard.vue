<template>
  <a-card :loading="loading" :body-style="{ padding: '20px 24px 8px' }" :bordered="false">
    <div class="chart-card-header">
      <div class="meta">
        <span class="chart-card-title">
          <slot name="title">
            {{ title }}
          </slot>
        </span>
        <span class="chart-card-action">
          <slot name="action"></slot>
        </span>
      </div>
      <div class="total">
        <span class="pre-label">{{ label }}</span>
        <slot name="value">
          <span>{{ typeof value === 'function' && value() || value }}</span>
        </slot>
      </div>
    </div>
    <div class="chart-card-content">
      <div class="content-fix">
        <div id="my-chart" style="height: 56px;"></div>
      </div>
    </div>
    <div class="chart-card-footer">
      <div class="field">
        <slot name="footer"></slot>
      </div>
    </div>
  </a-card>
</template>

<script>
import { Column } from '@antv/g2plot'

export default {
  name: 'ChartCard',
  props: {
    title: {
      type: String,
      default: ''
    },
    label: {
      type: String,
      required: false,
      default: null
    },
    value: {
      type: [Function, Number, String],
      required: false,
      default: null
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      isMounted: false,
      //
      myChart: null,
      data: [
        { type: '2022-01', value: 2 },
        { type: '2022-02', value: 3 },
        { type: '2022-03', value: 4 },
        { type: '2022-04', value: 5 },
        { type: '2022-05', value: 5 },
        { type: '2022-06', value: 0.1 },
        { type: '2022-07', value: 0.1 },
        { type: '2022-08', value: 0.1 },
        { type: '2022-09', value: 0.1 },
        { type: '2022-10', value: 0.1 },
        { type: '2022-11', value: 0.1 },
        { type: '2022-12', value: 0.1 }
      ]
    }
  },
  mounted () {
    this.initMyChart()
    //
    this.isMounted = true
  },
  methods: {
    initMyChart () {
      this.myChart = new Column('my-chart', {
        data: this.data,
        xField: 'type',
        yField: 'value',
        maxColumnWidth: 64,
        // legend: { position: 'bottom' },
        legend: false,
        label: false,
        xAxis: {
          title: null,
          label: null,
          grid: null,
          line: null
        },
        yAxis: {
          title: null,
          label: null,
          grid: null,
          line: null
        }
      })
      this.myChart.render()
    },

    updateMyChart () {
      const chartData = this.data
      this.myChart.changeData(chartData)
    }
  }
}
</script>

<style lang="less" scoped>
  .chart-card-header {
    position: relative;
    overflow: hidden;
    width: 100%;

    .meta {
      position: relative;
      overflow: hidden;
      width: 100%;
      color: rgba(0, 0, 0, .45);
      font-size: 14px;
      line-height: 22px;
    }
  }

  .chart-card-action {
    cursor: pointer;
    position: absolute;
    top: 0;
    right: 0;
  }

  .chart-card-footer {
    border-top: 1px solid #e8e8e8;
    padding-top: 9px;
    margin-top: 8px;

    > * {
      position: relative;
    }

    .field {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      margin: 0;
    }
  }

  .chart-card-content {
    margin-bottom: 8px;
    position: relative;
    height: 46px;
    width: 100%;

    .content-fix {
      position: absolute;
      left: 0;
      bottom: 0;
      width: 100%;
    }
  }

  .total {
    overflow: hidden;
    text-overflow: ellipsis;
    word-break: break-all;
    white-space: nowrap;
    color: #000;
    margin-top: 4px;
    margin-bottom: 16px;
    font-size: 28px;
    line-height: 38px;
    height: 38px;

    .pre-label {
      color: #000;
      font-size: 14px;
      // vertical-align: top;
      margin-right: 16px;
    }
  }
</style>
