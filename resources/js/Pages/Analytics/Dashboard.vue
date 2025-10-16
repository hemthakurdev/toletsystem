<template>
    <div class="p-6">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Advanced Analytics Dashboard</h1>
            <p class="text-gray-600">Comprehensive insights and performance metrics</p>
        </div>

        <!-- Date Range Selector -->
        <div class="mb-6 bg-white p-4 rounded-lg shadow">
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Date Range:</label>
                <select v-model="selectedDateRange" @change="updateAllCharts" class="border border-gray-300 rounded-md px-3 py-2">
                    <option value="7d">Last 7 Days</option>
                    <option value="30d">Last 30 Days</option>
                    <option value="90d">Last 90 Days</option>
                    <option value="1y">Last Year</option>
                </select>
                <button @click="refreshAllData" class="bg-sky-800 text-white px-4 py-2 rounded-md hover:bg-sky-900">
                    Refresh Data
                </button>
                <button @click="exportAllCharts" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                    Export All
                </button>
            </div>
        </div>

        <!-- Key Metrics Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-sky-100 rounded-lg">
                        <svg class="w-8 h-8 text-sky-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                        <p class="text-2xl font-semibold text-gray-900">₹{{ formatNumber(overview.totalRevenue) }}</p>
                        <p class="text-xs" :class="overview.revenueGrowth >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ overview.revenueGrowth >= 0 ? '+' : '' }}{{ overview.revenueGrowth }}% from last period
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Active Users</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(overview.activeUsers) }}</p>
                        <p class="text-xs" :class="overview.userGrowth >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ overview.userGrowth >= 0 ? '+' : '' }}{{ overview.userGrowth }}% from last period
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Properties</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(overview.totalProperties) }}</p>
                        <p class="text-xs text-green-600">{{ overview.publishedProperties }} published</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-orange-100 rounded-lg">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Conversion Rate</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ overview.conversionRate }}%</p>
                        <p class="text-xs text-sky-800">{{ overview.totalLeads }} total leads</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Revenue Chart -->
            <RevenueChart ref="revenueChart" :period="selectedDateRange" />
            
            <!-- User Growth Chart -->
            <UserGrowthChart ref="userGrowthChart" :period="selectedDateRange" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Property Analytics Chart -->
            <PropertyAnalyticsChart ref="propertyChart" :period="selectedDateRange" />
            
            <!-- Financial Overview Chart -->
            <FinancialOverviewChart ref="financialChart" :period="selectedDateRange" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Property Type Distribution -->
            <PropertyTypeDistributionChart ref="propertyTypeChart" />
            
            <!-- Lead Conversion Funnel -->
            <LeadConversionChart ref="leadChart" :period="selectedDateRange" />
        </div>

        <!-- Performance Metrics -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Metrics</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-sky-800">{{ overview.avgResponseTime }}ms</div>
                    <div class="text-sm text-gray-600">Average Response Time</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600">{{ overview.uptime }}%</div>
                    <div class="text-sm text-gray-600">System Uptime</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600">{{ overview.satisfactionScore }}/5</div>
                    <div class="text-sm text-gray-600">User Satisfaction</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import RevenueChart from '@/Components/Charts/RevenueChart.vue'
import UserGrowthChart from '@/Components/Charts/UserGrowthChart.vue'
import PropertyAnalyticsChart from '@/Components/Charts/PropertyAnalyticsChart.vue'
import FinancialOverviewChart from '@/Components/Charts/FinancialOverviewChart.vue'
import PropertyTypeDistributionChart from '@/Components/Charts/PropertyTypeDistributionChart.vue'
import LeadConversionChart from '@/Components/Charts/LeadConversionChart.vue'

const selectedDateRange = ref('30d')
const overview = ref({
    totalRevenue: 0,
    revenueGrowth: 0,
    activeUsers: 0,
    userGrowth: 0,
    totalProperties: 0,
    publishedProperties: 0,
    conversionRate: 0,
    totalLeads: 0,
    avgResponseTime: 0,
    uptime: 0,
    satisfactionScore: 0
})

const revenueChart = ref(null)
const userGrowthChart = ref(null)
const propertyChart = ref(null)
const financialChart = ref(null)
const propertyTypeChart = ref(null)
const leadChart = ref(null)

const loadOverviewData = async () => {
    try {
        const response = await axios.get(`/api/v1/analytics/overview?period=${selectedDateRange.value}`)
        const data = response.data.data
        
        overview.value = {
            totalRevenue: data.total_revenue,
            revenueGrowth: data.revenue_growth,
            activeUsers: data.active_users,
            userGrowth: data.user_growth,
            totalProperties: data.total_properties,
            publishedProperties: data.published_properties,
            conversionRate: data.conversion_rate,
            totalLeads: data.total_leads,
            avgResponseTime: data.avg_response_time,
            uptime: data.uptime,
            satisfactionScore: data.satisfaction_score
        }
    } catch (error) {
        console.error('Failed to load overview data:', error)
    }
}

const updateAllCharts = () => {
    // Update all chart components with new period
    if (revenueChart.value) revenueChart.value.updateChart()
    if (userGrowthChart.value) userGrowthChart.value.updateChart()
    if (propertyChart.value) propertyChart.value.updateChart()
    if (financialChart.value) financialChart.value.updateChart()
    if (leadChart.value) leadChart.value.updateChart()
    
    // Reload overview data
    loadOverviewData()
}

const refreshAllData = () => {
    updateAllCharts()
}

const exportAllCharts = () => {
    // Export all charts as images
    const charts = [revenueChart, userGrowthChart, propertyChart, financialChart, propertyTypeChart, leadChart]
    
    charts.forEach((chartRef, index) => {
        if (chartRef.value && chartRef.value.exportChart) {
            setTimeout(() => {
                chartRef.value.exportChart()
            }, index * 500) // Stagger exports
        }
    })
}

const formatNumber = (num) => {
    return new Intl.NumberFormat('en-IN').format(num)
}

onMounted(() => {
    loadOverviewData()
})
</script>
