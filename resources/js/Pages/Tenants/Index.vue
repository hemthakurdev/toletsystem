<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Navigation -->
        <Header current-path="/tenants" />

        <!-- Main Content -->
        <div class="container-mobile py-6">
            <!-- Page Header -->
            <div class="px-4 py-6 sm:px-0">
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Tenants</h1>
                            <p class="mt-2 text-gray-600">Manage your tenant relationships and lease agreements</p>
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <button @click="showAddModal = true" class="btn btn-primary">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Tenant
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="stat-card">
                        <div class="stat-number text-sky-800">{{ props.stats.total }}</div>
                        <div class="stat-label">Total Tenants</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number text-green-600">{{ props.stats.active }}</div>
                        <div class="stat-label">Active Leases</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number text-yellow-600">{{ props.stats.expiring }}</div>
                        <div class="stat-label">Expiring Soon</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number text-red-600">{{ props.stats.overdue }}</div>
                        <div class="stat-label">Overdue Payments</div>
                    </div>
                </div>

                <!-- Filters and Search -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Search tenants..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Property</label>
                            <select v-model="filters.property_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                                <option value="">All Properties</option>
                                <option v-for="property in properties" :key="property.id" :value="property.id">
                                    {{ property.title }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select v-model="filters.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="terminated">Terminated</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lease Status</label>
                            <select v-model="filters.lease_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                                <option value="">All Lease Status</option>
                                <option value="active">Active</option>
                                <option value="expired">Expired</option>
                                <option value="terminated">Terminated</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-between">
                        <button @click="applyFilters" class="btn btn-primary">Apply Filters</button>
                        <button @click="clearFilters" class="btn btn-secondary">Clear Filters</button>
                    </div>
                </div>

                <!-- Tenants Table -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lease Period</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monthly Rent</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="tenant in props.tenants" :key="tenant.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" :src="getAvatarUrl(tenant.name)" :alt="tenant.name" />
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ tenant.name }}</div>
                                                <div class="text-sm text-gray-500">{{ tenant.email }}</div>
                                                <div class="text-sm text-gray-500">{{ tenant.phone }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ tenant.property?.title }}</div>
                                        <div class="text-sm text-gray-500">{{ tenant.property?.locality }}, {{ tenant.property?.city }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ formatDate(tenant.lease_start_date) }}</div>
                                        <div class="text-sm text-gray-500">to {{ formatDate(tenant.lease_end_date) }}</div>
                                        <div v-if="isLeaseExpiring(tenant.lease_end_date)" class="text-sm text-yellow-600 font-medium">
                                            Expires in {{ getDaysUntilExpiry(tenant.lease_end_date) }} days
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">₹{{ formatPrice(tenant.monthly_rent) }}</div>
                                        <div v-if="tenant.security_deposit" class="text-sm text-gray-500">
                                            Deposit: ₹{{ formatPrice(tenant.security_deposit) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStatusBadgeClass(tenant.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ tenant.status }}
                                        </span>
                                        <div class="mt-1">
                                            <span :class="getLeaseStatusBadgeClass(tenant.lease_status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                                {{ tenant.lease_status }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button @click="viewTenant(tenant)" class="text-sky-800 hover:text-sky-900">View</button>
                                            <button @click="editTenant(tenant)" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                            <button @click="viewPayments(tenant)" class="text-green-600 hover:text-green-900">Payments</button>
                                            <button @click="deleteTenant(tenant)" class="text-red-600 hover:text-red-900">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-if="tenants.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No tenants</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by adding a new tenant.</p>
                        <div class="mt-6">
                            <button @click="showAddModal = true" class="btn btn-primary">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Tenant
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Tenant Modal -->
        <div v-if="showAddModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Add New Tenant</h3>
                        <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <TenantForm @saved="onTenantSaved" @cancelled="showAddModal = false" :properties="props.properties" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Header from '../../Components/Header.vue'
import TenantForm from './TenantForm.vue'

const props = defineProps({
    tenants: {
        type: Array,
        default: () => []
    },
    properties: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            active: 0,
            expiring: 0,
            overdue: 0
        })
    }
})

const loading = ref(false)
const showAddModal = ref(false)

const filters = ref({
    search: '',
    property_id: '',
    status: '',
    lease_status: ''
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
        'active': 'bg-green-100 text-green-800',
        'inactive': 'bg-gray-100 text-gray-800',
        'terminated': 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const getLeaseStatusBadgeClass = (status) => {
    const classes = {
        'active': 'bg-sky-100 text-sky-900',
        'expired': 'bg-yellow-100 text-yellow-800',
        'terminated': 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const isLeaseExpiring = (endDate) => {
    const daysUntilExpiry = getDaysUntilExpiry(endDate)
    return daysUntilExpiry <= 30 && daysUntilExpiry > 0
}

const getDaysUntilExpiry = (endDate) => {
    const today = new Date()
    const expiry = new Date(endDate)
    const diffTime = expiry - today
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
}

// Data is now provided via props from the backend controller

const applyFilters = () => {
    // Filters will be handled by the backend controller
    // For now, we'll just reload the page to apply filters
    window.location.reload()
}

const clearFilters = () => {
    filters.value = {
        search: '',
        property_id: '',
        status: '',
        lease_status: ''
    }
    loadTenants()
}

const viewTenant = (tenant) => {
    window.location.href = `/tenants/${tenant.id}`
}

const editTenant = (tenant) => {
    window.location.href = `/tenants/${tenant.id}/edit`
}

const viewPayments = (tenant) => {
    window.location.href = `/tenants/${tenant.id}/payments`
}

const deleteTenant = async (tenant) => {
    if (confirm('Are you sure you want to delete this tenant?')) {
        try {
            const response = await fetch(`http://127.0.0.1:8000/api/v1/org/tenants/${tenant.id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Content-Type': 'application/json'
                }
            })
            
            if (response.ok) {
                tenants.value = tenants.value.filter(t => t.id !== tenant.id)
                calculateStats()
            }
        } catch (error) {
            console.error('Error deleting tenant:', error)
        }
    }
}

const onTenantSaved = () => {
    showAddModal.value = false
    // Reload the page to show the new tenant
    window.location.reload()
}

// Data is now provided via props from the backend controller
</script>
