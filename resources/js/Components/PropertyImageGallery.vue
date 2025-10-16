<template>
    <div class="property-image-gallery">
        <!-- Main Image Display -->
        <div class="main-image-container mb-4">
            <div v-if="images.length > 0" class="relative">
                <img
                    :src="getMainImageUrl()"
                    :alt="propertyTitle"
                    class="w-full h-64 md:h-96 object-cover rounded-lg shadow-lg"
                />
                
                <!-- Image Navigation -->
                <div v-if="images.length > 1" class="absolute inset-0 flex items-center justify-between p-4">
                    <button
                        @click="previousImage"
                        class="bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70 transition-all"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <button
                        @click="nextImage"
                        class="bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70 transition-all"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Image Counter -->
                <div v-if="images.length > 1" class="absolute bottom-4 right-4 bg-black bg-opacity-50 text-white px-3 py-1 rounded-full text-sm">
                    {{ currentImageIndex + 1 }} / {{ images.length }}
                </div>
                
                <!-- Fullscreen Button -->
                <button
                    @click="openFullscreen"
                    class="absolute top-4 right-4 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70 transition-all"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                    </svg>
                </button>
            </div>
            
            <!-- No Images Placeholder -->
            <div v-else class="w-full h-64 md:h-96 bg-gray-200 rounded-lg flex items-center justify-center">
                <div class="text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-gray-500">No images available</p>
                </div>
            </div>
        </div>

        <!-- Thumbnail Grid -->
        <div v-if="images.length > 1" class="thumbnail-grid">
            <div class="grid grid-cols-4 md:grid-cols-6 gap-2">
                <button
                    v-for="(image, index) in images"
                    :key="index"
                    @click="setCurrentImage(index)"
                    :class="[
                        'relative aspect-w-16 aspect-h-12 rounded-lg overflow-hidden border-2 transition-all',
                        currentImageIndex === index ? 'border-sky-500' : 'border-gray-200 hover:border-gray-300'
                    ]"
                >
                    <img
                        :src="getThumbnailUrl(image)"
                        :alt="`Thumbnail ${index + 1}`"
                        class="w-full h-full object-cover"
                    />
                </button>
            </div>
        </div>

        <!-- Upload Section (for property owners) -->
        <div v-if="canUpload" class="mt-6">
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                <div class="text-center">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Upload Property Images</h3>
                    <p class="text-sm text-gray-600 mb-4">Add more images to showcase your property</p>
                    <button @click="showUploadModal = true" class="btn-primary">
                        Upload Images
                    </button>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <div v-if="showUploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Upload Property Images</h3>
                        <button @click="showUploadModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <ImageUpload
                        :property-id="propertyId"
                        @images-uploaded="onImagesUploaded"
                    />
                </div>
            </div>
        </div>

        <!-- Fullscreen Modal -->
        <div v-if="showFullscreen" class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center">
            <div class="relative max-w-7xl max-h-full p-4">
                <button
                    @click="showFullscreen = false"
                    class="absolute top-4 right-4 text-white hover:text-gray-300 z-10"
                >
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                
                <img
                    :src="getMainImageUrl()"
                    :alt="propertyTitle"
                    class="max-w-full max-h-full object-contain"
                />
                
                <!-- Fullscreen Navigation -->
                <div v-if="images.length > 1" class="absolute inset-0 flex items-center justify-between p-4">
                    <button
                        @click="previousImage"
                        class="bg-black bg-opacity-50 text-white p-3 rounded-full hover:bg-opacity-70 transition-all"
                    >
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <button
                        @click="nextImage"
                        class="bg-black bg-opacity-50 text-white p-3 rounded-full hover:bg-opacity-70 transition-all"
                    >
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Fullscreen Counter -->
                <div v-if="images.length > 1" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-50 text-white px-4 py-2 rounded-full text-lg">
                    {{ currentImageIndex + 1 }} / {{ images.length }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import ImageUpload from './ImageUpload.vue'

const props = defineProps({
    images: {
        type: Array,
        default: () => []
    },
    propertyId: {
        type: [String, Number],
        required: true
    },
    propertyTitle: {
        type: String,
        default: 'Property'
    },
    canUpload: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['images-uploaded'])

const currentImageIndex = ref(0)
const showUploadModal = ref(false)
const showFullscreen = ref(false)

const getMainImageUrl = () => {
    if (props.images.length === 0) return ''
    const image = props.images[currentImageIndex.value]
    return getImageUrl(image, 'large')
}

const getThumbnailUrl = (image) => {
    return getImageUrl(image, 'small')
}

const getImageUrl = (image, size = 'medium') => {
    if (!image) return ''
    
    // If image is a string (path), construct URL
    if (typeof image === 'string') {
        return `/storage/${image}`
    }
    
    // If image is an object with thumbnails
    if (image.thumbnails && image.thumbnails[size]) {
        return `/storage/${image.thumbnails[size]}`
    }
    
    // Fallback to original
    if (image.original) {
        return `/storage/${image.original}`
    }
    
    return ''
}

const setCurrentImage = (index) => {
    currentImageIndex.value = index
}

const nextImage = () => {
    if (props.images.length > 1) {
        currentImageIndex.value = (currentImageIndex.value + 1) % props.images.length
    }
}

const previousImage = () => {
    if (props.images.length > 1) {
        currentImageIndex.value = currentImageIndex.value === 0 
            ? props.images.length - 1 
            : currentImageIndex.value - 1
    }
}

const openFullscreen = () => {
    showFullscreen.value = true
}

const onImagesUploaded = (uploadedImages) => {
    emit('images-uploaded', uploadedImages)
    showUploadModal.value = false
}

// Keyboard navigation
const handleKeydown = (event) => {
    if (showFullscreen.value) {
        switch (event.key) {
            case 'ArrowLeft':
                previousImage()
                break
            case 'ArrowRight':
                nextImage()
                break
            case 'Escape':
                showFullscreen.value = false
                break
        }
    }
}

onMounted(() => {
    document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown)
})
</script>

<style scoped>
.aspect-w-16 {
    position: relative;
    padding-bottom: 75%; /* 16:12 aspect ratio */
}

.aspect-h-12 {
    position: absolute;
    height: 100%;
    width: 100%;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
}
</style>

