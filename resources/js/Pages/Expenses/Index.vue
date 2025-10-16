<template>
    <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Expense Management</h1>
                <p class="text-gray-600">Track and manage your property expenses</p>
            </div>
            <button @click="showAddModal = true" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Expense
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-sky-100 rounded-lg">
                        <svg class="w-6 h-6 text-sky-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Total Expenses</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total_expenses || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Total Amount</p>
                        <p class="text-2xl font-bold text-gray-900">₹{{ formatNumber(stats.total_amount || 0) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Pending</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.pending_expenses || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">This Month</p>
                        <p class="text-2xl font-bold text-gray-900">₹{{ formatNumber(stats.monthly_expenses || 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input
                        v-model="filters.search"
                        @input="debouncedSearch"
                        type="text"
                        placeholder="Search expenses..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Property</label>
                    <select v-model="filters.property_id" @change="loadExpenses" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                        <option value="">All Properties</option>
                        <option v-for="property in properties" :key="property.id" :value="property.id">
                            {{ property.title }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select v-model="filters.category" @change="loadExpenses" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                        <option value="">All Categories</option>
                        <option v-for="category in categories" :key="category" :value="category">
                            {{ category }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select v-model="filters.status" @change="loadExpenses" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Expenses Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Expense Details
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Property
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="loading">
                            <td colspan="6" class="px-6 py-4 text-center">
                                <div class="flex justify-center">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-sky-800"></div>
                                </div>
                            </td>
                        </tr>
                        <tr v-else-if="expenses.length === 0">
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No expenses found
                            </td>
                        </tr>
                        <tr v-else v-for="expense in expenses" :key="expense.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ expense.description }}</div>
                                    <div class="text-sm text-gray-500">{{ expense.category }}</div>
                                    <div v-if="expense.vendor" class="text-sm text-gray-500">Vendor: {{ expense.vendor }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ expense.property?.title || 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">₹{{ formatNumber(expense.amount) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ formatDate(expense.expense_date) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getStatusClass(expense.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ expense.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button @click="viewExpense(expense)" class="text-sky-800 hover:text-sky-900">
                                        View
                                    </button>
                                    <button @click="editExpense(expense)" class="text-indigo-600 hover:text-indigo-900">
                                        Edit
                                    </button>
                                    <button v-if="expense.status === 'pending'" @click="approveExpense(expense)" class="text-green-600 hover:text-green-900">
                                        Approve
                                    </button>
                                    <button v-if="expense.status === 'pending'" @click="rejectExpense(expense)" class="text-red-600 hover:text-red-900">
                                        Reject
                                    </button>
                                    <button @click="deleteExpense(expense)" class="text-red-600 hover:text-red-900">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button @click="loadExpenses(pagination.current_page - 1)" :disabled="!pagination.prev_page_url" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Previous
                    </button>
                    <button @click="loadExpenses(pagination.current_page + 1)" :disabled="!pagination.next_page_url" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Next
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <button @click="loadExpenses(pagination.current_page - 1)" :disabled="!pagination.prev_page_url" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                Previous
                            </button>
                            <button @click="loadExpenses(pagination.current_page + 1)" :disabled="!pagination.next_page_url" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                Next
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Expense Modal -->
        <div v-if="showAddModal || showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            {{ showEditModal ? 'Edit Expense' : 'Add New Expense' }}
                        </h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <ExpenseForm
                        :expense="editingExpense"
                        :properties="properties"
                        :categories="categories"
                        @saved="onExpenseSaved"
                        @cancelled="closeModal"
                    />
                </div>
            </div>
        </div>

        <!-- View Expense Modal -->
        <div v-if="showViewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Expense Details</h3>
                        <button @click="showViewModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div v-if="viewingExpense" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <p class="mt-1 text-sm text-gray-900">{{ viewingExpense.description }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Category</label>
                                <p class="mt-1 text-sm text-gray-900">{{ viewingExpense.category }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Amount</label>
                                <p class="mt-1 text-sm text-gray-900">₹{{ formatNumber(viewingExpense.amount) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date</label>
                                <p class="mt-1 text-sm text-gray-900">{{ formatDate(viewingExpense.expense_date) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Property</label>
                                <p class="mt-1 text-sm text-gray-900">{{ viewingExpense.property?.title || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <span :class="getStatusClass(viewingExpense.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ viewingExpense.status }}
                                </span>
                            </div>
                        </div>
                        
                        <div v-if="viewingExpense.vendor">
                            <label class="block text-sm font-medium text-gray-700">Vendor</label>
                            <p class="mt-1 text-sm text-gray-900">{{ viewingExpense.vendor }}</p>
                        </div>
                        
                        <div v-if="viewingExpense.notes">
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <p class="mt-1 text-sm text-gray-900">{{ viewingExpense.notes }}</p>
                        </div>
                        
                        <div v-if="viewingExpense.receipt_file">
                            <label class="block text-sm font-medium text-gray-700">Receipt</label>
                            <a :href="getReceiptUrl(viewingExpense.receipt_file)" target="_blank" class="mt-1 text-sm text-sky-800 hover:text-sky-900">
                                View Receipt
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import ExpenseForm from './ExpenseForm.vue'

const expenses = ref([])
const properties = ref([])
const categories = ref([
    'Maintenance', 'Utilities', 'Insurance', 'Property Tax', 'Repairs',
    'Cleaning', 'Security', 'Legal', 'Marketing', 'Other'
])
const stats = ref({})
const loading = ref(false)
const showAddModal = ref(false)
const showEditModal = ref(false)
const showViewModal = ref(false)
const editingExpense = ref(null)
const viewingExpense = ref(null)
const pagination = ref(null)

const filters = ref({
    search: '',
    property_id: '',
    category: '',
    status: ''
})

let searchTimeout = null

const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        loadExpenses()
    }, 500)
}

const loadExpenses = async (page = 1) => {
    loading.value = true
    try {
        const params = new URLSearchParams()
        Object.keys(filters.value).forEach(key => {
            if (filters.value[key]) {
                params.append(key, filters.value[key])
            }
        })
        params.append('page', page)
        
        const response = await axios.get(`/api/v1/org/expenses?${params.toString()}`)
        if (response.data.success) {
            expenses.value = response.data.data.data
            pagination.value = response.data.data
        }
    } catch (error) {
        console.error('Error loading expenses:', error)
    } finally {
        loading.value = false
    }
}

const loadProperties = async () => {
    try {
        const response = await axios.get('/api/v1/org/properties')
        if (response.data.success) {
            properties.value = response.data.data.data
        }
    } catch (error) {
        console.error('Error loading properties:', error)
    }
}

const loadStats = async () => {
    try {
        const response = await axios.get('/api/v1/org/expenses/statistics')
        if (response.data.success) {
            stats.value = response.data.data
        }
    } catch (error) {
        console.error('Error loading stats:', error)
    }
}

const viewExpense = (expense) => {
    viewingExpense.value = expense
    showViewModal.value = true
}

const editExpense = (expense) => {
    editingExpense.value = expense
    showEditModal.value = true
}

const approveExpense = async (expense) => {
    if (confirm('Are you sure you want to approve this expense?')) {
        try {
            const response = await axios.post(`/api/v1/org/expenses/${expense.id}/approve`)
            if (response.data.success) {
                loadExpenses()
                loadStats()
            }
        } catch (error) {
            console.error('Error approving expense:', error)
        }
    }
}

const rejectExpense = async (expense) => {
    const reason = prompt('Please provide a reason for rejection:')
    if (reason) {
        try {
            const response = await axios.post(`/api/v1/org/expenses/${expense.id}/reject`, {
                rejection_reason: reason
            })
            if (response.data.success) {
                loadExpenses()
                loadStats()
            }
        } catch (error) {
            console.error('Error rejecting expense:', error)
        }
    }
}

const deleteExpense = async (expense) => {
    if (confirm('Are you sure you want to delete this expense?')) {
        try {
            const response = await axios.delete(`/api/v1/org/expenses/${expense.id}`)
            if (response.data.success) {
                loadExpenses()
                loadStats()
            }
        } catch (error) {
            console.error('Error deleting expense:', error)
        }
    }
}

const onExpenseSaved = () => {
    closeModal()
    loadExpenses()
    loadStats()
}

const closeModal = () => {
    showAddModal.value = false
    showEditModal.value = false
    editingExpense.value = null
}

const getStatusClass = (status) => {
    switch (status) {
        case 'approved':
            return 'bg-green-100 text-green-800'
        case 'rejected':
            return 'bg-red-100 text-red-800'
        case 'pending':
            return 'bg-yellow-100 text-yellow-800'
        default:
            return 'bg-gray-100 text-gray-800'
    }
}

const formatNumber = (number) => {
    return new Intl.NumberFormat('en-IN').format(number)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-IN')
}

const getReceiptUrl = (filePath) => {
    return `/storage/${filePath}`
}

onMounted(() => {
    loadExpenses()
    loadProperties()
    loadStats()
})
</script>

