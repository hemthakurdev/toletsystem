<template>
    <div class="property-comparison">
        <!-- Comparison Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Property Comparison</h2>
                    <p class="text-gray-600">Compare up to 3 properties side by side</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="clearComparison" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Clear All
                    </button>
                    <button @click="exportComparison" class="px-4 py-2 bg-sky-800 text-white rounded-md text-sm font-medium hover:bg-sky-900">
                        Export Comparison
                    </button>
                </div>
            </div>
        </div>

        <!-- Comparison Table -->
        <div v-if="comparisonProperties.length > 0" class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Property Details
                            </th>
                            <th v-for="(property, index) in comparisonProperties" :key="property.id" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex flex-col items-center">
                                    <span>Property {{ index + 1 }}</span>
                                    <button @click="removeFromComparison(property.id)" class="mt-1 text-red-600 hover:text-red-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Images -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                Images
                            </td>
                            <td v-for="property in comparisonProperties" :key="property.id" class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="w-32 h-24 mx-auto bg-gray-200 rounded-lg overflow-hidden">
                                    <img
                                        v-if="getPropertyImage(property)"
                                        :src="getPropertyImage(property)"
                                        :alt="property.title"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Title & Location -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                Title & Location
                            </td>
                            <td v-for="property in comparisonProperties" :key="property.id" class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm">
                                    <div class="font-medium text-gray-900">{{ property.title }}</div>
                                    <div class="text-gray-500">{{ property.locality }}, {{ property.city }}</div>
                                </div>
                            </td>
                        </tr>

                        <!-- Price -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                Price
                            </td>
                            <td v-for="property in comparisonProperties" :key="property.id" class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm font-medium text-gray-900">₹{{ formatNumber(property.price) }}</div>
                                <div class="text-xs text-gray-500">{{ property.category }}</div>
                            </td>
                        </tr>

                        <!-- Property Type -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                Property Type
                            </td>
                            <td v-for="property in comparisonProperties" :key="property.id" class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-sky-100 text-sky-900">
                                    {{ property.property_type }}
                                </span>
                            </td>
                        </tr>

                        <!-- Bedrooms -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                Bedrooms
                            </td>
                            <td v-for="property in comparisonProperties" :key="property.id" class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center">
                                    <svg class="w-4 h-4 text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                                    </svg>
                                    {{ property.bedrooms || 'N/A' }}
                                </div>
                            </td>
                        </tr>

                        <!-- Bathrooms -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                Bathrooms
                            </td>
                            <td v-for="property in comparisonProperties" :key="property.id" class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center">
                                    <svg class="w-4 h-4 text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                                    </svg>
                                    {{ property.bathrooms || 'N/A' }}
                                </div>
                            </td>
                        </tr>

                        <!-- Area -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                Area
                            </td>
                            <td v-for="property in comparisonProperties" :key="property.id" class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm text-gray-900">
                                    {{ property.area_sqft ? formatNumber(property.area_sqft) + ' sq ft' : 'N/A' }}
                                </div>
                            </td>
                        </tr>

                        <!-- Furnished Status -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                Furnished Status
                            </td>
                            <td v-for="property in comparisonProperties" :key="property.id" class="px-6 py-4 whitespace-nowrap text-center">
                                <span :class="getFurnishedStatusClass(property.furnished_status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ formatFurnishedStatus(property.furnished_status) }}
                                </span>
                            </td>
                        </tr>

                        <!-- Security Deposit -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                Security Deposit
                            </td>
                            <td v-for="property in comparisonProperties" :key="property.id" class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm text-gray-900">
                                    {{ property.security_deposit ? '₹' + formatNumber(property.security_deposit) : 'N/A' }}
                                </div>
                            </td>
                        </tr>

                        <!-- Amenities -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                Amenities
                            </td>
                            <td v-for="property in comparisonProperties" :key="property.id" class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm">
                                    <div v-if="getPropertyAmenities(property).length > 0" class="space-y-1">
                                        <div v-for="amenity in getPropertyAmenities(property).slice(0, 3)" :key="amenity" class="text-gray-600">
                                            {{ amenity }}
                                        </div>
                                        <div v-if="getPropertyAmenities(property).length > 3" class="text-sky-800 text-xs">
                                            +{{ getPropertyAmenities(property).length - 3 }} more
                                        </div>
                                    </div>
                                    <div v-else class="text-gray-400">No amenities listed</div>
                                </div>
                            </td>
                        </tr>

                        <!-- Description -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                Description
                            </td>
                            <td v-for="property in comparisonProperties" :key="property.id" class="px-6 py-4 text-center">
                                <div class="text-sm text-gray-600 max-w-xs mx-auto">
                                    {{ property.short_description || property.long_description || 'No description available' }}
                                </div>
                            </td>
                        </tr>

                        <!-- Actions -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                Actions
                            </td>
                            <td v-for="property in comparisonProperties" :key="property.id" class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex flex-col space-y-2">
                                    <button @click="viewProperty(property)" class="px-3 py-1 bg-sky-800 text-white text-xs rounded hover:bg-sky-900">
                                        View Details
                                    </button>
                                    <button @click="contactOwner(property)" class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                                        Contact Owner
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-lg shadow-lg p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Properties to Compare</h3>
            <p class="text-gray-600 mb-4">Add properties to your comparison list to see them side by side</p>
            <button @click="$emit('browse-properties')" class="px-4 py-2 bg-sky-800 text-white rounded-md hover:bg-sky-900">
                Browse Properties
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    properties: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['property-removed', 'property-viewed', 'contact-owner', 'browse-properties'])

const comparisonProperties = ref([...props.properties])

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

const getPropertyAmenities = (property) => {
    if (property.amenities) {
        if (Array.isArray(property.amenities)) {
            return property.amenities
        }
        if (typeof property.amenities === 'string') {
            try {
                return JSON.parse(property.amenities)
            } catch {
                return []
            }
        }
    }
    return []
}

const formatFurnishedStatus = (status) => {
    if (!status) return 'N/A'
    return status.split('_').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ')
}

const getFurnishedStatusClass = (status) => {
    switch (status) {
        case 'furnished':
            return 'bg-green-100 text-green-800'
        case 'semi_furnished':
            return 'bg-yellow-100 text-yellow-800'
        case 'unfurnished':
            return 'bg-gray-100 text-gray-800'
        default:
            return 'bg-gray-100 text-gray-800'
    }
}

const formatNumber = (number) => {
    return new Intl.NumberFormat('en-IN').format(number)
}

const removeFromComparison = (propertyId) => {
    const index = comparisonProperties.value.findIndex(p => p.id === propertyId)
    if (index > -1) {
        comparisonProperties.value.splice(index, 1)
        emit('property-removed', propertyId)
    }
}

const clearComparison = () => {
    comparisonProperties.value = []
    emit('property-removed', 'all')
}

const viewProperty = (property) => {
    emit('property-viewed', property)
}

const contactOwner = (property) => {
    emit('contact-owner', property)
}

const exportComparison = () => {
    // Create a CSV export of the comparison
    const headers = [
        'Property', 'Title', 'Location', 'Price', 'Type', 'Bedrooms', 
        'Bathrooms', 'Area', 'Furnished Status', 'Security Deposit', 'Amenities'
    ]
    
    const rows = comparisonProperties.value.map(property => [
        property.title,
        property.title,
        `${property.locality}, ${property.city}`,
        property.price,
        property.property_type,
        property.bedrooms || 'N/A',
        property.bathrooms || 'N/A',
        property.area_sqft || 'N/A',
        formatFurnishedStatus(property.furnished_status),
        property.security_deposit || 'N/A',
        getPropertyAmenities(property).join(', ')
    ])
    
    const csvContent = [headers, ...rows]
        .map(row => row.map(field => `"${field}"`).join(','))
        .join('\n')
    
    const blob = new Blob([csvContent], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = 'property-comparison.csv'
    link.click()
    window.URL.revokeObjectURL(url)
}
</script>
