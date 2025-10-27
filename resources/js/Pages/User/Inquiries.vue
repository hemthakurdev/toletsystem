<template>
  <div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">My Inquiries</h1>
    <div v-if="leads.length === 0" class="text-gray-600">No inquiries found.</div>
    <div v-else>
      <table class="min-w-full bg-white border rounded">
        <thead>
          <tr>
            <th class="px-4 py-2 border-b">Property</th>
            <th class="px-4 py-2 border-b">Message</th>
            <th class="px-4 py-2 border-b">Status</th>
            <th class="px-4 py-2 border-b">Date</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="lead in leads" :key="lead.id">
            <td class="px-4 py-2 border-b">{{ lead.property_id }}</td>
            <td class="px-4 py-2 border-b">{{ lead.message }}</td>
            <td class="px-4 py-2 border-b">{{ lead.status }}</td>
            <td class="px-4 py-2 border-b">{{ new Date(lead.created_at).toLocaleString() }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  leads: Array
})
</script>
