<template>
    <nav class="glass sticky top-0 z-50 backdrop-blur-md">
        <div class="container-mobile">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="/" class="text-2xl font-bold bg-gradient-to-r from-sky-800 to-sky-900 bg-clip-text text-transparent">
                            SaleMitra
                        </a>
                    </div>
                    <div class="hidden lg:ml-10 lg:flex lg:items-baseline lg:space-x-4">
                        <!-- Frontend Navigation - same for all users -->
                        <a href="/user/dashboard" :class="getNavLinkClass('/user/dashboard')">Dashboard</a>
                        <a href="/marketplace" :class="getNavLinkClass('/marketplace')">Browse Properties</a>
                        <a href="/user/favorites" :class="getNavLinkClass('/user/favorites')">Favorites</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Control Panel Menu -->
                    <a href="/dashboard" class="hidden sm:flex items-center px-3 py-2 text-sm font-medium text-sky-600 hover:text-sky-800 hover:bg-sky-50 border border-sky-200 hover:border-sky-300 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                        Control Panel
                    </a>
                    
                    <!-- Notifications Button -->
                    <div class="relative">
                        <button 
                            @click="toggleNotifications"
                            class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <!-- Notification Badge -->
                            <span v-if="notificationCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium">
                                {{ notificationCount > 9 ? '9+' : notificationCount }}
                            </span>
                        </button>
                        
                        <!-- Notifications Dropdown -->
                        <div v-if="showNotifications" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <h3 class="text-sm font-medium text-gray-900">Notifications</h3>
                            </div>
                            
                            <!-- Notification Items -->
                            <div class="max-h-64 overflow-y-auto">
                                <div v-if="notifications.length === 0" class="px-4 py-8 text-center text-gray-500">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    <p class="text-sm">No notifications</p>
                                </div>
                                
                                <div v-for="notification in notifications" :key="notification.id" class="px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-b-0">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0">
                                            <div :class="[
                                                'w-2 h-2 rounded-full mt-2',
                                                notification.type === 'success' ? 'bg-green-500' : 
                                                notification.type === 'warning' ? 'bg-yellow-500' : 
                                                notification.type === 'error' ? 'bg-red-500' : 'bg-blue-500'
                                            ]"></div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900">{{ notification.title }}</p>
                                            <p class="text-sm text-gray-500 mt-1">{{ notification.message }}</p>
                                            <p class="text-xs text-gray-400 mt-1">{{ notification.time }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="notifications.length > 0" class="px-4 py-2 border-t border-gray-100">
                                <a href="/user/notifications" class="text-sm text-sky-600 hover:text-sky-800 font-medium">View all notifications</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Profile Dropdown -->
                    <div class="relative">
                        <button 
                            @click="toggleUserMenu"
                            class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <div class="hidden sm:block text-sm text-right">
                                <p class="text-gray-700 font-medium">{{ page.props.auth?.user?.name || 'User' }}</p>
                                <p class="text-gray-500 text-xs">{{ userTypeLabel }}</p>
                            </div>
                            <ImagePlaceholder 
                                :src="page.props.auth?.user?.avatar_url"
                                :alt="(page.props.auth?.user?.name || 'User') + ' Profile'"
                                size="sm"
                                shape="circle"
                                :placeholder-text="(page.props.auth?.user?.name || 'User').charAt(0)"
                                className="border-2 border-white shadow-soft"
                            />
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <!-- User Dropdown Menu -->
                        <div v-if="showUserMenu" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-900">{{ page.props.auth?.user?.name || 'User' }}</p>
                                <p class="text-sm text-gray-500">{{ page.props.auth?.user?.email || 'user@example.com' }}</p>
                            </div>
                            
                            <!-- Unified Menu Items for both user types -->
                            <a href="/user/profile" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Profile Settings
                            </a>
                            
                            <a href="/user/favorites" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                                My Favorites
                            </a>
                            
                            <a href="/user/inquiries" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                My Inquiries
                            </a>
                            
                            <div class="border-t border-gray-100 my-1"></div>
                            
                            <a href="/help" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Help & Support
                            </a>
                            
                            <div class="border-t border-gray-100 my-1"></div>
                            
                            <button @click="logout" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Sign Out
                            </button>
                        </div>
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
                <div class="flex flex-col space-y-2 mb-4">
                    <!-- Frontend Navigation - same for all users -->
                    <a href="/user/dashboard" :class="getMobileNavLinkClass('/user/dashboard')">Dashboard</a>
                    <a href="/marketplace" :class="getMobileNavLinkClass('/marketplace')">Browse Properties</a>
                    <a href="/user/favorites" :class="getMobileNavLinkClass('/user/favorites')">Favorites</a>
                    <a href="/dashboard" :class="getMobileNavLinkClass('/dashboard')">Control Panel</a>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import ImagePlaceholder from './ImagePlaceholder.vue'

const props = defineProps({
    currentPath: {
        type: String,
        default: ''
    }
})

const page = usePage()

const showMobileMenu = ref(false)
const showUserMenu = ref(false)
const showNotifications = ref(false)

// Sample notifications data - in real app, this would come from API
const notifications = ref([
    {
        id: 1,
        title: 'New property match',
        message: 'A property matching your criteria has been added',
        type: 'info',
        time: '2 minutes ago'
    },
    {
        id: 2,
        title: 'Inquiry response',
        message: 'Property owner responded to your inquiry',
        type: 'success',
        time: '1 hour ago'
    },
    {
        id: 3,
        title: 'Price drop alert',
        message: 'A property in your favorites has reduced price',
        type: 'warning',
        time: '3 hours ago'
    }
])

const notificationCount = computed(() => notifications.value.length)

// User type detection
const isFrontendUser = computed(() => {
    return page.props.auth?.user?.user_type === 'frontend'
})

const userTypeLabel = computed(() => {
    const userType = page.props.auth?.user?.user_type
    if (userType === 'frontend') return 'Frontend User'
    if (userType === 'organization') return 'Organization User'
    return 'User'
})

const toggleMobileMenu = () => {
    showMobileMenu.value = !showMobileMenu.value
}

const toggleUserMenu = () => {
    showUserMenu.value = !showUserMenu.value
    showNotifications.value = false // Close notifications when opening user menu
}

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value
    showUserMenu.value = false // Close user menu when opening notifications
}

// Close menus when clicking outside
const closeMenus = (event) => {
    if (!event.target.closest('.relative')) {
        showUserMenu.value = false
        showNotifications.value = false
    }
}

// Add event listener for clicking outside
onMounted(() => {
    document.addEventListener('click', closeMenus)
})

onUnmounted(() => {
    document.removeEventListener('click', closeMenus)
})

const getNavLinkClass = (path) => {
    const baseClass = 'px-3 py-2 rounded-md text-sm font-medium transition-colors'
    const activeClass = 'text-sky-600 bg-sky-50'
    const inactiveClass = 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
    
    return `${baseClass} ${isActivePath(path) ? activeClass : inactiveClass}`
}

const getMobileNavLinkClass = (path) => {
    const baseClass = 'block px-3 py-2 rounded-md text-base font-medium transition-colors'
    const activeClass = 'text-sky-600 bg-sky-50'
    const inactiveClass = 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
    
    return `${baseClass} ${isActivePath(path) ? activeClass : inactiveClass}`
}

const isActivePath = (path) => {
    if (!props.currentPath) return false
    
    // Handle exact matches and sub-paths
    if (path === '/user/dashboard') {
        return props.currentPath === '/user/dashboard'
    }
    
    // For other paths, check if current path starts with the path
    return props.currentPath.startsWith(path)
}

const logout = () => {
    router.post('/user/logout', {}, {
        onSuccess: () => {
            router.visit('/')
        }
    })
}
</script>

<style scoped>
.container-mobile {
    max-width: 80rem;
    margin: 0 auto;
    padding: 0 1rem;
}

.glass {
    background-color: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid #e5e7eb;
}

.shadow-soft {
    box-shadow: 0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04);
}
</style>
