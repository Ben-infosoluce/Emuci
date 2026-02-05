<template>
    <div class="max-w-md mx-auto p-4 border rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Choisissez des motifs de rejet</h2>

        <!-- Liste déroulante class="max-w-[900px] w-[900px]" -->
        <details class="border rounded-lg" v-if="items.length">
            <summary class="cursor-pointer px-3 py-2 bg-gray-100 rounded-t-lg">
                Sélectionnez vos motifs
            </summary>

            <div class="grid grid-cols-2 gap-2 text-sm border border-gray-300 rounded-md p-4 mb-4 cursor-pointer ">
                <label v-for="item in items" :key="item.id" class="flex items-center gap-2">
                    <input type="checkbox" v-model="selected" :value="item.id" class="rounded cursor-pointer " />
                    <span class="cursor-pointer max-w-[900px] w-[900px]">{{ item.motif }}</span>
                </label>
            </div>
        </details>

        <p v-else class="text-gray-500 italic">Chargement des données...</p>

        <!-- Bouton d'envoi -->
        <button @click="saveSelection" class="mt-4 w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600"
            :disabled="!selected.length">
            Enregistrer
        </button>

        <!-- Aperçu sélection -->
        <p v-if="selected.length" class="mt-3 text-sm text-gray-600">
            ✅ Sélectionné : {{ selected.join(", ") }}
        </p>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";


const items = ref([]);       // Liste des motifs
const selected = ref([]);    // IDs sélectionnés

// Récupération depuis l'API au montage du composant
const fetchItems = async () => {
    try {
        const res = await fetch("/minister/mt1/get/rejets/data");
        const data = await res.json();
        items.value = data; // data doit être un tableau [{id, motif, service_id, id_type_services}, ...]
    } catch (err) {
        console.error("Erreur de chargement des motifs :", err);
    }
};

// Envoi dans la DB
const saveSelection = async () => {
    try {
        console.log("Éléments sélectionnés :", selected.value);
        await fetch("/save/data/rejets", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ selected: selected.value }),
        });
        alert("✅ Sélection enregistrée !");
    } catch (err) {
        console.error(err);
        alert("❌ Erreur lors de l'enregistrement !");
    }
};

// Appel au montage
onMounted(fetchItems);
</script>
