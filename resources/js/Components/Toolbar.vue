<template>
  <div class="flex items-center gap-2 flex-wrap w-full">
    <n-input
      v-model:value="model"
      :placeholder="placeholder || 'Cari...'"
      clearable
      class="w-full sm:w-72"
      @keydown.enter.prevent="emitSearch"
    />
    <n-button @click="emitSearch">Cari</n-button>
    <slot />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { NInput, NButton } from 'naive-ui'

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Cari...' }
})
const emit = defineEmits(['update:modelValue','search'])

const model = ref(props.modelValue)
watch(() => props.modelValue, v => { model.value = v || '' })
watch(model, v => emit('update:modelValue', v))

function emitSearch(){ emit('search', model.value) }
</script>
