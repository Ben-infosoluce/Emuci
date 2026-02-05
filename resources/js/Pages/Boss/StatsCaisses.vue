<template>
    <div class="bg-gray-50 min-h-screen p-6">
        <!-- HEADER -->
        <div class="flex items-center justify-between mb-6">
            <!-- Date actuelle et filtre -->
            <div class="flex items-center space-x-3">
                <p class="text-sm font-semibold text-gray-700">
                    DATE :
                    <span class="font-bold italic text-black">{{ currentDate }}</span>
                </p>

                <!-- 🔍 Filtre par date -->
                <input type="date" v-model="selectedDate" @change="fetchPaiements"
                    class="border border-gray-300 rounded-md px-3 py-1 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
            </div>

            <!-- <div class="flex items-center space-x-6 text-sm font-medium">
                <button class="flex items-center space-x-1 text-gray-700 hover:text-black">
                    <i class="pi pi-print"></i>
                    <span>Imprimer</span>
                </button>
                <button class="flex items-center space-x-1 text-gray-700 hover:text-black">
                    <i class="pi pi-upload"></i>
                    <span>Exporter</span>
                </button>
            </div> -->
        </div>

        <!-- CONTENT BOX -->
        <div class="bg-white shadow rounded-xl p-4">
            <div class="grid grid-cols-4 divide-x divide-gray-200">
                <!-- EMUCI -->
                <div class="p-4">
                    <h2 class="text-gray-800 text-xl font-extrabold mb-2 text-center">
                        {{ totals.emuci.toLocaleString() }} F
                    </h2>
                    <p class="text-gray-500 text-xs text-center uppercase tracking-wide mb-4">
                        Total EMUCI
                    </p>

                    <div class="space-y-3">
                        <div v-for="(item, i) in details.emuci" :key="i"
                            class="border rounded-md p-2 hover:shadow transition">
                            <p class="text-sm font-medium text-gray-800">{{ item.element_facturation }}</p>
                            <p class="text-amber-600 font-semibold">{{ item.montant.toLocaleString() }} F</p>
                        </div>
                    </div>
                </div>

                <!-- QUIPUX -->
                <div class="p-4">
                    <h2 class="text-gray-800 text-xl font-extrabold mb-2 text-center">
                        {{ totals.quipux.toLocaleString() }} F
                    </h2>
                    <p class="text-gray-500 text-xs text-center uppercase tracking-wide mb-4">
                        Total QUIPUX
                    </p>

                    <div class="space-y-3">
                        <div v-for="(item, i) in details.quipux" :key="i"
                            class="border rounded-md p-2 hover:shadow transition">
                            <p class="text-sm font-medium text-gray-800">{{ item.element_facturation }}</p>
                            <p class="text-amber-600 font-semibold">{{ item.montant.toLocaleString() }} F</p>
                        </div>
                    </div>
                </div>

                <!-- DGTT -->
                <div class="p-4">
                    <h2 class="text-gray-800 text-xl font-extrabold mb-2 text-center">
                        {{ totals.dgtt.toLocaleString() }} F
                    </h2>
                    <p class="text-gray-500 text-xs text-center uppercase tracking-wide mb-4">
                        Total DGTT
                    </p>

                    <div class="space-y-3">
                        <div v-for="(item, i) in details.dgtt" :key="i"
                            class="border rounded-md p-2 hover:shadow transition">
                            <p class="text-sm font-medium text-gray-800">{{ item.element_facturation }}</p>
                            <p class="text-amber-600 font-semibold">{{ item.montant.toLocaleString() }} F</p>
                        </div>
                    </div>
                </div>

                <!-- MINISTÈRE -->
                <div class="p-4">
                    <h2 class="text-gray-800 text-xl font-extrabold mb-2 text-center">
                        {{ totals.ministere.toLocaleString() }} F
                    </h2>
                    <p class="text-gray-500 text-xs text-center uppercase tracking-wide mb-4">
                        Total MINISTÈRE
                    </p>

                    <div class="space-y-3">
                        <div v-for="(item, i) in details.ministere" :key="i"
                            class="border rounded-md p-2 hover:shadow transition">
                            <p class="text-sm font-medium text-gray-800">{{ item.element_facturation }}</p>
                            <p class="text-amber-600 font-semibold">{{ item.montant.toLocaleString() }} F</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOOTER -->
            <!-- <div class="flex justify-end mt-6">
                <button
                    class="flex items-center space-x-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold px-6 py-2 rounded-md transition">
                    <span>ARRÊTER LA CAISSE</span>
                    <input type="checkbox" class="accent-white scale-125" />
                </button>
            </div> -->
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const paiements = ref([])
const selectedDate = ref(new Date().toISOString().split('T')[0]) // par défaut aujourd’hui

// 🔹 Charger les paiements
const fetchPaiements = async () => {
    try {
        const { data } = await axios.get('/paiement/data/stat', {
            params: { date: selectedDate.value } // envoi du paramètre ?date=YYYY-MM-DD
        })
        paiements.value = data
    } catch (error) {
        console.error('Erreur lors du chargement des paiements :', error)
    }
}

onMounted(fetchPaiements)

// 🔹 Regrouper les données par entité
const details = computed(() => {
    const result = { emuci: [], quipux: [], dgtt: [], ministere: [] }

    paiements.value.forEach(p => {
        if (p.description) {
            const lignes = JSON.parse(p.description)
            lignes.forEach(item => {
                switch (item.id_entite) {
                    case 2:
                        result.emuci.push(item)
                        break
                    case 3:
                        result.quipux.push(item)
                        break
                    case 4:
                        result.dgtt.push(item)
                        break
                    default:
                        result.ministere.push(item)
                }
            })
        }
    })
    return result
})

// 🔹 Totaux
const totals = computed(() => ({
    emuci: details.value.emuci.reduce((s, i) => s + Number(i.montant), 0),
    quipux: details.value.quipux.reduce((s, i) => s + Number(i.montant), 0),
    dgtt: details.value.dgtt.reduce((s, i) => s + Number(i.montant), 0),
    ministere: details.value.ministere.reduce((s, i) => s + Number(i.montant), 0)
}))

const currentDate = new Date().toLocaleDateString('fr-FR')
</script>



<script>
import Main from '/resources/js/Pages/Main.vue';

export default {
    layout: Main,
};
</script>
