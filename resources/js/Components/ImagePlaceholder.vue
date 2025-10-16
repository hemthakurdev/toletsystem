<template>
    <div 
        :class="[
            'image-placeholder relative overflow-hidden',
            sizeClasses,
            shapeClasses,
            className
        ]"
    >
        <!-- Loading State -->
        <div v-if="loading" class="absolute inset-0 flex items-center justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-sky-800"></div>
        </div>
        
        <!-- Error State -->
        <div v-else-if="error" class="absolute inset-0 flex flex-col items-center justify-center text-gray-400">
            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <span class="text-xs">Failed to load</span>
        </div>
        
        <!-- Image Loaded -->
        <img 
            v-else-if="src && !loading && !error"
            :src="src" 
            :alt="alt"
            :class="['w-full h-full object-cover', imageClasses]"
            @load="onLoad"
            @error="onError"
        />
        
        <!-- Default Placeholder -->
        <div v-else class="absolute inset-0 flex flex-col items-center justify-center text-gray-400">
            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-xs">{{ placeholderText }}</span>
        </div>
        
        <!-- Overlay Content -->
        <div v-if="overlay" class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <slot name="overlay"></slot>
        </div>
        
        <!-- Badge -->
        <div v-if="badge" class="absolute top-2 right-2">
            <span :class="['badge', badgeClass]">{{ badge }}</span>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    src: {
        type: String,
        default: null
    },
    alt: {
        type: String,
        default: 'Image'
    },
    size: {
        type: String,
        default: 'medium', // xs, sm, medium, lg, xl, full
        validator: (value) => ['xs', 'sm', 'medium', 'lg', 'xl', 'full'].includes(value)
    },
    shape: {
        type: String,
        default: 'rounded', // square, rounded, circle
        validator: (value) => ['square', 'rounded', 'circle'].includes(value)
    },
    placeholderText: {
        type: String,
        default: 'No image'
    },
    overlay: {
        type: Boolean,
        default: false
    },
    badge: {
        type: String,
        default: null
    },
    badgeClass: {
        type: String,
        default: 'badge-info'
    },
    className: {
        type: String,
        default: ''
    },
    imageClasses: {
        type: String,
        default: ''
    }
})

const loading = ref(false)
const error = ref(false)

const sizeClasses = computed(() => {
    const sizes = {
        xs: 'w-8 h-8',
        sm: 'w-12 h-12',
        medium: 'w-24 h-24',
        lg: 'w-32 h-32',
        xl: 'w-48 h-48',
        full: 'w-full h-full'
    }
    return sizes[props.size] || sizes.medium
})

const shapeClasses = computed(() => {
    const shapes = {
        square: 'rounded-none',
        rounded: 'rounded-xl',
        circle: 'rounded-full'
    }
    return shapes[props.shape] || shapes.rounded
})

const onLoad = () => {
    loading.value = false
    error.value = false
}

const onError = () => {
    loading.value = false
    error.value = true
}

// Watch for src changes
watch(() => props.src, (newSrc) => {
    if (newSrc) {
        loading.value = true
        error.value = false
    }
}, { immediate: true })
</script>
