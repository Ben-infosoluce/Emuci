<template>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Autorisation de la société de crédit<span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <input type="file" id="credit-file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                @change="handleFileChange" />
            <div
                class="flex items-center justify-between w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-500 cursor-pointer">
                <span>{{ fileName || 'Importer le fichier' }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.586-6.586a4 4 0 00-5.656-5.656l-6.586 6.586a6 6 0 108.486 8.486l6.586-6.586" />
                </svg>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const fileName = ref('')

function handleFileChange(event) {
    const file = event.target.files[0]
    if (file) {
        fileName.value = file.name
    }
}
</script>
