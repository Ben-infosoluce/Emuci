<template>
    <div class="flex gap-6 items-start">
        <!-- Zone de Drop -->
        <div class="flex flex-col items-center justify-center border-2 border-dashed rounded-2xl cursor-pointer transition
             hover:border-blue-500 hover:bg-blue-50"
            :class="isDragging ? 'border-blue-500 bg-blue-100' : 'border-gray-300 bg-gray-50'"
            :style="{ width: width, height: height }" @dragover.prevent="onDragOver" @dragleave.prevent="onDragLeave"
            @drop.prevent="onDrop" @click="onClick">
            <p class="text-gray-600 text-sm text-center">
                {{ dropText }}
            </p>
            <input type="file" :accept="accept" class="hidden" ref="fileInput" @change="onFileChange" />
        </div>

        <!-- Preview avec taille identique -->
        <div v-if="preview" class="flex flex-col items-center">
            <p class="text-gray-700 text-sm mb-2">{{ previewTitle }}</p>
            <div class="rounded-2xl overflow-hidden shadow-md border border-gray-200 cursor-pointer hover:opacity-80 transition"
                :style="{ width: width, height: height }" @click="isModalOpen = true">
                <img :src="preview" alt="Preview" class="w-full h-full object-cover" />
            </div>
            <p class="text-xs text-gray-500 mt-2 truncate text-center" :style="{ width: width }">
                {{ file?.name }}
            </p>
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
                <div v-if="modalInfo && modalInfo.length" class="flex-1 space-y-3">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">{{ modalTitle }}</h2>
                    <p v-for="(text, i) in modalInfo" :key="i">{{ text }}</p>
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
import { ref, watch } from "vue";

// ✅ Props configurables
const props = defineProps({
    width: { type: String, default: "16rem" },  // équivalent w-64
    height: { type: String, default: "12rem" }, // équivalent h-48
    dropText: { type: String, default: "Glisser-déposer un fichier ici ou cliquez pour parcourir" },
    previewTitle: { type: String, default: "Aperçu :" },
    accept: { type: String, default: "image/*" },
    modalTitle: { type: String, default: "Informations" },
    modalInfo: { type: Array, default: () => [] } // Liste de paragraphes à afficher
});

const emit = defineEmits(["file-selected"]);

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
        emit("file-selected", selectedFile);
    } else {
        alert("Veuillez sélectionner un fichier image.");
    }
};
</script>
