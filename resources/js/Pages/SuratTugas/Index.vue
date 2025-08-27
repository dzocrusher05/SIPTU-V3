<template>
  <AdminLayout title="Surat Tugas">
    <div class="max-w-7xl mx-auto">
      <PageHeader title="Surat Tugas" :crumbs="[{ label: 'Dashboard', href: '/spa' }, { label: 'Kepegawaian' }, { label: 'Surat Tugas' }]">
        <template #actions>
          <Toolbar v-model="q" placeholder="Cari nomor/lokasi" @search="search">
            <n-button type="primary" @click="openCreate">Tambah</n-button>
          </Toolbar>
        </template>
      </PageHeader>
      <div class="overflow-x-auto -mx-1 sm:mx-0">
        <div class="min-w-[720px] sm:min-w-0">
          <n-data-table :columns="columns" :data="rows" :bordered="false" size="small" :remote="true" @update:sorter="onSort" />
        </div>
      </div>
      <div class="mt-4 flex justify-end">
        <n-pagination :page="page" :page-count="pageCount" @update:page="goPage" />
      </div>
    </div>
  </AdminLayout>
      <n-modal v-model:show="showModal" preset="dialog" :title="isEdit ? 'Ubah Surat Tugas' : 'Tambah Surat Tugas'">
        <n-form label-placement="top">
          <n-form-item label="Nomor ST" :feedback="form.errors?.nomor_st">
            <n-input v-model:value="form.nomor_st" placeholder="Nomor ST" />
          </n-form-item>
          <n-form-item label="Tanggal ST" :feedback="form.errors?.tanggal_st">
            <DatePicker v-model="form.tanggal_st" />
          </n-form-item>
          <n-form-item label="Rentang Tugas" :feedback="form.errors?.tanggal_mulai">
            <DateRangePicker :start="form.tanggal_mulai" :end="form.tanggal_selesai" @update:start="v => form.tanggal_mulai = v" @update:end="v => form.tanggal_selesai = v" />
          </n-form-item>
          <n-form-item label="Lokasi" :feedback="form.errors?.lokasi_tugas">
            <n-select v-model:value="form.lokasi_tugas" :options="lokasiOptions" filterable placeholder="Pilih Lokasi" />
          </n-form-item>
          <n-form-item v-if="form.lokasi_tugas === 'Lainnya'" label="Tujuan Tugas (lainnya)" :feedback="form.errors?.lokasi_tugas_custom">
            <n-input v-model:value="form.lokasi_tugas_custom" placeholder="Tulis tujuan tugas" />
          </n-form-item>
          <n-form-item label="Deskripsi" :feedback="form.errors?.deskripsi_tugas">
            <n-input v-model:value="form.deskripsi_tugas" placeholder="Deskripsi" />
          </n-form-item>
          <n-form-item label="Pegawai (opsional)" :feedback="form.errors?.pegawai_ids">
            <n-select v-model:value="form.pegawai_ids" :options="pegawaiOptions || []" multiple filterable placeholder="Pilih Pegawai" />
          </n-form-item>
          <n-form-item label="MAK (opsional)" :feedback="form.errors?.mak_ids">
            <n-select v-model:value="form.mak_ids" :options="makOptions || []" multiple filterable placeholder="Pilih MAK" />
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
import DateRangePicker from '../../Components/DateRangePicker.vue'

const props = defineProps({ items: Object, search: String, sort: String, dir: String, pegawaiOptions: Array, makOptions: Array })
const q = ref(props.search || '')
const page = ref(props.items.current_page || 1)
const pageCount = computed(() => props.items.last_page || 1)
const rows = computed(() => props.items.data || [])
const columns = [
  { title: 'Nomor ST', key: 'nomor_st', sorter: 'default' },
  { title: 'Tanggal ST', key: 'tanggal_st', sorter: 'default' },
  { title: 'Mulai', key: 'tanggal_mulai', sorter: 'default' },
  { title: 'Selesai', key: 'tanggal_selesai', sorter: 'default' },
  { title: 'Lokasi', key: 'lokasi_tugas', sorter: 'default' },
  { title: '', key: 'actions', render(row){ return h('div', { class: 'text-right' }, [
    h(NButton, { text: true, size: 'small', onClick: () => openEdit(row) }, { default: () => 'Edit' }),
    h(NButton, { text: true, type: 'error', size: 'small', onClick: () => doDelete(row) }, { default: () => 'Hapus' }),
  ]) } }
]
function search(){ router.get('/spa/surat-tugas', { q: q.value, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true }) }
function goPage(p){ router.get('/spa/surat-tugas', { q: q.value, page: p, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true }) }
watch(() => props.items.current_page, (val)=>{ if (val) page.value = val })
const showModal = ref(false)
const sort = ref(props.sort || '')
const dir = ref(props.dir || 'asc')
function onSort(sorter){
  if (!sorter || !sorter.columnKey || !sorter.order){ sort.value=''; dir.value='asc' }
  else { sort.value = sorter.columnKey; dir.value = sorter.order === 'descend' ? 'desc' : 'asc' }
  router.get('/spa/surat-tugas', { q: q.value, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true })
}
const isEdit = ref(false)
const form = useForm({ id: null, nomor_st: '', tanggal_st: '', tanggal_mulai: '', tanggal_selesai: '', lokasi_tugas: '', lokasi_tugas_custom: '', deskripsi_tugas: '', pegawai_ids: [], mak_ids: [] })
const lokasiOptions = [
  { label: 'Kota. Palopo', value: 'Kota. Palopo' },
  { label: 'Kab. Luwu', value: 'Kab. Luwu' },
  { label: 'Kab. Luwu Utara', value: 'Kab. Luwu Utara' },
  { label: 'Kab. Luwu Timur', value: 'Kab. Luwu Timur' },
  { label: 'Kab. Toraja Utara', value: 'Kab. Toraja Utara' },
  { label: 'Kab. Tana Toraja', value: 'Kab. Tana Toraja' },
  { label: 'Kab. Enrekang', value: 'Kab. Enrekang' },
  { label: 'Lainnya', value: 'Lainnya' },
]
function openCreate(){ isEdit.value=false; form.reset(); showModal.value=true }
function openEdit(row){
  isEdit.value=true;
  const allowed = new Set(lokasiOptions.map(o => o.value))
  const isListed = allowed.has(row.lokasi_tugas)
  Object.assign(form, {
    id: row.id,
    nomor_st: row.nomor_st,
    tanggal_st: row.tanggal_st,
    tanggal_mulai: row.tanggal_mulai,
    tanggal_selesai: row.tanggal_selesai,
    lokasi_tugas: isListed ? row.lokasi_tugas : 'Lainnya',
    lokasi_tugas_custom: isListed ? '' : (row.lokasi_tugas || ''),
    deskripsi_tugas: row.deskripsi_tugas,
    pegawai_ids: row.pegawai_ids || [],
    mak_ids: row.mak_ids || []
  });
  showModal.value=true
}
function submit(){ if(isEdit.value){ form.put(`/spa/surat-tugas/${form.id}`, { onSuccess: ()=> showModal.value=false }) } else { form.post('/spa/surat-tugas', { onSuccess: ()=> showModal.value=false }) } }
function doDelete(row){ router.delete(`/spa/surat-tugas/${row.id}`, { preserveState: true }) }
</script>
