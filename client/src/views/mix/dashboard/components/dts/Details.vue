<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-09-30 20:50:48
 * @LastEditors: freeair
 * @LastEditTime: 2021-10-19 10:47:03
-->
<template>
  <page-header-wrapper :title="false">

    <a-card :title="userInfo.belongToDeptName" :bordered="false" :headStyle="{marginBottom: '8px'}">
    </a-card>

    <a-card :bordered="false" title="进度" :loading="loading" :body-style="{marginBottom: '8px', marginRight: '100px'}">
      <router-link slot="extra" to="/dashboard/dts/list">返回</router-link>

      <a-steps size="small" :current="steps.current">
        <a-step v-for="(item, j) in steps.step" :key="j" :title="item.title" >
          <a-icon slot="icon" v-if="item.icon" type="loading" />
          <template v-slot:description>
            <div v-for="(d, i) in item.description" :key="i">{{ d }}</div>
          </template>
        </a-step>
      </a-steps>
    </a-card>

    <a-card :bordered="false" title="详情" :loading="loading" :body-style="{marginBottom: '8px'}">
      <a slot="extra" @click="onQueryDetails">刷新</a>

      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="单号">{{ details.ticket_id }}</a-descriptions-item>
        <a-descriptions-item label="类别">{{ details.type }}</a-descriptions-item>
        <a-descriptions-item label="影响程度">{{ details.level }}</a-descriptions-item>
        <a-descriptions-item label="进度">{{ details.place_at }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="标题">{{ details.title }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="所属单元">{{ details.equipment_unit }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="创建时间">{{ details.created_at }}</a-descriptions-item>
        <a-descriptions-item label="更新时间">{{ details.updated_at }}</a-descriptions-item>
      </a-descriptions>
      <a-descriptions title="" :column="{ xxl: 4, xl: 3, lg: 3, md: 3, sm: 2, xs: 1 }">
        <a-descriptions-item label="创建人">{{ details.creator }}</a-descriptions-item>
        <a-descriptions-item label="处理人">{{ details.handler }}</a-descriptions-item>
        <a-descriptions-item label="审核人">{{ details.reviewer }}</a-descriptions-item>
      </a-descriptions>

      <div style="margin-bottom: 8px">进展:</div>
      <div style="width: 100%">
        <a-textarea id="textarea_id" v-model="details.progress" :defaultValue="details.progress" :rows="10" readOnly/>
      </div>
    </a-card>

    <a-card v-if="OpVisible" :bordered="false" title="操作" :loading="loading" :body-style="{marginBottom: '8px'}">
      <router-link slot="extra" to="/dashboard/dts/list">返回</router-link>
      <div style="width: 100%">
        <a-form-model :model="operation">
          <a-form-model-item label="新进展">
            <a-textarea v-model="operation.newProgress" :defaultValue="defaultProgressText" :rows="6" />
          </a-form-model-item>
          <a-form-model-item>
            <a-button type="primary" @click="handleSubmitNewProgress" style="margin-top: 8px; margin-right: 16px">更新进展</a-button>
            <a-button-group>
              <a-button v-if="isCheck" @click="handleChangeHandler">更换处理人</a-button>
              <a-button v-if="isCheck" @click="handleToReview">提交审核</a-button>
              <a-button v-if="isReview" @click="handleToCheckAgain">重新检查</a-button>
              <a-button v-if="isReview" @click="handleToResolve">解决</a-button>
              <a-button v-if="isCreator" @click="handleToClose">关闭</a-button>
            </a-button-group>
          </a-form-model-item>
        </a-form-model>
      </div>
    </a-card>

    <a-modal
      title="更换处理人"
      :visible="newHandlerDialogVisible"
      :centered="true"
      :maskClosable="false"
      okText="提交"
      @ok="onSubmitNewHandler"
      @cancel="() => { this.newHandlerDialogVisible = false }"
    >
      <div style="margin-bottom: 8px">新处理人:</div>
      <a-select v-model="newHandler" placeholder="请选择" style="width: 60%">
        <a-select-option v-for="d in handlerOptions" :key="d.id" :value="d.id" :disabled="d.status === '0'">
          {{ d.username }}
        </a-select-option>
      </a-select>
    </a-modal>

    <a-modal
      title="提交审核"
      :visible="reviewDialogVisible"
      :centered="true"
      :maskClosable="false"
      okText="提交"
      @ok="onSubmitReview"
      @cancel="() => { this.reviewDialogVisible = false }"
    >
      <div style="margin-bottom: 8px">审核人:</div>
      <a-select v-model="reviewer" placeholder="请选择" style="width: 60%">
        <a-select-option v-for="d in reviewerOptions" :key="d.id" :value="d.id" :disabled="d.status === '0'">
          {{ d.username }}
        </a-select-option>
      </a-select>
    </a-modal>

  </page-header-wrapper>
</template>

<script>
import { mapGetters } from 'vuex'
import { baseMixin } from '@/store/app-mixin'
import { getDtsTicketDetails, putDtsTicketProgress, putDtsTicketHandler, postDtsTicketToReview } from '@/api/service'

export default {
  name: 'DtsTicketDetails',
  mixins: [baseMixin],
  data () {
    return {
      ticketId: '0',
      loading: false,
      steps: {
        current: 0
      },
      details: {
        ticket_id: '',
        type: '',
        level: '',
        place_at: '',
        title: '',
        equipment_unit: '',
        creator: '',
        created_at: '',
        handler: '',
        updated_at: '',
        progress: ''
      },
      defaultProgressText: '',
      //
      operation: {},
      //
      isCreator: false,
      isCheck: false,
      isReview: false,
      //
      newHandlerDialogVisible: false,
      handlerOptions: [],
      newHandler: undefined,
      //
      reviewDialogVisible: false,
      reviewerOptions: [],
      reviewer: undefined
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ]),
    OpVisible: function () {
      return this.isCreator || this.isCheck || this.isReview
    }
  },
  created () {
    this.ticketId = (this.$route.params.ticketId) ? this.$route.params.ticketId : '0'
  },
  mounted () {
    this.onQueryDetails()
  },
  methods: {

    // 查询
    onQueryDetails () {
      if (this.ticketId === '0') {
        return
      }
      const query = {
        station_id: this.userInfo.belongToDeptId,
        ticket_id: this.ticketId
      }
      this.loading = true
      getDtsTicketDetails(query)
        .then(data => {
          this.loading = false
          //
          Object.assign(this.details, data.ticket)
          this.steps = data.steps
          this.defaultProgressText = data.progressText
          this.isCreator = data.view.isCreator
          this.isCheck = data.view.isCheck
          this.isReview = data.view.isReview
          this.handlerOptions = data.handlers
          this.reviewerOptions = data.reviewers
        })
        .catch((err) => {
          this.loading = false
          if (err.response) {
          }
        })
    },

    handleSubmitNewProgress () {
      if (this.operation.newProgress === undefined) {
        return
      }
      this.$confirm({
        title: '你想要提交新进展吗？',
        onOk: () => {
          const progress = {
            station_id: this.userInfo.belongToDeptId,
            ticket_id: this.ticketId,
            progress: this.operation.newProgress
          }
          putDtsTicketProgress(progress)
            .then(() => {
              this.operation.newProgress = this.defaultProgressText
              // this.details.progress = data
              // const textarea = document.getElementById('textarea_id')
              // textarea.scrollTop = 0
              window.scroll(0, 0)
              this.onQueryDetails()
            })
            .catch((err) => {
              if (err.response) {
              }
            })
        }
      })
    },

    //
    handleChangeHandler () {
      this.newHandlerDialogVisible = true
    },

    onSubmitNewHandler () {
      if (this.newHandler !== undefined) {
        this.newHandlerDialogVisible = false
        const handler = {
          station_id: this.userInfo.belongToDeptId,
          ticket_id: this.ticketId,
          handler: this.newHandler
        }
        putDtsTicketHandler(handler)
          .then(() => {
            //
            this.$router.push({ path: '/dashboard/dts/list' })
          })
          .catch((err) => {
            this.newHandler = undefined
            this.newHandlerDialogVisible = false
            if (err.response) {
            }
          })
      }
    },

    handleToReview () {
      this.reviewDialogVisible = true
    },

    onSubmitReview () {
      if (this.reviewer !== undefined) {
        this.reviewDialogVisible = false
        const reviewer = {
          station_id: this.userInfo.belongToDeptId,
          ticket_id: this.ticketId,
          reviewer: this.reviewer
        }
        postDtsTicketToReview(reviewer)
          .then(() => {
            this.$router.push({ path: '/dashboard/dts/list' })
          })
          .catch((err) => {
            this.reviewer = undefined
            this.reviewDialogVisible = false
            if (err.response) {
            }
          })
      }
    },

    // handleToReview () {
    //   this.$confirm({
    //     title: '你想要提交审核吗？',
    //     onOk: () => {
    //       const review = {
    //         station_id: this.userInfo.belongToDeptId,
    //         ticket_id: this.ticketId
    //       }
    //       postDtsTicketToReview(review)
    //         .then(() => {
    //           this.$router.push({ path: '/dashboard/dts/list' })
    //         })
    //         .catch((err) => {
    //           if (err.response) {
    //           }
    //         })
    //     }
    //   })
    // },

    //
    handleToCheckAgain () {
      this.$confirm({
        title: '你想要重新检查吗？',
        onOk: () => {
          console.log('OK')
        }
      })
    },

    handleToResolve () {
      this.$confirm({
        title: '问题解决了吗？',
        onOk: () => {
          console.log('OK')
        }
      })
    },

    handleToClose () {
      this.$confirm({
        title: '你想要关闭问题单吗？',
        onOk: () => {
          console.log('OK')
        }
      })
    }

  }
}
</script>
