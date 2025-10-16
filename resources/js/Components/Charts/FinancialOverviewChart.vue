<template>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Financial Overview</h3>
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
                <p class="text-sm text-gray-600">Total Revenue</p>
                <p class="text-lg font-semibold text-green-600">₹{{ formatNumber(financialData.revenue) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Total Expenses</p>
                <p class="text-lg font-semibold text-red-600">₹{{ formatNumber(financialData.expenses) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Net Profit</p>
                <p class="text-lg font-semibold" :class="financialData.profit >= 0 ? 'text-green-600' : 'text-red-600'">
                    ₹{{ formatNumber(financialData.profit) }}
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Profit Margin</p>
                <p class="text-lg font-semibold" :class="financialData.margin >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ financialData.margin }}%
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
const financialData = ref({
    revenue: 0,
    expenses: 0,
    profit: 0,
    margin: 0
})

const loadFinancialData = async () => {
    try {
        const response = await axios.get(`/api/v1/analytics/financial?period=${selectedPeriod.value}`)
        const data = response.data.data
        
        financialData.value = {
            revenue: data.total_revenue,
            expenses: data.total_expenses,
            profit: data.net_profit,
            margin: data.profit_margin
        }
        
        updateChartData(data.chart_data)
    } catch (error) {
        console.error('Failed to load financial data:', error)
    }
}

const updateChartData = (data) => {
    if (chart.value) {
        chart.value.data.labels = data.labels
        chart.value.data.datasets[0].data = data.revenue
        chart.value.data.datasets[1].data = data.expenses
        chart.value.data.datasets[2].data = data.profit
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
                        label: 'Revenue',
                        data: [],
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1
                    },
                    {
                        label: 'Expenses',
                        data: [],
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderColor: 'rgb(239, 68, 68)',
                        borderWidth: 1
                    },
                    {
                        label: 'Net Profit',
                        data: [],
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgb(59, 130, 246)',
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
                        beginAtZero: true,
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
    loadFinancialData()
}

const exportChart = () => {
    if (chart.value) {
        const url = chart.value.toBase64Image()
        const link = document.createElement('a')
        link.download = `financial-overview-chart-${selectedPeriod.value}.png`
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
    loadFinancialData()
})

onUnmounted(() => {
    if (chart.value) {
        chart.value.destroy()
    }
})
</script>
