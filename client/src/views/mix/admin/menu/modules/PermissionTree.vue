<!--
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-30 20:13:01
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-02 22:03:20
-->
<template>
  <a-modal
    :title="modalTitle"
    :visible="modalVisible"
    :width="700"
    :centered="true"
    :maskClosable="false"
    @ok="handleOk"
    @change="handleVisibleChange"
  >
    <a-tree
      v-model="checkedKeys2"
      checkable
      :replaceFields="{ children:'children', title:'title', key:'id' }"
      autoExpandParent
      defaultExpandAll
      :selected-keys="selectedKeys"
      :tree-data="treeData"
    />
  </a-modal>
</template>

<script>

export default {
  name: 'PermissionTree',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    role: {
      type: Object,
      default: null
    },
    treeData: {
      type: Array,
      default: () => []
    },
    checkedKeys: {
      type: Array,
      default: () => []
    }
  },
  data () {
    return {
      modalTitle: '',
      checkedKeys2: [],
      selectedKeys: [],
      //
      modalVisible: false,
      labelCol: {
        xs: { span: 24 },
        sm: { span: 7 }
      },
      wrapperCol: {
        xs: { span: 24 },
        sm: { span: 13 }
      }
    }
  },
  watch: {
    visible: {
      handler: function (val) {
        this.modalTitle = '角色权限' + ' - ' + this.role.name
        this.modalVisible = val
      },
      immediate: true
    },
    checkedKeys: {
      handler: function (val) {
        this.checkedKeys2 = val
      },
      immediate: true
    }
  },
  methods: {
    handleOk () {
      const res = {
        role_id: this.role.id,
        menus: this.checkedKeys2
      }
      this.$emit('submitPermission', res)
      this.modalVisible = false
      this.$emit('update:visible', false)
    },

    handleVisibleChange (visible) {
      this.$emit('update:visible', visible)
    }

  }
}
</script>
