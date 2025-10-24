<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Navigation -->
        <Header current-path="/invoices" />

        <!-- Main Content -->
        <div class="container-mobile py-6">
            <!-- Page Header -->
            <div class="px-4 py-6 sm:px-0">
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Invoices</h1>
                            <p class="mt-2 text-gray-600">Manage invoices and track payments</p>
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                                <button @click="showAddModal = true" class="btn btn-primary">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Create Invoice
                                </button>
                                <button @click="showBulkModal = true" class="btn btn-secondary">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Bulk Generate
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="stat-card">
                        <div class="stat-number text-sky-800">{{ stats.total_invoices }}</div>
                        <div class="stat-label">Total Invoices</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number text-green-600">{{ stats.paid_invoices }}</div>
                        <div class="stat-label">Paid</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number text-yellow-600">{{ stats.pending_invoices }}</div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number text-red-600">{{ stats.overdue_invoices }}</div>
                        <div class="stat-label">Overdue</div>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Total Amount</h3>
                        <p class="text-2xl font-bold text-gray-900">₹{{ formatPrice(stats.total_amount) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Paid Amount</h3>
                        <p class="text-2xl font-bold text-green-600">₹{{ formatPrice(stats.paid_amount) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Outstanding</h3>
                        <p class="text-2xl font-bold text-red-600">₹{{ formatPrice(stats.pending_amount + stats.overdue_amount) }}</p>
                    </div>
                </div>

                <!-- Filters and Search -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Search invoices..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tenant</label>
                            <select v-model="filters.tenant_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                                <option value="">All Tenants</option>
                                <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                                    {{ tenant.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select v-model="filters.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="overdue">Overdue</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                            <select v-model="filters.type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                                <option value="">All Types</option>
                                <option value="rent">Rent</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="penalty">Penalty</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                            <input
                                v-model="filters.due_date_from"
                                type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                            />
                        </div>
                    </div>
                    <div class="mt-4 flex justify-between">
                        <button @click="applyFilters" class="btn btn-primary">Apply Filters</button>
                        <button @click="clearFilters" class="btn btn-secondary">Clear Filters</button>
                    </div>
                </div>

                <!-- Invoices Table -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ invoice.invoice_number }}</div>
                                            <div class="text-sm text-gray-500">{{ invoice.type }}</div>
                                            <div class="text-sm text-gray-500">{{ formatDate(invoice.created_at) }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <img class="h-8 w-8 rounded-full" :src="getAvatarUrl(invoice.tenant?.name)" :alt="invoice.tenant?.name" />
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ invoice.tenant?.name }}</div>
                                                <div class="text-sm text-gray-500">{{ invoice.tenant?.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ invoice.property?.title }}</div>
                                        <div class="text-sm text-gray-500">{{ invoice.property?.locality }}, {{ invoice.property?.city }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">₹{{ formatPrice(invoice.amount) }}</div>
                                        <div v-if="invoice.payments && invoice.payments.length > 0" class="text-sm text-green-600">
                                            Paid: ₹{{ formatPrice(getTotalPaid(invoice.payments)) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ formatDate(invoice.due_date) }}</div>
                                        <div v-if="isOverdue(invoice.due_date, invoice.status)" class="text-sm text-red-600 font-medium">
                                            Overdue
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStatusBadgeClass(invoice.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ invoice.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button @click="viewInvoice(invoice)" class="text-sky-800 hover:text-sky-900">View</button>
                                            <button @click="downloadPdf(invoice)" class="text-green-600 hover:text-green-900">PDF</button>
                                            <button @click="editInvoice(invoice)" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                            <button v-if="invoice.status !== 'paid'" @click="openPaymentModal(invoice)" class="text-purple-600 hover:text-purple-900">Pay</button>
                                            <button @click="deleteInvoice(invoice)" class="text-red-600 hover:text-red-900">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-if="invoices.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No invoices</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new invoice.</p>
                        <div class="mt-6">
                            <button @click="showAddModal = true" class="btn btn-primary">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Create Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Invoice Modal -->
        <div v-if="showAddModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Create New Invoice</h3>
                        <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <InvoiceForm @saved="onInvoiceSaved" @cancelled="showAddModal = false" :tenants="tenants" :properties="properties" />
                </div>
            </div>
        </div>

        <!-- Bulk Generate Modal -->
        <div v-if="showBulkModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Generate Monthly Rent Invoices</h3>
                        <button @click="showBulkModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <BulkInvoiceForm @saved="onBulkInvoicesGenerated" @cancelled="showBulkModal = false" />
                </div>
            </div>
        </div>
        
        <!-- Payment Modal -->
        <PaymentModal 
            v-if="showPaymentModal" 
            :show="showPaymentModal" 
            :invoice="selectedInvoice"
            @close="closePaymentModal"
            @payment-success="onPaymentSuccess"
            @payment-failed="onPaymentFailed"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Header from '../../Components/Header.vue'
import InvoiceForm from './InvoiceForm.vue'
import BulkInvoiceForm from './BulkInvoiceForm.vue'
import PaymentModal from '../../Components/PaymentModal.vue'

const invoices = ref([])
const tenants = ref([])
const properties = ref([])
const loading = ref(false)
const showAddModal = ref(false)
const showBulkModal = ref(false)
const showPaymentModal = ref(false)
const selectedInvoice = ref(null)

const stats = ref({
    total_invoices: 0,
    paid_invoices: 0,
    pending_invoices: 0,
    overdue_invoices: 0,
    total_amount: 0,
    paid_amount: 0,
    pending_amount: 0,
    overdue_amount: 0
})

const filters = ref({
    search: '',
    tenant_id: '',
    status: '',
    type: '',
    due_date_from: ''
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-IN').format(price)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-IN')
}

const getAvatarUrl = (name) => {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&color=7F9CF5&background=EBF4FF`
}

const getStatusBadgeClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'paid': 'bg-green-100 text-green-800',
        'overdue': 'bg-red-100 text-red-800',
        'cancelled': 'bg-gray-100 text-gray-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const isOverdue = (dueDate, status) => {
    if (status === 'paid' || status === 'cancelled') return false
    return new Date(dueDate) < new Date()
}

const getTotalPaid = (payments) => {
    return payments.reduce((total, payment) => total + parseFloat(payment.amount), 0)
}

const loadInvoices = async () => {
    loading.value = true
    try {
        const response = await fetch('http://127.0.0.1:8000/api/v1/org/invoices?' + new URLSearchParams(filters.value))
        const data = await response.json()
        if (data.success) {
            invoices.value = data.data.data
        }
    } catch (error) {
        console.error('Error loading invoices:', error)
    } finally {
        loading.value = false
    }
}

const loadStatistics = async () => {
    try {
        const response = await fetch('http://127.0.0.1:8000/api/v1/org/invoices/statistics')
        const data = await response.json()
        if (data.success) {
            stats.value = data.data
        }
    } catch (error) {
        console.error('Error loading statistics:', error)
    }
}

const loadTenants = async () => {
    try {
        const response = await fetch('http://127.0.0.1:8000/api/v1/org/tenants')
        const data = await response.json()
        if (data.success) {
            tenants.value = data.data.data
        }
    } catch (error) {
        console.error('Error loading tenants:', error)
    }
}

const loadProperties = async () => {
    try {
        const response = await fetch('http://127.0.0.1:8000/api/v1/org/properties')
        const data = await response.json()
        if (data.success) {
            properties.value = data.data.data
        }
    } catch (error) {
        console.error('Error loading properties:', error)
    }
}

const applyFilters = () => {
    loadInvoices()
}

const clearFilters = () => {
    filters.value = {
        search: '',
        tenant_id: '',
        status: '',
        type: '',
        due_date_from: ''
    }
    loadInvoices()
}

const viewInvoice = (invoice) => {
    window.location.href = `/invoices/${invoice.id}`
}

const editInvoice = (invoice) => {
    window.location.href = `/invoices/${invoice.id}/edit`
}

const downloadPdf = async (invoice) => {
    try {
        const response = await fetch(`http://127.0.0.1:8000/api/v1/org/invoices/${invoice.id}/pdf`, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        })
        
        if (response.ok) {
            const blob = await response.blob()
            const url = window.URL.createObjectURL(blob)
            const a = document.createElement('a')
            a.href = url
            a.download = `invoice-${invoice.invoice_number}.pdf`
            document.body.appendChild(a)
            a.click()
            window.URL.revokeObjectURL(url)
            document.body.removeChild(a)
        }
    } catch (error) {
        console.error('Error downloading PDF:', error)
    }
}

const deleteInvoice = async (invoice) => {
    if (confirm('Are you sure you want to delete this invoice?')) {
        try {
            const response = await fetch(`http://127.0.0.1:8000/api/v1/org/invoices/${invoice.id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Content-Type': 'application/json'
                }
            })
            
            if (response.ok) {
                invoices.value = invoices.value.filter(i => i.id !== invoice.id)
                loadStatistics()
            }
        } catch (error) {
            console.error('Error deleting invoice:', error)
        }
    }
}

const onInvoiceSaved = () => {
    showAddModal.value = false
    loadInvoices()
    loadStatistics()
}

const onBulkInvoicesGenerated = () => {
    showBulkModal.value = false
    loadInvoices()
    loadStatistics()
}

const openPaymentModal = (invoice) => {
    selectedInvoice.value = invoice
    showPaymentModal.value = true
}

const closePaymentModal = () => {
    showPaymentModal.value = false
    selectedInvoice.value = null
}

const onPaymentSuccess = (paymentData) => {
    alert('Payment completed successfully!')
    loadInvoices()
    loadStatistics()
}

const onPaymentFailed = (error) => {
    alert('Payment failed: ' + error)
}

onMounted(() => {
    loadInvoices()
    loadStatistics()
    loadTenants()
    loadProperties()
})
</script>
