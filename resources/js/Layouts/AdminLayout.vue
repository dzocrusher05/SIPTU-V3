<template>
  <n-config-provider :theme="isDark ? darkTheme : null" :theme-overrides="themeOverrides">
    <n-message-provider>
    <n-layout has-sider position="absolute" class="min-h-screen">
      <n-layout-sider
        bordered
        collapse-mode="width"
        :collapsed-width="collapsedWidth"
        :width="240"
        :collapsed="collapsed"
        show-trigger
        :style="isMobile ? { position: 'fixed', height: '100%', zIndex: 40, left: 0, top: 0 } : {}"
        @collapse="collapsed = true"
        @expand="collapsed = false"
      >
        <div class="p-3 text-xl font-semibold flex items-center gap-2">
          <span>SIPTU.V3</span>
        </div>
        <n-menu :value="activeKey" :options="menuOptions" :default-expanded-keys="defaultExpandedKeys" @update:value="onSelect" />
      </n-layout-sider>
      <n-layout>
        <n-layout-header bordered>
          <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-14">
            <div class="flex items-center gap-3">
              <button class="lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-md border" @click="toggleSider">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                  <path d="M3.75 6.75h16.5a.75.75 0 0 0 0-1.5H3.75a.75.75 0 0 0 0 1.5zm0 6h16.5a.75.75 0 0 0 0-1.5H3.75a.75.75 0 0 0 0 1.5zm0 6h16.5a.75.75 0 0 0 0-1.5H3.75a.75.75 0 0 0 0 1.5z" />
                </svg>
              </button>
              <div class="font-semibold !text-black">{{ title }}</div>
            </div>
            <div class="flex items-center gap-3">
              <n-switch :value="isDark" @update:value="toggleDark">
                <template #checked>Dark</template>
                <template #unchecked>Light</template>
              </n-switch>
              <Link :href="route('logout')" method="post" as="button" class="px-3 py-1 border rounded">
                Logout
              </Link>
            </div>
          </div>
        </n-layout-header>
        <n-layout-content content-style="padding: 16px;">
          <slot />
        </n-layout-content>
      </n-layout>
    </n-layout>
    <!-- Overlay for mobile sidebar -->
    <div v-if="isMobile && !collapsed" class="fixed inset-0 bg-black/40 z-30 lg:hidden" @click="collapsed = true"></div>
    <FlashToaster />
    </n-message-provider>
  </n-config-provider>
  
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, h } from 'vue'
import { NLayout, NLayoutHeader, NLayoutSider, NLayoutContent, NMenu, NSwitch, NConfigProvider, NMessageProvider, NIcon, darkTheme } from 'naive-ui'
import { Link, router } from '@inertiajs/vue3'
import FlashToaster from '../Components/FlashToaster.vue'

const props = defineProps({ title: { type: String, default: 'Admin' } })

const collapsed = ref(false)
const isMobile = ref(false)
const collapsedWidth = computed(() => isMobile.value ? 0 : 64)
const isDark = ref(false)
const darkKey = 'siptuv3:theme:dark'
onMounted(() => {
  const saved = localStorage.getItem(darkKey)
  isDark.value = saved === '1'
  updateIsMobile()
  window.addEventListener('resize', updateIsMobile)
})
onBeforeUnmount(() => window.removeEventListener('resize', updateIsMobile))
function toggleDark(val){
  isDark.value = val
  localStorage.setItem(darkKey, val ? '1' : '0')
}
function updateIsMobile(){
  isMobile.value = window.innerWidth < 1024 // lg breakpoint
  if (isMobile.value) collapsed.value = true
}
function toggleSider(){
  collapsed.value = !collapsed.value
}

function renderIcon(pathD){
  return () => h(NIcon, null, {
    default: () => h('svg', { xmlns:'http://www.w3.org/2000/svg', viewBox:'0 0 24 24', fill:'currentColor', width: 18, height: 18 }, [ h('path', { d: pathD }) ])
  })
}
const icons = {
  home: 'M11.47 3.84a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 0 1-.53 1.28h-.69v6.5a.75.75 0 0 1-.75.75h-4.5a.75.75 0 0 1-.75-.75v-4.25a2.25 2.25 0 0 0-2.25-2.25h-1.5A2.25 2.25 0 0 0 8 16.06V20.5a.75.75 0 0 1-.75.75h-4.5a.75.75 0 0 1-.75-.75V13.81h-.69a.75.75 0 0 1-.53-1.28l8.69-8.69z',
  users: 'M15 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM4.5 18.75a6.75 6.75 0 0 1 13.5 0v.75h-13.5v-.75z',
  briefcase: 'M9 3.75A2.25 2.25 0 0 1 11.25 1.5h1.5A2.25 2.25 0 0 1 15 3.75V6h3.75A1.75 1.75 0 0 1 20.5 7.75v9.5A1.75 1.75 0 0 1 18.75 19H5.25A1.75 1.75 0 0 1 3.5 17.25v-9.5A1.75 1.75 0 0 1 5.25 6H9V3.75zM10.5 6h3V3.75a.75.75 0 0 0-.75-.75h-1.5a.75.75 0 0 0-.75.75V6z',
  doc: 'M6.75 2.25A1.5 1.5 0 0 0 5.25 3.75v16.5A1.5 1.5 0 0 0 6.75 21.75h10.5a1.5 1.5 0 0 0 1.5-1.5V8.56a1.5 1.5 0 0 0-.44-1.06l-4.56-4.56a1.5 1.5 0 0 0-1.06-.44H6.75zM9 9.75h6a.75.75 0 1 1 0 1.5H9a.75.75 0 0 1 0-1.5zm0 3h6a.75.75 0 1 1 0 1.5H9a.75.75 0 0 1 0-1.5z',
  money: 'M3 6.75A1.75 1.75 0 0 1 4.75 5h14.5A1.75 1.75 0 0 1 21 6.75v10.5A1.75 1.75 0 0 1 19.25 19H4.75A1.75 1.75 0 0 1 3 17.25V6.75zM7.5 12a4.5 4.5 0 1 0 9 0 4.5 4.5 0 0 0-9 0z',
  chip: 'M4.5 6.75A2.25 2.25 0 0 1 6.75 4.5h10.5A2.25 2.25 0 0 1 19.5 6.75v10.5A2.25 2.25 0 0 1 17.25 19.5H6.75A2.25 2.25 0 0 1 4.5 17.25V6.75zM9 9h6v6H9V9z',
  folder: 'M2.25 6.75A1.75 1.75 0 0 1 4 5h4.19a1.5 1.5 0 0 1 1.06.44L10.81 7H20a1.75 1.75 0 0 1 1.75 1.75v7.5A1.75 1.75 0 0 1 20 18H4a1.75 1.75 0 0 1-1.75-1.75v-9.5z',
  square: 'M4 4h16v16H4z',
  circle: 'M12 2a10 10 0 1 1 0 20 10 10 0 0 1 0-20z'
}

const menuOptions = [
  { label: 'Dashboard', key: '/spa', icon: renderIcon(icons.home) },
  {
    label: 'Kepegawaian', key: 'grp-kepeg', icon: renderIcon(icons.users),
    children: [
      { label: 'Data Pegawai', key: '/spa/pegawai', icon: renderIcon(icons.users) },
      { label: 'KGB Updates', key: '/spa/kgb-updates', icon: renderIcon(icons.circle) },
      { label: 'Surat Tugas', key: '/spa/surat-tugas', icon: renderIcon(icons.doc) }
    ]
  },
  {
    label: 'BMN', key: 'grp-bmn', icon: renderIcon(icons.briefcase),
    children: [
      { label: 'Data BMN', key: '/spa/bmn', icon: renderIcon(icons.briefcase) },
      { label: 'Peminjaman BMN', key: '/spa/peminjaman-bmn', icon: renderIcon(icons.folder) }
    ]
  },
  {
    label: 'Keuangan', key: 'grp-keu', icon: renderIcon(icons.money),
    children: [
      { label: 'MAK', key: '/spa/mak', icon: renderIcon(icons.money) },
      { label: 'LPJ', key: '/spa/lpj', icon: renderIcon(icons.square) }
    ]
  },
  { label: 'IT Aset', key: '/spa/it-assets', icon: renderIcon(icons.chip) }
]

const activeKey = computed(() => {
  const path = window.location.pathname
  if (path === '/spa') return '/spa'
  for (const m of menuOptions) {
    if (m.children) {
      const child = m.children.find(c => path.startsWith(c.key))
      if (child) return child.key
    } else if (path.startsWith(m.key)) {
      return m.key
    }
  }
  return '/spa'
})

function onSelect(key) {
  if (String(key).startsWith('grp-')) return
  router.visit(key)
}

const defaultExpandedKeys = computed(() => {
  const path = window.location.pathname
  const keys = []
  for (const m of menuOptions) {
    if (m.children && m.children.some(c => path.startsWith(c.key))) keys.push(m.key)
  }
  return keys
})

const route = (name, params = {}) => {
  // Basic route helper fallback; you can replace with Ziggy if needed
  if (name === 'logout') return '/logout'
  return '/'
}

const themeOverrides = computed(() => ({
  common: {
    primaryColor: '#2563eb',
    primaryColorHover: '#1d4ed8',
    primaryColorPressed: '#1e40af',
    borderRadius: '8px',
    fontFamily: 'Figtree, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, Apple Color Emoji, Segoe UI Emoji'
  },
  Button: {
    borderRadius: '8px'
  },
  Card: {
    borderRadius: '10px'
  },
  Input: {
    borderRadius: '8px'
  },
  Pagination: {
    itemBorderRadius: '8px'
  },
  Menu: {
    itemColorActive: 'rgba(37,99,235,0.08)',
    itemTextColorActive: '#1d4ed8',
    itemIconColorActive: '#1d4ed8',
    borderRadius: '6px'
  }
}))

// FlashToaster handles showing flash messages inside NMessageProvider
</script>

<style scoped>
</style>
