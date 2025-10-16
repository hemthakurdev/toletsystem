<template>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Property Analytics</h3>
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
                <p class="text-sm text-gray-600">Total Properties</p>
                <p class="text-lg font-semibold text-sky-800">{{ formatNumber(propertyData.total) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Published</p>
                <p class="text-lg font-semibold text-green-600">{{ formatNumber(propertyData.published) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Pending</p>
                <p class="text-lg font-semibold text-yellow-600">{{ formatNumber(propertyData.pending) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Views</p>
                <p class="text-lg font-semibold text-purple-600">{{ formatNumber(propertyData.views) }}</p>
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
const propertyData = ref({
    total: 0,
    published: 0,
    pending: 0,
    views: 0
})

const loadPropertyData = async () => {
    try {
        const response = await axios.get(`/api/v1/analytics/properties?period=${selectedPeriod.value}`)
        const data = response.data.data
        
        propertyData.value = {
            total: data.total_properties,
            published: data.published_properties,
            pending: data.pending_properties,
            views: data.total_views
        }
        
        updateChartData(data.chart_data)
    } catch (error) {
        console.error('Failed to load property data:', error)
    }
}

const updateChartData = (data) => {
    if (chart.value) {
        chart.value.data.labels = data.labels
        chart.value.data.datasets[0].data = data.properties_added
        chart.value.data.datasets[1].data = data.properties_published
        chart.value.data.datasets[2].data = data.views
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
                        label: 'Properties Added',
                        data: [],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Properties Published',
                        data: [],
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Property Views',
                        data: [],
                        borderColor: 'rgb(147, 51, 234)',
                        backgroundColor: 'rgba(147, 51, 234, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1'
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
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Properties'
                        },
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value)
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Views'
                        },
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false,
                        },
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
    loadPropertyData()
}

const exportChart = () => {
    if (chart.value) {
        const url = chart.value.toBase64Image()
        const link = document.createElement('a')
        link.download = `property-analytics-chart-${selectedPeriod.value}.png`
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
    loadPropertyData()
})

onUnmounted(() => {
    if (chart.value) {
        chart.value.destroy()
    }
})
</script>
