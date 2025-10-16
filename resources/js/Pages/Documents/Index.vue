<template>
    <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Document Management</h1>
                <p class="text-gray-600">Manage your property documents and files</p>
            </div>
            <button @click="showAddModal = true" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Upload Document
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-sky-100 rounded-lg">
                        <svg class="w-6 h-6 text-sky-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.29-1.009-5.824-2.709M15 6.291A7.962 7.962 0 0012 5c-2.34 0-4.29 1.009-5.824 2.709M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Total Documents</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total_documents || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Total Size</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatFileSize(stats.total_size || 0) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Expiring Soon</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.expiring_soon || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Recent Uploads</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.recent_documents?.length || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input
                        v-model="filters.search"
                        @input="debouncedSearch"
                        type="text"
                        placeholder="Search documents..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Property</label>
                    <select v-model="filters.property_id" @change="loadDocuments" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                        <option value="">All Properties</option>
                        <option v-for="property in properties" :key="property.id" :value="property.id">
                            {{ property.title }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select v-model="filters.category" @change="loadDocuments" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                        <option value="">All Categories</option>
                        <option v-for="category in categories" :key="category" :value="category">
                            {{ category }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Document Type</label>
                    <select v-model="filters.document_type" @change="loadDocuments" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                        <option value="">All Types</option>
                        <option v-for="type in documentTypes" :key="type" :value="type">
                            {{ type }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Documents Grid -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div v-if="loading" class="p-8 text-center">
                <div class="flex justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-sky-800"></div>
                </div>
            </div>
            
            <div v-else-if="documents.length === 0" class="p-8 text-center text-gray-500">
                No documents found
            </div>
            
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
                <div v-for="document in documents" :key="document.id" class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center">
                            <div class="p-2 bg-sky-100 rounded-lg">
                                <svg class="w-6 h-6 text-sky-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.29-1.009-5.824-2.709M15 6.291A7.962 7.962 0 0012 5c-2.34 0-4.29 1.009-5.824 2.709M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-gray-900 truncate">{{ document.title }}</h3>
                                <p class="text-xs text-gray-500">{{ document.category }}</p>
                            </div>
                        </div>
                        <div class="flex space-x-1">
                            <button @click="viewDocument(document)" class="text-sky-800 hover:text-sky-900">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            <button @click="downloadDocument(document)" class="text-green-600 hover:text-green-900">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </button>
                            <button @click="editDocument(document)" class="text-indigo-600 hover:text-indigo-900">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button @click="deleteDocument(document)" class="text-red-600 hover:text-red-900">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <div v-if="document.property" class="text-xs text-gray-600">
                            <span class="font-medium">Property:</span> {{ document.property.title }}
                        </div>
                        <div v-if="document.tenant" class="text-xs text-gray-600">
                            <span class="font-medium">Tenant:</span> {{ document.tenant.name }}
                        </div>
                        <div class="text-xs text-gray-600">
                            <span class="font-medium">Size:</span> {{ formatFileSize(document.file_size) }}
                        </div>
                        <div class="text-xs text-gray-600">
                            <span class="font-medium">Uploaded:</span> {{ formatDate(document.created_at) }}
                        </div>
                        <div v-if="document.expiry_date" class="text-xs text-gray-600">
                            <span class="font-medium">Expires:</span> {{ formatDate(document.expiry_date) }}
                        </div>
                    </div>
                    
                    <div v-if="document.description" class="mt-3 text-xs text-gray-600 line-clamp-2">
                        {{ document.description }}
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pagination" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button @click="loadDocuments(pagination.current_page - 1)" :disabled="!pagination.prev_page_url" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Previous
                    </button>
                    <button @click="loadDocuments(pagination.current_page + 1)" :disabled="!pagination.next_page_url" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Next
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <button @click="loadDocuments(pagination.current_page - 1)" :disabled="!pagination.prev_page_url" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                Previous
                            </button>
                            <button @click="loadDocuments(pagination.current_page + 1)" :disabled="!pagination.next_page_url" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                Next
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Document Modal -->
        <div v-if="showAddModal || showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            {{ showEditModal ? 'Edit Document' : 'Upload New Document' }}
                        </h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <DocumentForm
                        :document="editingDocument"
                        :properties="properties"
                        :tenants="tenants"
                        :categories="categories"
                        :document-types="documentTypes"
                        @saved="onDocumentSaved"
                        @cancelled="closeModal"
                    />
                </div>
            </div>
        </div>

        <!-- View Document Modal -->
        <div v-if="showViewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Document Details</h3>
                        <button @click="showViewModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div v-if="viewingDocument" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Title</label>
                                <p class="mt-1 text-sm text-gray-900">{{ viewingDocument.title }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Category</label>
                                <p class="mt-1 text-sm text-gray-900">{{ viewingDocument.category }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Document Type</label>
                                <p class="mt-1 text-sm text-gray-900">{{ viewingDocument.document_type }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">File Size</label>
                                <p class="mt-1 text-sm text-gray-900">{{ formatFileSize(viewingDocument.file_size) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Uploaded</label>
                                <p class="mt-1 text-sm text-gray-900">{{ formatDate(viewingDocument.created_at) }}</p>
                            </div>
                            <div v-if="viewingDocument.expiry_date">
                                <label class="block text-sm font-medium text-gray-700">Expires</label>
                                <p class="mt-1 text-sm text-gray-900">{{ formatDate(viewingDocument.expiry_date) }}</p>
                            </div>
                        </div>
                        
                        <div v-if="viewingDocument.property">
                            <label class="block text-sm font-medium text-gray-700">Property</label>
                            <p class="mt-1 text-sm text-gray-900">{{ viewingDocument.property.title }}</p>
                        </div>
                        
                        <div v-if="viewingDocument.tenant">
                            <label class="block text-sm font-medium text-gray-700">Tenant</label>
                            <p class="mt-1 text-sm text-gray-900">{{ viewingDocument.tenant.name }}</p>
                        </div>
                        
                        <div v-if="viewingDocument.description">
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <p class="mt-1 text-sm text-gray-900">{{ viewingDocument.description }}</p>
                        </div>
                        
                        <div class="flex space-x-3">
                            <button @click="downloadDocument(viewingDocument)" class="px-4 py-2 bg-sky-800 text-white rounded-md hover:bg-sky-900">
                                Download
                            </button>
                            <button @click="editDocument(viewingDocument)" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Edit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import DocumentForm from './DocumentForm.vue'

const documents = ref([])
const properties = ref([])
const tenants = ref([])
const categories = ref([
    'lease_agreement', 'rent_receipt', 'maintenance_bill', 'utility_bill',
    'insurance_document', 'property_tax', 'tenant_id_proof', 'tenant_agreement',
    'inspection_report', 'legal_document', 'financial_document', 'other'
])
const documentTypes = ref([
    'pdf', 'image', 'spreadsheet', 'word_document', 'text', 'other'
])
const stats = ref({})
const loading = ref(false)
const showAddModal = ref(false)
const showEditModal = ref(false)
const showViewModal = ref(false)
const editingDocument = ref(null)
const viewingDocument = ref(null)
const pagination = ref(null)

const filters = ref({
    search: '',
    property_id: '',
    category: '',
    document_type: ''
})

let searchTimeout = null

const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        loadDocuments()
    }, 500)
}

const loadDocuments = async (page = 1) => {
    loading.value = true
    try {
        const params = new URLSearchParams()
        Object.keys(filters.value).forEach(key => {
            if (filters.value[key]) {
                params.append(key, filters.value[key])
            }
        })
        params.append('page', page)
        
        const response = await axios.get(`/api/v1/org/documents?${params.toString()}`)
        if (response.data.success) {
            documents.value = response.data.data.data
            pagination.value = response.data.data
        }
    } catch (error) {
        console.error('Error loading documents:', error)
    } finally {
        loading.value = false
    }
}

const loadProperties = async () => {
    try {
        const response = await axios.get('/api/v1/org/properties')
        if (response.data.success) {
            properties.value = response.data.data.data
        }
    } catch (error) {
        console.error('Error loading properties:', error)
    }
}

const loadTenants = async () => {
    try {
        const response = await axios.get('/api/v1/org/tenants')
        if (response.data.success) {
            tenants.value = response.data.data.data
        }
    } catch (error) {
        console.error('Error loading tenants:', error)
    }
}

const loadStats = async () => {
    try {
        const response = await axios.get('/api/v1/org/documents/statistics')
        if (response.data.success) {
            stats.value = response.data.data
        }
    } catch (error) {
        console.error('Error loading stats:', error)
    }
}

const viewDocument = (document) => {
    viewingDocument.value = document
    showViewModal.value = true
}

const editDocument = (document) => {
    editingDocument.value = document
    showEditModal.value = true
}

const downloadDocument = async (document) => {
    try {
        const response = await axios.get(`/api/v1/org/documents/${document.id}/download`, {
            responseType: 'blob'
        })
        
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', document.file_name)
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
    } catch (error) {
        console.error('Error downloading document:', error)
    }
}

const deleteDocument = async (document) => {
    if (confirm('Are you sure you want to delete this document?')) {
        try {
            const response = await axios.delete(`/api/v1/org/documents/${document.id}`)
            if (response.data.success) {
                loadDocuments()
                loadStats()
            }
        } catch (error) {
            console.error('Error deleting document:', error)
        }
    }
}

const onDocumentSaved = () => {
    closeModal()
    loadDocuments()
    loadStats()
}

const closeModal = () => {
    showAddModal.value = false
    showEditModal.value = false
    editingDocument.value = null
}

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-IN')
}

onMounted(() => {
    loadDocuments()
    loadProperties()
    loadTenants()
    loadStats()
})
</script>

