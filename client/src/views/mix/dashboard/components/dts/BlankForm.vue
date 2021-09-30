<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-07-05 21:44:53
 * @LastEditors: freeair
 * @LastEditTime: 2021-09-30 21:04:18
-->
<template>
  <page-header-wrapper :title="false">
    <a-card :title="userInfo.belongToDeptName" :bordered="false" :headStyle="{marginBottom: '8px'}">
    </a-card>

    <a-card title="新问题单" :bordered="false" :body-style="{marginBottom: '8px'}" >
      <router-link slot="extra" to="/dashboard/dts">返回</router-link>

      <a-form-model
        ref="form"
        :model="record"
        :rules="rules"
        :label-col="labelCol"
        :wrapper-col="wrapperCol"
      >
        <a-form-model-item label="类别" prop="type">
          <a-radio-group name="radioGroup1" v-model="record.type">
            <a-radio value="1">隐患</a-radio>
            <a-radio value="2">故障</a-radio>
          </a-radio-group>
        </a-form-model-item>
        <a-form-model-item label="标题" prop="title">
          <a-input v-model="record.title"></a-input>
        </a-form-model-item>
        <a-form-model-item label="影响程度" prop="level">
          <a-select v-model="record.level" placeholder="请选择" >
            <a-select-option value="1">紧急</a-select-option>
            <a-select-option value="2">严重</a-select-option>
            <a-select-option value="3">一般</a-select-option>
          </a-select>
        </a-form-model-item>

        <a-form-model-item label="所属单元" prop="equipmentUnit">
          <a-cascader
            :options="cascaderOptions"
            v-model="equipmentUnit"
            :allowClear="true"
            expand-trigger="hover"
            change-on-select
            :fieldNames="{ label: 'name', value: 'id', children: 'children' }"
            placeholder="请选择"
          />
        </a-form-model-item>

        <a-form-model-item label="进展" prop="progress">
          <a-textarea v-model="record.progress" :rows="10" />
        </a-form-model-item>

        <a-form-model-item :wrapperCol="{ span: 24 }" style="text-align: center">
          <!-- <a-button :disabled="!ready">保存</a-button> -->
          <a-button type="primary" @click="onSubmit" :disabled="!ready" style="margin-left: 8px">提交</a-button>
        </a-form-model-item>
      </a-form-model>
    </a-card>
  </page-header-wrapper>
</template>

<script>
import { mapGetters } from 'vuex'
import { getEquipmentUnit } from '@/api/manage'
import { getDtsProgressTemplate, postDtsDraft } from '@/api/service'
import { listToTree } from '@/utils/util'

export default {
  name: 'DtsBlankForm',
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
      cascaderOptions: [],
      equipmentUnit: [],
      //
      record: {
        progress: ''
      },
      rules: {}
    }
  },
  created: function () {
    this.ready = false
    this.getProgressTemplate()
    this.getFormItems()
  },
  computed: {
    ...mapGetters([
      'userInfo'
    ])
  },
  methods: {
    getFormItems () {
      getEquipmentUnit()
        .then((data) => {
            this.ready = true
            listToTree(data, this.cascaderOptions, '1')
          })
          //  网络异常，清空页面数据显示，防止错误的操作
          .catch((err) => {
            this.cascaderOptions.splice(0, this.cascaderOptions.length)
            if (err.response) {
            }
          })
    },

    getProgressTemplate () {
      getDtsProgressTemplate()
        .then((data) => {
            this.record.progress = data
          })
          //  网络异常，清空页面数据显示，防止错误的操作
          .catch((err) => {
            this.record.progress = ''
            if (err.response) {
            }
          })
    },

    onSubmit () {
      this.$refs.form.validate(valid => {
        if (valid) {
          let temp = ''
          this.equipmentUnit.forEach(element => {
            temp = temp + '+' + element
          })
          temp = temp + '+'
          //
          const data = { ...this.record }
          data.equipment_unit = temp
          data.station_id = this.userInfo.belongToDeptId

          postDtsDraft(data)
            .then(() => {
              this.$router.push({ path: `/dashboard/dts/list` })
            })
            //  网络异常，清空页面数据显示，防止错误的操作
            .catch((err) => {
              if (err.response) {
              }
            })
        } else {
          return false
        }
      })
    }
  }
}
</script>
