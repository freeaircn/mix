<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-30 20:13:01
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-29 10:38:43
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
    <div style="width: 100%">
      <a-textarea id="textarea_id" v-model="record.text" :rows="9"/>
    </div>
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
      modalVisible: false
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
      this.$confirm({
        title: '确认提交？',
        onOk: () => {
          return new Promise((resolve, reject) => {
            const result = Object.assign({}, this.record)
            this.$emit('confirm', result)
            this.modalVisible = false
            this.$emit('update:visible', false)
            resolve()
          })
        }
      })
    },
    handleVisibleChange (visible) {
      this.$emit('update:visible', visible)
    }
  }
}
</script>
