<template>
    <div v-if="show" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-[1000] p-4" role="dialog" aria-modal="true">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Lead Details</h2>
                    <p class="text-gray-600">{{ lead?.name }} - {{ lead?.property?.title }}</p>
                </div>
                <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="flex h-[calc(90vh-120px)]">
                <!-- Left Panel - Lead Info -->
                <div class="w-1/2 p-6 border-r border-gray-200 overflow-y-auto">
                    <div class="space-y-6">
                        <!-- Lead Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Lead Information</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Name:</span>
                                    <span class="font-medium">{{ lead?.name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Phone:</span>
                                    <span class="font-medium">{{ lead?.phone }}</span>
                                </div>
                                <div v-if="lead?.email" class="flex justify-between">
                                    <span class="text-gray-600">Email:</span>
                                    <span class="font-medium">{{ lead?.email }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Source:</span>
                                    <span class="badge badge-info">{{ lead?.source?.replace('_', ' ') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Status:</span>
                                    <span :class="getStatusBadgeClass(lead?.status)">{{ lead?.status?.replace('_', ' ') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Lead Score:</span>
                                    <div class="flex items-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                            <div 
                                                class="h-2 rounded-full" 
                                                :class="getScoreColor(lead?.lead_score)"
                                                :style="{ width: (lead?.lead_score || 0) + '%' }"
                                            ></div>
                                        </div>
                                        <span class="font-medium">{{ lead?.lead_score }}</span>
                                    </div>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Created:</span>
                                    <span class="font-medium">{{ formatDate(lead?.created_at) }}</span>
                                </div>
                                <div v-if="lead?.contacted_at" class="flex justify-between">
                                    <span class="text-gray-600">Contacted:</span>
                                    <span class="font-medium">{{ formatDate(lead?.contacted_at) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Property Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Property Information</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Title:</span>
                                    <span class="font-medium">{{ lead?.property?.title }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Type:</span>
                                    <span class="font-medium">{{ lead?.property?.property_type }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Price:</span>
                                    <span class="font-medium">₹{{ lead?.property?.price?.toLocaleString() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Location:</span>
                                    <span class="font-medium">{{ lead?.property?.city }}, {{ lead?.property?.locality }}</span>
                                </div>
                                <div v-if="lead?.property?.bedrooms" class="flex justify-between">
                                    <span class="text-gray-600">Bedrooms:</span>
                                    <span class="font-medium">{{ lead?.property?.bedrooms }}</span>
                                </div>
                                <div v-if="lead?.property?.bathrooms" class="flex justify-between">
                                    <span class="text-gray-600">Bathrooms:</span>
                                    <span class="font-medium">{{ lead?.property?.bathrooms }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Original Message -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Original Message</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-gray-700">{{ lead?.message }}</p>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes</h3>
                            <textarea
                                v-model="notes"
                                rows="4"
                                class="form-textarea w-full"
                                placeholder="Add notes about this lead..."
                                @blur="updateNotes"
                            ></textarea>
                        </div>

                        <!-- Actions -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                            <div class="flex flex-wrap gap-2">
                                <button 
                                    v-if="lead?.status === 'new'" 
                                    @click="markContacted" 
                                    class="btn btn-success"
                                >
                                    Mark as Contacted
                                </button>
                                <button 
                                    v-if="lead?.status === 'contacted'" 
                                    @click="markConverted" 
                                    class="btn btn-primary"
                                >
                                    Mark as Converted
                                </button>
                                <button 
                                    v-if="lead?.status !== 'not_interested'" 
                                    @click="markNotInterested" 
                                    class="btn btn-danger"
                                >
                                    Mark as Not Interested
                                </button>
                                <a 
                                    :href="`tel:${lead?.phone}`" 
                                    class="btn btn-secondary"
                                >
                                    Call
                                </a>
                                <a 
                                    v-if="lead?.email" 
                                    :href="`mailto:${lead?.email}`" 
                                    class="btn btn-secondary"
                                >
                                    Email
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Panel - Conversations -->
                <div class="w-1/2 p-6 overflow-y-auto">
                    <div class="space-y-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-900">Conversations</h3>
                            <button @click="showAddConversation = !showAddConversation" class="btn btn-primary">
                                Add Note
                            </button>
                        </div>

                        <!-- Add Conversation Form -->
                        <div v-if="showAddConversation" class="bg-gray-50 p-4 rounded-lg">
                            <textarea
                                v-model="newConversation"
                                rows="3"
                                class="form-textarea w-full mb-3"
                                placeholder="Add a note about this lead..."
                            ></textarea>
                            <div class="flex space-x-2">
                                <button @click="addConversation" class="btn btn-primary">Add Note</button>
                                <button @click="showAddConversation = false" class="btn btn-secondary">Cancel</button>
                            </div>
                        </div>

                        <!-- Conversations List -->
                        <div class="space-y-4">
                            <div v-if="conversations.length === 0" class="text-center py-8 text-gray-500">
                                No conversations yet
                            </div>
                            <div 
                                v-for="conversation in conversations" 
                                :key="conversation.id"
                                class="border-l-4 border-sky-500 pl-4 py-2"
                            >
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ conversation.sender_type === 'user' ? 'You' : conversation.sender_type }}
                                    </span>
                                    <span class="text-xs text-gray-500">{{ formatDate(conversation.created_at) }}</span>
                                </div>
                                <p class="text-gray-700">{{ conversation.message }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
    show: Boolean,
    lead: Object
})

const emit = defineEmits(['close', 'updated'])

// Auth config helper: token (SPA) or session (cookies)
const getAuthConfig = () => {
    const token = localStorage.getItem('token')
    if (token) {
        return { headers: { 'Authorization': 'Bearer ' + token } }
    }
    return { withCredentials: true }
}

// Base paths: API for token users, web for session users
const hasToken = !!localStorage.getItem('token')
const apiLeadsPath = '/api/v1/leads'
const webLeadsPath = '/org/leads'
const activeLeadsBasePath = ref(hasToken ? apiLeadsPath : webLeadsPath)

const notes = ref('')
const conversations = ref([])
const showAddConversation = ref(false)
const newConversation = ref('')

watch(() => props.lead, (newLead) => {
    if (newLead) {
        notes.value = newLead.notes || ''
        loadConversations()
    }
}, { immediate: true })

const loadConversations = async () => {
    if (!props.lead?.id) return
    try {
        const base = hasToken ? apiLeadsPath : webLeadsPath
        const response = await axios.get(`${base}/${props.lead.id}`, getAuthConfig())
        if (response.data.success) {
            conversations.value = response.data.data.conversations || []
        }
    } catch (error) {
        // ignore in UI
    }
}

const updateNotes = async () => {
    if (!props.lead?.id) return
    try {
        const base = hasToken ? apiLeadsPath : webLeadsPath
        const response = await axios.put(`${base}/${props.lead.id}`, { notes: notes.value }, getAuthConfig())
        if (response.data.success) {
            props.lead.notes = notes.value
        }
    } catch (error) {
        // ignore in UI
    }
}

const addConversation = async () => {
    if (!newConversation.value.trim() || !props.lead?.id) return
    try {
        const base = hasToken ? apiLeadsPath : webLeadsPath
        const response = await axios.post(`${base}/${props.lead.id}/conversations`, { message: newConversation.value }, getAuthConfig())
        if (response.data.success) {
            conversations.value.push(response.data.data)
            newConversation.value = ''
            showAddConversation.value = false
        }
    } catch (error) {
        // ignore in UI
    }
}

const markContacted = async () => {
    try {
        const response = await axios.post(`${activeLeadsBasePath.value}/${props.lead.id}/mark-contacted`, {}, getAuthConfig())
        if (response.data.success) {
            props.lead.status = 'contacted'
            props.lead.contacted_at = new Date().toISOString()
            emit('updated')
        }
    } catch (error) {
        // ignore in UI
    }
}

const markConverted = async () => {
    try {
        const response = await axios.post(`${activeLeadsBasePath.value}/${props.lead.id}/mark-converted`, {}, getAuthConfig())
        if (response.data.success) {
            props.lead.status = 'converted'
            emit('updated')
        }
    } catch (error) {
        // ignore in UI
    }
}

const markNotInterested = async () => {
    try {
        const base = hasToken ? apiLeadsPath : webLeadsPath
        const response = await axios.post(`${base}/${props.lead.id}/mark-not-interested`, {}, getAuthConfig())
        if (response.data.success) {
            props.lead.status = 'not_interested'
            emit('updated')
        }
    } catch (error) {
        // ignore in UI
    }
}

const closeModal = () => {
    emit('close')
    showAddConversation.value = false
    newConversation.value = ''
}

const getStatusBadgeClass = (status) => {
    const classes = {
        'new': 'badge badge-info',
        'contacted': 'badge badge-warning',
        'interested': 'badge badge-success',
        'not_interested': 'badge badge-danger',
        'converted': 'badge badge-success'
    }
    return classes[status] || 'badge badge-gray'
}

const getScoreColor = (score) => {
    if (score >= 80) return 'bg-red-500'
    if (score >= 60) return 'bg-yellow-500'
    if (score >= 40) return 'bg-sky-500'
    return 'bg-gray-500'
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}
</script>
