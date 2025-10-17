<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Navigation -->
        <Header current-path="/properties">
            <template #action-button>
                <button @click="showAddModal = true" class="btn btn-primary hidden sm:flex">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Property
                </button>
            </template>
            <template #mobile-action-button>
                <button @click="showAddModal = true" class="btn btn-primary w-full">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Property
                </button>
            </template>
        </Header>

        <!-- Main Content -->
        <div class="container-mobile py-6">
            <!-- Page Header -->
            <div class="mb-6">
                <div class="card">
                    <div class="card-body">
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Properties</h1>
                        <p class="mt-2 text-gray-600">Manage your property portfolio</p>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="card mb-6">
                <div class="card-body">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Search properties..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                            <select v-model="filters.city" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                                <option value="">All Cities</option>
                                <option value="Mumbai">Mumbai</option>
                                <option value="Delhi">Delhi</option>
                                <option value="Bangalore">Bangalore</option>
                                <option value="Chennai">Chennai</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                            <select v-model="filters.property_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                                <option value="">All Types</option>
                                <option value="rent">Rent</option>
                                <option value="sale">Sale</option>
                                <option value="pg">PG</option>
                                <option value="commercial">Commercial</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select v-model="filters.availability_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                                <option value="">All Status</option>
                                <option value="available">Available</option>
                                <option value="occupied">Occupied</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-between">
                        <button @click="applyFilters" class="btn btn-primary">Apply Filters</button>
                        <button @click="clearFilters" class="btn btn-secondary">Clear Filters</button>
                    </div>
                </div>
            </div>

            <!-- Properties Grid -->
            <div class="grid-responsive">
                <ModernCard v-for="property in props.properties" :key="property.id" className="overflow-hidden">
                    <!-- Property Image -->
                    <div class="relative h-48">
                        <ImagePlaceholder 
                            :src="property.images && property.images[0] ? property.images[0] : null"
                            :alt="property.title"
                            size="full"
                            shape="square"
                            placeholder-text="Property Image"
                            className="h-full"
                        />
                        <div class="absolute top-3 right-3">
                            <span v-if="property.is_published" class="badge-success">Published</span>
                            <span v-else class="badge-warning">Draft</span>
                        </div>
                    </div>

                    <!-- Property Details -->
                    <div class="card-body">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="text-lg font-semibold text-gray-900 line-clamp-1">{{ property.title }}</h3>
                            <span class="text-lg font-bold text-sky-800">₹{{ formatPrice(property.price) }}</span>
                        </div>
                        
                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ property.short_description }}</p>
                        
                        <div class="flex items-center text-sm text-gray-500 mb-3">
                            <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="truncate">{{ property.locality }}, {{ property.city }}</span>
                        </div>

                        <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                            <div class="flex items-center space-x-3">
                                <span v-if="property.bedrooms" class="badge-gray">{{ property.bedrooms }} BHK</span>
                                <span v-if="property.area_sqft" class="badge-gray">{{ property.area_sqft }} sq ft</span>
                            </div>
                            <span class="capitalize text-xs">{{ property.furnished_status }}</span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex space-x-2">
                            <button @click="viewProperty(property)" class="btn btn-secondary flex-1 text-sm">View</button>
                            <button @click="editProperty(property)" class="btn btn-primary flex-1 text-sm">Edit</button>
                            <button @click="deleteProperty(property)" class="px-3 py-2 text-red-600 hover:text-red-800 text-sm">Delete</button>
                        </div>
                    </div>
                </ModernCard>

                <!-- Empty State -->
                <div v-if="props.properties.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No properties</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new property.</p>
                    <div class="mt-6">
                        <button @click="showAddModal = true" class="btn btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Property
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Property Modal -->
        <div v-if="showAddModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Add New Property</h3>
                        <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <PropertyForm @saved="onPropertySaved" @cancelled="showAddModal = false" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import Header from '../../Components/Header.vue'
import PropertyForm from './PropertyForm.vue'
import ImagePlaceholder from '../../Components/ImagePlaceholder.vue'
import ModernCard from '../../Components/ModernCard.vue'

const props = defineProps({
    properties: {
        type: Array,
        default: () => []
    }
})

const loading = ref(false)
const showAddModal = ref(false)

const filters = ref({
    search: '',
    city: '',
    property_type: '',
    availability_status: ''
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-IN').format(price)
}

const applyFilters = () => {
    // Filters will be handled by the backend controller
    // For now, we'll just reload the page to apply filters
    window.location.reload()
}

const clearFilters = () => {
    filters.value = {
        search: '',
        city: '',
        property_type: '',
        availability_status: ''
    }
    loadProperties()
}

const viewProperty = (property) => {
    // Navigate to property detail page
    window.location.href = `/properties/${property.id}`
}

const editProperty = (property) => {
    // Navigate to property edit page
    window.location.href = `/properties/${property.id}/edit`
}

const deleteProperty = async (property) => {
    if (confirm('Are you sure you want to delete this property?')) {
        try {
            const response = await fetch(`/api/v1/properties/${property.id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Content-Type': 'application/json'
                }
            })
            
            if (response.ok) {
                properties.value = properties.value.filter(p => p.id !== property.id)
            }
        } catch (error) {
            console.error('Error deleting property:', error)
        }
    }
}

const onPropertySaved = () => {
    showAddModal.value = false
    // Reload the page to show the new property
    window.location.reload()
}

// Properties are now passed as props from the backend controller
</script>
