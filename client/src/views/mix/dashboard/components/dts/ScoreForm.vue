<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-30 20:13:01
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-28 11:26:52
-->
<template>
  <a-modal
    :title="title"
    :visible="modalVisible"
    :width="700"
    :centered="true"
    :destroyOnClose="true"
    :maskClosable="false"
    @ok="handleOk"
    @change="handleVisibleChange"
  >
    <a-form-model
      ref="form"
      :model="record"
      :rules="rules"
      :label-col="labelCol"
      :wrapper-col="wrapperCol"
    >
      <a-form-model-item label="分数" prop="title">
        <a-input-number v-model="record.score" :min="-100" :max="100"></a-input-number>
      </a-form-model-item>
      <a-form-model-item label="评分说明" prop="title">
        <a-textarea v-model="record.score_desc" :rows="6" />
      </a-form-model-item>
    </a-form-model>
  </a-modal>
</template>

<script>

export default {
  name: 'Form',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: ''
    },
    record: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      modalVisible: false,
      labelCol: {
        lg: { span: 4 }, sm: { span: 4 }
      },
      wrapperCol: {
        lg: { span: 18 }, sm: { span: 18 }
      },
      rules: {
        score: [{ required: true, message: '请选择', trigger: ['change'] }],
        score_desc: [{ required: true, message: '请输入', trigger: ['change'] }]
      }
    }
  },
  watch: {
    visible: {
      handler: function (val) {
        this.modalVisible = val
      },
      immediate: true
    }
  },
  methods: {
    handleOk () {
      const result = Object.assign({}, this.record)
      this.$emit('confirm', result)
      this.modalVisible = false
      this.$emit('update:visible', false)
    },
    handleVisibleChange (visible) {
      this.$emit('update:visible', visible)
    }
  }
}
</script>
