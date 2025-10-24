<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-sky-50 to-indigo-100 relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
        
        <!-- Navigation -->
        <Header current-path="/dashboard">
            <template #notifications>
                <div class="relative">
                    <button class="p-2 text-gray-400 hover:text-sky-600 transition-all duration-300 rounded-xl hover:bg-sky-50 group">
                        <span class="sr-only">View notifications</span>
                        <svg class="h-6 w-6 group-hover:scale-110 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.828 7l2.586 2.586a2 2 0 002.828 0L12.828 7H4.828z" />
                        </svg>
                        <!-- Notification Badge -->
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full animate-pulse"></span>
                    </button>
                </div>
            </template>
        </Header>

        <!-- Main Content -->
        <div class="container-mobile py-6">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="card">
                    <div class="card-body">
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
                            {{ props.userType === 'frontend' ? 'Welcome to Your Dashboard' : 'Welcome to SaleMitra Dashboard' }}
                        </h1>
                        <p class="text-gray-600">
                            {{ props.userType === 'frontend' 
                                ? 'Track your favorites, inquiries, and property searches.' 
                                : 'Manage your properties, tenants, and finances all in one place.' 
                            }}
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Main Stats -->
            <div class="grid-responsive mb-8">
                <!-- Frontend User Stats -->
                <template v-if="props.userType === 'frontend'">
                    <div class="stat-card group cursor-pointer">
                        <div class="icon-container icon-sky mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                        <div class="stat-number">{{ props.stats.total_favorites }}</div>
                        <div class="stat-label">Favorites</div>
                        <div class="mt-2 text-xs text-emerald-600 font-medium">
                            <span class="inline-flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Saved properties
                            </span>
                        </div>
                    </div>
                    
                    <div class="stat-card group cursor-pointer">
                        <div class="icon-container icon-emerald mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <div class="stat-number">{{ props.stats.total_inquiries }}</div>
                        <div class="stat-label">Inquiries</div>
                        <div class="mt-2 text-xs text-emerald-600 font-medium">
                            <span class="inline-flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Messages sent
                            </span>
                        </div>
                    </div>
                    
                    <div class="stat-card group cursor-pointer">
                        <div class="icon-container icon-amber mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <div class="stat-number">{{ props.stats.saved_searches }}</div>
                        <div class="stat-label">Saved Searches</div>
                        <div class="mt-2 text-xs text-emerald-600 font-medium">
                            <span class="inline-flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Active alerts
                            </span>
                        </div>
                    </div>
                    
                    <div class="stat-card group cursor-pointer">
                        <div class="icon-container icon-purple mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                        <div class="stat-number">{{ props.stats.recent_views }}</div>
                        <div class="stat-label">Recent Views</div>
                        <div class="mt-2 text-xs text-emerald-600 font-medium">
                            <span class="inline-flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                This week
                            </span>
                        </div>
                    </div>
                </template>
                
                <!-- Organization User Stats -->
                <template v-else>
                    <div class="stat-card group cursor-pointer">
                        <div class="icon-container icon-sky mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div class="stat-number">{{ props.stats.total_properties }}</div>
                        <div class="stat-label">Total Properties</div>
                        <div class="mt-2 text-xs text-emerald-600 font-medium">
                            <span class="inline-flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                +12% this month
                            </span>
                        </div>
                    </div>
                
                <div class="stat-card group cursor-pointer">
                    <div class="icon-container icon-emerald mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="stat-number text-emerald-600">{{ props.stats.occupied_units }}</div>
                    <div class="stat-label">Occupied Units</div>
                    <div class="mt-2 text-xs text-emerald-600 font-medium">
                        <span class="inline-flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            +8% this month
                        </span>
                    </div>
                </div>
                
                <div class="stat-card group cursor-pointer">
                    <div class="icon-container icon-amber mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="stat-number text-amber-600">{{ loading ? '...' : formatCurrency(stats.monthly_revenue) }}</div>
                    <div class="stat-label">Monthly Revenue</div>
                    <div class="mt-2 text-xs text-emerald-600 font-medium">
                        <span class="inline-flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            +15% this month
                        </span>
                    </div>
                </div>
                
                <div class="stat-card group cursor-pointer">
                    <div class="icon-container icon-red mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="stat-number text-red-600">{{ props.stats.pending_invoices }}</div>
                    <div class="stat-label">Pending Invoices</div>
                    <div class="mt-2 text-xs text-red-600 font-medium">
                        <span class="inline-flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            -3% this month
                        </span>
                    </div>
                </div>
                </template>
            </div>

            <!-- Secondary Stats -->
            <template v-if="props.userType !== 'frontend'">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="stat-card">
                    <div class="stat-number text-purple-600">{{ props.stats.total_tenants || 0 }}</div>
                    <div class="stat-label">Total Tenants</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number text-indigo-600">{{ props.stats.total_leads || 0 }}</div>
                    <div class="stat-label">Total Leads</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number text-pink-600">{{ loading ? '...' : formatCurrency(stats.total_revenue || 0) }}</div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number text-teal-600">{{ props.stats.collection_rate || '0%' }}</div>
                    <div class="stat-label">Collection Rate</div>
                </div>
            </div>

            <!-- Additional Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="dashboard-card">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Property Statistics</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Available Units</span>
                            <span class="font-semibold text-green-600">{{ props.stats.available_units || 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Occupancy Rate</span>
                            <span class="font-semibold text-sky-800">{{ props.stats.occupancy_rate || '0%' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Average Rent</span>
                            <span class="font-semibold text-purple-600">{{ loading ? '...' : formatCurrency(stats.average_rent || 0) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Area</span>
                            <span class="font-semibold text-indigo-600">{{ loading ? '...' : (stats.total_area || 0) + ' sq ft' }}</span>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Overview</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Paid Invoices</span>
                            <span class="font-semibold text-green-600">{{ props.stats.paid_invoices || 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Overdue Amount</span>
                            <span class="font-semibold text-red-600">{{ loading ? '...' : formatCurrency(stats.overdue_amount || 0) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">This Month Revenue</span>
                            <span class="font-semibold text-sky-800">{{ loading ? '...' : formatCurrency(stats.this_month_revenue || 0) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Last Month Revenue</span>
                            <span class="font-semibold text-gray-600">{{ loading ? '...' : formatCurrency(stats.last_month_revenue || 0) }}</span>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
                    <div class="space-y-3">
                                <div v-if="props.recentActivity.length === 0" class="text-center text-gray-500 py-4">
                                    No recent activity
                                </div>
                                <div v-else v-for="activity in props.recentActivity" :key="activity.id" class="flex items-center text-sm">
                            <div :class="`w-2 h-2 bg-${getActivityColor(activity.type)}-400 rounded-full mr-3`"></div>
                            <span class="text-gray-600">{{ activity.message }}</span>
                            <span class="text-gray-400 text-xs ml-auto">{{ formatDate(activity.created_at) }}</span>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Upcoming Tasks</h3>
                    <div class="space-y-3">
                                <div v-if="props.upcomingTasks.length === 0" class="text-center text-gray-500 py-4">
                                    No upcoming tasks
                                </div>
                                <div v-else v-for="task in props.upcomingTasks" :key="task.id" class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">{{ task.description || 'Invoice payment due' }}</span>
                            <span :class="`font-medium ${
                                getDaysUntilDue(task.due_date) === 'Overdue' ? 'text-red-600' :
                                getDaysUntilDue(task.due_date) === 'Today' ? 'text-orange-600' :
                                'text-sky-800'
                            }`">
                                {{ getDaysUntilDue(task.due_date) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import Header from '../Components/Header.vue'
import ImagePlaceholder from '../Components/ImagePlaceholder.vue'
import ModernCard from '../Components/ModernCard.vue'

// Props from backend
const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_properties: 0,
            occupied_units: 0,
            monthly_revenue: 0,
            pending_invoices: 0,
            total_tenants: 0,
            total_leads: 0,
            total_revenue: 0,
            collection_rate: '0%',
            available_units: 0,
            occupancy_rate: '0%',
            average_rent: 0,
            total_area: 0,
            paid_invoices: 0,
            overdue_amount: 0,
            this_month_revenue: 0,
            last_month_revenue: 0
        })
    },
    recentActivity: {
        type: Array,
        default: () => []
    },
    upcomingTasks: {
        type: Array,
        default: () => []
    },
    userType: {
        type: String,
        default: 'organization'
    }
})

// Mobile menu is now handled by the Header component

// Dashboard data is now provided via props from the backend

// Format currency
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 0
    }).format(amount)
}

// Format date
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-IN', {
        day: 'numeric',
        month: 'short'
    })
}

// Get days until due
const getDaysUntilDue = (dueDate) => {
    const today = new Date()
    const due = new Date(dueDate)
    const diffTime = due - today
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    
    if (diffDays < 0) return 'Overdue'
    if (diffDays === 0) return 'Today'
    if (diffDays === 1) return '1 day'
    return `${diffDays} days`
}

// Get activity color
const getActivityColor = (type) => {
    const colors = {
        'lead': 'green',
        'payment': 'blue',
        'published': 'yellow',
        'overdue': 'red',
        'default': 'gray'
    }
    return colors[type] || colors.default
}

// Data is provided via props, no need for onMounted
</script>
