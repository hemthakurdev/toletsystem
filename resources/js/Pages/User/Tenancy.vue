<template>
  <div class="min-h-screen bg-gray-100">
    <Header :current-path="$page.url" />
    <div class="container mx-auto py-8">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-sky-900">My Rent & Invoices</h1>
        <button class="btn btn-secondary" @click="loadInvoices">Refresh</button>
      </div>

      <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
          <input v-model="filters.property_id" type="number" placeholder="Property ID (optional)" class="form-input" />
          <select v-model="filters.status" class="form-select">
            <option value="">All Status</option>
            <option value="draft">Draft</option>
            <option value="sent">Sent</option>
            <option value="overdue">Overdue</option>
            <option value="paid">Paid</option>
          </select>
          <button class="btn btn-primary" @click="applyFilters">Apply Filters</button>
          <button class="btn btn-secondary" @click="clearFilters">Clear</button>
        </div>
      </div>

      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-sky-800"></div>
        <p class="mt-2 text-gray-600">Loading invoices...</p>
      </div>

      <div v-else class="overflow-x-auto bg-white rounded-lg shadow-sm">
        <table class="min-w-full">
          <thead class="bg-sky-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-sky-900">Invoice</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-sky-900">Property</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-sky-900">Dates</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-sky-900">Amount</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-sky-900">Status</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-sky-900">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="inv in invoices" :key="inv.id" class="border-t">
              <td class="px-4 py-3">
                <div class="font-medium text-gray-900">{{ inv.invoice_no }}</div>
                <div class="text-xs text-gray-500">Created: {{ formatDate(inv.invoice_date) }}</div>
              </td>
              <td class="px-4 py-3">
                <div class="font-medium text-gray-900">{{ inv.property?.title || `#${inv.property_id}` }}</div>
                <div class="text-xs text-gray-500">{{ inv.property?.locality }}, {{ inv.property?.city }}</div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm text-gray-900">Due: {{ formatDate(inv.due_date) }}</div>
                <div v-if="inv.status !== 'paid' && isOverdue(inv)" class="text-xs text-red-600">Overdue</div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm font-semibold text-gray-900">₹{{ formatAmount(inv.total_amount || inv.amount) }}</div>
              </td>
              <td class="px-4 py-3">
                <span :class="statusBadge(inv.status)" class="px-2 py-1 rounded text-xs font-semibold">{{ inv.status }}</span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <a v-if="inv.pdf_url" :href="`/storage/${inv.pdf_url}`" target="_blank" class="btn btn-secondary btn-sm">View PDF</a>
                  <button v-if="inv.status !== 'paid'" class="btn btn-primary btn-sm" @click="payInvoice(inv)">Pay Now</button>
                </div>
              </td>
            </tr>
            <tr v-if="invoices.length === 0">
              <td colspan="6" class="px-4 py-6 text-center text-gray-500">No invoices found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import Header from '@/Components/Header.vue'
import { ref, onMounted } from 'vue'
import axios from 'axios'

const invoices = ref([])
const loading = ref(false)
const filters = ref({ property_id: '', status: '' })

const formatDate = (d) => d ? new Date(d).toLocaleDateString() : '—'
const formatAmount = (n) => new Intl.NumberFormat('en-IN').format(n || 0)
const isOverdue = (inv) => inv.due_date && new Date(inv.due_date) < new Date() && inv.status !== 'paid'
const statusBadge = (status) => ({
  draft: 'bg-gray-100 text-gray-700',
  sent: 'bg-blue-100 text-blue-700',
  overdue: 'bg-red-100 text-red-700',
  paid: 'bg-green-100 text-green-700'
}[status] || 'bg-gray-100 text-gray-700')

const loadInvoices = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.value.property_id) params.property_id = filters.value.property_id
    if (filters.value.status) params.status = filters.value.status
    const res = await axios.get('/user/api/v1/user/invoices', { params, withCredentials: true })
    if (res.data?.success) {
      const payload = res.data.data
      invoices.value = Array.isArray(payload.data) ? payload.data : (Array.isArray(payload) ? payload : [])
    }
  } catch (e) {
    // noop display-friendly
  } finally {
    loading.value = false
  }
}

const applyFilters = () => loadInvoices()
const clearFilters = () => { filters.value = { property_id: '', status: '' }; loadInvoices() }
const payInvoice = (inv) => {
  // placeholder: integrate payment gateway in future
  alert('Online payment coming soon. Please contact owner to settle this invoice.')
}

onMounted(() => {
  // pick up property_id from query if present
  try {
    const url = new URL(window.location.href)
    const pid = url.searchParams.get('property_id')
    if (pid) filters.value.property_id = pid
  } catch (_) {}
  loadInvoices()
})
</script>

<style scoped>
.container { max-width: 80rem; }
</style>


