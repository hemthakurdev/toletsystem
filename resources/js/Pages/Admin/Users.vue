<template>
    <div class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
            <p class="text-gray-600">Manage users across all organizations</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-sky-100 rounded-lg">
                        <svg class="w-6 h-6 text-sky-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Users</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ stats.total_users }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Active Users</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ stats.active_users }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Inactive Users</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ stats.inactive_users }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Suspended Users</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ stats.suspended_users }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Actions -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="relative">
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Search users..."
                            class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-800 focus:border-transparent"
                            @input="debouncedSearch"
                        >
                        <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <select v-model="filters.status" @change="loadUsers" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-800 focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>

                    <select v-model="filters.org_id" @change="loadUsers" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-800 focus:border-transparent">
                        <option value="">All Organizations</option>
                        <option v-for="org in organizations" :key="org.id" :value="org.id">{{ org.name }}</option>
                    </select>
                </div>

                <button
                    @click="showAddModal = true"
                    class="px-4 py-2 bg-sky-800 text-white rounded-lg hover:bg-sky-900 focus:ring-2 focus:ring-sky-800 focus:ring-offset-2"
                >
                    Add User
                </button>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" class="rounded">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Organization</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" v-model="selectedUsers" :value="user.id" class="rounded">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700">{{ user.name.charAt(0) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                        <div class="text-sm text-gray-500">{{ user.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ user.organization?.name || 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="role in user.roles"
                                        :key="role.id"
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        :class="getRoleBadgeClass(role.name)"
                                    >
                                        {{ role.name }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    :class="getStatusBadgeClass(user.status)"
                                >
                                    {{ user.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(user.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button
                                        @click="editUser(user)"
                                        class="text-sky-800 hover:text-sky-900"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        v-if="user.status === 'active'"
                                        @click="suspendUser(user)"
                                        class="text-yellow-600 hover:text-yellow-900"
                                    >
                                        Suspend
                                    </button>
                                    <button
                                        v-else
                                        @click="activateUser(user)"
                                        class="text-green-600 hover:text-green-900"
                                    >
                                        Activate
                                    </button>
                                    <button
                                        v-if="!user.roles.some(role => role.name === 'super_admin')"
                                        @click="deleteUser(user)"
                                        class="text-red-600 hover:text-red-900"
                                    >
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
                    <button
                        @click="loadUsers(pagination.current_page - 1)"
                        :disabled="pagination.current_page <= 1"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                    >
                        Previous
                    </button>
                    <button
                        @click="loadUsers(pagination.current_page + 1)"
                        :disabled="pagination.current_page >= pagination.last_page"
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                    >
                        Next
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ pagination.from }}</span>
                            to
                            <span class="font-medium">{{ pagination.to }}</span>
                            of
                            <span class="font-medium">{{ pagination.total }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <button
                                v-for="page in getPageNumbers()"
                                :key="page"
                                @click="loadUsers(page)"
                                :class="[
                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                    page === pagination.current_page
                                        ? 'z-10 bg-sky-50 border-sky-500 text-sky-800'
                                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                                ]"
                            >
                                {{ page }}
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div v-if="selectedUsers.length > 0" class="mt-4 p-4 bg-sky-50 rounded-lg">
            <div class="flex items-center justify-between">
                <span class="text-sm text-sky-900">{{ selectedUsers.length }} users selected</span>
                <div class="flex space-x-2">
                    <button
                        @click="bulkActivate"
                        class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700"
                    >
                        Activate
                    </button>
                    <button
                        @click="bulkSuspend"
                        class="px-3 py-1 text-sm bg-yellow-600 text-white rounded hover:bg-yellow-700"
                    >
                        Suspend
                    </button>
                </div>
            </div>
        </div>

        <!-- Add/Edit User Modal -->
        <div v-if="showAddModal || showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            {{ showEditModal ? 'Edit User' : 'Add New User' }}
                        </h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <UserForm
                        :user="editingUser"
                        :organizations="organizations"
                        :roles="roles"
                        @saved="onUserSaved"
                        @cancelled="closeModal"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import UserForm from '../../Components/UserForm.vue'

const users = ref([])
const organizations = ref([])
const roles = ref([])
const stats = ref({
    total_users: 0,
    active_users: 0,
    inactive_users: 0,
    suspended_users: 0
})
const loading = ref(false)
const showAddModal = ref(false)
const showEditModal = ref(false)
const editingUser = ref(null)
const selectedUsers = ref([])
const selectAll = ref(false)
const pagination = ref(null)

const filters = ref({
    search: '',
    status: '',
    org_id: ''
})

let searchTimeout = null

const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        loadUsers()
    }, 500)
}

const loadUsers = async (page = 1) => {
    loading.value = true
    try {
        const params = new URLSearchParams({
            page: page,
            ...filters.value
        })

        const response = await axios.get(`/api/v1/admin/users?${params}`, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        })

        if (response.data.success) {
            users.value = response.data.data.data
            pagination.value = {
                current_page: response.data.data.current_page,
                last_page: response.data.data.last_page,
                from: response.data.data.from,
                to: response.data.data.to,
                total: response.data.data.total
            }
        }
    } catch (error) {
        console.error('Error loading users:', error)
    } finally {
        loading.value = false
    }
}

const loadStatistics = async () => {
    try {
        const response = await axios.get('/api/v1/admin/users/statistics', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        })

        if (response.data.success) {
            stats.value = response.data.data
        }
    } catch (error) {
        console.error('Error loading statistics:', error)
    }
}

const loadOrganizations = async () => {
    try {
        const response = await axios.get('/api/v1/admin/organizations', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        })

        if (response.data.success) {
            organizations.value = response.data.data.data
        }
    } catch (error) {
        console.error('Error loading organizations:', error)
    }
}

const loadRoles = async () => {
    try {
        const response = await axios.get('/api/v1/roles', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        })

        if (response.data.success) {
            roles.value = response.data.data
        }
    } catch (error) {
        console.error('Error loading roles:', error)
    }
}

const editUser = (user) => {
    editingUser.value = user
    showEditModal.value = true
}

const suspendUser = async (user) => {
    if (confirm('Are you sure you want to suspend this user?')) {
        try {
            const response = await axios.post(`/api/v1/admin/users/${user.id}/suspend`, {}, {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                }
            })

            if (response.data.success) {
                loadUsers()
                loadStatistics()
            }
        } catch (error) {
            console.error('Error suspending user:', error)
        }
    }
}

const activateUser = async (user) => {
    try {
        const response = await axios.post(`/api/v1/admin/users/${user.id}/activate`, {}, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        })

        if (response.data.success) {
            loadUsers()
            loadStatistics()
        }
    } catch (error) {
        console.error('Error activating user:', error)
    }
}

const deleteUser = async (user) => {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        try {
            const response = await axios.delete(`/api/v1/admin/users/${user.id}`, {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                }
            })

            if (response.data.success) {
                loadUsers()
                loadStatistics()
            }
        } catch (error) {
            console.error('Error deleting user:', error)
        }
    }
}

const bulkActivate = async () => {
    if (confirm(`Are you sure you want to activate ${selectedUsers.value.length} users?`)) {
        try {
            const response = await axios.post('/api/v1/admin/users/bulk-activate', {
                user_ids: selectedUsers.value
            }, {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                }
            })

            if (response.data.success) {
                selectedUsers.value = []
                selectAll.value = false
                loadUsers()
                loadStatistics()
            }
        } catch (error) {
            console.error('Error bulk activating users:', error)
        }
    }
}

const bulkSuspend = async () => {
    if (confirm(`Are you sure you want to suspend ${selectedUsers.value.length} users?`)) {
        try {
            const response = await axios.post('/api/v1/admin/users/bulk-suspend', {
                user_ids: selectedUsers.value
            }, {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                }
            })

            if (response.data.success) {
                selectedUsers.value = []
                selectAll.value = false
                loadUsers()
                loadStatistics()
            }
        } catch (error) {
            console.error('Error bulk suspending users:', error)
        }
    }
}

const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedUsers.value = users.value.map(user => user.id)
    } else {
        selectedUsers.value = []
    }
}

const onUserSaved = () => {
    closeModal()
    loadUsers()
    loadStatistics()
}

const closeModal = () => {
    showAddModal.value = false
    showEditModal.value = false
    editingUser.value = null
}

const getRoleBadgeClass = (roleName) => {
    const classes = {
        'super_admin': 'bg-red-100 text-red-800',
        'admin': 'bg-sky-100 text-sky-900',
        'staff': 'bg-green-100 text-green-800',
        'support': 'bg-yellow-100 text-yellow-800'
    }
    return classes[roleName] || 'bg-gray-100 text-gray-800'
}

const getStatusBadgeClass = (status) => {
    const classes = {
        'active': 'bg-green-100 text-green-800',
        'inactive': 'bg-yellow-100 text-yellow-800',
        'suspended': 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const getPageNumbers = () => {
    if (!pagination.value) return []
    
    const current = pagination.value.current_page
    const last = pagination.value.last_page
    const pages = []
    
    for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
        pages.push(i)
    }
    
    return pages
}

onMounted(() => {
    loadUsers()
    loadStatistics()
    loadOrganizations()
    loadRoles()
})
</script>
