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
                            <a href="/marketplace" class="nav-link">Properties</a>
                            <a href="/about" class="nav-link">About</a>
                            <a href="/contact" class="nav-link">Contact</a>
                        </nav>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="/login" class="text-gray-600 hover:text-gray-900">Login</a>
                        <a href="/register" class="btn-primary">Sign Up</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div v-if="loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-sky-800"></div>
                <p class="mt-2 text-gray-600">Loading property details...</p>
            </div>

            <div v-else-if="property" class="px-4 sm:px-0">
                <!-- Breadcrumb -->
                <nav class="flex mb-6" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="/" class="text-gray-700 hover:text-sky-800">Home</a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="/marketplace" class="ml-1 text-gray-700 hover:text-sky-800 md:ml-2">Properties</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-500 md:ml-2">{{ property.title }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Property Images -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <div v-if="property.images && property.images.length > 0" class="relative">
                                <img 
                                    :src="property.images[0]" 
                                    :alt="property.title"
                                    class="w-full h-96 object-cover"
                                />
                                <div v-if="property.featured" class="absolute top-4 left-4">
                                    <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                        Featured
                                    </span>
                                </div>
                                <div v-if="property.availability_status && property.availability_status !== 'vacant'" class="absolute top-4 right-4">
                                    <span class="bg-red-600 text-white px-3 py-1 rounded-full text-sm font-medium">
                                        Already Leased
                                    </span>
                                </div>
                            </div>
                            <div v-else class="w-full h-96 bg-gray-200 flex items-center justify-center">
                                <div class="text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <p>No images available</p>
                                </div>
                            </div>
                        </div>

                        <!-- Property Details -->
                        <div class="mt-8 bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Property Details</h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Basic Information</h3>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Property Type:</span>
                                            <span class="font-medium">{{ property.property_type }}</span>
                                        </div>
                                        <div v-if="property.bedrooms" class="flex justify-between">
                                            <span class="text-gray-600">Bedrooms:</span>
                                            <span class="font-medium">{{ property.bedrooms }}</span>
                                        </div>
                                        <div v-if="property.bathrooms" class="flex justify-between">
                                            <span class="text-gray-600">Bathrooms:</span>
                                            <span class="font-medium">{{ property.bathrooms }}</span>
                                        </div>
                                        <div v-if="property.area_sqft" class="flex justify-between">
                                            <span class="text-gray-600">Area:</span>
                                            <span class="font-medium">{{ property.area_sqft }} sq ft</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Furnished:</span>
                                            <span class="font-medium">{{ property.furnished_status?.replace('_', ' ') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Availability:</span>
                                            <span class="font-medium" :class="property.availability_status === 'vacant' ? 'text-green-700' : 'text-red-600'">
                                                {{ property.availability_status === 'vacant' ? 'Available' : 'Already Leased' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Location</h3>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">City:</span>
                                            <span class="font-medium">{{ property.city }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Locality:</span>
                                            <span class="font-medium">{{ property.locality }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Pincode:</span>
                                            <span class="font-medium">{{ property.pincode }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div v-if="property.long_description" class="mt-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Description</h3>
                                <p class="text-gray-700 leading-relaxed">{{ property.long_description }}</p>
                            </div>

                            <!-- Amenities -->
                            <div v-if="property.amenities && property.amenities.length > 0" class="mt-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Amenities</h3>
                                <div class="flex flex-wrap gap-2">
                                    <span 
                                        v-for="amenity in property.amenities" 
                                        :key="amenity"
                                        class="bg-sky-100 text-sky-900 px-3 py-1 rounded-full text-sm"
                                    >
                                        {{ amenity }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Card -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-sm p-6 sticky top-6">
                            <div class="text-center mb-6">
                                <h3 class="text-2xl font-bold text-gray-900">₹{{ property.price?.toLocaleString() }}</h3>
                                <p class="text-gray-600">per month</p>
                                <div v-if="property.security_deposit" class="mt-2">
                                    <p class="text-sm text-gray-500">Security Deposit: ₹{{ property.security_deposit?.toLocaleString() }}</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div v-if="property.availability_status && property.availability_status !== 'vacant'" class="w-full">
                                    <div class="bg-red-50 text-red-700 border border-red-200 px-4 py-3 rounded-md text-center font-medium">
                                        Already Leased
                                    </div>
                                </div>
                                <button 
                                    v-else
                                    @click="showLeadForm = true" 
                                    class="btn btn-primary w-full"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    Contact Owner
                                </button>

                                <button class="btn btn-secondary w-full">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                    Save Property
                                </button>

                                <button class="btn btn-secondary w-full">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                                    </svg>
                                    Share
                                </button>
                            </div>

                            <!-- Property Owner Info -->
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Listed by</h4>
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-sky-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-sky-800">
                                                {{ property.organization?.name?.charAt(0).toUpperCase() }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ property.organization?.name }}</p>
                                        <p class="text-sm text-gray-500">Property Owner</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.29-1.009-5.824-2.709M15 6.291A7.962 7.962 0 0012 5c-2.34 0-4.29 1.009-5.824 2.709M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Property not found</h3>
                <p class="mt-1 text-sm text-gray-500">The property you're looking for doesn't exist or has been removed.</p>
                <div class="mt-6">
                    <a href="/marketplace" class="btn-primary">Browse Properties</a>
                </div>
            </div>
        </div>

        <!-- Lead Form Modal -->
        <LeadForm 
            v-if="showLeadForm" 
            :show="showLeadForm" 
            :property="property"
            @close="showLeadForm = false"
            @success="onLeadSuccess"
            @error="onLeadError"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import FrontendHeader from '../../Components/FrontendHeader.vue'
import LeadForm from '../../Components/LeadForm.vue'

const page = usePage()
const route = page.props.route
const property = ref(null)
const loading = ref(true)
const showLeadForm = ref(false)

const loadProperty = async () => {
    try {
        const response = await axios.get(`/api/v1/properties/${page.props.id}`)
        
        if (response.data.success) {
            property.value = response.data.data
        }
    } catch (error) {
        console.error('Error loading property:', error)
    } finally {
        loading.value = false
    }
}

const onLeadSuccess = (lead) => {
    alert('Your inquiry has been sent successfully! The property owner will contact you soon.')
    showLeadForm.value = false
}

const onLeadError = (error) => {
    alert('Failed to send inquiry: ' + error)
}

onMounted(() => {
    loadProperty()
})
</script>
