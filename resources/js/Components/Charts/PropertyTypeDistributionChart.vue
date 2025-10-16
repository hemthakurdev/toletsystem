<template>
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Property Type Distribution</h3>
            <div class="flex space-x-2">
                <select v-model="selectedCategory" @change="updateChart" class="text-sm border border-gray-300 rounded-md px-3 py-1">
                    <option value="all">All Categories</option>
                    <option value="rent">Rent Only</option>
                    <option value="sale">Sale Only</option>
                </select>
                <button @click="exportChart" class="text-sm bg-sky-800 text-white px-3 py-1 rounded-md hover:bg-sky-900">
                    Export
                </button>
            </div>
        </div>
        
        <div class="h-80">
            <canvas ref="chartCanvas"></canvas>
        </div>
        
        <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div v-for="(type, index) in propertyTypes" :key="index" class="flex items-center justify-center space-x-2">
                <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: getColor(index) }"></div>
                <div>
                    <p class="text-sm text-gray-600">{{ type.name }}</p>
                    <p class="text-lg font-semibold">{{ type.count }}</p>
                </div>
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
const selectedCategory = ref('all')
const chart = ref(null)
const propertyTypes = ref([])

const colors = [
    'rgba(59, 130, 246, 0.8)',
    'rgba(34, 197, 94, 0.8)',
    'rgba(147, 51, 234, 0.8)',
    'rgba(239, 68, 68, 0.8)',
    'rgba(245, 158, 11, 0.8)',
    'rgba(236, 72, 153, 0.8)',
    'rgba(6, 182, 212, 0.8)',
    'rgba(16, 185, 129, 0.8)'
]

const borderColors = [
    'rgb(59, 130, 246)',
    'rgb(34, 197, 94)',
    'rgb(147, 51, 234)',
    'rgb(239, 68, 68)',
    'rgb(245, 158, 11)',
    'rgb(236, 72, 153)',
    'rgb(6, 182, 212)',
    'rgb(16, 185, 129)'
]

const loadPropertyTypeData = async () => {
    try {
        const response = await axios.get(`/api/v1/analytics/property-types?category=${selectedCategory.value}`)
        const data = response.data.data
        
        propertyTypes.value = data.types
        
        updateChartData(data.types)
    } catch (error) {
        console.error('Failed to load property type data:', error)
    }
}

const updateChartData = (types) => {
    if (chart.value) {
        chart.value.data.labels = types.map(type => type.name)
        chart.value.data.datasets[0].data = types.map(type => type.count)
        chart.value.data.datasets[0].backgroundColor = types.map((_, index) => colors[index % colors.length])
        chart.value.data.datasets[0].borderColor = types.map((_, index) => borderColors[index % borderColors.length])
        chart.value.update()
    }
}

const createChart = () => {
    if (chartCanvas.value) {
        chart.value = new Chart(chartCanvas.value, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [
                    {
                        data: [],
                        backgroundColor: colors,
                        borderColor: borderColors,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0)
                                const percentage = ((context.parsed / total) * 100).toFixed(1)
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)'
                            }
                        }
                    }
                },
                cutout: '50%'
            }
        })
    }
}

const updateChart = () => {
    loadPropertyTypeData()
}

const exportChart = () => {
    if (chart.value) {
        const url = chart.value.toBase64Image()
        const link = document.createElement('a')
        link.download = `property-type-distribution-${selectedCategory.value}.png`
        link.href = url
        link.click()
    }
}

const getColor = (index) => {
    return colors[index % colors.length]
}

onMounted(async () => {
    await nextTick()
    createChart()
    loadPropertyTypeData()
})

onUnmounted(() => {
    if (chart.value) {
        chart.value.destroy()
    }
})
</script>
