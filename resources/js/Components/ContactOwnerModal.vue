<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
      <button @click="$emit('close')" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
      <h2 class="text-xl font-bold mb-4">Contact Property Owner</h2>
      <form @submit.prevent="submitForm">
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Name</label>
          <input v-model="form.name" type="text" class="w-full border rounded px-3 py-2" :class="{'border-red-400': errors.name}" />
          <div v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name }}</div>
        </div>
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Email</label>
          <input v-model="form.email" type="email" class="w-full border rounded px-3 py-2" :class="{'border-red-400': errors.email}" />
          <div v-if="errors.email" class="text-xs text-red-500 mt-1">{{ errors.email }}</div>
        </div>
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Phone</label>
          <input v-model="form.phone" type="tel" class="w-full border rounded px-3 py-2" :class="{'border-red-400': errors.phone}" />
          <div v-if="errors.phone" class="text-xs text-red-500 mt-1">{{ errors.phone }}</div>
        </div>
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Message</label>
          <textarea v-model="form.message" rows="3" class="w-full border rounded px-3 py-2" :class="{'border-red-400': errors.message}"></textarea>
          <div v-if="errors.message" class="text-xs text-red-500 mt-1">{{ errors.message }}</div>
        </div>
        <div v-if="errorMessage" class="text-xs text-red-600 mb-2">{{ errorMessage }}</div>
        <div v-if="successMessage" class="text-xs text-green-600 mb-2">{{ successMessage }}</div>
        <button type="submit" :disabled="processing" class="btn btn-primary w-full mt-2 flex items-center justify-center">
          <span v-if="processing" class="animate-spin mr-2"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span>
          <span v-if="processing">Sending...</span>
          <span v-else>Send Message</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  show: Boolean,
  propertyId: [String, Number],
  user: Object
})

const emit = defineEmits(['close','submitted'])

const form = ref({
  name: '',
  email: '',
  phone: '',
  message: ''
})
const errors = ref({})
const errorMessage = ref('')
const successMessage = ref('')
const processing = ref(false)

watch(() => props.user, (user) => {
  if (user) {
    form.value.name = user.name || ''
    form.value.email = user.email || ''
    form.value.phone = user.phone || ''
  }
}, { immediate: true })

const validate = () => {
  errors.value = {}
  if (!form.value.name) errors.value.name = 'Name is required'
  if (!form.value.email) errors.value.email = 'Email is required'
  if (!form.value.phone) errors.value.phone = 'Phone is required'
  if (!form.value.message) errors.value.message = 'Message is required'
  return Object.keys(errors.value).length === 0
}

const submitForm = async () => {
  errorMessage.value = ''
  successMessage.value = ''
  if (!props.user) {
    window.location.href = '/user/login?redirect=' + encodeURIComponent(window.location.pathname)
    return
  }
  if (!validate()) {
    errorMessage.value = 'Please fill all required fields.'
    return
  }
  processing.value = true
  try {
    const response = await fetch(`/marketplace/properties/${props.propertyId}/contact`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify(form.value)
    })
    const data = await response.json()
    if (!response.ok) {
      if (response.status === 422) {
        errors.value = data.errors
        errorMessage.value = 'Please correct the errors.'
        return
      }
      errorMessage.value = data.message || 'Failed to send message.'
      return
    }
    successMessage.value = data.message || 'Message sent successfully!'
    emit('submitted')
    setTimeout(() => {
      emit('close')
    }, 2000)
  } catch (e) {
    errorMessage.value = 'Failed to send message.'
  } finally {
    processing.value = false
  }
}
</script>

<style scoped>
.btn-primary {
  background: #0284c7;
  color: #fff;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-weight: 500;
  transition: background 0.2s;
}
.btn-primary:disabled {
  background: #7dd3fc;
  cursor: not-allowed;
}
</style>
