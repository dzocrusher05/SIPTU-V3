<template>
  <n-input ref="wrapper" v-model:value="display" :placeholder="placeholder || 'YYYY-MM-DD'" clearable />
  
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import { NInput } from 'naive-ui'
import flatpickr from 'flatpickr'

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'YYYY-MM-DD' },
  config: { type: Object, default: () => ({}) },
})
const emit = defineEmits(['update:modelValue'])
const wrapper = ref(null)
const display = ref(props.modelValue || '')
let instance = null

onMounted(async () => {
  await nextTick()
  const inputEl = wrapper.value?.$el?.querySelector('input')
  if (!inputEl) return
  instance = flatpickr(inputEl, {
    dateFormat: 'Y-m-d',
    defaultDate: props.modelValue || null,
    ...props.config,
    onChange: (dates) => {
      const d = dates && dates[0] ? formatDate(dates[0]) : ''
      display.value = d
      emit('update:modelValue', d)
    }
  })
})

onBeforeUnmount(() => { if (instance) { instance.destroy(); instance = null } })

watch(() => props.modelValue, (val) => {
  display.value = val || ''
  if (instance) instance.setDate(val || null, true)
})

watch(display, (val) => {
  if (!val) emit('update:modelValue', '')
})

function formatDate(date){
  const pad = (n) => String(n).padStart(2,'0')
  return `${date.getFullYear()}-${pad(date.getMonth()+1)}-${pad(date.getDate())}`
}
</script>
