<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Navigation -->
        <nav class="glass sticky top-0 z-50 backdrop-blur-md">
            <div class="container-mobile">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <h1 class="text-2xl font-bold bg-gradient-to-r from-sky-800 to-sky-900 bg-clip-text text-transparent">
                                SaleMitra
                            </h1>
                        </div>
                        <div class="hidden lg:ml-10 lg:flex lg:items-baseline lg:space-x-4">
                            <a href="/user/dashboard" class="nav-link active">Dashboard</a>
                            <a href="/marketplace" class="nav-link">Browse Properties</a>
                            <a href="/user/favorites" class="nav-link">Favorites</a>
                            <a href="/user/profile" class="nav-link">Profile</a>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="p-2 text-gray-400 hover:text-gray-600 transition-colors rounded-lg hover:bg-gray-100">
                                <span class="sr-only">View notifications</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.828 7l2.586 2.586a2 2 0 002.828 0L12.828 7H4.828z" />
                                </svg>
                            </button>
                        </div>
                        <!-- User Profile -->
                        <div class="flex items-center space-x-3">
                            <div class="hidden sm:block text-sm">
                                <p class="text-gray-700 font-medium">{{ $page.props.auth?.user?.name || 'User' }}</p>
                                <p class="text-gray-500 text-xs">Frontend User</p>
                            </div>
                            <ImagePlaceholder 
                                :src="$page.props.auth?.user?.avatar_url"
                                :alt="($page.props.auth?.user?.name || 'User') + ' Profile'"
                                size="sm"
                                shape="circle"
                                :placeholder-text="($page.props.auth?.user?.name || 'User').charAt(0)"
                                className="border-2 border-white shadow-soft"
                            />
                            <button @click="logout" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">Logout</button>
                        </div>
                        <!-- Mobile Menu Button -->
                        <button 
                            @click="toggleMobileMenu"
                            class="lg:hidden p-2 text-gray-600 hover:text-gray-900 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
                <!-- Mobile Menu -->
                <div v-if="showMobileMenu" class="lg:hidden py-4 border-t border-gray-200">
                    <div class="flex flex-col space-y-2">
                        <a href="/user/dashboard" class="nav-link active">Dashboard</a>
                        <a href="/marketplace" class="nav-link">Browse Properties</a>
                        <a href="/user/favorites" class="nav-link">Favorites</a>
                        <a href="/user/profile" class="nav-link">Profile</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="container-mobile py-6">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="card">
                    <div class="card-body">
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Welcome back, {{ $page.props.auth?.user?.name || 'User' }}!</h1>
                        <p class="text-gray-600">Discover and manage your property interests.</p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="grid-responsive mb-8">
                <ModernCard className="text-center">
                        <div class="flex items-center justify-center w-12 h-12 bg-sky-100 rounded-xl mx-auto mb-4">
                            <svg class="w-6 h-6 text-sky-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-sky-800 mb-2">{{ loading ? '...' : stats.favorites }}</div>
                    <div class="text-sm text-gray-600">Favorite Properties</div>
                </ModernCard>
                
                <ModernCard className="text-center">
                    <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-xl mx-auto mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-green-600 mb-2">{{ loading ? '...' : stats.inquiries }}</div>
                    <div class="text-sm text-gray-600">Property Inquiries</div>
                </ModernCard>
                
                <ModernCard className="text-center">
                    <div class="flex items-center justify-center w-12 h-12 bg-yellow-100 rounded-xl mx-auto mb-4">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-yellow-600 mb-2">{{ loading ? '...' : stats.saved_searches }}</div>
                    <div class="text-sm text-gray-600">Saved Searches</div>
                </ModernCard>
                
                <ModernCard className="text-center">
                    <div class="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-xl mx-auto mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-purple-600 mb-2">{{ loading ? '...' : stats.recent_views }}</div>
                    <div class="text-sm text-gray-600">Recent Views</div>
                </ModernCard>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Browse Properties -->
                <ModernCard>
                    <div class="card-body">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 bg-sky-100 rounded-lg flex items-center justify-center">
                                    <svg class="h-6 w-6 text-sky-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">Browse Properties</h3>
                                <p class="text-sm text-gray-600">Find your perfect property</p>
                            </div>
                        </div>
                        <p class="text-gray-600 mb-4">Discover thousands of properties available for rent, sale, or PG accommodation.</p>
                        <a href="/marketplace" class="btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Start Browsing
                        </a>
                    </div>
                </ModernCard>

                <!-- Saved Searches -->
                <ModernCard>
                    <div class="card-body">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">Saved Searches</h3>
                                <p class="text-sm text-gray-600">Get notified of new matches</p>
                            </div>
                        </div>
                        <p class="text-gray-600 mb-4">Save your search criteria and get notified when new properties match your preferences.</p>
                        <a href="/user/saved-searches" class="btn-secondary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                            </svg>
                            Manage Searches
                        </a>
                    </div>
                </ModernCard>
            </div>

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Favorites -->
                <ModernCard>
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Favorites</h3>
                    </div>
                    <div class="card-body">
                        <div v-if="loading" class="space-y-4">
                            <div v-for="i in 3" :key="i" class="flex items-center space-x-4">
                                <div class="skeleton w-16 h-16 rounded-lg"></div>
                                <div class="flex-1">
                                    <div class="skeleton-text w-3/4 mb-2"></div>
                                    <div class="skeleton-text w-1/2"></div>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="recentFavorites.length === 0" class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No favorites yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Start browsing properties to add them to your favorites.</p>
                            <div class="mt-6">
                                <a href="/marketplace" class="btn-primary">Browse Properties</a>
                            </div>
                        </div>
                        <div v-else class="space-y-4">
                            <div v-for="favorite in recentFavorites" :key="favorite.id" class="flex items-center space-x-4">
                                <ImagePlaceholder 
                                    :src="favorite.property?.images?.[0]"
                                    :alt="favorite.property?.title"
                                    size="md"
                                    shape="rounded"
                                    className="flex-shrink-0"
                                />
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 truncate">{{ favorite.property?.title }}</h4>
                                    <p class="text-sm text-gray-500">{{ favorite.property?.locality }}, {{ favorite.property?.city }}</p>
                                    <p class="text-sm font-semibold text-sky-800">₹{{ formatPrice(favorite.property?.price) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </ModernCard>

                <!-- Recent Inquiries -->
                <ModernCard>
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Inquiries</h3>
                    </div>
                    <div class="card-body">
                        <div v-if="loading" class="space-y-4">
                            <div v-for="i in 3" :key="i" class="flex items-center space-x-4">
                                <div class="skeleton w-16 h-16 rounded-lg"></div>
                                <div class="flex-1">
                                    <div class="skeleton-text w-3/4 mb-2"></div>
                                    <div class="skeleton-text w-1/2"></div>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="recentInquiries.length === 0" class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No inquiries yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Contact property owners to start your inquiries.</p>
                            <div class="mt-6">
                                <a href="/marketplace" class="btn-primary">Browse Properties</a>
                            </div>
                        </div>
                        <div v-else class="space-y-4">
                            <div v-for="inquiry in recentInquiries" :key="inquiry.id" class="flex items-center space-x-4">
                                <ImagePlaceholder 
                                    :src="inquiry.property?.images?.[0]"
                                    :alt="inquiry.property?.title"
                                    size="md"
                                    shape="rounded"
                                    className="flex-shrink-0"
                                />
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 truncate">{{ inquiry.property?.title }}</h4>
                                    <p class="text-sm text-gray-500">{{ inquiry.property?.locality }}, {{ inquiry.property?.city }}</p>
                                    <p class="text-sm text-gray-600">{{ inquiry.message }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </ModernCard>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import ImagePlaceholder from '../../Components/ImagePlaceholder.vue'
import ModernCard from '../../Components/ModernCard.vue'
import axios from 'axios'

const loading = ref(true)
const showMobileMenu = ref(false)
const stats = ref({
    favorites: 0,
    inquiries: 0,
    saved_searches: 0,
    recent_views: 0
})
const recentFavorites = ref([])
const recentInquiries = ref([])

const toggleMobileMenu = () => {
    showMobileMenu.value = !showMobileMenu.value
}

const logout = async () => {
    try {
        await axios.post('/user/logout')
        router.visit('/')
    } catch (error) {
        console.error('Logout error:', error)
        router.visit('/')
    }
}

const formatPrice = (price) => {
    if (!price) return '0'
    return new Intl.NumberFormat('en-IN').format(price)
}

const loadDashboardData = async () => {
    try {
        // Load user dashboard data
        const response = await axios.get('/api/v1/user/dashboard')
        if (response.data.success) {
            stats.value = response.data.data.stats
            recentFavorites.value = response.data.data.recent_favorites || []
            recentInquiries.value = response.data.data.recent_inquiries || []
        }
    } catch (error) {
        console.error('Error loading dashboard data:', error)
        // Set default values for demo
        stats.value = {
            favorites: 3,
            inquiries: 2,
            saved_searches: 1,
            recent_views: 12
        }
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    loadDashboardData()
})
</script>
