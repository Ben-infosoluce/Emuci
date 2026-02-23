<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Messages de succès -->
                    <div v-if="$page.props.flash?.success"
                        class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        {{ $page.props.flash?.success }}
                    </div>

                    <!-- Bouton créer -->
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                        <h3 class="text-xl font-bold">Gestion des Modèles</h3>
                        <div class="flex items-center gap-3 w-full md:w-auto">
                            <div class="relative w-full md:w-64">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </span>
                                <input v-model="searchQuery" type="text" placeholder="Modèle ou marque..."
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 sm:text-sm transition shadow-sm" />
                            </div>
                            <Link href="/models/new" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-sm whitespace-nowrap">
                                + Nouveau Modèle
                            </Link>
                        </div>
                    </div>

                    <div v-if="loading" class="flex justify-center items-center py-10">
                        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
                        <span class="ml-3 text-lg">Chargement...</span>
                    </div>

                    <!-- Tableau -->
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full border-collapse border border-gray-300">
                            <thead class="bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <th class="border border-gray-300 px-4 py-2 text-left">ID</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Marque</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Nom</th>
                                    <th class="border border-gray-300 px-4 py-2 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="model in modelsData.data" :key="model.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="border border-gray-300 px-4 py-2">{{ model.id }}</td>
                                    <td class="border border-gray-300 px-4 py-2">
                                        <span
                                            class="inline-block bg-purple-100 text-purple-800 px-2 py-1 rounded text-sm">
                                            {{ model.marque?.nom || '-' }}
                                        </span>
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2 font-semibold">{{ model.nom }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-center">
                                        <Link :href="`/models/edit/${model.id}`"
                                            class="text-blue-500 hover:text-blue-700 mr-3">
                                            Éditer
                                        </Link>
                                        <button @click="deleteModel(model.id)" class="text-red-500 hover:text-red-700">
                                            Supprimer
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="mt-4 flex justify-between items-center">
                            <button v-if="modelsData.prev_page_url" @click="fetchModels(modelsData.prev_page_url)"
                                class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                                Précédent
                            </button>
                            <span class="text-gray-600">
                                Page {{ modelsData.current_page }} sur {{ modelsData.last_page }}
                            </span>
                            <button v-if="modelsData.next_page_url" @click="fetchModels(modelsData.next_page_url)"
                                class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                                Suivant
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const modelsData = ref({ data: [], current_page: 1, last_page: 1 });
const loading = ref(true);
const searchQuery = ref('');

const fetchModels = async (url = '/models/get/data') => {
    loading.value = true;
    try {
        const separator = url.includes('?') ? '&' : '?';
        const searchParam = searchQuery.value ? `${separator}search=${encodeURIComponent(searchQuery.value)}` : '';
        const response = await axios.get(`${url}${searchParam}`);
        modelsData.value = response.data.models;
    } catch (error) {
        console.error("Erreur lors du chargement des modèles:", error);
    } finally {
        loading.value = false;
    }
};

// Debounce search
let searchTimeout = null;
watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchModels();
    }, 500);
});

onMounted(() => {
    fetchModels();
});

const deleteModel = (id) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce modèle ?')) {
        router.delete(`/models/delete/${id}`, {
            onSuccess: () => fetchModels()
        });
    }
};
</script>


<script>
import Main from '/resources/js/Pages/Main.vue';
export default { layout: Main };
</script>