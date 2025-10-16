<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Property Comparison</h1>
                        <p class="text-gray-600">Compare properties side by side</p>
                    </div>
                    <div class="flex space-x-3">
                        <button @click="goToSearch" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Browse Properties
                        </button>
                        <button @click="shareComparison" class="px-4 py-2 bg-sky-800 text-white rounded-md text-sm font-medium hover:bg-sky-900">
                            Share Comparison
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <PropertyComparison
                :properties="comparisonProperties"
                @property-removed="onPropertyRemoved"
                @property-viewed="onPropertyViewed"
                @contact-owner="onContactOwner"
                @browse-properties="goToSearch"
            />
        </div>

        <!-- Property Selection Modal -->
        <div v-if="showPropertySelection" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Select Properties to Compare</h3>
                        <button @click="showPropertySelection = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <!-- Search -->
                        <div class="flex space-x-4">
                            <input
                                v-model="searchQuery"
                                @input="searchProperties"
                                type="text"
                                placeholder="Search properties..."
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800"
                            />
                            <button @click="searchProperties" class="px-4 py-2 bg-sky-800 text-white rounded-md hover:bg-sky-900">
                                Search
                            </button>
                        </div>

                        <!-- Property Grid -->
                        <div v-if="searchResults.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto">
                            <div
                                v-for="property in searchResults"
                                :key="property.id"
                                class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
                                :class="{ 'border-sky-500 bg-sky-50': isSelected(property.id) }"
                                @click="toggleProperty(property)"
                            >
                                <div class="flex items-start space-x-3">
                                    <div class="w-16 h-16 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                        <img
                                            v-if="getPropertyImage(property)"
                                            :src="getPropertyImage(property)"
                                            :alt="property.title"
                                            class="w-full h-full object-cover"
                                        />
                                        <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-medium text-gray-900 truncate">{{ property.title }}</h4>
                                        <p class="text-xs text-gray-500">{{ property.locality }}, {{ property.city }}</p>
                                        <p class="text-sm font-medium text-gray-900">₹{{ formatNumber(property.price) }}</p>
                                        <div class="flex items-center text-xs text-gray-500 mt-1">
                                            <span v-if="property.bedrooms">{{ property.bedrooms }} BHK</span>
                                            <span v-if="property.area_sqft" class="ml-2">{{ formatNumber(property.area_sqft) }} sq ft</span>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <div v-if="isSelected(property.id)" class="w-5 h-5 bg-sky-800 rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div v-else class="w-5 h-5 border-2 border-gray-300 rounded-full"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="searching" class="text-center py-8">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-sky-800 mx-auto"></div>
                            <p class="text-gray-500 mt-2">Searching properties...</p>
                        </div>

                        <div v-else class="text-center py-8 text-gray-500">
                            No properties found. Try a different search term.
                        </div>

                        <!-- Selected Properties Summary -->
                        <div v-if="selectedProperties.length > 0" class="border-t pt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">
                                Selected Properties ({{ selectedProperties.length }}/3)
                            </h4>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="property in selectedProperties"
                                    :key="property.id"
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-sky-100 text-sky-900"
                                >
                                    {{ property.title }}
                                    <button @click="removeSelected(property.id)" class="ml-1 text-sky-800 hover:text-sky-900">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end space-x-3 pt-4 border-t">
                            <button @click="showPropertySelection = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button @click="addToComparison" :disabled="selectedProperties.length === 0" class="px-4 py-2 bg-sky-800 text-white rounded-md text-sm font-medium hover:bg-sky-900 disabled:opacity-50">
                                Add to Comparison ({{ selectedProperties.length }})
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import PropertyComparison from '../../Components/PropertyComparison.vue'

// Router is already imported from @inertiajs/vue3

const comparisonProperties = ref([])
const showPropertySelection = ref(false)
const searchResults = ref([])
const selectedProperties = ref([])
const searchQuery = ref('')
const searching = ref(false)

const getPropertyImage = (property) => {
    if (property.images && property.images.length > 0) {
        const image = property.images[0]
        if (typeof image === 'string') {
            return `/storage/${image}`
        }
        if (image.thumbnails && image.thumbnails.medium) {
            return `/storage/${image.thumbnails.medium}`
        }
        if (image.original) {
            return `/storage/${image.original}`
        }
    }
    return null
}

const formatNumber = (number) => {
    return new Intl.NumberFormat('en-IN').format(number)
}

const searchProperties = async () => {
    if (!searchQuery.value.trim()) return
    
    searching.value = true
    try {
        const response = await axios.get(`/api/v1/search/properties?search=${encodeURIComponent(searchQuery.value)}&per_page=20`)
        if (response.data.success) {
            searchResults.value = response.data.data.data
        }
    } catch (error) {
        console.error('Error searching properties:', error)
    } finally {
        searching.value = false
    }
}

const isSelected = (propertyId) => {
    return selectedProperties.value.some(p => p.id === propertyId)
}

const toggleProperty = (property) => {
    if (isSelected(property.id)) {
        selectedProperties.value = selectedProperties.value.filter(p => p.id !== property.id)
    } else if (selectedProperties.value.length < 3) {
        selectedProperties.value.push(property)
    }
}

const removeSelected = (propertyId) => {
    selectedProperties.value = selectedProperties.value.filter(p => p.id !== propertyId)
}

const addToComparison = () => {
    comparisonProperties.value = [...comparisonProperties.value, ...selectedProperties.value]
    selectedProperties.value = []
    showPropertySelection.value = false
}

const onPropertyRemoved = (propertyId) => {
    if (propertyId === 'all') {
        comparisonProperties.value = []
    } else {
        comparisonProperties.value = comparisonProperties.value.filter(p => p.id !== propertyId)
    }
}

const onPropertyViewed = (property) => {
    router.push(`/marketplace/properties/${property.id}`)
}

const onContactOwner = (property) => {
    // Implement contact owner functionality
    console.log('Contact owner for property:', property.id)
}

const goToSearch = () => {
    router.push('/marketplace')
}

const shareComparison = () => {
    if (comparisonProperties.value.length === 0) return
    
    const propertyIds = comparisonProperties.value.map(p => p.id).join(',')
    const shareUrl = `${window.location.origin}/compare?properties=${propertyIds}`
    
    if (navigator.share) {
        navigator.share({
            title: 'Property Comparison',
            text: 'Check out this property comparison',
            url: shareUrl
        })
    } else {
        navigator.clipboard.writeText(shareUrl).then(() => {
            alert('Comparison link copied to clipboard!')
        })
    }
}

// Load properties from URL parameters
const loadPropertiesFromUrl = () => {
    const urlParams = new URLSearchParams(window.location.search)
    const propertyIds = urlParams.get('properties')
    
    if (propertyIds) {
        const ids = propertyIds.split(',')
        // Load properties by IDs
        ids.forEach(async (id) => {
            try {
                const response = await axios.get(`/api/v1/properties/${id}`)
                if (response.data.success) {
                    comparisonProperties.value.push(response.data.data)
                }
            } catch (error) {
                console.error('Error loading property:', error)
            }
        })
    }
}

onMounted(() => {
    loadPropertiesFromUrl()
    
    // If no properties in comparison, show selection modal
    if (comparisonProperties.value.length === 0) {
        showPropertySelection.value = true
    }
})
</script>
