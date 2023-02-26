<!--
 * @Description:
 * @Author: freeair
 * @Date: 2023-02-26 08:58:37
 * @LastEditors: freeair
 * @LastEditTime: 2023-02-26 10:49:10
-->
<template>
  <div class="wrapper">
    <!-- <h4 class="title">{{ title }}</h4> -->
    <ul class="list">
      <li :key="index" v-for="(item, index) in list">
        <span :class="index < 3 ? 'active' : null">{{ index + 1 }}</span>
        <span>{{ item.name }}</span>
        <span>{{ item.date }}</span>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'DocList',
  // ['title', 'list']
  props: {
    title: {
      type: String,
      default: ''
    },
    list: {
      type: Array,
      default: null
    }
  }
}
</script>

<style lang="less" scoped>

  .wrapper {
    padding: 0 16px 0px 16px;

    .list {
      margin: 0px 0 0;
      padding: 0;
      list-style: none;

      li {
        margin-bottom: 16px;

        span {
          color: rgba(0, 0, 0, .65);
          font-size: 14px;
          line-height: 22px;

          &:first-child {
            background-color: #f5f5f5;
            border-radius: 20px;
            display: inline-block;
            font-size: 12px;
            font-weight: 600;
            margin-right: 24px;
            height: 20px;
            line-height: 20px;
            width: 20px;
            text-align: center;
          }
          &:nth-child(2) {
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            max-width: 300px;
            min-width: 100px;
            display: inline-block;
            // color: #4574f7;
          }
          &.active {
            background-color: #314659;
            color: #fff;
          }
          &:last-child {
            float: right;
            min-width: 80px;
          }
        }
      }
    }
  }

  .mobile .wrapper {
    padding: 0 32px 32px 32px;
  }

</style>
