<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Frontend Header for logged in users -->
        <FrontendHeader v-if="$page.props.auth?.user" :current-path="$page.url" />
        
        <!-- Public Header for non-logged in users -->
        <header v-else class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <a href="/" class="text-2xl font-bold text-sky-800">SaleMitra</a>
                        <nav class="ml-10 flex items-baseline space-x-4">
                            <a href="/" class="nav-link">Home</a>
                            <a href="/marketplace" class="nav-link active">Properties</a>
                            <a href="/about" class="nav-link">About</a>
                            <a href="/contact" class="nav-link">Contact</a>
                        </nav>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="/login" class="text-gray-600 hover:text-gray-900">Login</a>
                        <a href="/register" class="btn btn-primary">Sign Up</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Search Bar -->
        <div class="bg-white border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="md:col-span-2">
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Search by location, property name..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-800"
                        />
                    </div>
                    <div>
                        <select v-model="filters.city" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-800">
                            <option value="">All Cities</option>
                            <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
                        </select>
                    </div>
                    <div>
                        <select v-model="filters.property_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-800">
                            <option value="">All Types</option>
                            <option v-for="type in propertyTypes" :key="type" :value="type">{{ type }}</option>
                        </select>
                    </div>
                    <div>
                        <select v-model="filters.transaction_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-800">
                            <option value="">All</option>
                            <option value="rent">Rent</option>
                            <option value="sale">Sale</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex flex-col sm:flex-row gap-3 items-start">
                    <button @click="applyFilters" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Search Properties
                    </button>
                    <button @click="showAdvancedFilters = !showAdvancedFilters" class="btn btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                        </svg>
                        {{ showAdvancedFilters ? 'Hide' : 'Show' }} Advanced Filters
                    </button>
                </div>

                <!-- Advanced Filters -->
                <div v-if="showAdvancedFilters" class="mt-6 p-6 bg-gray-50 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
                            <div class="flex space-x-2">
                                <input
                                    v-model="filters.min_price"
                                    type="number"
                                    placeholder="Min"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800"
                                />
                                <input
                                    v-model="filters.max_price"
                                    type="number"
                                    placeholder="Max"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bedrooms</label>
                            <select v-model="filters.min_bedrooms" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                                <option value="">Any</option>
                                <option value="1">1+</option>
                                <option value="2">2+</option>
                                <option value="3">3+</option>
                                <option value="4">4+</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bathrooms</label>
                            <select v-model="filters.min_bathrooms" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                                <option value="">Any</option>
                                <option value="1">1+</option>
                                <option value="2">2+</option>
                                <option value="3">3+</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Furnished</label>
                            <select v-model="filters.furnished_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                                <option value="">Any</option>
                                <option value="furnished">Furnished</option>
                                <option value="semi_furnished">Semi-Furnished</option>
                                <option value="unfurnished">Unfurnished</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Properties</h1>
                    <p class="text-gray-600">{{ properties.length }} properties found</p>
                </div>
                <div class="flex items-center space-x-4">
                    <select v-model="sortBy" @change="applyFilters" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-800">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="price_low_to_high">Price: Low to High</option>
                        <option value="price_high_to_low">Price: High to Low</option>
                    </select>
                    <div class="flex border border-gray-300 rounded-lg">
                        <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-sky-800 text-white' : 'bg-white text-gray-600'" class="px-4 py-2 rounded-l-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                            </svg>
                        </button>
                        <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-sky-800 text-white' : 'bg-white text-gray-600'" class="px-4 py-2 rounded-r-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Properties Grid/List -->
            <div v-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="property in properties" :key="property.id" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="relative h-48 bg-gray-200 flex items-center justify-center overflow-hidden">
                        <img v-if="getPropertyImage(property)" 
                             :src="getPropertyImage(property)" 
                             :alt="property.title"
                             class="w-full h-full object-cover">
                        <svg v-else class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        
                        <!-- Favorite Button (only show for logged in users) -->
                        <div v-if="$page.props.auth?.user" class="absolute top-3 right-3">
                            <FavoriteButton :property-id="property.id" />
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-lg font-semibold text-gray-900">{{ property.title }}</h3>
                            <span v-if="property.featured" class="bg-sky-100 text-sky-900 text-xs font-semibold px-2 py-1 rounded-full">Featured</span>
                        </div>
                        <p class="text-gray-600 text-sm mb-2">{{ property.locality }}, {{ property.city }}</p>
                        <p class="text-gray-700 text-sm mb-4">{{ property.short_description ? property.short_description.substring(0, 100) + '...' : 'No description available' }}</p>
                        <div class="flex justify-between items-center">
                            <div class="text-2xl font-bold text-sky-800">₹{{ formatPrice(property.price) }}</div>
                            <div class="text-sm text-gray-500">{{ property.property_type }}</div>
                        </div>
                        <div class="mt-4 flex justify-between text-sm text-gray-600">
                            <span>{{ property.bedrooms }} Beds</span>
                            <span>{{ property.bathrooms }} Baths</span>
                            <span>{{ property.area_sqft }} sq ft</span>
                        </div>
                        <div class="mt-4 flex space-x-2">
                            <button @click="viewProperty(property)" class="btn btn-primary flex-1">
                                View Details
                            </button>
                            <button @click="contactOwner(property)" class="btn btn-secondary flex-1">
                                Contact
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div v-else class="space-y-4">
                <div v-for="property in properties" :key="property.id" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="flex">
                        <div class="relative w-64 h-48 bg-gray-200 flex items-center justify-center overflow-hidden">
                            <img v-if="getPropertyImage(property)" 
                                 :src="getPropertyImage(property)" 
                                 :alt="property.title"
                                 class="w-full h-full object-cover">
                            <svg v-else class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            
                            <!-- Favorite Button (only show for logged in users) -->
                            <div v-if="$page.props.auth?.user" class="absolute top-2 right-2">
                                <FavoriteButton :property-id="property.id" />
                            </div>
                        </div>
                        <div class="flex-1 p-6">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-xl font-semibold text-gray-900">{{ property.title }}</h3>
                                <div class="flex items-center space-x-2">
                                    <span v-if="property.featured" class="bg-sky-100 text-sky-900 text-xs font-semibold px-2 py-1 rounded-full">Featured</span>
                                    <div class="text-2xl font-bold text-sky-800">₹{{ formatPrice(property.price) }}</div>
                                </div>
                            </div>
                            <p class="text-gray-600 mb-2">{{ property.locality }}, {{ property.city }}</p>
                            <p class="text-gray-700 mb-4">{{ property.short_description || 'No description available' }}</p>
                            <div class="flex justify-between items-center">
                                <div class="flex space-x-6 text-sm text-gray-600">
                                    <span>{{ property.bedrooms }} Beds</span>
                                    <span>{{ property.bathrooms }} Baths</span>
                                    <span>{{ property.area_sqft }} sq ft</span>
                                    <span>{{ property.property_type }}</span>
                                </div>
                                <div class="flex space-x-2">
                                    <button @click="viewProperty(property)" class="btn btn-primary">
                                        View Details
                                    </button>
                                    <button @click="contactOwner(property)" class="btn btn-secondary">
                                        Contact
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="properties.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No properties found</h3>
                <p class="mt-1 text-sm text-gray-500">Try adjusting your search criteria.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import FrontendHeader from '../../Components/FrontendHeader.vue'
import FavoriteButton from '../../Components/FavoriteButton.vue'

const properties = ref([])
const cities = ref([])
const propertyTypes = ref([])
const loading = ref(false)
const showAdvancedFilters = ref(false)
const viewMode = ref('grid')
const sortBy = ref('newest')

const filters = ref({
    search: '',
    city: '',
    property_type: '',
    transaction_type: '',
    min_price: '',
    max_price: '',
    min_bedrooms: '',
    min_bathrooms: '',
    furnished_status: ''
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-IN').format(price)
}

const getPropertyImage = (property) => {
    if (property.images && property.images.length > 0) {
        const firstImage = property.images[0]
        if (firstImage.urls && firstImage.urls.medium) {
            return firstImage.urls.medium
        }
        if (firstImage.original) {
            return `/storage/${firstImage.original}`
        }
    }
    return null
}

const loadProperties = async () => {
    loading.value = true
    try {
        const params = new URLSearchParams()
        Object.keys(filters.value).forEach(key => {
            if (filters.value[key]) {
                params.append(key, filters.value[key])
            }
        })
        params.append('sort_by', sortBy.value)
        
        const response = await fetch(`/api/v1/search/properties?${params.toString()}`)
        const data = await response.json()
        if (data.success) {
            properties.value = data.data.data
        }
    } catch (error) {
        console.error('Error loading properties:', error)
    } finally {
        loading.value = false
    }
}

const loadCities = async () => {
    try {
        const response = await fetch('/api/v1/search/cities')
        const data = await response.json()
        if (data.success) {
            cities.value = data.data
        }
    } catch (error) {
        console.error('Error loading cities:', error)
    }
}

const loadPropertyTypes = async () => {
    try {
        const response = await fetch('/api/v1/search/property-types')
        const data = await response.json()
        if (data.success) {
            propertyTypes.value = data.data
        }
    } catch (error) {
        console.error('Error loading property types:', error)
    }
}

const applyFilters = () => {
    loadProperties()
}

const viewProperty = (property) => {
    window.location.href = `/marketplace/properties/${property.id}`
}

const contactOwner = (property) => {
    // Open contact modal or redirect to contact form
    const contactUrl = `/marketplace/properties/${property.id}/contact`
    window.location.href = contactUrl
}

onMounted(() => {
    loadProperties()
    loadCities()
    loadPropertyTypes()
})
</script>
