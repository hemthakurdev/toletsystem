<template>
    <button
        @click="toggleFavorite"
        :disabled="loading"
        :class="[
            'p-2 rounded-full transition-all duration-200',
            isFavorited 
                ? 'bg-red-50 text-red-500 hover:bg-red-100' 
                : 'bg-white text-gray-400 hover:text-red-500 hover:bg-red-50',
            'shadow-md hover:shadow-lg'
        ]"
        :title="isFavorited ? 'Remove from favorites' : 'Add to favorites'"
    >
        <svg v-if="loading" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <svg v-else class="w-5 h-5" :fill="isFavorited ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
    </button>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
    propertyId: {
        type: [Number, String],
        required: true
    },
    initialFavorited: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['favorite-toggled'])

const loading = ref(false)
const isFavorited = ref(props.initialFavorited)

const toggleFavorite = async () => {
    if (loading.value) return
    
    loading.value = true
    
    try {
        const response = await axios.post('/user/api/v1/user/favorites/toggle', {
            property_id: props.propertyId
        })
        
        if (response.data.success) {
            isFavorited.value = response.data.data.is_favorited
            emit('favorite-toggled', {
                propertyId: props.propertyId,
                isFavorited: isFavorited.value
            })
        }
    } catch (error) {
        console.error('Error toggling favorite:', error)
        // Handle error - could show toast notification
    } finally {
        loading.value = false
    }
}

const checkFavoriteStatus = async () => {
    try {
        const response = await axios.get(`/user/api/v1/user/favorites/check/${props.propertyId}`)
        
        if (response.data.success) {
            isFavorited.value = response.data.data.is_favorited
        }
    } catch (error) {
        console.error('Error checking favorite status:', error)
    }
}

// Watch for propertyId changes
watch(() => props.propertyId, () => {
    if (props.propertyId) {
        checkFavoriteStatus()
    }
}, { immediate: true })

onMounted(() => {
    if (props.propertyId) {
        checkFavoriteStatus()
    }
})
</script>
