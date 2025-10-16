<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <h1 class="text-2xl font-bold text-sky-800">SaleMitra</h1>
                        </div>
                        <div class="ml-10 flex items-baseline space-x-4">
                            <a href="/dashboard" class="nav-link">Dashboard</a>
                            <a href="/properties" class="nav-link">Properties</a>
                            <a href="/tenants" class="nav-link">Tenants</a>
                            <a href="/invoices" class="nav-link">Invoices</a>
                            <a href="/leads" class="nav-link active">Leads</a>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <img class="h-8 w-8 rounded-full" :src="$page.props.auth.user?.avatar_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent($page.props.auth.user?.name || 'User') + '&color=7F9CF5&background=EBF4FF'" alt="Profile" />
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="px-4 py-6 sm:px-0">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Lead Management</h1>
                        <p class="text-gray-600 mt-1">Manage inquiries from potential tenants</p>
                    </div>
                    <div class="flex space-x-3">
                        <button @click="refreshData" class="btn-secondary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Refresh
                        </button>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="stat-card">
                        <div class="stat-number text-sky-800">{{ stats.total_leads || 0 }}</div>
                        <div class="stat-label">Total Leads</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number text-green-600">{{ stats.new_leads || 0 }}</div>
                        <div class="stat-label">New Leads</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number text-yellow-600">{{ stats.contacted_leads || 0 }}</div>
                        <div class="stat-label">Contacted</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number text-purple-600">{{ stats.converted_leads || 0 }}</div>
                        <div class="stat-label">Converted</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-6">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex-1 min-w-64">
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Search leads..."
                                class="form-input"
                                @input="debouncedSearch"
                            />
                        </div>
                        <select v-model="filters.status" @change="loadLeads" class="form-select">
                            <option value="">All Status</option>
                            <option value="new">New</option>
                            <option value="contacted">Contacted</option>
                            <option value="interested">Interested</option>
                            <option value="not_interested">Not Interested</option>
                            <option value="converted">Converted</option>
                        </select>
                        <select v-model="filters.source" @change="loadLeads" class="form-select">
                            <option value="">All Sources</option>
                            <option value="public_listing">Public Listing</option>
                            <option value="manual">Manual</option>
                            <option value="referral">Referral</option>
                        </select>
                        <label class="flex items-center">
                            <input v-model="filters.high_priority" type="checkbox" @change="loadLeads" class="mr-2">
                            High Priority Only
                        </label>
                    </div>
                </div>

                <!-- Leads Table -->
                <div class="card">
                    <div v-if="loading" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-sky-800"></div>
                        <p class="mt-2 text-gray-600">Loading leads...</p>
                    </div>

                    <div v-else-if="leads.length === 0" class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No leads found</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by publishing your properties to the marketplace.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lead</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="lead in leads" :key="lead.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-sky-100 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-sky-800">{{ lead.name.charAt(0).toUpperCase() }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ lead.name }}</div>
                                                <div class="text-sm text-gray-500">{{ lead.phone }}</div>
                                                <div v-if="lead.email" class="text-sm text-gray-500">{{ lead.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ lead.property?.title }}</div>
                                        <div class="text-sm text-gray-500">{{ lead.property?.city }}, {{ lead.property?.locality }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="badge badge-info">{{ lead.source.replace('_', ' ') }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStatusBadgeClass(lead.status)">{{ lead.status.replace('_', ' ') }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                <div 
                                                    class="h-2 rounded-full" 
                                                    :class="getScoreColor(lead.lead_score)"
                                                    :style="{ width: lead.lead_score + '%' }"
                                                ></div>
                                            </div>
                                            <span class="text-sm text-gray-600">{{ lead.lead_score }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(lead.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button @click="viewLead(lead)" class="text-sky-800 hover:text-sky-900">View</button>
                                            <button v-if="lead.status === 'new'" @click="markContacted(lead)" class="text-green-600 hover:text-green-900">Contact</button>
                                            <button v-if="lead.status === 'contacted'" @click="markConverted(lead)" class="text-purple-600 hover:text-purple-900">Convert</button>
                                            <button @click="deleteLead(lead)" class="text-red-600 hover:text-red-900">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="pagination && pagination.last_page > 1" class="px-6 py-3 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
                            </div>
                            <div class="flex space-x-2">
                                <button 
                                    @click="loadLeads(pagination.current_page - 1)"
                                    :disabled="pagination.current_page <= 1"
                                    class="btn-secondary"
                                >
                                    Previous
                                </button>
                                <button 
                                    @click="loadLeads(pagination.current_page + 1)"
                                    :disabled="pagination.current_page >= pagination.last_page"
                                    class="btn-secondary"
                                >
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lead Detail Modal -->
        <LeadDetailModal 
            v-if="showLeadModal" 
            :show="showLeadModal" 
            :lead="selectedLead"
            @close="closeLeadModal"
            @updated="onLeadUpdated"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import LeadDetailModal from './LeadDetailModal.vue'

const leads = ref([])
const stats = ref({})
const loading = ref(false)
const showLeadModal = ref(false)
const selectedLead = ref(null)
const pagination = ref(null)

const filters = ref({
    search: '',
    status: '',
    source: '',
    high_priority: false
})

let searchTimeout = null

const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        loadLeads()
    }, 500)
}

const loadLeads = async (page = 1) => {
    loading.value = true
    try {
        const params = new URLSearchParams({
            page: page,
            ...filters.value
        })

        const response = await axios.get(`http://127.0.0.1:8000/api/v1/org/leads?${params}`, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        })

        if (response.data.success) {
            leads.value = response.data.data.data
            pagination.value = {
                current_page: response.data.data.current_page,
                last_page: response.data.data.last_page,
                from: response.data.data.from,
                to: response.data.data.to,
                total: response.data.data.total
            }
        }
    } catch (error) {
        console.error('Error loading leads:', error)
    } finally {
        loading.value = false
    }
}

const loadStatistics = async () => {
    try {
        const response = await axios.get('http://127.0.0.1:8000/api/v1/org/leads/statistics', {
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

const refreshData = () => {
    loadLeads()
    loadStatistics()
}

const viewLead = (lead) => {
    selectedLead.value = lead
    showLeadModal.value = true
}

const closeLeadModal = () => {
    showLeadModal.value = false
    selectedLead.value = null
}

const onLeadUpdated = () => {
    loadLeads()
    loadStatistics()
}

const markContacted = async (lead) => {
    try {
        const response = await axios.post(`http://127.0.0.1:8000/api/v1/org/leads/${lead.id}/mark-contacted`, {}, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        })

        if (response.data.success) {
            lead.status = 'contacted'
            lead.contacted_at = new Date().toISOString()
            lead.lead_score = response.data.data.lead_score
        }
    } catch (error) {
        console.error('Error marking lead as contacted:', error)
        alert('Failed to update lead status')
    }
}

const markConverted = async (lead) => {
    try {
        const response = await axios.post(`http://127.0.0.1:8000/api/v1/org/leads/${lead.id}/mark-converted`, {}, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        })

        if (response.data.success) {
            lead.status = 'converted'
            lead.lead_score = response.data.data.lead_score
        }
    } catch (error) {
        console.error('Error marking lead as converted:', error)
        alert('Failed to update lead status')
    }
}

const deleteLead = async (lead) => {
    if (!confirm('Are you sure you want to delete this lead?')) return

    try {
        const response = await axios.delete(`http://127.0.0.1:8000/api/v1/org/leads/${lead.id}`, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        })

        if (response.data.success) {
            leads.value = leads.value.filter(l => l.id !== lead.id)
            loadStatistics()
        }
    } catch (error) {
        console.error('Error deleting lead:', error)
        alert('Failed to delete lead')
    }
}

const getStatusBadgeClass = (status) => {
    const classes = {
        'new': 'badge badge-info',
        'contacted': 'badge badge-warning',
        'interested': 'badge badge-success',
        'not_interested': 'badge badge-danger',
        'converted': 'badge badge-success'
    }
    return classes[status] || 'badge badge-gray'
}

const getScoreColor = (score) => {
    if (score >= 80) return 'bg-red-500'
    if (score >= 60) return 'bg-yellow-500'
    if (score >= 40) return 'bg-sky-500'
    return 'bg-gray-500'
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

onMounted(() => {
    loadLeads()
    loadStatistics()
})
</script>
