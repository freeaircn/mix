<template>
  <page-header-wrapper :title="false">
    <div>
      <a-card style="margin-bottom: 8px;" :bordered="false">
        <img
          slot="cover"
          alt="example"
          src="@/assets/pic/party/header/ssh.svg"
          style="height:200px;"
        />
      </a-card>

      <a-row :gutter="8" style="margin-bottom: 8px;">
        <a-col :xl="16" :lg="16" :md="16" :sm="24" :xs="24">
          <a-card title="支部风采" :bordered="false" :headStyle="config.cardHeaderStyle">
            <a-row :gutter="8">
              <a-col :xl="12" :lg="12" :md="12" :sm="0" :xs="0">
                <a-carousel arrows dots-class="slick-dots slick-thumb" :autoplay="true">
                  <div><img alt="example" src="@/assets/pic/party/header/ssh.svg"/></div>
                  <div><img alt="example" src="@/assets/pic/party/header/ssh.svg"/></div>
                  <div><img alt="example" src="@/assets/pic/party/header/ssh.svg"/></div>
                </a-carousel>
              </a-col>
              <a-col :xl="12" :lg="12" :md="12" :sm="24" :xs="24">
                <!-- <doc-list :list="docList"></doc-list> -->
                <doc-table :listData="docList"></doc-table>
              </a-col>
            </a-row>
          </a-card>
        </a-col>

        <a-col :xl="8" :lg="8" :md="8" :sm="24" :xs="24">
          <a-card title="主题教育" :bordered="false" :headStyle="config.cardHeaderStyle">
            <a-row :gutter="8">
              <doc-table :listData="docList"></doc-table>
            </a-row></a-card>
        </a-col>
      </a-row>

      <a-row :gutter="8" style="margin-bottom: 8px;">
        <a-col :xl="8" :lg="8" :md="8" :sm="24" :xs="24">
          <a-card title="党务公开" :bordered="false" :headStyle="config.cardHeaderStyle">
            <a-row :gutter="8">
              <doc-table :listData="docList"></doc-table>
            </a-row></a-card>
        </a-col>
        <a-col :xl="8" :lg="8" :md="8" :sm="24" :xs="24">
          <a-card title="会议纪要" :bordered="false" :headStyle="config.cardHeaderStyle">
            <a-row :gutter="8">
              <doc-table :listData="docList"></doc-table>
            </a-row></a-card>
        </a-col>
        <a-col :xl="8" :lg="8" :md="8" :sm="24" :xs="24">
          <a-card title="学习园地" :bordered="false" :headStyle="config.cardHeaderStyle">
            <a-row :gutter="8">
              <doc-table :listData="docList"></doc-table>
            </a-row></a-card>
        </a-col>
      </a-row>

    </div>
  </page-header-wrapper>
</template>

<script>
import { mapGetters } from 'vuex'
import { PageHeaderWrapper } from '@ant-design-vue/pro-layout'
//
import { partyBranch as CONFIG } from '@/config/myConfig'
import DocTable from '@/views/mix/partybranch/components/DocTable'

const docList = []
for (let i = 0; i < 7; i++) {
  docList.push({
    name: '松山河口电厂松山河口电厂松山河口电厂' + (i + 1),
    date: '2023-2-26'
  })
}

export default {
  name: 'Home',
  components: {
    PageHeaderWrapper,
    DocTable
  },
  data () {
    return {
      user: {},
      docList,
      config: CONFIG
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ]),
    currentUser: function () {
      return {
        name: this.userInfo.username,
        department: this.userInfo.department
      }
    }
  },
  created () {
    this.user = this.userInfo
  },
  // mounted () {
  // },
  methods: {
    onTest () {
      this.$message.warning('未开通')
    }
  }
}
</script>

<style lang="less" scoped>
  .project-list {

    .card-title {
      font-size: 0;

      a {
        color: rgba(0, 0, 0, 0.85);
        margin-left: 8px;
        line-height: 32px;
        height: 32px;
        display: inline-block;
        vertical-align: top;
        font-size: 14px;

        &:hover {
          color: #1890ff;
        }
      }

      router-link {
        color: rgba(0, 0, 0, 0.85);
        margin-left: 8px;
        line-height: 32px;
        height: 32px;
        display: inline-block;
        vertical-align: top;
        font-size: 14px;

        &:hover {
          color: #1890ff;
        }
      }
    }

    .card-description {
      color: rgba(0, 0, 0, 0.45);
      height: 32px;
      line-height: 22px;
      overflow: hidden;
    }

    // .project-item {
    //   display: flex;
    //   margin-top: 8px;
    //   overflow: hidden;
    //   font-size: 12px;
    //   height: 20px;
    //   line-height: 20px;

    //   a {
    //     color: rgba(0, 0, 0, 0.45);
    //     display: inline-block;
    //     flex: 1 1 0;

    //     &:hover {
    //       color: #1890ff;
    //     }
    //   }

    //   .datetime {
    //     color: rgba(0, 0, 0, 0.25);
    //     flex: 0 0 auto;
    //     float: right;
    //   }
    // }

    // .ant-card-meta-description {
    //   color: rgba(0, 0, 0, 0.45);
    //   height: 44px;
    //   line-height: 22px;
    //   overflow: hidden;
    // }
  }

  .item-group {
    padding: 20px 0 8px 24px;
    font-size: 0;

    a {
      color: rgba(0, 0, 0, 0.65);
      display: inline-block;
      font-size: 14px;
      margin-bottom: 13px;
      width: 25%;
    }
  }

  .members {
    a {
      display: block;
      margin: 12px 0;
      line-height: 24px;
      height: 24px;

      .member {
        font-size: 14px;
        color: rgba(0, 0, 0, .65);
        line-height: 24px;
        max-width: 100px;
        vertical-align: top;
        margin-left: 12px;
        transition: all 0.3s;
        display: inline-block;
      }

      &:hover {
        span {
          color: #1890ff;
        }
      }
    }
  }

  .mobile {

    .project-list {

      .project-card-grid {
        width: 100%;
      }
    }

    .more-info {
      border: 0;
      padding-top: 16px;
      margin: 16px 0 16px;
    }

    .headerContent .title .welcome-text {
      display: none;
    }
  }
</style>
