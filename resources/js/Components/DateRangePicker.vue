<template>
  <n-input ref="wrapper" v-model:value="display" :placeholder="placeholder || 'YYYY-MM-DD to YYYY-MM-DD'" readonly clearable />
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import { NInput } from 'naive-ui'
import flatpickr from 'flatpickr'

const props = defineProps({
  start: { type: String, default: '' },
  end: { type: String, default: '' },
  placeholder: { type: String, default: 'YYYY-MM-DD to YYYY-MM-DD' },
  config: { type: Object, default: () => ({}) },
})
const emit = defineEmits(['update:start','update:end'])
const wrapper = ref(null)
const display = ref('')
let instance = null

function refreshDisplay(s, e){
  display.value = [s, e].filter(Boolean).join(' to ')
}

onMounted(async () => {
  await nextTick()
  const inputEl = wrapper.value?.$el?.querySelector('input')
  if (!inputEl) return
  instance = flatpickr(inputEl, {
    mode: 'range',
    dateFormat: 'Y-m-d',
    defaultDate: [props.start, props.end].filter(Boolean),
    ...props.config,
    onChange: (dates) => {
      const fmt = (d) => d ? formatDate(d) : ''
      const s = dates[0] ? fmt(dates[0]) : ''
      const e = dates[1] ? fmt(dates[1]) : ''
      emit('update:start', s)
      emit('update:end', e)
      refreshDisplay(s, e)
    }
  })
  refreshDisplay(props.start, props.end)
})

onBeforeUnmount(() => { if (instance) { instance.destroy(); instance = null } })

watch(() => [props.start, props.end], ([s,e]) => {
  if (instance) instance.setDate([s,e].filter(Boolean), true)
  refreshDisplay(s, e)
})

function formatDate(date){
  const pad = (n) => String(n).padStart(2,'0')
  return `${date.getFullYear()}-${pad(date.getMonth()+1)}-${pad(date.getDate())}`
}
</script>
