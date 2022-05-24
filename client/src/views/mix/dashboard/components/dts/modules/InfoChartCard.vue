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
      <div class="info-content">
        <span class="pre-label">{{ label }}</span>
        <slot name="info">
          <span>{{ typeof info === 'function' && info() || info }}</span>
        </slot>
      </div>
    </div>
    <div class="chart-card-content">
      <div class="content-fix">
        <slot></slot>
      </div>
    </div>
  </a-card>
</template>

<script>
export default {
  name: 'InfoChartCard',
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
    info: {
      type: [Function, Number, String],
      required: false,
      default: null
    },
    loading: {
      type: Boolean,
      default: false
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
      color: #000000bf;
      font-size: 15px;
      line-height: 23px;
    }
  }

  .chart-card-action {
    cursor: pointer;
    position: absolute;
    top: 0;
    right: 0;
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

  .info-content {
    overflow: hidden;
    text-overflow: ellipsis;
    word-break: break-all;
    white-space: nowrap;
    color: #1890ff;
    margin-top: 4px;
    margin-bottom: 16px;
    font-size: 28px;
    line-height: 38px;
    height: 38px;

    .pre-label {
      color: rgba(0, 0, 0, 0.749);
      font-size: 14px;
      // vertical-align: top;
      margin-right: 16px;
    }
  }
</style>
