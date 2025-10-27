<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Frontend Header -->
        <FrontendHeader :current-path="$page.url" />

        <!-- Main Content -->
        <div class="container-mobile py-6">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 mb-2">My Favorites</h2>
                        <p class="text-gray-600">Properties you've saved for later</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-sm text-gray-500">
                            {{ favorites.length }} {{ favorites.length === 1 ? 'property' : 'properties' }}
                        </div>
                        <button
                            @click="refreshFavorites"
                            :disabled="loading"
                            class="btn btn-secondary btn-sm"
                        >
                            <svg v-if="loading" class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading && favorites.length === 0" class="flex justify-center items-center py-12">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-sky-600 mx-auto mb-4"></div>
                    <p class="text-gray-600">Loading your favorites...</p>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!loading && favorites.length === 0" class="text-center py-12">
                <div class="max-w-md mx-auto">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No favorites yet</h3>
                    <p class="text-gray-600 mb-6">Start exploring properties and save your favorites to see them here.</p>
                    <a href="/marketplace" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Browse Properties
                    </a>
                </div>
            </div>

            <!-- Favorites Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="property in favorites"
                    :key="property.id"
                    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden"
                >
                    <!-- Property Image -->
                    <div class="relative h-48 bg-gray-200">
                        <img
                            v-if="property.media && property.media.length > 0"
                            :src="property.media[0].original_url"
                            :alt="property.title"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z" />
                            </svg>
                        </div>
                        
                        <!-- Favorite Button -->
                        <button
                            @click="toggleFavorite(property.id)"
                            :disabled="favoriteLoading[property.id]"
                            class="absolute top-3 right-3 p-2 bg-white rounded-full shadow-md hover:shadow-lg transition-shadow duration-200"
                        >
                            <svg v-if="favoriteLoading[property.id]" class="w-5 h-5 animate-spin text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <svg v-else class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                            </svg>
                        </button>

                        <!-- Property Type Badge -->
                        <div class="absolute top-3 left-3">
                            <span class="px-2 py-1 bg-sky-600 text-white text-xs font-medium rounded-full">
                                {{ property.property_type }}
                            </span>
                        </div>
                    </div>

                    <!-- Property Details -->
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="text-lg font-semibold text-gray-900 line-clamp-1">
                                {{ property.title }}
                            </h3>
                        </div>

                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                            {{ property.short_description }}
                        </p>

                        <div class="flex items-center text-sm text-gray-500 mb-3">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ property.city }}, {{ property.locality }}
                        </div>

                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-4 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                                    </svg>
                                    {{ property.bedrooms }} bed
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                    </svg>
                                    {{ property.bathrooms }} bath
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                    </svg>
                                    {{ property.area_sqft }} sqft
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="text-lg font-bold text-sky-600">
                                ₹{{ formatPrice(property.price) }}
                                <span class="text-sm font-normal text-gray-500">/month</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button
                                    @click="viewProperty(property.id)"
                                    class="btn btn-secondary btn-sm"
                                >
                                    View Details
                                </button>
                                <button
                                    @click="contactOwner(property)"
                                    class="btn btn-primary btn-sm"
                                >
                                    Contact
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Load More Button -->
            <div v-if="hasMorePages && !loading" class="text-center mt-8">
                <button
                    @click="loadMore"
                    class="btn btn-secondary"
                >
                    Load More Properties
                </button>
            </div>

            <!-- Loading More -->
            <div v-if="loadingMore" class="text-center mt-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-sky-600 mx-auto"></div>
                <p class="text-gray-600 mt-2">Loading more properties...</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import FrontendHeader from '../../Components/FrontendHeader.vue'
import axios from 'axios'

const loading = ref(true)
const loadingMore = ref(false)
const favorites = ref([])
const currentPage = ref(1)
const hasMorePages = ref(false)
const favoriteLoading = ref({})

const formatPrice = (price) => {
    if (!price) return '0'
    return new Intl.NumberFormat('en-IN').format(price)
}

const loadFavorites = async (page = 1, append = false) => {
    try {
        if (page === 1) {
            loading.value = true
        } else {
            loadingMore.value = true
        }

        const response = await axios.get(`/user/api/v1/user/favorites?page=${page}`)
        
        if (response.data.success) {
            const newFavorites = response.data.data.favorites
            
            if (append) {
                favorites.value = [...favorites.value, ...newFavorites]
            } else {
                favorites.value = newFavorites
            }
            
            currentPage.value = page
            hasMorePages.value = page < response.data.data.pagination.last_page
        }
    } catch (error) {
        console.error('Error loading favorites:', error)
        console.error('Error response:', error.response?.data)
        // Handle error - could show toast notification
    } finally {
        loading.value = false
        loadingMore.value = false
    }
}

const loadMore = () => {
    if (hasMorePages.value && !loadingMore.value) {
        loadFavorites(currentPage.value + 1, true)
    }
}

const refreshFavorites = () => {
    loadFavorites(1, false)
}

const toggleFavorite = async (propertyId) => {
    favoriteLoading.value[propertyId] = true
    
    try {
        const response = await axios.post('/user/api/v1/user/favorites/toggle', {
            property_id: propertyId
        })
        
        if (response.data.success) {
            // Remove from favorites list
            favorites.value = favorites.value.filter(property => property.id !== propertyId)
        }
    } catch (error) {
        console.error('Error toggling favorite:', error)
        // Handle error - could show toast notification
    } finally {
        favoriteLoading.value[propertyId] = false
    }
}

const viewProperty = (propertyId) => {
    router.visit(`/properties/${propertyId}`)
}

const contactOwner = (property) => {
    // Navigate to contact form or open modal
    router.visit(`/properties/${property.id}/contact`)
}

onMounted(() => {
    loadFavorites()
})
</script>

<style scoped>
.container-mobile {
    max-width: 80rem;
    margin: 0 auto;
    padding: 0 1rem;
}

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
