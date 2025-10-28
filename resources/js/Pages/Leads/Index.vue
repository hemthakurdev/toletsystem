<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Navigation -->
        <Header current-path="/leads">
        </Header>

        <!-- Main Content -->
        <div class="container-mobile py-6">
            <!-- Page Header -->
            <div class="px-4 py-6 sm:px-0">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Lead Management</h1>
                        <p class="text-gray-600 mt-1">Manage inquiries from potential tenants</p>
                    </div>
                    <div class="flex space-x-3">
                        <button @click="refreshData" class="btn btn-secondary">
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
                    <div class="flex items-center gap-3 flex-wrap md:flex-nowrap px-4 md:px-6 py-4">
                        <div class="w-full md:w-80">
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Search leads..."
                                class="form-input w-full"
                                @input="debouncedSearch"
                            />
                        </div>
                        <div class="w-full md:w-44">
                            <select v-model="filters.status" @change="loadLeads" class="form-select w-full">
                                <option value="">All Status</option>
                                <option value="new">New</option>
                                <option value="contacted">Contacted</option>
                                <option value="interested">Interested</option>
                                <option value="not_interested">Not Interested</option>
                                <option value="converted">Converted</option>
                            </select>
                        </div>
                        <div class="w-full md:w-48">
                            <select v-model="filters.source" @change="loadLeads" class="form-select w-full">
                                <option value="">All Sources</option>
                                <option value="public_listing">Public Listing</option>
                                <option value="manual">Manual</option>
                                <option value="referral">Referral</option>
                            </select>
                        </div>
                        <label class="flex items-center whitespace-nowrap">
                            <input v-model="filters.high_priority" type="checkbox" @change="loadLeads" class="mr-2">
                            High Priority Only
                        </label>
                        <div class="ml-auto">
                            <button @click="refreshData" class="btn btn-secondary">Apply</button>
                        </div>
                    </div>
                </div>

                <!-- Leads Table -->
                <div class="card">
                    <!-- Table help text -->
                    <div class="px-6 py-3 bg-sky-50 border-b border-sky-100 text-sm text-sky-900">
                        <span class="font-medium">Actions guide:</span>
                        <span class="ml-2"><span class="font-semibold">Contact</span> marks a lead as contacted and updates the lead score.</span>
                        <span class="ml-2"><span class="font-semibold">Convert</span> marks a contacted lead as converted to a tenant/customer.</span>
                    </div>
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
                                    class="btn btn-secondary"
                                >
                                    Previous
                                </button>
                                <button 
                                    @click="loadLeads(pagination.current_page + 1)"
                                    :disabled="pagination.current_page >= pagination.last_page"
                                    class="btn btn-secondary"
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
            :show="showLeadModal" 
            :lead="selectedLead"
            @close="closeLeadModal"
            @updated="onLeadUpdated"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Header from '../../Components/Header.vue'
import axios from 'axios'

// Helper to build axios config supporting either token-based auth (SPA) or cookie-based session (Laravel Sanctum)
const getAuthConfig = () => {
    const token = localStorage.getItem('token')
    if (token) {
        return {
            headers: { 'Authorization': 'Bearer ' + token }
        }
    }
    // use cookies (Sanctum/session) for session auth
    return { withCredentials: true }
}

// Determine base paths for token (SPA) and session (web), with auto-fallback between them
const hasToken = !!localStorage.getItem('token')
const apiLeadsPath = '/api/v1/leads'
const webLeadsPath = '/org/leads'
const activeLeadsBasePath = ref(hasToken ? apiLeadsPath : webLeadsPath)
import LeadDetailModal from './LeadDetailModal.vue'

const leads = ref([])
const stats = ref({})
const loading = ref(false)
const lastApiStatus = ref(null)
const lastApiError = ref(null)
const rawApiResponse = ref(null)
const rawLeadsResponse = ref(null)
const rawStatsResponse = ref(null)
const showRawResponse = ref(false)
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
        // Build query params, omitting empty filters so backend doesn't filter by empty values
        const paramsObj = { page }
        const f = filters.value
        if (f.search && f.search.trim().length > 0) paramsObj.search = f.search.trim()
        if (f.status) paramsObj.status = f.status
        if (f.source) paramsObj.source = f.source
        if (f.high_priority === true) paramsObj.high_priority = '1'
        const params = new URLSearchParams(paramsObj)

        const tryFetch = async (base) => {
            return await axios.get(`${base}?${params}`, getAuthConfig())
        }

        let response
        try {
            response = await tryFetch(activeLeadsBasePath.value)
        } catch (e) {
            // Fallback to the other base if first fails
            const fallback = activeLeadsBasePath.value === apiLeadsPath ? webLeadsPath : apiLeadsPath
            response = await tryFetch(fallback)
            activeLeadsBasePath.value = fallback
        }

        lastApiStatus.value = response.status
        rawLeadsResponse.value = response.data
        rawApiResponse.value = response.data

        if (response.data?.success) {
            const payload = response.data.data
            if (payload && Array.isArray(payload.data)) {
                leads.value = payload.data
                pagination.value = {
                    current_page: payload.current_page,
                    last_page: payload.last_page,
                    from: payload.from,
                    to: payload.to,
                    total: payload.total
                }
            } else if (Array.isArray(payload)) {
                leads.value = payload
                pagination.value = null
            } else if (payload && Array.isArray(payload.data ?? payload)) {
                leads.value = payload.data ?? payload
                pagination.value = null
            } else {
            // Unexpected shape, keep UI stable
                leads.value = []
                pagination.value = null
            }
            lastApiError.value = null

            // If token path returned empty, attempt web path once to populate
            if (hasToken && activeLeadsBasePath.value === apiLeadsPath && leads.value.length === 0) {
                try {
                    const fb = await tryFetch(webLeadsPath)
                    if (fb.data?.success) {
                        const payload2 = fb.data.data
                        if (payload2 && Array.isArray(payload2.data)) {
                            leads.value = payload2.data
                            pagination.value = {
                                current_page: payload2.current_page,
                                last_page: payload2.last_page,
                                from: payload2.from,
                                to: payload2.to,
                                total: payload2.total
                            }
                        } else if (Array.isArray(payload2)) {
                            leads.value = payload2
                            pagination.value = null
                        } else if (payload2 && Array.isArray(payload2.data ?? payload2)) {
                            leads.value = payload2.data ?? payload2
                            pagination.value = null
                        }
                        activeLeadsBasePath.value = webLeadsPath
                    }
                } catch (_) {}
            }
        } else {
            lastApiError.value = response.data?.message || 'Failed to load leads'
        }
    } catch (error) {
        // Swallow noisy console in production UI
        lastApiStatus.value = error.response?.status || null
        lastApiError.value = error.response?.data?.message || error.message || 'Unknown error'
        rawApiResponse.value = error.response?.data ?? { error: error.message }
        rawLeadsResponse.value = error.response?.data ?? null
    } finally {
        loading.value = false
    }
}

const loadStatistics = async () => {
    try {
        try {
            const response = await axios.get(`${activeLeadsBasePath.value}/statistics`, getAuthConfig())
            lastApiStatus.value = response.status
            rawStatsResponse.value = response.data
            rawApiResponse.value = response.data
            if (response.data.success) {
                stats.value = response.data.data
            }
        } catch (e) {
            // Swallow noisy console in production UI
            lastApiStatus.value = e.response?.status || null
            lastApiError.value = e.response?.data?.message || e.message || 'Unknown error'
            rawApiResponse.value = e.response?.data ?? { error: e.message }
            rawStatsResponse.value = e.response?.data ?? null
        }
    } catch (error) {
        // Swallow noisy console in production UI
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
    const response = await axios.post(`${activeLeadsBasePath.value}/${lead.id}/mark-contacted`, {}, getAuthConfig())

        if (response.data.success) {
            lead.status = 'contacted'
            lead.contacted_at = new Date().toISOString()
            lead.lead_score = response.data.data.lead_score
        }
    } catch (error) {
        // Swallow noisy console in production UI
        alert('Failed to update lead status')
    }
}

const markConverted = async (lead) => {
    try {
    const response = await axios.post(`${activeLeadsBasePath.value}/${lead.id}/mark-converted`, {}, getAuthConfig())

        if (response.data.success) {
            lead.status = 'converted'
            lead.lead_score = response.data.data.lead_score
        }
    } catch (error) {
        // Swallow noisy console in production UI
        alert('Failed to update lead status')
    }
}

const deleteLead = async (lead) => {
    if (!confirm('Are you sure you want to delete this lead?')) return

    try {
    const response = await axios.delete(`${activeLeadsBasePath.value}/${lead.id}`, getAuthConfig())

        if (response.data.success) {
            leads.value = leads.value.filter(l => l.id !== lead.id)
            loadStatistics()
        }
    } catch (error) {
        // Swallow noisy console in production UI
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
