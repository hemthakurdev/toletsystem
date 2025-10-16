<template>
    <div class="advanced-search-filters bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Advanced Search Filters</h3>
            <button @click="toggleFilters" class="text-sky-800 hover:text-sky-900">
                {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
            </button>
        </div>

        <div v-if="showFilters" class="space-y-6">
            <!-- Location Filters -->
            <div class="border-b border-gray-200 pb-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Location</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <select v-model="filters.city" @change="loadLocalities" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                            <option value="">Select City</option>
                            <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Locality</label>
                        <select v-model="filters.locality" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                            <option value="">Select Locality</option>
                            <option v-for="locality in localities" :key="locality" :value="locality">{{ locality }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pincode</label>
                        <input v-model="filters.pincode" type="text" placeholder="Enter pincode" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                    </div>
                </div>
            </div>

            <!-- Property Details -->
            <div class="border-b border-gray-200 pb-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Property Details</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Property Type</label>
                        <select v-model="filters.property_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                            <option value="">Any Type</option>
                            <option v-for="type in propertyTypes" :key="type" :value="type">{{ type }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select v-model="filters.category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                            <option value="">Any Category</option>
                            <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Furnished Status</label>
                        <select v-model="filters.furnished_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                            <option value="">Any</option>
                            <option value="furnished">Furnished</option>
                            <option value="semi_furnished">Semi-Furnished</option>
                            <option value="unfurnished">Unfurnished</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Availability</label>
                        <select v-model="filters.available_from" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                            <option value="">Any Time</option>
                            <option value="immediate">Immediate</option>
                            <option value="1_month">Within 1 Month</option>
                            <option value="3_months">Within 3 Months</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Price Range -->
            <div class="border-b border-gray-200 pb-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Price Range</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Min Price (₹)</label>
                        <input v-model="filters.min_price" type="number" placeholder="Min price" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Price (₹)</label>
                        <input v-model="filters.max_price" type="number" placeholder="Max price" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>₹{{ formatNumber(priceRange.min_price || 0) }}</span>
                        <span>₹{{ formatNumber(priceRange.max_price || 0) }}</span>
                    </div>
                    <input
                        v-model="priceSlider"
                        type="range"
                        :min="priceRange.min_price || 0"
                        :max="priceRange.max_price || 100000"
                        step="1000"
                        class="w-full mt-2"
                        @input="updatePriceFromSlider"
                    />
                </div>
            </div>

            <!-- Area Range -->
            <div class="border-b border-gray-200 pb-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Area Range</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Min Area (sq ft)</label>
                        <input v-model="filters.min_area" type="number" placeholder="Min area" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Area (sq ft)</label>
                        <input v-model="filters.max_area" type="number" placeholder="Max area" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                    </div>
                </div>
            </div>

            <!-- Bedrooms & Bathrooms -->
            <div class="border-b border-gray-200 pb-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Bedrooms & Bathrooms</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bedrooms</label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="bedroom in bedroomOptions"
                                :key="bedroom"
                                @click="toggleBedroom(bedroom)"
                                :class="[
                                    'px-3 py-1 rounded-full text-sm border transition-all',
                                    filters.bedrooms.includes(bedroom)
                                        ? 'bg-sky-800 text-white border-sky-800'
                                        : 'bg-white text-gray-700 border-gray-300 hover:border-sky-500'
                                ]"
                            >
                                {{ bedroom === 'any' ? 'Any' : bedroom + '+' }}
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bathrooms</label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="bathroom in bathroomOptions"
                                :key="bathroom"
                                @click="toggleBathroom(bathroom)"
                                :class="[
                                    'px-3 py-1 rounded-full text-sm border transition-all',
                                    filters.bathrooms.includes(bathroom)
                                        ? 'bg-sky-800 text-white border-sky-800'
                                        : 'bg-white text-gray-700 border-gray-300 hover:border-sky-500'
                                ]"
                            >
                                {{ bathroom === 'any' ? 'Any' : bathroom + '+' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amenities -->
            <div class="border-b border-gray-200 pb-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Amenities</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    <label v-for="amenity in amenities" :key="amenity" class="flex items-center">
                        <input
                            v-model="filters.amenities"
                            :value="amenity"
                            type="checkbox"
                            class="h-4 w-4 text-sky-800 focus:ring-sky-800 border-gray-300 rounded"
                        />
                        <span class="ml-2 text-sm text-gray-700">{{ amenity }}</span>
                    </label>
                </div>
            </div>

            <!-- Sort Options -->
            <div class="border-b border-gray-200 pb-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Sort By</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                        <select v-model="filters.sort_by" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                            <option value="created_at">Newest First</option>
                            <option value="price_low">Price: Low to High</option>
                            <option value="price_high">Price: High to Low</option>
                            <option value="area_large">Area: Large to Small</option>
                            <option value="area_small">Area: Small to Large</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Results Per Page</label>
                        <select v-model="filters.per_page" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                            <option value="12">12 per page</option>
                            <option value="24">24 per page</option>
                            <option value="48">48 per page</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between items-center">
                <button @click="clearFilters" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-sky-800">
                    Clear All Filters
                </button>
                <div class="flex space-x-3">
                    <button @click="saveSearch" class="px-4 py-2 border border-sky-800 text-sky-800 rounded-md text-sm font-medium hover:bg-sky-50 focus:outline-none focus:ring-2 focus:ring-sky-800">
                        Save Search
                    </button>
                    <button @click="applyFilters" class="px-4 py-2 bg-sky-800 border border-transparent rounded-md text-sm font-medium text-white hover:bg-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-800">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
    initialFilters: {
        type: Object,
        default: () => ({})
    }
})

const emit = defineEmits(['filters-changed', 'search-saved'])

const showFilters = ref(false)
const cities = ref([])
const localities = ref([])
const propertyTypes = ref([])
const categories = ref([])
const amenities = ref([])
const priceRange = ref({ min_price: 0, max_price: 100000 })
const areaRange = ref({ min_area: 0, max_area: 10000 })

const bedroomOptions = ['any', 1, 2, 3, 4, 5]
const bathroomOptions = ['any', 1, 2, 3, 4]

const filters = reactive({
    city: '',
    locality: '',
    pincode: '',
    property_type: '',
    category: '',
    furnished_status: '',
    available_from: '',
    min_price: '',
    max_price: '',
    min_area: '',
    max_area: '',
    bedrooms: [],
    bathrooms: [],
    amenities: [],
    sort_by: 'created_at',
    per_page: 12
})

const priceSlider = ref(0)

// Load filter options
const loadCities = async () => {
    try {
        const response = await axios.get('/api/v1/search/cities')
        if (response.data.success) {
            cities.value = response.data.data
        }
    } catch (error) {
        console.error('Error loading cities:', error)
    }
}

const loadLocalities = async () => {
    if (!filters.city) {
        localities.value = []
        return
    }
    
    try {
        const response = await axios.get(`/api/v1/search/localities?city=${filters.city}`)
        if (response.data.success) {
            localities.value = response.data.data
        }
    } catch (error) {
        console.error('Error loading localities:', error)
    }
}

const loadPropertyTypes = async () => {
    try {
        const response = await axios.get('/api/v1/search/property-types')
        if (response.data.success) {
            propertyTypes.value = response.data.data
        }
    } catch (error) {
        console.error('Error loading property types:', error)
    }
}

const loadCategories = async () => {
    try {
        const response = await axios.get('/api/v1/search/categories')
        if (response.data.success) {
            categories.value = response.data.data
        }
    } catch (error) {
        console.error('Error loading categories:', error)
    }
}

const loadAmenities = async () => {
    try {
        const response = await axios.get('/api/v1/search/amenities')
        if (response.data.success) {
            amenities.value = response.data.data
        }
    } catch (error) {
        console.error('Error loading amenities:', error)
    }
}

const loadPriceRange = async () => {
    try {
        const response = await axios.get('/api/v1/search/price-range')
        if (response.data.success) {
            priceRange.value = response.data.data
        }
    } catch (error) {
        console.error('Error loading price range:', error)
    }
}

const loadAreaRange = async () => {
    try {
        const response = await axios.get('/api/v1/search/area-range')
        if (response.data.success) {
            areaRange.value = response.data.data
        }
    } catch (error) {
        console.error('Error loading area range:', error)
    }
}

// Filter methods
const toggleFilters = () => {
    showFilters.value = !showFilters.value
}

const toggleBedroom = (bedroom) => {
    const index = filters.bedrooms.indexOf(bedroom)
    if (index > -1) {
        filters.bedrooms.splice(index, 1)
    } else {
        filters.bedrooms.push(bedroom)
    }
}

const toggleBathroom = (bathroom) => {
    const index = filters.bathrooms.indexOf(bathroom)
    if (index > -1) {
        filters.bathrooms.splice(index, 1)
    } else {
        filters.bathrooms.push(bathroom)
    }
}

const updatePriceFromSlider = () => {
    const sliderValue = parseInt(priceSlider.value)
    const range = priceRange.value.max_price - priceRange.value.min_price
    const price = priceRange.value.min_price + (sliderValue / 100) * range
    
    if (!filters.max_price || filters.max_price > price) {
        filters.max_price = Math.round(price)
    }
}

const clearFilters = () => {
    Object.keys(filters).forEach(key => {
        if (Array.isArray(filters[key])) {
            filters[key] = []
        } else {
            filters[key] = key === 'per_page' ? 12 : key === 'sort_by' ? 'created_at' : ''
        }
    })
    priceSlider.value = 0
}

const applyFilters = () => {
    // Clean up empty values
    const cleanFilters = {}
    Object.keys(filters).forEach(key => {
        if (filters[key] !== '' && filters[key] !== null && filters[key] !== undefined) {
            if (Array.isArray(filters[key]) && filters[key].length > 0) {
                cleanFilters[key] = filters[key]
            } else if (!Array.isArray(filters[key])) {
                cleanFilters[key] = filters[key]
            }
        }
    })
    
    emit('filters-changed', cleanFilters)
}

const saveSearch = () => {
    emit('search-saved', filters)
}

const formatNumber = (number) => {
    return new Intl.NumberFormat('en-IN').format(number)
}

// Watch for city changes to load localities
watch(() => filters.city, () => {
    loadLocalities()
})

// Initialize filters from props
watch(() => props.initialFilters, (newFilters) => {
    Object.keys(newFilters).forEach(key => {
        if (filters.hasOwnProperty(key)) {
            filters[key] = newFilters[key]
        }
    })
}, { immediate: true })

onMounted(() => {
    loadCities()
    loadPropertyTypes()
    loadCategories()
    loadAmenities()
    loadPriceRange()
    loadAreaRange()
})
</script>
