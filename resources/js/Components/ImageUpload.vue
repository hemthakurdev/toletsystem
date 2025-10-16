<template>
    <div class="image-upload-container">
        <!-- Upload Area -->
        <div class="upload-area" :class="{ 'dragover': isDragOver }" @drop="handleDrop" @dragover="handleDragOver" @dragleave="handleDragLeave">
            <input
                ref="fileInput"
                type="file"
                multiple
                accept="image/*"
                @change="handleFileSelect"
                class="hidden"
            />
            
            <div v-if="!isUploading" class="upload-content">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <p class="text-lg font-medium text-gray-900 mb-2">Upload Property Images</p>
                <p class="text-sm text-gray-600 mb-4">Drag and drop images here, or click to select</p>
                <button @click="$refs.fileInput.click()" class="btn-primary">
                    Choose Images
                </button>
                <p class="text-xs text-gray-500 mt-2">Supports JPEG, PNG, GIF, WebP (Max 10MB each)</p>
            </div>
            
            <div v-else class="upload-content">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-sky-800 mx-auto mb-4"></div>
                <p class="text-lg font-medium text-gray-900">Uploading Images...</p>
                <p class="text-sm text-gray-600">{{ uploadProgress }}% Complete</p>
            </div>
        </div>

        <!-- Image Preview Grid -->
        <div v-if="images.length > 0" class="mt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Uploaded Images ({{ images.length }})</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div v-for="(image, index) in images" :key="index" class="relative group">
                    <div class="aspect-w-16 aspect-h-12 bg-gray-200 rounded-lg overflow-hidden">
                        <img
                            :src="getImageUrl(image, 'medium')"
                            :alt="`Property image ${index + 1}`"
                            class="w-full h-full object-cover"
                        />
                    </div>
                    
                    <!-- Image Actions -->
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-200 rounded-lg flex items-center justify-center">
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex space-x-2">
                            <button
                                @click="viewImage(image)"
                                class="bg-white text-gray-900 px-3 py-1 rounded-md text-sm font-medium hover:bg-gray-100"
                            >
                                View
                            </button>
                            <button
                                @click="deleteImage(index)"
                                class="bg-red-600 text-white px-3 py-1 rounded-md text-sm font-medium hover:bg-red-700"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                    
                    <!-- Image Info -->
                    <div class="mt-2 text-xs text-gray-600">
                        <p class="truncate">{{ image.filename }}</p>
                        <p>{{ formatFileSize(image.size) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Image Modal -->
        <div v-if="showImageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50" @click="closeImageModal">
            <div class="max-w-4xl max-h-full p-4" @click.stop>
                <button @click="closeImageModal" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <img
                    :src="getImageUrl(selectedImage, 'large')"
                    :alt="selectedImage?.filename"
                    class="max-w-full max-h-full object-contain rounded-lg"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    propertyId: {
        type: [Number, String],
        required: true
    },
    initialImages: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['images-uploaded', 'images-deleted'])

const fileInput = ref(null)
const images = ref([...props.initialImages])
const isUploading = ref(false)
const isDragOver = ref(false)
const uploadProgress = ref(0)
const showImageModal = ref(false)
const selectedImage = ref(null)

const getImageUrl = (image, size = 'medium') => {
    if (!image) return ''
    
    if (image.urls && image.urls[size]) {
        return image.urls[size]
    }
    
    // Fallback for images without URLs
    if (image.original) {
        return `/storage/${image.original}`
    }
    
    return ''
}

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files)
    uploadFiles(files)
}

const handleDrop = (event) => {
    event.preventDefault()
    isDragOver.value = false
    
    const files = Array.from(event.dataTransfer.files).filter(file => 
        file.type.startsWith('image/')
    )
    
    if (files.length > 0) {
        uploadFiles(files)
    }
}

const handleDragOver = (event) => {
    event.preventDefault()
    isDragOver.value = true
}

const handleDragLeave = (event) => {
    event.preventDefault()
    isDragOver.value = false
}

const uploadFiles = async (files) => {
    if (files.length === 0) return
    
    // Validate files
    const maxSize = 10 * 1024 * 1024 // 10MB
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
    
    for (const file of files) {
        if (!allowedTypes.includes(file.type)) {
            alert(`File ${file.name} is not a supported image type.`)
            return
        }
        if (file.size > maxSize) {
            alert(`File ${file.name} is too large. Maximum size is 10MB.`)
            return
        }
    }
    
    isUploading.value = true
    uploadProgress.value = 0
    
    try {
        const formData = new FormData()
        files.forEach(file => {
            formData.append('images[]', file)
        })
        
        const response = await fetch(`/api/v1/org/properties/${props.propertyId}/images`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
            },
            body: formData
        })
        
        const data = await response.json()
        
        if (data.success) {
            // Add new images to the list
            images.value.push(...data.data.uploaded_images)
            emit('images-uploaded', data.data.uploaded_images)
        } else {
            alert('Failed to upload images: ' + (data.message || 'Unknown error'))
        }
    } catch (error) {
        console.error('Error uploading images:', error)
        alert('Error uploading images. Please try again.')
    } finally {
        isUploading.value = false
        uploadProgress.value = 0
        if (fileInput.value) {
            fileInput.value.value = ''
        }
    }
}

const deleteImage = async (index) => {
    if (!confirm('Are you sure you want to delete this image?')) {
        return
    }
    
    const image = images.value[index]
    
    try {
        const response = await fetch(`/api/v1/org/properties/${props.propertyId}/images`, {
            method: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                image_paths: [image.original]
            })
        })
        
        const data = await response.json()
        
        if (data.success) {
            images.value.splice(index, 1)
            emit('images-deleted', [image.original])
        } else {
            alert('Failed to delete image: ' + (data.message || 'Unknown error'))
        }
    } catch (error) {
        console.error('Error deleting image:', error)
        alert('Error deleting image. Please try again.')
    }
}

const viewImage = (image) => {
    selectedImage.value = image
    showImageModal.value = true
}

const closeImageModal = () => {
    showImageModal.value = false
    selectedImage.value = null
}
</script>

<style scoped>
.upload-area {
    border: 2px dashed #d1d5db;
    border-radius: 0.5rem;
    padding: 2rem;
    text-align: center;
    transition: all 0.2s;
}

.upload-area.dragover {
    border-color: #3b82f6;
    background-color: #eff6ff;
}

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
