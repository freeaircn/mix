<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-05 21:44:53
 * @LastEditors: freeair
 * @LastEditTime: 2022-06-06 22:08:46
-->
<template>
  <page-header-wrapper :title="false">
    <a-card title="编辑" :bordered="false" :body-style="{marginBottom: '8px'}" >
      <a-form-model
        ref="form"
        :model="record"
        :rules="rules"
        :label-col="labelCol"
        :wrapper-col="wrapperCol"
      >
        <a-form-model-item label="单号" prop="dts_id">
          <a-input v-model="record.dts_id" readOnly></a-input>
        </a-form-model-item>

        <a-form-model-item label="站点" prop="station">
          <a-input v-model="record.station" readOnly></a-input>
        </a-form-model-item>

        <a-form-model-item label="类别" prop="type">
          <a-radio-group name="radioGroup1" v-model="record.type">
            <a-radio value="1">隐患</a-radio>
            <a-radio value="2">缺陷</a-radio>
          </a-radio-group>
        </a-form-model-item>
        <a-form-model-item label="标题" prop="title">
          <a-input v-model="record.title"></a-input>
        </a-form-model-item>
        <a-form-model-item label="影响等级" prop="level">
          <a-select v-model="record.level" placeholder="请选择" >
            <a-select-option value="1">紧急</a-select-option>
            <a-select-option value="2">严重</a-select-option>
            <a-select-option value="3">一般</a-select-option>
          </a-select>
        </a-form-model-item>

        <a-form-model-item label="设备" prop="device">
          <a-cascader
            :options="deviceItems"
            v-model="device"
            :allowClear="true"
            expand-trigger="hover"
            change-on-select
            :fieldNames="{ label: 'name', value: 'id', children: 'children' }"
            placeholder="请选择"
          />
        </a-form-model-item>

        <a-form-model-item label="创建日期" prop="created_at">
          <!-- <a-input v-model="record.created_at"></a-input> -->
          <a-date-picker v-model="created_date" showTime @change="onDatePickerChange" />
        </a-form-model-item>

        <a-form-model-item label="描述" prop="description">
          <a-textarea v-model="record.description" :rows="10" />
        </a-form-model-item>

        <a-form-model-item label="进展" prop="progress">
          <a-textarea v-model="record.progress" :rows="10" />
        </a-form-model-item>

        <a-form-model-item :wrapperCol="{ span: 24 }" style="text-align: center">
          <a-button type="primary" @click="onSubmit" :disabled="!ready" >确认</a-button>
          <!-- <router-link slot="extra" to="/dashboard/dts"><a-button>取消</a-button></router-link> -->
          <a-button style="margin-left: 16px" @click="onCancel">取消</a-button>
        </a-form-model-item>
      </a-form-model>
    </a-card>
  </page-header-wrapper>
</template>

<script>
import moment from 'moment'
import { mapGetters } from 'vuex'
import { queryDts, updateDts } from '@/api/mix/dts'
import { listToTree, strSplitToArray, arraySplitToStr } from '@/utils/util'

export default {
  name: 'DtsEditForm',
  data () {
    return {
      labelCol: {
        lg: { span: 7 }, sm: { span: 7 }
      },
      wrapperCol: {
        lg: { span: 10 }, sm: { span: 17 }
      },
      //
      ready: false,
      // stationItems: [],
      deviceItems: [],
      device: [],
      created_date: null,
      //
      dts_id: '0',
      record: {
        dts_id: '',
        station: '',
        title: '',
        type: '',
        level: '',
        description: '',
        progress: '',
        created_at: ''
      },
      rules: {
        // station_id: [{ required: true, message: '请选择', trigger: ['change'] }],
        type: [{ required: true, message: '请选择', trigger: ['change'] }],
        title: [{ required: true, message: '请输入', trigger: ['change'] }],
        level: [{ required: true, message: '请选择', trigger: ['change'] }]
      }
    }
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  created () {
    this.dts_id = (this.$route.params.id) ? this.$route.params.id : '0'
  },
  mounted () {
    this.onGetDetails()
  },
  methods: {
    onDatePickerChange (date, dateString) {
      this.record.created_at = dateString
    },

    onGetDetails () {
      const params = { resource: 'pre_edit', dts_id: this.dts_id }
      queryDts(params)
        .then((data) => {
          Object.assign(this.record, data.record)
          //
          this.created_date = moment(this.record.created_at)
          listToTree(data.deviceList, this.deviceItems, this.userInfo.allowDefaultDeptId)
          this.device = strSplitToArray(data.record.device)
          //
          this.ready = true
        })
        .catch(() => {
          this.record.dts_id = ''
          this.record.station = ''
          this.record.title = ''
          this.record.type = ''
          this.record.level = ''
          this.record.description = ''
          this.record.progress = ''
          this.record.created_at = ''
          this.device = []
          this.deviceItems.splice(0, this.deviceItems.length)
          this.created_date = null
          this.ready = false
        })
    },

    onSubmit () {
      this.$refs.form.validate(valid => {
        if (valid) {
          var data = { ...this.record }
          data.device = arraySplitToStr(this.device)
          data.resource = 'req_edit'
          updateDts(data)
            .then(() => {
              this.$router.push({ path: `/dashboard/dts/list` })
            })
            .catch(() => {
            })
        } else {
          return false
        }
      })
    },

    onCancel () {
      // this.$router.push({ path: `/dashboard/dts/list` })
      this.$router.back()
    }
  }
}
</script>
