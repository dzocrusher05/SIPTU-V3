<template>
  <div />
</template>

<script setup>
import { watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useMessage } from 'naive-ui'

const page = usePage()
const message = useMessage()

watch(() => page.props.flash, (f) => {
  if (!f) return
  if (f.success) message.success(String(f.success))
  if (f.error) message.error(String(f.error))
  if (f.warning) message.warning(String(f.warning))
  if (f.info) message.info(String(f.info))
}, { deep: true, immediate: true })
</script>
