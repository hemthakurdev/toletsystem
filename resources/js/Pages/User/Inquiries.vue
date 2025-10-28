<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Use the main Header component -->
    <Header :current-path="$page.url" />
    <div class="container mx-auto py-10">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-sky-900 flex items-center gap-2">
          <svg class="w-8 h-8 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 01.88 7.903A4.5 4.5 0 1112 6.5c.338 0 .67.03.995.086"/></svg>
          My Inquiries
        </h1>
        <button class="btn btn-secondary" @click="refreshPage">Refresh</button>
      </div>
      <div v-if="debug" class="mb-4 text-xs text-gray-500 bg-gray-50 border-l-4 border-sky-300 p-2 rounded">{{ debug }}</div>
      <div v-if="leads.length === 0" class="flex flex-col items-center justify-center py-16">
        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 018 0v2M9 17a4 4 0 01-8 0v-2a4 4 0 018 0v2zm0 0v-2a4 4 0 018 0v2m0 0a4 4 0 01-8 0v-2a4 4 0 018 0v2z"/></svg>
        <div class="text-lg font-semibold text-gray-700 mb-2">No inquiries found</div>
        <div class="text-gray-500">You haven't submitted any property inquiries yet.</div>
      </div>
      <div v-else>
        <!-- Mobile Card List -->
        <div class="md:hidden space-y-3">
          <div v-for="lead in leads" :key="`m-${lead.id}`" class="bg-white rounded-lg border shadow-sm p-4">
            <div class="flex items-start gap-3">
              <div class="h-10 w-10 rounded-full bg-sky-100 flex items-center justify-center text-sky-800 font-semibold" :title="lead.property?.title">
                {{ (lead.property?.title || lead.name || '?').charAt(0).toUpperCase() }}
              </div>
              <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between">
                  <a v-if="lead.property" :href="`/marketplace/properties/${lead.property.id}`" class="font-medium text-sky-800 hover:underline truncate">
                    {{ lead.property.title }}
                  </a>
                  <span v-else class="text-gray-400 italic">Property not found</span>
                  <span :class="statusClass(lead.status)" class="ml-3">{{ lead.status }}</span>
                </div>
                <div class="text-xs text-gray-500 truncate" v-if="lead.property">
                  {{ lead.property.locality }}, {{ lead.property.city }} · ₹{{ lead.property.price?.toLocaleString() }}
                </div>
                <div class="text-sm text-gray-700 mt-2 line-clamp-2">{{ lead.message }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ new Date(lead.created_at).toLocaleString() }}</div>
                <div class="flex items-center gap-2 mt-3">
                  <button class="btn btn-primary btn-sm" @click="openConversations(lead)">
                    {{ expandedLeadId === lead.id ? 'Hide Conversations' : 'Conversations' }}
                  </button>
                  <button class="btn btn-danger btn-sm" :disabled="deletingId === lead.id" @click="cancelInquiry(lead)">
                    {{ deletingId === lead.id ? 'Cancelling...' : 'Cancel' }}
                  </button>
                </div>
              </div>
            </div>
            <div v-if="expandedLeadId === lead.id" class="mt-3 border-t pt-3">
              <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-gray-800">Conversations</h3>
                <div class="flex items-center gap-3">
                  <span v-if="loadingConversations" class="text-xs text-gray-500">Loading...</span>
                  <button class="text-xs text-sky-700 underline" @click="reloadConversations(lead)">Reload</button>
                </div>
              </div>
              <div class="space-y-3 max-h-56 overflow-y-auto bg-white rounded border p-3">
                <div v-if="fetchError" class="text-xs text-red-600">{{ fetchError }}</div>
                <div v-else-if="(conversationsMap[lead.id] || []).length === 0" class="text-sm text-gray-500">No conversations yet</div>
                <div v-else v-for="c in (conversationsMap[lead.id] || [])" :key="c.id || c.created_at" class="border-l-4 border-sky-500 pl-3">
                  <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-gray-700">{{ prettySender(c.sender_type) }}</span>
                    <span class="text-[11px] text-gray-500">{{ new Date(c.created_at).toLocaleString() }}</span>
                  </div>
                  <div class="text-sm text-gray-800">{{ c.message }}</div>
                </div>
              </div>
              <div class="mt-3">
                <h3 class="text-sm font-semibold text-gray-800 mb-2">Add a Note</h3>
                <textarea v-model="newNote" rows="3" class="form-textarea w-full mb-3" placeholder="Write a note..."></textarea>
                <div class="flex items-center gap-2">
                  <button class="btn btn-primary btn-sm" :disabled="savingNote" @click="submitNote(lead)">{{ savingNote ? 'Saving...' : 'Save Note' }}</button>
                  <button class="btn btn-secondary btn-sm" @click="cancelAddNote(true)">Cancel</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full bg-white border rounded-lg shadow-sm">
          <thead class="bg-sky-50 sticky top-0 z-10">
            <tr>
              <th class="px-4 py-3 border-b text-left text-xs font-semibold uppercase tracking-wide text-sky-900">Property</th>
              <th class="px-4 py-3 border-b text-left text-xs font-semibold uppercase tracking-wide text-sky-900">Your message</th>
              <th class="px-4 py-3 border-b text-left text-xs font-semibold uppercase tracking-wide text-sky-900">Status</th>
              <th class="px-4 py-3 border-b text-left text-xs font-semibold uppercase tracking-wide text-sky-900">Date</th>
              <th class="px-4 py-3 border-b text-left text-xs font-semibold uppercase tracking-wide text-sky-900">Actions</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="lead in leads" :key="lead.id">
            <tr class="hover:bg-sky-50 transition odd:bg-white even:bg-gray-50">
              <td class="px-4 py-3 border-b">
                <div v-if="lead.property" class="flex items-start gap-3">
                  <div class="h-10 w-10 rounded-full bg-sky-100 flex items-center justify-center text-sky-800 font-semibold" :title="lead.property.title">
                    {{ (lead.property.title || '?').charAt(0).toUpperCase() }}
                  </div>
                  <div class="min-w-0">
                    <a :href="`/marketplace/properties/${lead.property.id}`" class="font-medium text-sky-800 hover:underline truncate block" :title="lead.property.title">
                      {{ lead.property.title }}
                    </a>
                    <div class="text-xs text-gray-500 truncate" :title="`${lead.property.locality}, ${lead.property.city}`">
                      {{ lead.property.locality }}, {{ lead.property.city }}
                    </div>
                    <div class="text-xs text-gray-700">₹{{ lead.property.price?.toLocaleString() }}</div>
                  </div>
                </div>
                <div v-else class="text-gray-400 italic">Property not found</div>
              </td>
              <td class="px-4 py-3 border-b max-w-xs truncate" :title="lead.message">{{ lead.message }}</td>
              <td class="px-4 py-3 border-b">
                <span :class="statusClass(lead.status)">{{ lead.status }}</span>
              </td>
              <td class="px-4 py-3 border-b whitespace-nowrap text-sm text-gray-700">{{ new Date(lead.created_at).toLocaleString() }}</td>
              <td class="px-4 py-3 border-b">
                <div class="flex items-center gap-2">
                  <button class="btn btn-primary btn-sm" @click="openConversations(lead)">
                    {{ expandedLeadId === lead.id ? 'Hide Conversations' : 'Conversations' }}
                  </button>
                  <button class="btn btn-danger btn-sm" :disabled="deletingId === lead.id" @click="cancelInquiry(lead)">
                    {{ deletingId === lead.id ? 'Cancelling...' : 'Cancel Inquiry' }}
                  </button>
                </div>
              </td>
            </tr>
            <!-- Conversations Row -->
            <tr v-if="expandedLeadId === lead.id" :key="`conv-${lead.id}`">
              <td colspan="5" class="px-4 py-4 bg-gray-50 border-b">
                <div class="flex flex-col md:flex-row gap-6">
                  <div class="md:w-1/2">
                    <div class="flex items-center justify-between mb-2">
                      <h3 class="text-sm font-semibold text-gray-800">Conversations</h3>
                      <div class="flex items-center gap-3">
                        <span v-if="loadingConversations" class="text-xs text-gray-500">Loading...</span>
                        <button class="text-xs text-sky-700 underline" @click="reloadConversations(lead)">Reload</button>
                      </div>
                    </div>
                    <div class="space-y-3 max-h-64 overflow-y-auto bg-white rounded border p-3">
                      <div v-if="fetchError" class="text-xs text-red-600">{{ fetchError }}</div>
                      <div v-else-if="(conversationsMap[lead.id] || []).length === 0" class="text-sm text-gray-500">No conversations yet</div>
                      <div v-else v-for="c in (conversationsMap[lead.id] || [])" :key="c.id || c.created_at" class="border-l-4 border-sky-500 pl-3">
                        <div class="flex items-center justify-between mb-1">
                          <span class="text-xs font-medium text-gray-700">{{ prettySender(c.sender_type) }}</span>
                          <span class="text-[11px] text-gray-500">{{ new Date(c.created_at).toLocaleString() }}</span>
                        </div>
                        <div class="text-sm text-gray-800">{{ c.message }}</div>
                      </div>
                    </div>
                  </div>
                  <div class="md:w-1/2">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Add a Note</h3>
                    <textarea ref="noteInput" v-model="newNote" rows="3" class="form-textarea w-full mb-3" placeholder="Write a note to the property owner..."></textarea>
                    <div class="flex items-center gap-2">
                      <button class="btn btn-primary btn-sm" :disabled="savingNote" @click="submitNote(lead)">
                        {{ savingNote ? 'Saving...' : 'Save Note' }}
                      </button>
                      <button class="btn btn-secondary btn-sm" @click="cancelAddNote(true)">Cancel</button>
                    </div>
                    <div v-if="noteError" class="text-xs text-red-600 mt-2">{{ noteError }}</div>
                    <div v-if="noteSuccess" class="text-xs text-green-600 mt-2">Note added.</div>
                  </div>
                </div>
              </td>
            </tr>
            </template>
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Header from '@/Components/Header.vue'
import { ref } from 'vue'
import axios from 'axios'
const props = defineProps({
  leads: Array,
  debug: String
})

function statusClass(status) {
  if (status === 'new') return 'inline-block px-2 py-1 rounded bg-sky-100 text-sky-700 text-xs font-semibold';
  if (status === 'contacted') return 'inline-block px-2 py-1 rounded bg-green-100 text-green-700 text-xs font-semibold';
  if (status === 'converted') return 'inline-block px-2 py-1 rounded bg-purple-100 text-purple-700 text-xs font-semibold';
  return 'inline-block px-2 py-1 rounded bg-gray-100 text-gray-700 text-xs font-semibold';
}

const expandedLeadId = ref(null)
const loadingConversations = ref(false)
const conversationsMap = ref({})
const newNote = ref('')
const savingNote = ref(false)
const noteError = ref('')
const noteSuccess = ref(false)
const deletingId = ref(null)
const fetchError = ref('')

const getAuthConfig = () => ({ withCredentials: true })

const refreshPage = () => { window.location.reload() }

const fetchConversations = async (leadId) => {
  loadingConversations.value = true
  // Reset any previous error before fetching
  if (typeof fetchError !== 'undefined') fetchError.value = ''
  try {
    const res = await axios.get(`/org/leads/${leadId}`, getAuthConfig())
    if (res.data?.success) {
      conversationsMap.value[leadId] = res.data.data.conversations || []
    }
  } catch (e) {
    if (typeof fetchError !== 'undefined') fetchError.value = e.response?.data?.message || 'Failed to load conversations'
  } finally {
    loadingConversations.value = false
  }
}

const openConversations = async (lead) => {
  noteError.value = ''
  noteSuccess.value = false
  fetchError.value = ''
  if (expandedLeadId.value === lead.id) {
    expandedLeadId.value = null
    return
  }
  expandedLeadId.value = lead.id
  await fetchConversations(lead.id)
}

const reloadConversations = async (lead) => {
  await fetchConversations(lead.id)
}

const submitNote = async (lead) => {
  if (!newNote.value.trim()) {
    noteError.value = 'Please write a note'
    return
  }
  noteError.value = ''
  noteSuccess.value = false
  savingNote.value = true
  try {
    const res = await axios.post(`/org/leads/${lead.id}/conversations`, { message: newNote.value.trim() }, getAuthConfig())
    if (res.data?.success) {
      conversationsMap.value[lead.id] = (conversationsMap.value[lead.id] || []).concat(res.data.data)
      newNote.value = ''
      noteSuccess.value = true
      setTimeout(() => { noteSuccess.value = false }, 1500)
    } else {
      noteError.value = res.data?.message || 'Failed to add note'
    }
  } catch (e) {
    noteError.value = e.response?.data?.message || 'Failed to add note'
  } finally {
    savingNote.value = false
  }
}

const cancelInquiry = async (lead) => {
  if (!confirm('Cancel this inquiry? This cannot be undone.')) return
  deletingId.value = lead.id
  try {
    const res = await axios.delete(`/org/leads/${lead.id}`, getAuthConfig())
    if (res.data?.success) {
      // Remove from table
      const idx = props.leads.findIndex(l => l.id === lead.id)
      if (idx !== -1) props.leads.splice(idx, 1)
      if (expandedLeadId.value === lead.id) expandedLeadId.value = null
    }
  } catch (e) {
    alert('Failed to cancel inquiry')
  } finally {
    deletingId.value = null
  }
}

const prettySender = (type) => {
  if (type === 'tenant') return 'You'
  if (type === 'owner') return 'Owner'
  if (type === 'system') return 'System'
  return type
}
</script>

<style scoped>
table {
  border-collapse: separate;
  border-spacing: 0;
}
th, td {
  border-bottom: 1px solid #e5e7eb;
}
</style>
