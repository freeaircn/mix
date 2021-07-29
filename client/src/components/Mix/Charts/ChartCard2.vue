<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-28 23:22:43
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-29 03:54:46
-->
<template>
  <a-card :loading="loading" :body-style="{ padding: '16px 16px 8px 16px' }" :bordered="false">
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
      <div class="chart-card-content">
        <div class="content-fix">
          <slot></slot>
        </div>
      </div>
      <div class="chart-card-footer">
        <div class="field">
          <slot name="footer"></slot>
        </div>
      </div>
    </div></a-card>
</template>

<script>
export default {
  name: 'ChartCard2',
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
    margin-bottom: 12px;
    position: relative;
    height: 100px;
    width: 100%;

    .content-fix {
      position: absolute;
      left: 0;
      bottom: 0;
      width: 100%;
    }
  }
</style>
