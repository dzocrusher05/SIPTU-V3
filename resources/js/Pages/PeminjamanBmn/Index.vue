<template>
  <AdminLayout title="Peminjaman BMN">
    <div class="max-w-7xl mx-auto">
      <PageHeader title="Peminjaman BMN" :crumbs="[{ label: 'Dashboard', href: '/spa' }, { label: 'BMN', href: '/spa/bmn' }, { label: 'Peminjaman' }]">
        <template #actions>
          <Toolbar v-model="q" placeholder="Cari nama/kode/nup" @search="search">
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
      <n-modal v-model:show="showModal" preset="dialog" :title="isEdit ? 'Ubah Peminjaman' : 'Tambah Peminjaman'">
        <n-form label-placement="top">
          <n-form-item label="BMN" :feedback="form.errors?.bmn_id">
            <n-select v-model:value="form.bmn_id" :options="bmnOptions" filterable placeholder="Pilih BMN" />
          </n-form-item>
          <n-form-item label="Pegawai" :feedback="form.errors?.pegawai_id">
            <n-select v-model:value="form.pegawai_id" :options="pegawaiOptions" filterable placeholder="Pilih Pegawai" />
          </n-form-item>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <n-form-item label="Tanggal Mulai" :feedback="form.errors?.tanggal_mulai">
              <DatePicker v-model="form.tanggal_mulai" />
            </n-form-item>
            <n-form-item label="Sampai" :feedback="form.errors?.tanggal_sampai">
              <DatePicker v-model="form.tanggal_sampai" />
            </n-form-item>
          </div>
          <n-form-item label="Keperluan" :feedback="form.errors?.keperluan">
            <n-input v-model:value="form.keperluan" placeholder="Keperluan" />
          </n-form-item>
          <n-form-item label="Kota Tujuan" :feedback="form.errors?.lokasi_tujuan">
            <n-select v-model:value="form.lokasi_tujuan" :options="kotaOptions" filterable placeholder="Pilih kota tujuan" />
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
import { NButton, NDataTable, NInput, NPagination, NModal, NForm, NFormItem, NSelect } from 'naive-ui'
import { computed, ref, watch, h } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import DatePicker from '../../Components/DatePicker.vue'

const props = defineProps({ items: Object, search: String, sort: String, dir: String, bmnsOptions: Array, pegawaiOptions: Array })
const q = ref(props.search || '')
const page = ref(props.items.current_page || 1)
const pageCount = computed(() => props.items.last_page || 1)
const rows = computed(() => props.items.data || [])
const columns = [
  { title: 'BMN', key: 'bmn_label' },
  { title: 'Pegawai', key: 'pegawai_nama' },
  { title: 'Tgl Pinjam', key: 'tanggal_pinjam', sorter: 'default' },
  { title: 'Tgl Kembali', key: 'tanggal_kembali', sorter: 'default' },
  { title: 'Tujuan', key: 'lokasi_tujuan' },
  { title: 'Status', key: 'status', sorter: 'default' },
  { title: '', key: 'actions', render(row){ return h('div', { class: 'text-right space-x-1' }, [
    row.status==='pending' ? h(NButton, { text: true, size: 'small', onClick: () => doApprove(row) }, { default: () => 'Setujui' }) : null,
    row.status==='dipinjam' ? h(NButton, { text: true, size: 'small', onClick: () => doReturn(row) }, { default: () => 'Kembalikan' }) : null,
    h(NButton, { text: true, size: 'small', onClick: () => openEdit(row) }, { default: () => 'Edit' }),
    h(NButton, { text: true, type: 'error', size: 'small', onClick: () => doDelete(row) }, { default: () => 'Hapus' }),
  ]) } }
]
function search(){ router.get('/spa/peminjaman-bmn', { q: q.value, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true }) }
function goPage(p){ router.get('/spa/peminjaman-bmn', { q: q.value, page: p, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true }) }
watch(() => props.items.current_page, (val)=>{ if (val) page.value = val })
const showModal = ref(false)
const sort = ref(props.sort || '')
const dir = ref(props.dir || 'asc')
function onSort(sorter){
  if (!sorter || !sorter.columnKey || !sorter.order){ sort.value=''; dir.value='asc' }
  else { sort.value = sorter.columnKey; dir.value = sorter.order === 'descend' ? 'desc' : 'asc' }
  router.get('/spa/peminjaman-bmn', { q: q.value, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true })
}
const isEdit = ref(false)
const form = useForm({ id: null, bmn_id: null, pegawai_id: null, tanggal_mulai: '', tanggal_sampai: '', keperluan: '', lokasi_tujuan: '', keterangan: '' })
const bmnOptions = computed(()=> (props.bmnsOptions || []))
const pegawaiOptions = computed(()=> (props.pegawaiOptions || []))
const kotaOptions = [
  { label: 'Kota Palopo', value: 'Kota Palopo' },
  { label: 'Kab. Luwu', value: 'Kab. Luwu' },
  { label: 'Kab. Luwu Timur', value: 'Kab. Luwu Timur' },
  { label: 'Kab. Luwu Utara', value: 'Kab. Luwu Utara' },
  { label: 'Kab. Tana Toraja', value: 'Kab. Tana Toraja' },
  { label: 'Kab. Toraja Utara', value: 'Kab. Toraja Utara' },
  { label: 'Kab. Enrekang', value: 'Kab. Enrekang' },
]
function openCreate(){ isEdit.value=false; form.reset(); showModal.value=true }
function openEdit(row){
  isEdit.value=true;
  Object.assign(form, { id: row.id, bmn_id: row.bmn_id, pegawai_id: row.pegawai_id, tanggal_mulai: row.tanggal_pinjam, tanggal_sampai: row.tanggal_kembali, keperluan: row.keperluan, lokasi_tujuan: row.lokasi_tujuan, keterangan: row.keterangan })
  showModal.value=true
}
function submit(){ if(isEdit.value){ form.put(`/spa/peminjaman-bmn/${form.id}`, { onSuccess: ()=> showModal.value=false }) } else { form.post('/spa/peminjaman-bmn', { onSuccess: ()=> showModal.value=false }) } }
function doDelete(row){ router.delete(`/spa/peminjaman-bmn/${row.id}`, { preserveState: true }) }
function doApprove(row){ router.put(`/spa/peminjaman-bmn/${row.id}/approve`, {}, { preserveState: true }) }
function doReturn(row){ router.put(`/spa/peminjaman-bmn/${row.id}/return`, {}, { preserveState: true }) }
</script>
