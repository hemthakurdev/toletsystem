<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Organizations</h1>
                        <p class="mt-1 text-sm text-gray-500">Manage all organizations on the platform</p>
                    </div>
                    <div class="flex space-x-4">
                        <button @click="showAddModal = true" class="btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Organization
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Filters -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Search organizations..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select v-model="filters.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Plan</label>
                        <select v-model="filters.plan_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                            <option value="">All Plans</option>
                            <option v-for="plan in plans" :key="plan.id" :value="plan.id">
                                {{ plan.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button @click="applyFilters" class="w-full btn-primary">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Organizations Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Organization</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Properties</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="org in organizations" :key="org.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ org.name }}</div>
                                        <div class="text-sm text-gray-500">{{ org.email }}</div>
                                        <div class="text-sm text-gray-500">{{ org.phone }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ org.plan?.name || 'No Plan' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getStatusBadgeClass(org.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ org.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ org.users?.length || 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ org.properties?.length || 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(org.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button @click="viewOrganization(org)" class="text-sky-800 hover:text-sky-900">View</button>
                                        <button @click="editOrganization(org)" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                        <button v-if="org.status === 'active'" @click="suspendOrganization(org)" class="text-yellow-600 hover:text-yellow-900">Suspend</button>
                                        <button v-if="org.status === 'suspended'" @click="activateOrganization(org)" class="text-green-600 hover:text-green-900">Activate</button>
                                        <button @click="deleteOrganization(org)" class="text-red-600 hover:text-red-900">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="organizations.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No organizations</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new organization.</p>
                    <div class="mt-6">
                        <button @click="showAddModal = true" class="btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Organization
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Organization Modal -->
        <div v-if="showAddModal || showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="closeModal">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-screen overflow-y-auto" @click.stop>
                <!-- Header -->
                <div class="flex justify-between items-center p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ showAddModal ? 'Add Organization' : 'Edit Organization' }}
                    </h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="saveOrganization" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Organization Name *</label>
                            <input v-model="form.name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                            <input v-model="form.email" type="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone *</label>
                            <input v-model="form.phone" type="tel" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Plan *</label>
                            <select v-model="form.plan_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                                <option value="">Select Plan</option>
                                <option v-for="plan in plans" :key="plan.id" :value="plan.id">
                                    {{ plan.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                            <input v-model="form.city" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">State *</label>
                            <input v-model="form.state" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pincode *</label>
                            <input v-model="form.pincode" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                        </div>
                        <div v-if="showAddModal">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select v-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
                        <textarea v-model="form.address" required rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"></textarea>
                    </div>

                    <!-- Admin User Details (only for new organizations) -->
                    <div v-if="showAddModal" class="border-t pt-4">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Admin User Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Admin Name *</label>
                                <input v-model="form.admin_name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Admin Email *</label>
                                <input v-model="form.admin_email" type="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Admin Password *</label>
                                <input v-model="form.admin_password" type="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" :disabled="loading" class="px-4 py-2 bg-sky-800 text-white rounded-md hover:bg-sky-900 disabled:opacity-50">
                            <span v-if="loading">Saving...</span>
                            <span v-else>{{ showAddModal ? 'Create' : 'Update' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const organizations = ref([])
const plans = ref([])
const loading = ref(false)
const showAddModal = ref(false)
const showEditModal = ref(false)
const selectedOrganization = ref(null)

const filters = ref({
    search: '',
    status: '',
    plan_id: '',
})

const form = ref({
    name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    state: '',
    pincode: '',
    plan_id: '',
    status: 'active',
    admin_name: '',
    admin_email: '',
    admin_password: '',
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-IN')
}

const getStatusBadgeClass = (status) => {
    const classes = {
        'active': 'bg-green-100 text-green-800',
        'inactive': 'bg-gray-100 text-gray-800',
        'suspended': 'bg-red-100 text-red-800',
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const loadOrganizations = async () => {
    try {
        const params = new URLSearchParams()
        if (filters.value.search) params.append('search', filters.value.search)
        if (filters.value.status) params.append('status', filters.value.status)
        if (filters.value.plan_id) params.append('plan_id', filters.value.plan_id)

        const response = await fetch(`/api/v1/admin/organizations?${params}`, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
            }
        })
        const data = await response.json()
        if (data.success) {
            organizations.value = data.data.data
        }
    } catch (error) {
        console.error('Error loading organizations:', error)
    }
}

const loadPlans = async () => {
    try {
        const response = await fetch('/api/v1/admin/plans', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
            }
        })
        const data = await response.json()
        if (data.success) {
            plans.value = data.data
        }
    } catch (error) {
        console.error('Error loading plans:', error)
    }
}

const applyFilters = () => {
    loadOrganizations()
}

const viewOrganization = (org) => {
    // Navigate to organization details
    console.log('View organization:', org)
}

const editOrganization = (org) => {
    selectedOrganization.value = org
    form.value = {
        name: org.name,
        email: org.email,
        phone: org.phone,
        address: org.address,
        city: org.city,
        state: org.state,
        pincode: org.pincode,
        plan_id: org.plan_id,
        status: org.status,
    }
    showEditModal.value = true
}

const suspendOrganization = async (org) => {
    if (!confirm('Are you sure you want to suspend this organization?')) return

    try {
        const response = await fetch(`/api/v1/admin/organizations/${org.id}/suspend`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
            }
        })
        const data = await response.json()
        if (data.success) {
            alert('Organization suspended successfully')
            loadOrganizations()
        } else {
            alert('Error: ' + data.message)
        }
    } catch (error) {
        console.error('Error suspending organization:', error)
        alert('Failed to suspend organization')
    }
}

const activateOrganization = async (org) => {
    if (!confirm('Are you sure you want to activate this organization?')) return

    try {
        const response = await fetch(`/api/v1/admin/organizations/${org.id}/activate`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
            }
        })
        const data = await response.json()
        if (data.success) {
            alert('Organization activated successfully')
            loadOrganizations()
        } else {
            alert('Error: ' + data.message)
        }
    } catch (error) {
        console.error('Error activating organization:', error)
        alert('Failed to activate organization')
    }
}

const deleteOrganization = async (org) => {
    if (!confirm('Are you sure you want to delete this organization? This action cannot be undone.')) return

    try {
        const response = await fetch(`/api/v1/admin/organizations/${org.id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
            }
        })
        const data = await response.json()
        if (data.success) {
            alert('Organization deleted successfully')
            loadOrganizations()
        } else {
            alert('Error: ' + data.message)
        }
    } catch (error) {
        console.error('Error deleting organization:', error)
        alert('Failed to delete organization')
    }
}

const saveOrganization = async () => {
    loading.value = true

    try {
        const url = showAddModal.value 
            ? '/api/v1/admin/organizations'
            : `/api/v1/admin/organizations/${selectedOrganization.value.id}`
        
        const method = showAddModal.value ? 'POST' : 'PUT'

        const response = await fetch(url, {
            method,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(form.value)
        })

        const data = await response.json()
        if (data.success) {
            alert(showAddModal.value ? 'Organization created successfully' : 'Organization updated successfully')
            closeModal()
            loadOrganizations()
        } else {
            alert('Error: ' + data.message)
        }
    } catch (error) {
        console.error('Error saving organization:', error)
        alert('Failed to save organization')
    } finally {
        loading.value = false
    }
}

const closeModal = () => {
    showAddModal.value = false
    showEditModal.value = false
    selectedOrganization.value = null
    form.value = {
        name: '',
        email: '',
        phone: '',
        address: '',
        city: '',
        state: '',
        pincode: '',
        plan_id: '',
        status: 'active',
        admin_name: '',
        admin_email: '',
        admin_password: '',
    }
}

onMounted(() => {
    loadOrganizations()
    loadPlans()
})
</script>
