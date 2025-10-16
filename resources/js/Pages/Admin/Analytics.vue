<template>
    <div class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Analytics Dashboard</h1>
            <p class="text-gray-600">Platform overview and performance metrics</p>
        </div>

        <!-- Overview Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-sky-100 rounded-lg">
                        <svg class="w-6 h-6 text-sky-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Organizations</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ overview.total_organizations }}</p>
                        <p class="text-xs text-green-600">{{ overview.active_organizations }} active</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Users</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ overview.total_users }}</p>
                        <p class="text-xs text-sky-800">Across all orgs</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Properties</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ overview.total_properties }}</p>
                        <p class="text-xs text-green-600">{{ overview.published_properties }} published</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Revenue</p>
                        <p class="text-2xl font-semibold text-gray-900">₹{{ formatCurrency(overview.total_revenue) }}</p>
                        <p class="text-xs text-green-600">₹{{ formatCurrency(overview.monthly_revenue) }} this month</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Revenue Chart -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Revenue Trend</h3>
                <div class="h-64 flex items-center justify-center bg-gray-50 rounded">
                    <div class="text-center">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <p class="text-gray-500">Chart visualization would go here</p>
                        <p class="text-sm text-gray-400">Integration with Chart.js or similar</p>
                    </div>
                </div>
            </div>

            <!-- User Growth Chart -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900 mb-4">User Growth</h3>
                <div class="h-64 flex items-center justify-center bg-gray-50 rounded">
                    <div class="text-center">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-gray-500">User growth chart would go here</p>
                        <p class="text-sm text-gray-400">Integration with Chart.js or similar</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Statistics -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Property Statistics -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Property Statistics</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Total Properties</span>
                        <span class="text-sm font-medium">{{ overview.total_properties }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Published</span>
                        <span class="text-sm font-medium text-green-600">{{ overview.published_properties }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Pending Approval</span>
                        <span class="text-sm font-medium text-yellow-600">{{ overview.total_properties - overview.published_properties }}</span>
                    </div>
                </div>
            </div>

            <!-- Tenant Statistics -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Tenant Statistics</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Total Tenants</span>
                        <span class="text-sm font-medium">{{ overview.total_tenants }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Active Tenants</span>
                        <span class="text-sm font-medium text-green-600">{{ overview.active_tenants }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Occupancy Rate</span>
                        <span class="text-sm font-medium text-sky-800">
                            {{ overview.total_properties > 0 ? Math.round((overview.active_tenants / overview.total_properties) * 100) : 0 }}%
                        </span>
                    </div>
                </div>
            </div>

            <!-- Financial Statistics -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Statistics</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Total Invoices</span>
                        <span class="text-sm font-medium">{{ overview.total_invoices }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Paid Invoices</span>
                        <span class="text-sm font-medium text-green-600">{{ overview.paid_invoices }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Payment Rate</span>
                        <span class="text-sm font-medium text-sky-800">
                            {{ overview.total_invoices > 0 ? Math.round((overview.paid_invoices / overview.total_invoices) * 100) : 0 }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Health -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">System Health</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ systemHealth.database_connections }}</div>
                    <div class="text-sm text-gray-600">Database</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-sky-800">{{ systemHealth.total_storage_used.total_size_mb }}MB</div>
                    <div class="text-sm text-gray-600">Storage Used</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ systemHealth.active_sessions }}</div>
                    <div class="text-sm text-gray-600">Active Sessions</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ systemHealth.error_rate }}%</div>
                    <div class="text-sm text-gray-600">Error Rate</div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="mt-8 bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
            <div class="space-y-3">
                <div v-for="activity in recentActivity" :key="activity.id" class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900">{{ activity.description }}</p>
                        <p class="text-xs text-gray-500">{{ formatDate(activity.created_at) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const overview = ref({
    total_organizations: 0,
    active_organizations: 0,
    total_users: 0,
    total_properties: 0,
    published_properties: 0,
    total_tenants: 0,
    active_tenants: 0,
    total_invoices: 0,
    paid_invoices: 0,
    total_payments: 0,
    total_revenue: 0,
    monthly_revenue: 0
})

const systemHealth = ref({
    database_connections: 'healthy',
    total_storage_used: { total_size_mb: 0 },
    active_sessions: 0,
    error_rate: 0,
    response_time: 0,
    uptime: 'N/A'
})

const recentActivity = ref([])

const loadOverview = async () => {
    try {
        const response = await axios.get('/api/v1/admin/analytics/overview', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        })

        if (response.data.success) {
            overview.value = response.data.data
        }
    } catch (error) {
        console.error('Error loading overview:', error)
    }
}

const loadSystemHealth = async () => {
    try {
        const response = await axios.get('/api/v1/admin/analytics/system-health', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        })

        if (response.data.success) {
            systemHealth.value = response.data.data
        }
    } catch (error) {
        console.error('Error loading system health:', error)
    }
}

const loadRecentActivity = async () => {
    try {
        const response = await axios.get('/api/v1/admin/analytics/recent-activity', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        })

        if (response.data.success) {
            recentActivity.value = response.data.data
        }
    } catch (error) {
        console.error('Error loading recent activity:', error)
        // Mock data for demonstration
        recentActivity.value = [
            {
                id: 1,
                description: 'New organization "Demo Property Management" registered',
                created_at: new Date().toISOString()
            },
            {
                id: 2,
                description: 'Property "Luxury Villa in Bangalore" was approved',
                created_at: new Date(Date.now() - 3600000).toISOString()
            },
            {
                id: 3,
                description: 'Payment of ₹25,000 received for invoice #INV-001',
                created_at: new Date(Date.now() - 7200000).toISOString()
            }
        ]
    }
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-IN').format(amount || 0)
}

const formatDate = (date) => {
    return new Date(date).toLocaleString()
}

onMounted(() => {
    loadOverview()
    loadSystemHealth()
    loadRecentActivity()
})
</script>
