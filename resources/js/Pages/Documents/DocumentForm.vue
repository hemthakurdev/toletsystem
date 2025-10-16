<template>
    <form @submit.prevent="saveDocument" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                <input v-model="form.title" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800" placeholder="Document title">
                <div v-if="errors.title" class="text-red-500 text-sm mt-1">{{ errors.title[0] }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                <select v-model="form.category" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                    <option value="">Select Category</option>
                    <option v-for="category in categories" :key="category" :value="category">
                        {{ formatCategoryName(category) }}
                    </option>
                </select>
                <div v-if="errors.category" class="text-red-500 text-sm mt-1">{{ errors.category[0] }}</div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea v-model="form.description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800" placeholder="Document description"></textarea>
            <div v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description[0] }}</div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Document Type *</label>
                <select v-model="form.document_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                    <option value="">Select Type</option>
                    <option v-for="type in documentTypes" :key="type" :value="type">
                        {{ formatTypeName(type) }}
                    </option>
                </select>
                <div v-if="errors.document_type" class="text-red-500 text-sm mt-1">{{ errors.document_type[0] }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                <input v-model="form.expiry_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                <div v-if="errors.expiry_date" class="text-red-500 text-sm mt-1">{{ errors.expiry_date[0] }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Property</label>
                <select v-model="form.property_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                    <option value="">Select Property (Optional)</option>
                    <option v-for="property in properties" :key="property.id" :value="property.id">
                        {{ property.title }}
                    </option>
                </select>
                <div v-if="errors.property_id" class="text-red-500 text-sm mt-1">{{ errors.property_id[0] }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tenant</label>
                <select v-model="form.tenant_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                    <option value="">Select Tenant (Optional)</option>
                    <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                        {{ tenant.name }}
                    </option>
                </select>
                <div v-if="errors.tenant_id" class="text-red-500 text-sm mt-1">{{ errors.tenant_id[0] }}</div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">File *</label>
            <input @change="handleFileUpload" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
            <div v-if="errors.file" class="text-red-500 text-sm mt-1">{{ errors.file[0] }}</div>
            <div v-if="form.file" class="text-sm text-gray-600 mt-1">
                Selected: {{ form.file.name }} ({{ formatFileSize(form.file.size) }})
            </div>
            <div v-if="document && document.file_name" class="text-sm text-gray-600 mt-1">
                Current: {{ document.file_name }}
            </div>
        </div>

        <div class="flex items-center">
            <input v-model="form.is_public" type="checkbox" class="h-4 w-4 text-sky-800 focus:ring-sky-800 border-gray-300 rounded">
            <label class="ml-2 block text-sm text-gray-900">
                Make this document public
            </label>
        </div>

        <div class="flex justify-end space-x-3 pt-4">
            <button type="button" @click="$emit('cancelled')" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-sky-800">
                Cancel
            </button>
            <button type="submit" :disabled="saving" class="px-4 py-2 bg-sky-800 border border-transparent rounded-md text-sm font-medium text-white hover:bg-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-800 disabled:opacity-50">
                <span v-if="saving">Saving...</span>
                <span v-else>{{ document ? 'Update' : 'Upload' }} Document</span>
            </button>
        </div>
    </form>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
    document: {
        type: Object,
        default: null
    },
    properties: {
        type: Array,
        default: () => []
    },
    tenants: {
        type: Array,
        default: () => []
    },
    categories: {
        type: Array,
        default: () => []
    },
    documentTypes: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['saved', 'cancelled'])

const saving = ref(false)
const errors = ref({})

const form = reactive({
    title: '',
    description: '',
    document_type: '',
    category: '',
    property_id: '',
    tenant_id: '',
    expiry_date: '',
    is_public: false,
    file: null
})

// Watch for document prop changes to populate form
watch(() => props.document, (newDocument) => {
    if (newDocument) {
        form.title = newDocument.title || ''
        form.description = newDocument.description || ''
        form.document_type = newDocument.document_type || ''
        form.category = newDocument.category || ''
        form.property_id = newDocument.property_id || ''
        form.tenant_id = newDocument.tenant_id || ''
        form.expiry_date = newDocument.expiry_date || ''
        form.is_public = newDocument.is_public || false
        form.file = null
    } else {
        // Reset form for new document
        Object.keys(form).forEach(key => {
            if (key === 'file') {
                form[key] = null
            } else if (key === 'is_public') {
                form[key] = false
            } else {
                form[key] = ''
            }
        })
    }
}, { immediate: true })

const handleFileUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
        // Validate file size (20MB max)
        if (file.size > 20 * 1024 * 1024) {
            alert('File size must be less than 20MB')
            return
        }
        
        // Validate file type
        const allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'text/plain'
        ]
        
        if (!allowedTypes.includes(file.type)) {
            alert('Only PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, GIF, and TXT files are allowed')
            return
        }
        
        form.file = file
    }
}

const saveDocument = async () => {
    saving.value = true
    errors.value = {}
    
    try {
        const formData = new FormData()
        
        // Add form fields
        Object.keys(form).forEach(key => {
            if (form[key] !== null && form[key] !== '') {
                formData.append(key, form[key])
            }
        })
        
        let response
        if (props.document) {
            // Update existing document
            response = await axios.post(`/api/v1/org/documents/${props.document.id}`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
        } else {
            // Create new document
            response = await axios.post('/api/v1/org/documents', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
        }
        
        if (response.data.success) {
            emit('saved', response.data.data)
        }
    } catch (error) {
        if (error.response && error.response.status === 422) {
            errors.value = error.response.data.errors
        } else {
            console.error('Error saving document:', error)
            alert('Error saving document. Please try again.')
        }
    } finally {
        saving.value = false
    }
}

const formatCategoryName = (category) => {
    return category.split('_').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ')
}

const formatTypeName = (type) => {
    return type.split('_').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ')
}

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}
</script>

