<template>
  <AdminLayout title="IT Aset">
    <div class="max-w-7xl mx-auto">
      <PageHeader title="IT Aset" :crumbs="[{ label: 'Dashboard', href: '/spa' }, { label: 'IT Aset' }]">
        <template #actions>
          <Toolbar v-model="q" placeholder="Cari kode/nama/merek" @search="search">
            <n-button type="primary" @click="openCreate">Tambah</n-button>
          </Toolbar>
        </template>
      </PageHeader>
      <div class="overflow-x-auto -mx-1 sm:mx-0">
        <div class="min-w-[680px] sm:min-w-0">
          <n-data-table :columns="columns" :data="rows" :bordered="false" size="small" :remote="true" @update:sorter="onSort" />
        </div>
      </div>
      <div class="mt-4 flex justify-end">
        <n-pagination :page="page" :page-count="pageCount" @update:page="goPage" />
      </div>
    </div>
  </AdminLayout>
      <n-modal v-model:show="showModal" preset="dialog" :title="isEdit ? 'Ubah IT Aset' : 'Tambah IT Aset'">
        <n-form label-placement="top">
          <n-form-item label="Kode" :feedback="form.errors?.kode_aset">
            <n-input v-model:value="form.kode_aset" placeholder="Kode" />
          </n-form-item>
          <n-form-item label="Nama Perangkat" :feedback="form.errors?.nama_perangkat">
            <n-input v-model:value="form.nama_perangkat" placeholder="Nama Perangkat" />
          </n-form-item>
          <n-form-item label="Merek/Model" :feedback="form.errors?.merek_model">
            <n-input v-model:value="form.merek_model" placeholder="Merek/Model" />
          </n-form-item>
          <n-form-item label="Serial Number" :feedback="form.errors?.serial_number">
            <n-input v-model:value="form.serial_number" placeholder="Serial Number" />
          </n-form-item>
          <n-form-item label="Kondisi" :feedback="form.errors?.kondisi">
            <n-input v-model:value="form.kondisi" placeholder="Kondisi" />
          </n-form-item>
          <n-form-item label="Lokasi" :feedback="form.errors?.lokasi">
            <n-input v-model:value="form.lokasi" placeholder="Lokasi" />
          </n-form-item>
          <n-form-item label="Penanggung Jawab" :feedback="form.errors?.penanggung_jawab">
            <n-input v-model:value="form.penanggung_jawab" placeholder="Penanggung Jawab" />
          </n-form-item>
          <n-form-item label="Keterangan" :feedback="form.errors?.keterangan">
            <n-input v-model:value="form.keterangan" placeholder="Keterangan" />
          </n-form-item>
          <div class="flex justify-end gap-2">
            <n-button @click="showModal=false">Batal</n-button>
            <n-button type="primary" :loading="form.processing" @click="submit">Simpan</n-button>
          </div>
        </n-form>
      </n-modal>
    </template>

<script setup>
import AdminLayout from '../../Layouts/AdminLayout.vue'
import PageHeader from '../../Components/PageHeader.vue'
import Toolbar from '../../Components/Toolbar.vue'
import { NButton, NDataTable, NInput, NPagination, NModal, NForm, NFormItem } from 'naive-ui'
import { computed, ref, watch, h } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({ items: Object, search: String, sort: String, dir: String })
const q = ref(props.search || '')
const page = ref(props.items.current_page || 1)
const pageCount = computed(() => props.items.last_page || 1)
const rows = computed(() => props.items.data || [])
const columns = [
  { title: 'Kode', key: 'kode_aset', sorter: 'default' },
  { title: 'Perangkat', key: 'nama_perangkat', sorter: 'default' },
  { title: 'Merek/Model', key: 'merek_model', sorter: 'default' },
  { title: 'Serial', key: 'serial_number', sorter: 'default' },
  { title: 'Kondisi', key: 'kondisi', sorter: 'default' },
  { title: 'Lokasi', key: 'lokasi', sorter: 'default' },
  { title: 'PJ', key: 'penanggung_jawab', sorter: 'default' },
  { title: '', key: 'actions', render(row){ return h('div', { class: 'text-right' }, [
    h(NButton, { text: true, size: 'small', onClick: () => openEdit(row) }, { default: () => 'Edit' }),
    h(NButton, { text: true, type: 'error', size: 'small', onClick: () => doDelete(row) }, { default: () => 'Hapus' }),
  ]) } }
]
function search(){ router.get('/spa/it-assets', { q: q.value, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true }) }
function goPage(p){ router.get('/spa/it-assets', { q: q.value, page: p, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true }) }
watch(() => props.items.current_page, (val)=>{ if (val) page.value = val })
const sort = ref(props.sort || '')
const dir = ref(props.dir || 'asc')
function onSort(sorter){
  if (!sorter || !sorter.columnKey || !sorter.order){ sort.value=''; dir.value='asc' }
  else { sort.value = sorter.columnKey; dir.value = sorter.order === 'descend' ? 'desc' : 'asc' }
  router.get('/spa/it-assets', { q: q.value, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true })
}
const showModal = ref(false)
const isEdit = ref(false)
const form = useForm({ id: null, kode_aset: '', nama_perangkat: '', merek_model: '', serial_number: '', kondisi: '', lokasi: '', penanggung_jawab: '', keterangan: '' })
function openCreate(){ isEdit.value=false; form.reset(); showModal.value=true }
function openEdit(row){ isEdit.value=true; Object.assign(form, { id: row.id, kode_aset: row.kode_aset, nama_perangkat: row.nama_perangkat, merek_model: row.merek_model, serial_number: row.serial_number, kondisi: row.kondisi, lokasi: row.lokasi, penanggung_jawab: row.penanggung_jawab, keterangan: row.keterangan }); showModal.value=true }
function submit(){ if(isEdit.value){ form.put(`/spa/it-assets/${form.id}`, { onSuccess: ()=> showModal.value=false }) } else { form.post('/spa/it-assets', { onSuccess: ()=> showModal.value=false }) } }
function doDelete(row){ router.delete(`/spa/it-assets/${row.id}`, { preserveState: true }) }
</script>
