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
  name: 'ChartCard',
  props: {
    title: {
      type: String,
      default: ''
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
    margin-bottom: 8px;

    .meta {
      position: relative;
      overflow: hidden;
      width: 100%;
      color: rgba(0, 0, 0, 0.749);
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
    height: 320px;
    width: 100%;

    .content-fix {
      position: absolute;
      left: 0;
      bottom: 0;
      width: 100%;
    }
  }
</style>
