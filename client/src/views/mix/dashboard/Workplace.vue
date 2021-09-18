<template>
  <page-header-wrapper>
    <template v-slot:content>
      <div class="page-header-content">
        <div class="avatar">
          <a-avatar size="large" :src="currentUser.avatar"/>
        </div>
        <div class="content">
          <div class="content-title">
            <!-- {{ timeFix }}，{{ user.name }}<span class="welcome-text">，{{ welcome }}</span> -->
            {{ timeFix }}，{{ currentUser.name }}
          </div>
          <div>{{ currentUser.department }}</div>
        </div>
      </div>
    </template>
    <template v-slot:extraContent>
      <div class="extra-content">
        <div class="stat-item">
          <p>{{ date.toLocaleDateString() }}</p>
        </div>
      </div>
    </template>

    <div>

      <a-card class="project-list" style="margin-bottom: 16px;" :bordered="false" title="快速开始" :body-style="{ padding: 0 }">
        <a-row :gutter="0">
          <a-col :xl="8" :lg="8" :md="8" :sm="24" :xs="24">
            <a-card :bordered="true" >
              <a-card-meta>
                <div slot="title" class="card-title">
                  <!-- <a-avatar size="small" :src="item.cover"/> -->
                  <a-icon :component="letterRoundSolidJSVG"/>
                  <a>机组事件</a>
                </div>
                <div slot="description" class="card-description">
                  机组开停机，运行时间统计
                </div>
              </a-card-meta>
            </a-card>
          </a-col>

          <a-col :xl="8" :lg="8" :md="8" :sm="24" :xs="24">
            <a-card :bordered="true" >
              <a-card-meta>
                <div slot="title" class="card-title">
                  <!-- <a-avatar size="small" :src="item.cover"/> -->
                  <a>电度表</a>
                </div>
                <div slot="description" class="card-description">
                  电度表记录，发电量统计
                </div>
              </a-card-meta>
            </a-card>
          </a-col>

          <a-col :xl="8" :lg="8" :md="8" :sm="24" :xs="24">
            <a-card :bordered="true" >
              <a-card-meta>
                <div slot="title" class="card-title">
                  <!-- <a-avatar size="small" :src="item.cover"/> -->
                  <a>站点文档</a>
                </div>
                <div slot="description" class="card-description">
                  档案、图册检索，借阅记录
                </div>
              </a-card-meta>
            </a-card>
          </a-col>
        </a-row>
      </a-card>

      <a-card title="动态" :bordered="false">
        <a-list>
          <a-list-item>
            <a-list-item-meta>
              <!-- <a-avatar slot="avatar" :src="item.user.avatar"/> -->
              <div slot="title">
                <span>xxx</span>&nbsp;
              </div>
              <div slot="description">xxx</div>
            </a-list-item-meta>
          </a-list-item>
        </a-list>
      </a-card>

    </div>
  </page-header-wrapper>
</template>

<script>
import { timeFix } from '@/utils/util'
import { letterRoundSolidJSVG } from '@/core/icons'
// import { mapState } from 'vuex'
import { mapState, mapGetters } from 'vuex'
import { PageHeaderWrapper } from '@ant-design-vue/pro-layout'
// import { Radar } from '@/components'

// import { getRoleList, getServiceList } from '@/api/manage'

// const DataSet = require('@antv/data-set')

export default {
  name: 'Workplace',
  components: {
    PageHeaderWrapper
    // Radar
  },
  data () {
    return {
      timeFix: timeFix(),
      avatar: '',
      user: {},
      date: null,

      projects: [],
      loading: true,
      radarLoading: true,
      activities: [],
      teams: [],

      // data
      axis1Opts: {
        dataKey: 'item',
        line: null,
        tickLine: null,
        grid: {
          lineStyle: {
            lineDash: null
          },
          hideFirstLine: false
        }
      },
      axis2Opts: {
        dataKey: 'score',
        line: null,
        tickLine: null,
        grid: {
          type: 'polygon',
          lineStyle: {
            lineDash: null
          }
        }
      },
      scale: [{
        dataKey: 'score',
        min: 0,
        max: 80
      }],
      axisData: [
        { item: '引用', a: 70, b: 30, c: 40 },
        { item: '口碑', a: 60, b: 70, c: 40 },
        { item: '产量', a: 50, b: 60, c: 40 },
        { item: '贡献', a: 40, b: 50, c: 40 },
        { item: '热度', a: 60, b: 70, c: 40 },
        { item: '引用', a: 70, b: 50, c: 40 }
      ],
      radarData: [],
      //
      letterRoundSolidJSVG
    }
  },
  computed: {
    ...mapState({
      nickname: (state) => state.user.nickname,
      welcome: (state) => state.user.welcome
    }),
    ...mapGetters([
      'userInfo'
    ]),
    currentUser: function () {
      return {
        name: this.userInfo.username,
        avatar: process.env.VUE_APP_PUBLIC_BASE_URL + this.userInfo.avatarFile,
        department: this.userInfo.department
      }
    }
    // userInfo () {
    //   return this.$store.getters.userInfo
    // }
  },
  created () {
    this.user = this.userInfo
    this.avatar = this.userInfo.avatar

    this.date = new Date()

    // getRoleList().then(res => {
    //   // console.log('workplace -> call getRoleList()', res)
    // })

    // getServiceList().then(res => {
    //   // console.log('workplace -> call getServiceList()', res)
    // })
  },
  mounted () {
    // this.getProjects()
    // this.getActivity()
    // this.getTeams()
    // this.initRadar()
  },
  methods: {
    // getProjects () {
    //   this.$http.get('/list/search/projects')
    //     .then(res => {
    //       this.projects = res.result && res.result.data
    //       this.loading = false
    //     })
    // },
    // getActivity () {
    //   this.$http.get('/workplace/activity')
    //     .then(res => {
    //       this.activities = res.result
    //     })
    // },
    // getTeams () {
    //   this.$http.get('/workplace/teams')
    //     .then(res => {
    //       this.teams = res.result
    //     })
    // },
    // initRadar () {
    //   this.radarLoading = true

    //   this.$http.get('/workplace/radar')
    //     .then(res => {
    //       const dv = new DataSet.View().source(res.result)
    //       dv.transform({
    //         type: 'fold',
    //         fields: ['个人', '团队', '部门'],
    //         key: 'user',
    //         value: 'score'
    //       })

    //       this.radarData = dv.rows
    //       this.radarLoading = false
    //     })
    // }
  }
}
</script>

<style lang="less" scoped>
  @import "./Workplace.less";

  .project-list {

    .card-title {
      font-size: 0;

      a {
        color: rgba(0, 0, 0, 0.85);
        margin-left: 12px;
        line-height: 24px;
        height: 24px;
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
      height: 44px;
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
