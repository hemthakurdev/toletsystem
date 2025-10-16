<template>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">User Growth</h3>
            <div class="flex space-x-2">
                <select v-model="selectedPeriod" @change="updateChart" class="text-sm border border-gray-300 rounded-md px-3 py-1">
                    <option value="7d">Last 7 Days</option>
                    <option value="30d">Last 30 Days</option>
                    <option value="90d">Last 90 Days</option>
                    <option value="1y">Last Year</option>
                </select>
                <button @click="exportChart" class="text-sm bg-sky-800 text-white px-3 py-1 rounded-md hover:bg-sky-900">
                    Export
                </button>
            </div>
        </div>
        
        <div class="h-80">
            <canvas ref="chartCanvas"></canvas>
        </div>
        
        <div class="mt-4 grid grid-cols-4 gap-4 text-center">
            <div>
                <p class="text-sm text-gray-600">New Users</p>
                <p class="text-lg font-semibold text-green-600">{{ formatNumber(userData.newUsers) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Active Users</p>
                <p class="text-lg font-semibold text-sky-800">{{ formatNumber(userData.activeUsers) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Total Users</p>
                <p class="text-lg font-semibold text-purple-600">{{ formatNumber(userData.totalUsers) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Growth Rate</p>
                <p class="text-lg font-semibold" :class="userData.growth >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ userData.growth >= 0 ? '+' : '' }}{{ userData.growth }}%
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { Chart, registerables } from 'chart.js'
import axios from 'axios'

Chart.register(...registerables)

const chartCanvas = ref(null)
const selectedPeriod = ref('30d')
const chart = ref(null)
const userData = ref({
    newUsers: 0,
    activeUsers: 0,
    totalUsers: 0,
    growth: 0
})

const loadUserData = async () => {
    try {
        const response = await axios.get(`/api/v1/analytics/users?period=${selectedPeriod.value}`)
        const data = response.data.data
        
        userData.value = {
            newUsers: data.new_users,
            activeUsers: data.active_users,
            totalUsers: data.total_users,
            growth: data.growth_rate
        }
        
        updateChartData(data.chart_data)
    } catch (error) {
        console.error('Failed to load user data:', error)
    }
}

const updateChartData = (data) => {
    if (chart.value) {
        chart.value.data.labels = data.labels
        chart.value.data.datasets[0].data = data.new_users
        chart.value.data.datasets[1].data = data.active_users
        chart.value.data.datasets[2].data = data.total_users
        chart.value.update()
    }
}

const createChart = () => {
    if (chartCanvas.value) {
        chart.value = new Chart(chartCanvas.value, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'New Users',
                        data: [],
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1
                    },
                    {
                        label: 'Active Users',
                        data: [],
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1
                    },
                    {
                        label: 'Total Users',
                        data: [],
                        backgroundColor: 'rgba(147, 51, 234, 0.8)',
                        borderColor: 'rgb(147, 51, 234)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatNumber(context.parsed.y)
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Number of Users'
                        },
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value)
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        })
    }
}

const updateChart = () => {
    loadUserData()
}

const exportChart = () => {
    if (chart.value) {
        const url = chart.value.toBase64Image()
        const link = document.createElement('a')
        link.download = `user-growth-chart-${selectedPeriod.value}.png`
        link.href = url
        link.click()
    }
}

const formatNumber = (num) => {
    return new Intl.NumberFormat('en-IN').format(num)
}

onMounted(async () => {
    await nextTick()
    createChart()
    loadUserData()
})

onUnmounted(() => {
    if (chart.value) {
        chart.value.destroy()
    }
})
</script>
