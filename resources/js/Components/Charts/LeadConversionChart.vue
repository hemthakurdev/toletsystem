<template>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Lead Conversion Funnel</h3>
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
        
        <div class="mt-4 grid grid-cols-5 gap-4 text-center">
            <div>
                <p class="text-sm text-gray-600">New Leads</p>
                <p class="text-lg font-semibold text-sky-800">{{ formatNumber(leadData.new) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Contacted</p>
                <p class="text-lg font-semibold text-yellow-600">{{ formatNumber(leadData.contacted) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Interested</p>
                <p class="text-lg font-semibold text-orange-600">{{ formatNumber(leadData.interested) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Converted</p>
                <p class="text-lg font-semibold text-green-600">{{ formatNumber(leadData.converted) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Conversion Rate</p>
                <p class="text-lg font-semibold text-purple-600">{{ leadData.conversionRate }}%</p>
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
const leadData = ref({
    new: 0,
    contacted: 0,
    interested: 0,
    converted: 0,
    conversionRate: 0
})

const loadLeadData = async () => {
    try {
        const response = await axios.get(`/api/v1/analytics/leads?period=${selectedPeriod.value}`)
        const data = response.data.data
        
        leadData.value = {
            new: data.new_leads,
            contacted: data.contacted_leads,
            interested: data.interested_leads,
            converted: data.converted_leads,
            conversionRate: data.conversion_rate
        }
        
        updateChartData(data.funnel_data)
    } catch (error) {
        console.error('Failed to load lead data:', error)
    }
}

const updateChartData = (data) => {
    if (chart.value) {
        chart.value.data.labels = data.labels
        chart.value.data.datasets[0].data = data.values
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
                        label: 'Leads',
                        data: [],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(251, 146, 60, 0.8)',
                            'rgba(34, 197, 94, 0.8)'
                        ],
                        borderColor: [
                            'rgb(59, 130, 246)',
                            'rgb(245, 158, 11)',
                            'rgb(251, 146, 60)',
                            'rgb(34, 197, 94)'
                        ],
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + formatNumber(context.parsed.y)
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Lead Stage'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Number of Leads'
                        },
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatNumber(value)
                            }
                        }
                    }
                }
            }
        })
    }
}

const updateChart = () => {
    loadLeadData()
}

const exportChart = () => {
    if (chart.value) {
        const url = chart.value.toBase64Image()
        const link = document.createElement('a')
        link.download = `lead-conversion-chart-${selectedPeriod.value}.png`
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
    loadLeadData()
})

onUnmounted(() => {
    if (chart.value) {
        chart.value.destroy()
    }
})
</script>
