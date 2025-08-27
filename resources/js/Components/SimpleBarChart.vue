<template>
  <div class="w-full">
    <div class="flex items-center justify-between mb-2">
      <div class="text-sm text-gray-600 dark:text-gray-300">{{ title }}</div>
      <div class="flex gap-3 text-xs">
        <div v-for="(ds, i) in datasets" :key="i" class="flex items-center gap-1">
          <span class="inline-block w-3 h-3 rounded-sm" :style="{ background: ds.color }" />
          <span class="text-gray-600 dark:text-gray-300">{{ ds.name }}</span>
        </div>
      </div>
    </div>
    <svg :viewBox="`0 0 ${viewWidth} ${viewHeight}`" preserveAspectRatio="none" class="w-full h-48">
      <!-- axes -->
      <line :x1="paddingLeft" :y1="paddingTop" :x2="paddingLeft" :y2="viewHeight - paddingBottom" stroke="#e5e7eb" />
      <line :x1="paddingLeft" :y1="viewHeight - paddingBottom" :x2="viewWidth - paddingRight" :y2="viewHeight - paddingBottom" stroke="#e5e7eb" />

      <!-- bars -->
      <g v-for="(label, i) in labels" :key="i">
        <g v-for="(ds, j) in datasets" :key="j">
          <rect
            :x="x(i) + j * barWidth"
            :y="y(ds.data[i])"
            :width="barWidth - gap"
            :height="(viewHeight - paddingBottom) - y(ds.data[i])"
            :fill="ds.color"
            rx="2"
          />
        </g>
        <!-- label -->
        <text :x="x(i) + (groupWidth/2)" :y="viewHeight - paddingBottom + 12" text-anchor="middle" class="fill-gray-500" style="font-size: 10px;">{{ label }}</text>
      </g>
    </svg>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: { type: String, default: 'Tren 6 Bulan' },
  labels: { type: Array, required: true },
  datasets: { type: Array, required: true } // [{ name, color, data: [] }]
})

const viewWidth = 700
const viewHeight = 220
const paddingLeft = 36
const paddingRight = 12
const paddingTop = 10
const paddingBottom = 24
const gap = 4

const labels = computed(() => props.labels || [])
const datasets = computed(() => props.datasets || [])

const maxY = computed(() => {
  const values = []
  for (const ds of datasets.value) values.push(...(ds.data || []))
  const max = Math.max(1, ...values)
  // round up to a friendly tick
  if (max <= 5) return 5
  if (max <= 10) return 10
  if (max <= 25) return 25
  if (max <= 50) return 50
  if (max <= 100) return 100
  return Math.ceil(max / 50) * 50
})

const groupWidth = computed(() => {
  const count = labels.value.length
  const innerWidth = viewWidth - paddingLeft - paddingRight
  return count > 0 ? innerWidth / count : innerWidth
})

const barWidth = computed(() => {
  const groups = datasets.value.length || 1
  return (groupWidth.value - gap) / groups
})

function x(i){
  return paddingLeft + i * groupWidth.value
}
function y(val){
  const usable = viewHeight - paddingTop - paddingBottom
  const ratio = val / maxY.value
  return paddingTop + (1 - ratio) * usable
}
</script>

<style scoped>
.fill-gray-500 { fill: #6b7280; }
</style>

