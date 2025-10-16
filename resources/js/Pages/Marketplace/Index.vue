<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-sky-800">SaleMitra</h1>
                        <nav class="ml-10 flex items-baseline space-x-4">
                            <a href="/" class="nav-link active">Home</a>
                            <a href="/marketplace" class="nav-link">Properties</a>
                            <a href="/about" class="nav-link">About</a>
                            <a href="/contact" class="nav-link">Contact</a>
                        </nav>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="/user/login" class="text-gray-600 hover:text-gray-900">Login</a>
                        <div class="relative group">
                            <a href="/user/register" class="btn-primary">Sign Up</a>
                            <!-- Dropdown Menu -->
                            <div class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <div class="p-4">
                                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Choose Account Type</h3>
                                    <div class="mb-3">
                                        <a href="/choose-account-type" class="text-xs text-sky-800 hover:text-sky-700 underline">
                                            Not sure? Compare account types →
                                        </a>
                                    </div>
                                    <div class="space-y-3">
                                        <a href="/user/register" class="block p-3 rounded-lg border border-gray-200 hover:border-sky-400 hover:bg-sky-50 transition-colors">
                                            <div class="flex items-start space-x-3">
                                                <div class="flex-shrink-0">
                                                    <div class="w-8 h-8 bg-sky-100 rounded-lg flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-sky-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-medium text-gray-900">Individual User</h4>
                                                    <p class="text-xs text-gray-600">Find and rent properties</p>
                                                </div>
                                            </div>
                                        </a>
                                        <a href="/register" class="block p-3 rounded-lg border border-gray-200 hover:border-red-300 hover:bg-red-50 transition-colors">
                                            <div class="flex items-start space-x-3">
                                                <div class="flex-shrink-0">
                                                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-medium text-gray-900">Organization</h4>
                                                    <p class="text-xs text-gray-600">Manage properties & tenants</p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="bg-gradient-to-r from-sky-800 to-sky-900 text-white py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">
                    Find Your Perfect Property
                </h1>
                <p class="text-xl md:text-2xl mb-8 text-sky-100">
                    Discover thousands of properties for rent and sale across India
                </p>
                
                <!-- Search Bar -->
                <div class="max-w-4xl mx-auto">
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                                <div class="relative">
                                    <input
                                        v-model="searchForm.location"
                                        type="text"
                                        placeholder="Enter city, locality, or landmark"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-800"
                                        @input="getSuggestions"
                                    />
                                    <div v-if="suggestions.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg">
                                        <div v-for="suggestion in suggestions" :key="suggestion.value" 
                                             @click="selectSuggestion(suggestion)"
                                             class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0">
                                            <span class="text-sm text-gray-600">{{ suggestion.type }}:</span>
                                            <span class="ml-2">{{ suggestion.text }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Property Type</label>
                                <select v-model="searchForm.property_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-800">
                                    <option value="">All Types</option>
                                    <option value="apartment">Apartment</option>
                                    <option value="house">House</option>
                                    <option value="villa">Villa</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="plot">Plot</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Transaction</label>
                                <select v-model="searchForm.transaction_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-800">
                                    <option value="">All</option>
                                    <option value="rent">Rent</option>
                                    <option value="sale">Sale</option>
                                </select>
                            </div>
                        </div>
                        <button @click="searchProperties" class="mt-4 btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Search Properties
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics -->
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                    <div>
                        <div class="text-3xl font-bold text-sky-800">{{ stats.total_properties }}</div>
                        <div class="text-gray-600">Properties</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-sky-800">{{ stats.total_cities }}</div>
                        <div class="text-gray-600">Cities</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-sky-800">{{ stats.rent_properties }}</div>
                        <div class="text-gray-600">For Rent</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-sky-800">{{ stats.sale_properties }}</div>
                        <div class="text-gray-600">For Sale</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Properties -->
        <section class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Featured Properties</h2>
                    <p class="text-gray-600">Handpicked properties just for you</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="property in featuredProperties" :key="property.id" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="h-48 bg-gray-200 flex items-center justify-center overflow-hidden">
                            <img v-if="getPropertyImage(property)" 
                                 :src="getPropertyImage(property)" 
                                 :alt="property.title"
                                 class="w-full h-full object-cover">
                            <svg v-else class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-lg font-semibold text-gray-900">{{ property.title }}</h3>
                                <span class="bg-sky-100 text-sky-800 text-xs font-semibold px-2 py-1 rounded-full">Featured</span>
                            </div>
                            <p class="text-gray-600 text-sm mb-2">{{ property.locality }}, {{ property.city }}</p>
                            <p class="text-gray-700 text-sm mb-4">{{ property.description.substring(0, 100) }}...</p>
                            <div class="flex justify-between items-center">
                                <div class="text-2xl font-bold text-sky-800">₹{{ formatPrice(property.price) }}</div>
                                <div class="text-sm text-gray-500">{{ property.transaction_type }}</div>
                            </div>
                            <div class="mt-4 flex justify-between text-sm text-gray-600">
                                <span>{{ property.bedrooms }} Beds</span>
                                <span>{{ property.bathrooms }} Baths</span>
                                <span>{{ property.area }} sq ft</span>
                            </div>
                            <button @click="viewProperty(property)" class="mt-4 btn-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Recent Properties -->
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Latest Properties</h2>
                    <p class="text-gray-600">Recently added properties</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="property in recentProperties" :key="property.id" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="h-40 bg-gray-200 flex items-center justify-center overflow-hidden">
                            <img v-if="getPropertyImage(property)" 
                                 :src="getPropertyImage(property)" 
                                 :alt="property.title"
                                 class="w-full h-full object-cover">
                            <svg v-else class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-1">{{ property.title }}</h3>
                            <p class="text-gray-600 text-sm mb-2">{{ property.locality }}, {{ property.city }}</p>
                            <div class="flex justify-between items-center">
                                <div class="text-lg font-bold text-sky-800">₹{{ formatPrice(property.price) }}</div>
                                <div class="text-sm text-gray-500">{{ property.transaction_type }}</div>
                            </div>
                            <div class="mt-2 flex justify-between text-xs text-gray-600">
                                <span>{{ property.bedrooms }}B</span>
                                <span>{{ property.bathrooms }}B</span>
                                <span>{{ property.area }}sq ft</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-16 bg-sky-800 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold mb-4">Ready to Find Your Dream Property?</h2>
                <p class="text-xl mb-8 text-sky-100">Join thousands of satisfied customers who found their perfect home</p>
                <a href="/marketplace" class="bg-white text-sky-800 px-8 py-4 rounded-lg font-medium hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-white shadow-lg hover:shadow-xl flex items-center justify-center mx-auto w-fit">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Browse All Properties
                </a>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">SaleMitra</h3>
                        <p class="text-gray-400">Your trusted partner in finding the perfect property.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Quick Links</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="/marketplace" class="hover:text-white">Properties</a></li>
                            <li><a href="/about" class="hover:text-white">About Us</a></li>
                            <li><a href="/contact" class="hover:text-white">Contact</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Property Types</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="#" class="hover:text-white">Apartments</a></li>
                            <li><a href="#" class="hover:text-white">Houses</a></li>
                            <li><a href="#" class="hover:text-white">Commercial</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Contact Info</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li>Email: info@salemitra.com</li>
                            <li>Phone: +91 98765 43210</li>
                            <li>Address: Mumbai, India</li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                    <p>&copy; 2025 SaleMitra. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const featuredProperties = ref([])
const recentProperties = ref([])
const stats = ref({
    total_properties: 0,
    total_cities: 0,
    rent_properties: 0,
    sale_properties: 0
})

const suggestions = ref([])
const searchForm = ref({
    location: '',
    property_type: '',
    transaction_type: ''
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

const getSuggestions = async () => {
    if (searchForm.value.location.length < 2) {
        suggestions.value = []
        return
    }

    try {
        const response = await fetch(`http://127.0.0.1:8000/api/v1/search/suggestions?q=${encodeURIComponent(searchForm.value.location)}`)
        const data = await response.json()
        if (data.success) {
            suggestions.value = data.data
        }
    } catch (error) {
        console.error('Error getting suggestions:', error)
    }
}

const selectSuggestion = (suggestion) => {
    searchForm.value.location = suggestion.value
    suggestions.value = []
}

const searchProperties = () => {
    const params = new URLSearchParams()
    if (searchForm.value.location) params.append('search', searchForm.value.location)
    if (searchForm.value.property_type) params.append('property_type', searchForm.value.property_type)
    if (searchForm.value.transaction_type) params.append('transaction_type', searchForm.value.transaction_type)
    
    window.location.href = `/marketplace?${params.toString()}`
}

const viewProperty = (property) => {
    window.location.href = `/marketplace/properties/${property.id}`
}

const loadFeaturedProperties = async () => {
    try {
        const response = await fetch('http://127.0.0.1:8000/api/v1/search/featured-properties')
        const data = await response.json()
        if (data.success) {
            featuredProperties.value = data.data
        }
    } catch (error) {
        console.error('Error loading featured properties:', error)
    }
}

const loadRecentProperties = async () => {
    try {
        const response = await fetch('http://127.0.0.1:8000/api/v1/search/recent-properties')
        const data = await response.json()
        if (data.success) {
            recentProperties.value = data.data
        }
    } catch (error) {
        console.error('Error loading recent properties:', error)
    }
}

const loadStatistics = async () => {
    try {
        const response = await fetch('http://127.0.0.1:8000/api/v1/search/statistics')
        const data = await response.json()
        if (data.success) {
            stats.value = data.data
        }
    } catch (error) {
        console.error('Error loading statistics:', error)
    }
}

onMounted(() => {
    loadFeaturedProperties()
    loadRecentProperties()
    loadStatistics()
})
</script>
