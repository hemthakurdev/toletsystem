<template>
    <div class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Plan Management</h1>
            <p class="text-gray-600">Manage subscription plans for organizations</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-sky-100 rounded-lg">
                        <svg class="w-6 h-6 text-sky-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.29-1.009-5.824-2.709M15 6.291A7.962 7.962 0 0012 5c-2.34 0-4.29 1.009-5.824 2.709M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Plans</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ plans.length }}</p>
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
                        <p class="text-sm font-medium text-gray-600">Active Plans</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ activePlansCount }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                        <p class="text-2xl font-semibold text-gray-900">₹{{ totalRevenue.toLocaleString() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <div class="flex justify-between items-center">
                <div class="flex space-x-4">
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search plans..."
                            class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-800 focus:border-transparent"
                        >
                        <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <select v-model="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-800 focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <button
                    @click="showAddModal = true"
                    class="px-4 py-2 bg-sky-800 text-white rounded-lg hover:bg-sky-900 focus:ring-2 focus:ring-sky-800 focus:ring-offset-2"
                >
                    Add Plan
                </button>
            </div>
        </div>

        <!-- Plans Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="plan in filteredPlans"
                :key="plan.id"
                class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow"
            >
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ plan.name }}</h3>
                        <span
                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                            :class="plan.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                        >
                            {{ plan.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="mb-4">
                        <div class="text-3xl font-bold text-gray-900">
                            ₹{{ plan.price.toLocaleString() }}
                            <span class="text-sm font-normal text-gray-500">/{{ plan.billing_cycle }}</span>
                        </div>
                    </div>

                    <p class="text-gray-600 mb-4">{{ plan.description }}</p>

                    <div class="space-y-2 mb-6">
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ plan.max_properties }} Properties
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ plan.max_users }} Users
                        </div>
                        <div v-if="plan.features && plan.features.length > 0" class="flex items-center text-sm text-gray-600">
                            <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ plan.features.length }} Features
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="text-sm text-gray-500">
                            {{ plan.organizations_count || 0 }} organizations using this plan
                        </div>
                    </div>

                    <div class="flex space-x-2">
                        <button
                            @click="editPlan(plan)"
                            class="flex-1 px-3 py-2 text-sm bg-sky-800 text-white rounded hover:bg-sky-900"
                        >
                            Edit
                        </button>
                        <button
                            @click="togglePlanStatus(plan)"
                            class="px-3 py-2 text-sm rounded"
                            :class="plan.is_active ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-green-600 text-white hover:bg-green-700'"
                        >
                            {{ plan.is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                        <button
                            @click="deletePlan(plan)"
                            class="px-3 py-2 text-sm bg-gray-600 text-white rounded hover:bg-gray-700"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Plan Modal -->
        <div v-if="showAddModal || showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            {{ showEditModal ? 'Edit Plan' : 'Add New Plan' }}
                        </h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <PlanForm
                        :plan="editingPlan"
                        @saved="onPlanSaved"
                        @cancelled="closeModal"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import PlanForm from '../../Components/PlanForm.vue'

const plans = ref([])
const loading = ref(false)
const showAddModal = ref(false)
const showEditModal = ref(false)
const editingPlan = ref(null)
const searchQuery = ref('')
const statusFilter = ref('')

const filteredPlans = computed(() => {
    let filtered = plans.value

    if (searchQuery.value) {
        filtered = filtered.filter(plan =>
            plan.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            plan.description.toLowerCase().includes(searchQuery.value.toLowerCase())
        )
    }

    if (statusFilter.value) {
        filtered = filtered.filter(plan => 
            statusFilter.value === 'active' ? plan.is_active : !plan.is_active
        )
    }

    return filtered
})

const activePlansCount = computed(() => {
    return plans.value.filter(plan => plan.is_active).length
})

const totalRevenue = computed(() => {
    return plans.value.reduce((total, plan) => {
        return total + (plan.price * (plan.organizations_count || 0))
    }, 0)
})

const loadPlans = async () => {
    loading.value = true
    try {
        const response = await axios.get('/api/v1/admin/plans', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        })

        if (response.data.success) {
            plans.value = response.data.data
        }
    } catch (error) {
        console.error('Error loading plans:', error)
    } finally {
        loading.value = false
    }
}

const editPlan = (plan) => {
    editingPlan.value = plan
    showEditModal.value = true
}

const togglePlanStatus = async (plan) => {
    const action = plan.is_active ? 'deactivate' : 'activate'
    const message = `Are you sure you want to ${action} this plan?`
    
    if (confirm(message)) {
        try {
            const response = await axios.put(`/api/v1/admin/plans/${plan.id}`, {
                is_active: !plan.is_active
            }, {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                }
            })

            if (response.data.success) {
                loadPlans()
            }
        } catch (error) {
            console.error(`Error ${action}ing plan:`, error)
        }
    }
}

const deletePlan = async (plan) => {
    if (confirm('Are you sure you want to delete this plan? This action cannot be undone.')) {
        try {
            const response = await axios.delete(`/api/v1/admin/plans/${plan.id}`, {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                }
            })

            if (response.data.success) {
                loadPlans()
            }
        } catch (error) {
            console.error('Error deleting plan:', error)
        }
    }
}

const onPlanSaved = () => {
    closeModal()
    loadPlans()
}

const closeModal = () => {
    showAddModal.value = false
    showEditModal.value = false
    editingPlan.value = null
}

onMounted(() => {
    loadPlans()
})
</script>
