<template>
    <div class="flex gap-6 items-start">
        <!-- Zone de Drop -->
        <div class="flex flex-col items-center justify-center w-64 h-48 border-2 border-dashed rounded-2xl cursor-pointer transition
             hover:border-blue-500 hover:bg-blue-50"
            :class="isDragging ? 'border-blue-500 bg-blue-100' : 'border-gray-300 bg-gray-50'"
            @dragover.prevent="onDragOver" @dragleave.prevent="onDragLeave" @drop.prevent="onDrop" @click="onClick">
            <p class="text-gray-600 text-sm text-center">
                Glisser-déposer un fichier ici<br />ou cliquez pour parcourir
            </p>
            <input type="file" accept="image/*" class="hidden" ref="fileInput" @change="onFileChange" />
        </div>

        <!-- Preview avec taille identique -->
        <div class="flex flex-col items-center justify-center w-64 h-48 border-2 border-dashed rounded-2xl cursor-pointer transition
             ">
            <div v-if="preview" class="flex flex-col items-center">
                <p class="text-gray-700 text-sm mb-2">Aperçu :</p>
                <div class="w-64 h-48 rounded-2xl overflow-hidden shadow-md border border-gray-200 cursor-pointer hover:opacity-80 transition"
                    @click="isModalOpen = true">
                    <img :src="preview" alt="Preview" class="w-full h-full object-cover" />
                </div>
                <p class="text-xs text-gray-500 mt-2 truncate w-64 text-center">
                    {{ file?.name }}
                </p>
            </div>
            <div v-else class="text-gray-400 text-sm">Aperçu de la carte grise
            </div>
        </div>


        <!-- Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-11/12 max-w-5xl p-6 relative flex flex-col md:flex-row gap-6">
                <!-- Bouton fermer -->
                <button class="absolute top-3 right-3 text-gray-500 hover:text-black text-xl"
                    @click="isModalOpen = false">
                    ✕
                </button>

                <!-- Colonne infos -->
                <div class="flex-1 space-y-3">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Informations</h2>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                    <p>Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                    <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco.</p>
                    <p>Duis aute irure dolor in reprehenderit in voluptate velit esse.</p>
                    <p>Excepteur sint occaecat cupidatat non proident, sunt in culpa.</p>
                </div>

                <!-- Colonne image -->
                <div class="flex justify-center items-center flex-1">
                    <img :src="preview" alt="Preview Large" class="rounded-xl shadow-lg max-h-[70vh] object-contain" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";

const file = ref(null);
const preview = ref(null);
const isDragging = ref(false);
const fileInput = ref(null);
const isModalOpen = ref(false);

const onClick = () => {
    fileInput.value.click();
};

const onFileChange = (e) => {
    const selectedFile = e.target.files[0];
    handleFile(selectedFile);
};

const onDragOver = () => {
    isDragging.value = true;
};

const onDragLeave = () => {
    isDragging.value = false;
};

const onDrop = (e) => {
    isDragging.value = false;
    const droppedFile = e.dataTransfer.files[0];
    handleFile(droppedFile);
};

const handleFile = (selectedFile) => {
    if (selectedFile && selectedFile.type.startsWith("image/")) {
        file.value = selectedFile;
        preview.value = URL.createObjectURL(selectedFile);
    } else {
        alert("Veuillez sélectionner un fichier image.");
    }
};
</script>
