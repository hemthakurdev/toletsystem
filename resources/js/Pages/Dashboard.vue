<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Navigation -->
        <nav class="glass sticky top-0 z-50 backdrop-blur-md">
            <div class="container-mobile">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <h1 class="text-2xl font-bold bg-gradient-to-r from-sky-800 to-sky-900 bg-clip-text text-transparent">
                                SaleMitra
                            </h1>
                        </div>
                        <div class="hidden lg:ml-10 lg:flex lg:items-baseline lg:space-x-4">
                            <a href="/dashboard" class="nav-link active">Dashboard</a>
                            <a href="/properties" class="nav-link">Properties</a>
                            <a href="/tenants" class="nav-link">Tenants</a>
                            <a href="/invoices" class="nav-link">Invoices</a>
                            <a href="/leads" class="nav-link">Leads</a>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="p-2 text-gray-400 hover:text-gray-600 transition-colors rounded-lg hover:bg-gray-100">
                                <span class="sr-only">View notifications</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.828 7l2.586 2.586a2 2 0 002.828 0L12.828 7H4.828z" />
                                </svg>
                            </button>
                        </div>
                        <!-- User Profile -->
                        <div class="flex items-center space-x-3">
                            <div class="hidden sm:block text-sm">
                                <p class="text-gray-700 font-medium">{{ $page.props.auth?.user?.name || 'Demo User' }}</p>
                                <p class="text-gray-500 text-xs">{{ $page.props.auth?.user?.roles?.[0] || 'Admin' }}</p>
                            </div>
                            <ImagePlaceholder 
                                :src="$page.props.auth?.user?.avatar_url"
                                :alt="($page.props.auth?.user?.name || 'Demo User') + ' Profile'"
                                size="sm"
                                shape="circle"
                                :placeholder-text="($page.props.auth?.user?.name || 'Demo User').charAt(0)"
                                className="border-2 border-white shadow-soft"
                            />
                            <a href="/login" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">Login</a>
                        </div>
                        <!-- Mobile Menu Button -->
                        <button 
                            @click="toggleMobileMenu"
                            class="lg:hidden p-2 text-gray-600 hover:text-gray-900 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
                <!-- Mobile Menu -->
                <div v-if="showMobileMenu" class="lg:hidden py-4 border-t border-gray-200">
                    <div class="flex flex-col space-y-2">
                        <a href="/dashboard" class="nav-link active">Dashboard</a>
                        <a href="/properties" class="nav-link">Properties</a>
                        <a href="/tenants" class="nav-link">Tenants</a>
                        <a href="/invoices" class="nav-link">Invoices</a>
                        <a href="/leads" class="nav-link">Leads</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="container-mobile py-6">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="card">
                    <div class="card-body">
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Welcome to SaleMitra Dashboard</h1>
                        <p class="text-gray-600">Manage your properties, tenants, and finances all in one place.</p>
                    </div>
                </div>
            </div>
            
            <!-- Main Stats -->
            <div class="grid-responsive mb-8">
                <ModernCard className="text-center">
                        <div class="flex items-center justify-center w-12 h-12 bg-sky-100 rounded-xl mx-auto mb-4">
                            <svg class="w-6 h-6 text-sky-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-sky-800 mb-2">{{ loading ? '...' : stats.total_properties }}</div>
                    <div class="text-sm text-gray-600">Total Properties</div>
                </ModernCard>
                
                <ModernCard className="text-center">
                    <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-xl mx-auto mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-green-600 mb-2">{{ loading ? '...' : stats.occupied_units }}</div>
                    <div class="text-sm text-gray-600">Occupied Units</div>
                </ModernCard>
                
                <ModernCard className="text-center">
                    <div class="flex items-center justify-center w-12 h-12 bg-yellow-100 rounded-xl mx-auto mb-4">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-yellow-600 mb-2">{{ loading ? '...' : formatCurrency(stats.monthly_revenue) }}</div>
                    <div class="text-sm text-gray-600">Monthly Revenue</div>
                </ModernCard>
                
                <ModernCard className="text-center">
                    <div class="flex items-center justify-center w-12 h-12 bg-red-100 rounded-xl mx-auto mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-red-600 mb-2">{{ loading ? '...' : stats.pending_invoices }}</div>
                    <div class="text-sm text-gray-600">Pending Invoices</div>
                </ModernCard>
            </div>

            <!-- Secondary Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="stat-card">
                    <div class="stat-number text-purple-600">{{ loading ? '...' : stats.total_tenants || 0 }}</div>
                    <div class="stat-label">Total Tenants</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number text-indigo-600">{{ loading ? '...' : stats.total_leads || 0 }}</div>
                    <div class="stat-label">Total Leads</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number text-pink-600">{{ loading ? '...' : formatCurrency(stats.total_revenue || 0) }}</div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number text-teal-600">{{ loading ? '...' : stats.collection_rate || '0%' }}</div>
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
                            <span class="font-semibold text-green-600">{{ loading ? '...' : stats.available_units || 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Occupancy Rate</span>
                            <span class="font-semibold text-sky-800">{{ loading ? '...' : stats.occupancy_rate || '0%' }}</span>
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
                            <span class="font-semibold text-green-600">{{ loading ? '...' : stats.paid_invoices || 0 }}</span>
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
                        <div v-if="loading" class="text-center text-gray-500 py-4">
                            Loading activities...
                        </div>
                        <div v-else-if="recentActivity.length === 0" class="text-center text-gray-500 py-4">
                            No recent activity
                        </div>
                        <div v-else v-for="activity in recentActivity" :key="activity.id" class="flex items-center text-sm">
                            <div :class="`w-2 h-2 bg-${getActivityColor(activity.type)}-400 rounded-full mr-3`"></div>
                            <span class="text-gray-600">{{ activity.message }}</span>
                            <span class="text-gray-400 text-xs ml-auto">{{ formatDate(activity.created_at) }}</span>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Upcoming Tasks</h3>
                    <div class="space-y-3">
                        <div v-if="loading" class="text-center text-gray-500 py-4">
                            Loading tasks...
                        </div>
                        <div v-else-if="upcomingTasks.length === 0" class="text-center text-gray-500 py-4">
                            No upcoming tasks
                        </div>
                        <div v-else v-for="task in upcomingTasks" :key="task.id" class="flex items-center justify-between text-sm">
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
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import ImagePlaceholder from '../Components/ImagePlaceholder.vue'
import ModernCard from '../Components/ModernCard.vue'

// Reactive data
const stats = ref({
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

const recentActivity = ref([])
const upcomingTasks = ref([])
const showMobileMenu = ref(false)

// Mobile menu toggle
const toggleMobileMenu = () => {
    showMobileMenu.value = !showMobileMenu.value
}
const loading = ref(true)

// Load dashboard data
const loadDashboardData = async () => {
    try {
        loading.value = true
        
        // Load statistics
        const statsResponse = await fetch('http://127.0.0.1:8000/api/v1/org/analytics/overview')
        const statsData = await statsResponse.json()
        
        if (statsData.success) {
            stats.value = {
                ...stats.value,
                total_properties: statsData.data.total_properties || 0,
                occupied_units: statsData.data.occupied_units || 0,
                monthly_revenue: statsData.data.monthly_revenue || 0,
                pending_invoices: statsData.data.pending_invoices || 0,
                total_tenants: statsData.data.total_tenants || 0,
                total_leads: statsData.data.total_leads || 0,
                total_revenue: statsData.data.total_revenue || 0,
                collection_rate: statsData.data.collection_rate || '0%',
                available_units: statsData.data.available_units || 0,
                occupancy_rate: statsData.data.occupancy_rate || '0%',
                average_rent: statsData.data.average_rent || 0,
                total_area: statsData.data.total_area || 0,
                paid_invoices: statsData.data.paid_invoices || 0,
                overdue_amount: statsData.data.overdue_amount || 0,
                this_month_revenue: statsData.data.this_month_revenue || 0,
                last_month_revenue: statsData.data.last_month_revenue || 0
            }
        }
        
        // Load recent activity (notifications)
        const activityResponse = await fetch('http://127.0.0.1:8000/api/v1/org/notifications')
        const activityData = await activityResponse.json()
        
        if (activityData.success) {
            recentActivity.value = activityData.data.slice(0, 4) // Show last 4 activities
        }
        
        // Load upcoming tasks (invoices due soon)
        const tasksResponse = await fetch('http://127.0.0.1:8000/api/v1/org/invoices?status=pending&due_soon=true')
        const tasksData = await tasksResponse.json()
        
        if (tasksData.success) {
            upcomingTasks.value = tasksData.data.slice(0, 3) // Show next 3 tasks
        }
        
    } catch (error) {
        console.error('Error loading dashboard data:', error)
    } finally {
        loading.value = false
    }
}

// Removed button handlers - dashboard now focuses on stats only

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

onMounted(() => {
    loadDashboardData()
})
</script>
