<template>
    <div class="flex gap-6 items-start">
        <!-- Zone de Drop -->
        <div class="flex flex-col items-center justify-center border-2 border-dashed rounded-2xl cursor-pointer transition text-center p-4"
            :class="isDragging ? 'border-blue-500 bg-blue-100' : 'border-gray-300 bg-gray-50'"
            :style="{ width: width, height: height }" @dragover.prevent="onDragOver" @dragleave.prevent="onDragLeave"
            @drop.prevent="onDrop" @click="onClick">
            <p class="text-gray-600 text-sm">{{ dropText }}</p>
            <input type="file" :accept="accept" class="hidden" ref="fileInput" @change="onFileChange" />
        </div>

        <!-- Preview avec bouton de suppression -->
        <div v-if="preview"
            class="flex flex-col items-center justify-center border-2 border-dashed rounded-2xl transition p-2"
            :class="preview ? 'border-gray-300 bg-white' : 'border-gray-200 bg-gray-50'"
            :style="{ width: width, height: height }">
            <div v-if="preview" class="flex flex-col items-center w-full h-full">
                <!-- Conteneur avec bouton pour vider -->
                <div class="relative rounded-2xl overflow-hidden shadow-md border border-gray-200 cursor-pointer hover:opacity-80 transition w-full h-full"
                    @click="isModalOpen = true">
                    <!-- Image -->
                    <img :src="preview" alt="Preview" class="w-full h-full object-cover" />

                    <!-- Bouton vider -->
                    <button
                        class="absolute top-2 right-2 bg-white/80 hover:bg-white text-gray-700 hover:text-red-600 rounded-md shadow px-2 transition"
                        @click.stop="clearPreview" title="Vider l'image">
                        ✕
                    </button>
                </div>

                <!-- Nom du fichier -->
                <p class="text-xs text-gray-500 mt-2 truncate text-center w-full">
                    {{ previewText }}: {{ file?.name }}
                </p>
            </div>

            <div v-else class="text-gray-400 text-sm text-center">
                {{ previewPlaceholder }}
            </div>
        </div>

        <!-- Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div
                class="bg-white rounded-2xl shadow-xl w-11/12 max-w-8xl max-h-8xl p-6 relative flex flex-col md:flex-row gap-6">
                <!-- Bouton fermer modal -->
                <button class="absolute top-3 right-3 text-gray-500 hover:text-black text-xl"
                    @click="isModalOpen = false">
                    ✕
                </button>

                <!-- Colonne infos -->
                <div class="flex-1 space-y-3">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">{{ modalTitle }}</h2>
                    <div class="m-8">
                        <h3>Informations du véhicule</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 mb-6">
                            <p>Châssis (VIN) : {{ dossier?.r_dossier_vehicule?.vin }}</p>
                            <p>Marque du véhicule : {{ dossier?.r_dossier_vehicule?.marque }}</p>
                            <p>Modèle du véhicule : {{ dossier?.r_dossier_vehicule?.modele }}</p>
                            <p>Genre : {{ dossier?.r_dossier_vehicule?.genre_vehicule }}</p>
                            <p>Poids Total en charge : {{ dossier?.r_dossier_vehicule?.poids_total }}</p>
                            <p>Poids Utile : {{ dossier?.r_dossier_vehicule?.poids_utile }}</p>
                            <p>Sources d’énergie : {{ dossier?.r_dossier_vehicule?.source_energie }}</p>
                            <p>Couleur : {{ dossier?.r_dossier_vehicule?.couleur }}</p>
                            <p>Carrosserie : {{ dossier?.r_dossier_vehicule?.carrosserie }}</p>
                            <p>Type technique : {{ dossier?.r_dossier_vehicule?.type_technique }}</p>
                            <p>Poids à Vide : {{ dossier?.r_dossier_vehicule?.poids_vide }}</p>
                            <p>Puissance administrative : {{ dossier?.r_dossier_vehicule?.puissance_administrative }}
                            </p>
                            <p>Places Assises : {{ dossier?.r_dossier_vehicule?.places_assises }}</p>
                            <p>Nbre d’Essieux : {{ dossier?.r_dossier_vehicule?.nombre_essieux }}</p>
                        </div>

                        <hr />
                        <h3 class="mt-6">Informations du propriétaire :
                            <strong>{{ dossier?.r_dossier_client?.civilite }}, {{ dossier?.r_dossier_client?.nom }} {{
                                dossier?.r_dossier_client?.prenom }}</strong>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 mb-6">
                            <p>Adresse : {{ dossier?.r_dossier_client?.adresse }}</p>
                            <p>Email : {{ dossier?.r_dossier_client?.email }}</p>
                            <p>Téléphone : {{ dossier?.r_dossier_client?.telephone }}</p>
                            <p>Date de naissance : {{ dossier?.r_dossier_client?.date_naissance }}</p>
                            <p>Ville de naissance : {{ dossier?.r_dossier_client?.ville_naissance }}</p>
                        </div>
                        <hr />
                        <!-- Informations de l'entreprise -->
                        <h3 class="mt-6" v-if="entreprise"> <strong> Informations de l'entreprise </strong>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 mb-6" v-if="entreprise">
                            <p>Nom Entreprise : {{ entreprise?.nom_entreprise }}</p>
                            <p>Numéro de compte contribuale : {{ entreprise?.compte_contribuale }}</p>
                            <p>Registre de commerce : {{ entreprise?.registre_commerce }}</p>
                            <p>Nom du représentant légal : {{ entreprise?.nom_representant_legal }}</p>
                            <p>Téléphone du représentant légal : {{ entreprise?.telephone_representant_legal }}
                            </p>
                            <p>Profession représentant légal : {{ entreprise?.profession_representant_legal }}
                            </p>
                            <p>Date de naissance du représentant : {{
                                entreprise.date_de_naissance_representant_legal
                                }}</p>
                        </div>
                    </div>
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
import axios from "axios";
import { onMounted, watch, ref } from "vue";

const props = defineProps({
    width: { type: String, default: "16rem" },
    height: { type: String, default: "12rem" },
    dropText: {
        type: String,
        default: "Glisser-déposer un fichier ici ou cliquez pour parcourir",
    },
    previewText: { type: String, default: "Aperçu" },
    previewPlaceholder: { type: String, default: "Aperçu non disponible" },
    accept: { type: String, default: "image/*" },
    modalTitle: { type: String, default: "Informations" },
    dossier: { type: Object, default: () => ({}) },
});

const emit = defineEmits(["file-selected"]);

const file = ref(null);
const preview = ref(null);
const isDragging = ref(false);
const fileInput = ref(null);
const isModalOpen = ref(false);

const onClick = () => fileInput.value.click();

const onFileChange = (e) => {
    const selectedFile = e.target.files[0];
    handleFile(selectedFile);
};

const onDragOver = () => (isDragging.value = true);
const onDragLeave = () => (isDragging.value = false);

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

const clearPreview = () => {
    file.value = null;
    preview.value = null;
    fileInput.value.value = "";
};





const entreprise = ref(null);

async function fetchEntreprise(id) {
    try {
        const response = await axios.get(`/entreprises/${id}`);
        entreprise.value = response.data.data;
        // console.log("Données de l'entreprise :", entreprise.value);
    } catch (error) {
        console.error("Erreur :", error.response?.data || error);
        entreprise.value = null;
    }
}

// ✅ On écoute le changement de props.dossier
watch(
    () => props.dossier,
    (newDossier) => {
        // console.log("Dossier reçu en tant que prop :", newDossier);
        if (newDossier?.r_dossier_vehicule?.entreprise_id) {
            fetchEntreprise(newDossier.r_dossier_vehicule.entreprise_id);
        }
    },
    { immediate: true, deep: true } // immediate = lance la première fois, deep = écoute les objets imbriqués
);

</script>