<template>
  <AdminLayout title="KGB Updates">
    <div class="max-w-7xl mx-auto">
      <PageHeader title="KGB Updates" :crumbs="[{ label: 'Dashboard', href: '/spa' }, { label: 'Kepegawaian' }, { label: 'KGB Updates' }]">
        <template #actions>
          <Toolbar v-model="q" placeholder="Cari nama" @search="search">
            <n-button type="primary" @click="openCreate">Tambah</n-button>
          </Toolbar>
        </template>
      </PageHeader>
      <div class="overflow-x-auto -mx-1 sm:mx-0">
        <div class="min-w-[640px] sm:min-w-0">
          <n-data-table :columns="columns" :data="rows" :bordered="false" size="small" :remote="true" @update:sorter="onSort" />
        </div>
      </div>
      <div class="mt-4 flex justify-end">
        <n-pagination :page="page" :page-count="pageCount" @update:page="goPage" />
      </div>
    </div>
  </AdminLayout>
      <n-modal v-model:show="showModal" preset="dialog" :title="isEdit ? 'Ubah KGB' : 'Tambah KGB'">
        <n-form label-placement="top">
          <n-form-item label="Pegawai" :feedback="form.errors?.pegawai_id">
            <n-select v-model:value="form.pegawai_id" :options="pegawaiOptions || []" filterable placeholder="Pilih Pegawai" />
          </n-form-item>
          <n-form-item label="Tanggal KGB" :feedback="form.errors?.tanggal_kgb">
            <DatePicker v-model="form.tanggal_kgb" />
          </n-form-item>
          <n-form-item label="Jumlah Tahun" :feedback="form.errors?.jumlah_tahun">
            <n-input v-model:value="form.jumlah_tahun" placeholder="2" />
          </n-form-item>
          <n-form-item label="Tanggal Berikutnya" :feedback="form.errors?.tanggal_kgb_berikutnya">
            <DatePicker v-model="form.tanggal_kgb_berikutnya" />
          </n-form-item>
          <n-form-item label="Catatan" :feedback="form.errors?.catatan">
            <n-input v-model:value="form.catatan" placeholder="Catatan" />
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
import { NButton, NDataTable, NInput, NPagination, NModal, NForm, NFormItem, NSelect } from 'naive-ui'
import { computed, ref, watch, h } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import DatePicker from '../../Components/DatePicker.vue'

const props = defineProps({ items: Object, search: String, sort: String, dir: String, pegawaiOptions: Array })
const q = ref(props.search || '')
const page = ref(props.items.current_page || 1)
const pageCount = computed(() => props.items.last_page || 1)
const rows = computed(() => props.items.data || [])
const columns = [
  { title: 'Pegawai', key: 'pegawai_nama' },
  { title: 'Tanggal KGB', key: 'tanggal_kgb', sorter: 'default' },
  { title: 'Jml Tahun', key: 'jumlah_tahun', sorter: 'default' },
  { title: 'Berikutnya', key: 'tanggal_kgb_berikutnya', sorter: 'default' },
  { title: 'Catatan', key: 'catatan' },
  { title: '', key: 'actions', render(row){ return h('div', { class: 'text-right' }, [
    h(NButton, { text: true, size: 'small', onClick: () => openEdit(row) }, { default: () => 'Edit' }),
    h(NButton, { text: true, type: 'error', size: 'small', onClick: () => doDelete(row) }, { default: () => 'Hapus' }),
  ]) } }
]
function search(){ router.get('/spa/kgb-updates', { q: q.value, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true }) }
function goPage(p){ router.get('/spa/kgb-updates', { q: q.value, page: p, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true }) }
watch(() => props.items.current_page, (val)=>{ if (val) page.value = val })
const sort = ref(props.sort || '')
const dir = ref(props.dir || 'asc')
function onSort(sorter){
  if (!sorter || !sorter.columnKey || !sorter.order){ sort.value=''; dir.value='asc' }
  else { sort.value = sorter.columnKey; dir.value = sorter.order === 'descend' ? 'desc' : 'asc' }
  router.get('/spa/kgb-updates', { q: q.value, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true })
}
const showModal = ref(false)
const isEdit = ref(false)
const form = useForm({ id: null, pegawai_id: '', tanggal_kgb: '', jumlah_tahun: '', tanggal_kgb_berikutnya: '', catatan: '' })
function openCreate(){ isEdit.value=false; form.reset(); showModal.value=true }
function openEdit(row){ isEdit.value=true; Object.assign(form, { id: row.id, pegawai_id: row.pegawai_id, tanggal_kgb: row.tanggal_kgb, jumlah_tahun: row.jumlah_tahun, tanggal_kgb_berikutnya: row.tanggal_kgb_berikutnya, catatan: row.catatan }); showModal.value=true }
function submit(){ if(isEdit.value){ form.put(`/spa/kgb-updates/${form.id}`, { onSuccess: ()=> showModal.value=false }) } else { form.post('/spa/kgb-updates', { onSuccess: ()=> showModal.value=false }) } }
function doDelete(row){ router.delete(`/spa/kgb-updates/${row.id}`, { preserveState: true }) }
</script>
