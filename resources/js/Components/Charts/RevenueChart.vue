<template>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Revenue Analytics</h3>
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
        
        <div class="mt-4 grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-sm text-gray-600">Total Revenue</p>
                <p class="text-lg font-semibold text-green-600">₹{{ formatNumber(revenueData.total) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Average Daily</p>
                <p class="text-lg font-semibold text-sky-800">₹{{ formatNumber(revenueData.average) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Growth Rate</p>
                <p class="text-lg font-semibold" :class="revenueData.growth >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ revenueData.growth >= 0 ? '+' : '' }}{{ revenueData.growth }}%
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
const revenueData = ref({
    total: 0,
    average: 0,
    growth: 0
})

const loadRevenueData = async () => {
    try {
        const response = await axios.get(`/api/v1/analytics/revenue?period=${selectedPeriod.value}`)
        const data = response.data.data
        
        revenueData.value = {
            total: data.total_revenue,
            average: data.average_daily,
            growth: data.growth_rate
        }
        
        updateChartData(data.chart_data)
    } catch (error) {
        console.error('Failed to load revenue data:', error)
    }
}

const updateChartData = (data) => {
    if (chart.value) {
        chart.value.data.labels = data.labels
        chart.value.data.datasets[0].data = data.revenue
        chart.value.data.datasets[1].data = data.payments
        chart.value.update()
    }
}

const createChart = () => {
    if (chartCanvas.value) {
        chart.value = new Chart(chartCanvas.value, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Revenue',
                        data: [],
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Payments',
                        data: [],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
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
                                return context.dataset.label + ': ₹' + formatNumber(context.parsed.y)
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
                            text: 'Amount (₹)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '₹' + formatNumber(value)
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
    loadRevenueData()
}

const exportChart = () => {
    if (chart.value) {
        const url = chart.value.toBase64Image()
        const link = document.createElement('a')
        link.download = `revenue-chart-${selectedPeriod.value}.png`
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
    loadRevenueData()
})

onUnmounted(() => {
    if (chart.value) {
        chart.value.destroy()
    }
})
</script>
