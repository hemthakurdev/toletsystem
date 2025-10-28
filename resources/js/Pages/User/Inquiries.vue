<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Use the main Header component -->
    <Header :current-path="$page.url" />
    <div class="container mx-auto py-10">
      <h1 class="text-3xl font-bold mb-8 text-sky-900 flex items-center gap-2">
        <svg class="w-8 h-8 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 01.88 7.903A4.5 4.5 0 1112 6.5c.338 0 .67.03.995.086"/></svg>
        My Inquiries
      </h1>
      <div v-if="debug" class="mb-4 text-xs text-gray-500 bg-gray-50 border-l-4 border-sky-300 p-2 rounded">{{ debug }}</div>
      <div v-if="leads.length === 0" class="flex flex-col items-center justify-center py-16">
        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 018 0v2M9 17a4 4 0 01-8 0v-2a4 4 0 018 0v2zm0 0v-2a4 4 0 018 0v2m0 0a4 4 0 01-8 0v-2a4 4 0 018 0v2z"/></svg>
        <div class="text-lg font-semibold text-gray-700 mb-2">No inquiries found</div>
        <div class="text-gray-500">You haven't submitted any property inquiries yet.</div>
      </div>
      <div v-else class="overflow-x-auto">
        <table class="min-w-full bg-white border rounded shadow">
          <thead class="bg-sky-100">
            <tr>
              <th class="px-4 py-2 border-b text-left">Property</th>
              <th class="px-4 py-2 border-b text-left">Message</th>
              <th class="px-4 py-2 border-b text-left">Status</th>
              <th class="px-4 py-2 border-b text-left">Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="lead in leads" :key="lead.id" class="hover:bg-sky-50 transition">
              <td class="px-4 py-2 border-b">
                <div v-if="lead.property">
                  <a :href="`/marketplace/properties/${lead.property.id}`" class="font-semibold text-sky-700 hover:underline">
                    {{ lead.property.title }}
                  </a>
                  <div class="text-xs text-gray-500">
                    {{ lead.property.locality }}, {{ lead.property.city }}
                  </div>
                  <div class="text-xs text-gray-700">₹{{ lead.property.price?.toLocaleString() }}</div>
                </div>
                <div v-else class="text-gray-400 italic">Property not found</div>
              </td>
              <td class="px-4 py-2 border-b max-w-xs truncate" :title="lead.message">{{ lead.message }}</td>
              <td class="px-4 py-2 border-b">
                <span :class="statusClass(lead.status)">{{ lead.status }}</span>
              </td>
              <td class="px-4 py-2 border-b">{{ new Date(lead.created_at).toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import Header from '@/Components/Header.vue'
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
