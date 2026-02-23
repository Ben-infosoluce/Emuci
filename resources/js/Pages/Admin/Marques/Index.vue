<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <div v-if="$page.props.flash?.success"
                        class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        {{ $page.props.flash?.success }}
                    </div>

                    <div v-if="loading" class="flex justify-center items-center py-10">
                        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
                        <span class="ml-3 text-lg">Chargement des marques...</span>
                    </div>

                    <div v-else>
                        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                            <h3 class="text-xl font-bold">Gestion des Marques</h3>
                            <div class="flex items-center gap-3 w-full md:w-auto">
                                <div class="relative w-full md:w-64">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </span>
                                    <input v-model="searchQuery" type="text" placeholder="Rechercher une marque..."
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 sm:text-sm transition shadow-sm" />
                                </div>
                                <Link href="/marques/new" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-sm whitespace-nowrap">
                                    + Nouvelle Marque
                                </Link>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="marque in marques.data" :key="marque.id" 
                                @click="openModelsModal(marque)"
                                class="p-5 bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 cursor-pointer hover:shadow-md transition group">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="text-lg font-semibold group-hover:text-blue-600 transition">{{ marque.nom }}</h4>
                                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-blue-900 dark:text-blue-300">
                                        {{ marque.models_count || 0 }} modèles
                                    </span>
                                </div>
                                <div class="mt-4 flex justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <Link :href="`/marques/edit/${marque.id}`" @click.stop 
                                        class="text-sm text-blue-500 hover:text-blue-700 font-medium">Éditer</Link>
                                    <button @click.stop="deleteMarque(marque.id)" 
                                        class="text-sm text-red-500 hover:text-red-700 font-medium">Supprimer</button>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-8">
                            <nav class="flex justify-center items-center gap-4">
                                <button v-if="marques.prev_page_url" @click="fetchMarques(marques.prev_page_url)"
                                    class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                    Précédent
                                </button>
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Page {{ marques.current_page }} sur {{ marques.last_page }}
                                </span>
                                <button v-if="marques.next_page_url" @click="fetchMarques(marques.next_page_url)"
                                    class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                    Suivant
                                </button>
                            </nav>
                        </div>
                    </div>

                    <!-- Modal pour la gestion des modèles -->
                    <Dialog v-model:open="modelsModalOpen">
                        <DialogContent class="max-w-4xl max-h-[90vh] flex flex-col">
                            <DialogHeader>
                                <DialogTitle class="text-xl">Modèles pour {{ selectedMarque?.nom }}</DialogTitle>
                            </DialogHeader>
                            
                            <div class="flex-1 overflow-y-auto py-4">
                                <div v-if="modelsLoading" class="flex justify-center items-center py-10">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                                    <span class="ml-3">Chargement des modèles...</span>
                                </div>
                                
                                <div v-else>
                                    <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-3">
                                        <h5 class="font-semibold text-gray-700 dark:text-gray-300">Liste des modèles ({{ models.length }})</h5>
                                        <div class="flex items-center gap-2 w-full md:w-auto">
                                            <div class="relative w-full md:w-48">
                                                <input v-model="modelSearchQuery" type="text" placeholder="Filtrer..."
                                                    class="block w-full pl-3 pr-3 py-1.5 border border-gray-300 rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" />
                                            </div>
                                            <Button variant="default" size="sm" @click="createOpen = true" class="whitespace-nowrap">+ Ajouter</Button>
                                        </div>
                                    </div>

                                    <div v-if="models.length === 0" class="text-center py-10 bg-gray-50 dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-200 dark:border-gray-700">
                                        <p class="text-gray-500">Aucun modèle trouvé pour cette marque.</p>
                                    </div>

                                    <div v-else class="overflow-hidden border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                            <thead class="bg-gray-50 dark:bg-gray-800">
                                                <tr>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-800">
                                                <tr v-for="m in models" :key="m.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                                    <td class="px-4 py-3 text-sm text-gray-500">{{ m.id }}</td>
                                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ m.nom }}</td>
                                                    <td class="px-4 py-3 text-right text-sm font-medium space-x-3">
                                                        <button @click="openEdit(m)" class="text-blue-500 hover:text-blue-700">Éditer</button>
                                                        <button @click="deleteModel(m.id)" class="text-red-500 hover:text-red-700">Supprimer</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button variant="outline">Fermer</Button>
                                </DialogClose>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>

                    <!-- Modal Création de Modèle -->
                    <Dialog v-model:open="createOpen">
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Créer un Modèle pour {{ selectedMarque?.nom }}</DialogTitle>
                            </DialogHeader>
                            <form @submit.prevent="submitCreate">
                                <div class="space-y-4 py-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Nom du modèle</label>
                                        <input v-model="createForm.nom" required placeholder="Ex: Corolla, Mustang..."
                                            class="w-full px-3 py-2 border rounded-md dark:bg-gray-800 dark:border-gray-600" />
                                        <div v-if="createForm.errors.nom" class="text-xs text-red-500 mt-1">{{ createForm.errors.nom }}</div>
                                    </div>
                                </div>
                                <DialogFooter>
                                    <DialogClose asChild>
                                        <Button variant="outline" type="button">Annuler</Button>
                                    </DialogClose>
                                    <Button type="submit" :disabled="createForm.processing">
                                        {{ createForm.processing ? 'Création...' : 'Créer' }}
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>

                    <!-- Modal Édition de Modèle -->
                    <Dialog v-model:open="editOpen">
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Modifier le Modèle</DialogTitle>
                            </DialogHeader>
                            <form @submit.prevent="submitEdit">
                                <div class="space-y-4 py-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Nom du modèle</label>
                                        <input v-model="editForm.nom" required
                                            class="w-full px-3 py-2 border rounded-md dark:bg-gray-800 dark:border-gray-600" />
                                        <div v-if="editForm.errors.nom" class="text-xs text-red-500 mt-1">{{ editForm.errors.nom }}</div>
                                    </div>
                                </div>
                                <DialogFooter>
                                    <DialogClose asChild>
                                        <Button variant="outline" type="button">Annuler</Button>
                                    </DialogClose>
                                    <Button type="submit" :disabled="editForm.processing">
                                        {{ editForm.processing ? 'Enregistrement...' : 'Enregistrer' }}
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>

                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import { Dialog, DialogTrigger, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogClose } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    // Direct props inherited from Inertia if needed
});

// État des marques
const marques = ref({ data: [], current_page: 1, last_page: 1 });
const loading = ref(true);
const searchQuery = ref('');

// État des modèles (dans le modal)
const selectedMarque = ref(null);
const models = ref([]);
const modelsLoading = ref(false);
const modelsModalOpen = ref(false);
const modelSearchQuery = ref('');

const createOpen = ref(false);
const editOpen = ref(false);

const createForm = useForm({ marque_id: '', nom: '' });
const editForm = useForm({ marque_id: '', nom: '' });
let editingId = null;

// Fetch marques (pagination supportée)
const fetchMarques = async (url = '/marques/get/data') => {
    loading.value = true;
    try {
        const separator = url.includes('?') ? '&' : '?';
        const searchParam = searchQuery.value ? `${separator}search=${encodeURIComponent(searchQuery.value)}` : '';
        const response = await axios.get(`${url}${searchParam}`);
        marques.value = response.data.marques;
    } catch (error) {
        console.error("Erreur lors du chargement des marques:", error);
    } finally {
        loading.value = false;
    }
};

// Debounce search
let searchTimeout = null;
watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchMarques();
    }, 500);
});

// Fetch models
const fetchModels = async () => {
    if (!selectedMarque.value) return;
    modelsLoading.value = true;
    try {
        const searchParam = modelSearchQuery.value ? `?search=${encodeURIComponent(modelSearchQuery.value)}` : '';
        const response = await axios.get(`/marques/${selectedMarque.value.id}/models${searchParam}`);
        models.value = response.data.models;
    } catch (error) {
        console.error("Erreur lors du chargement des modèles:", error);
    } finally {
        modelsLoading.value = false;
    }
};

// Debounce model search
let modelSearchTimeout = null;
watch(modelSearchQuery, () => {
    clearTimeout(modelSearchTimeout);
    modelSearchTimeout = setTimeout(() => {
        fetchModels();
    }, 300);
});

// Modal handlers
const openModelsModal = (marque) => {
    selectedMarque.value = marque;
    modelSearchQuery.value = ''; // Reset search on open
    fetchModels();
    modelsModalOpen.value = true;
};

onMounted(() => {
    fetchMarques();
});

const submitCreate = () => {
    createForm.marque_id = selectedMarque.value.id;
    createForm.post('/models/new', {
        onSuccess: () => {
            createOpen.value = false;
            createForm.reset();
            fetchModels(selectedMarque.value.id);
            fetchMarques(); // Refresh count on main list
        }
    });
};

const openEdit = (m) => {
    editingId = m.id;
    editForm.marque_id = m.marque_id;
    editForm.nom = m.nom;
    editOpen.value = true;
};

const submitEdit = () => {
    editForm.put(`/models/update/${editingId}`, {
        onSuccess: () => {
            editOpen.value = false;
            fetchModels(selectedMarque.value.id);
        }
    });
};

const deleteModel = (id) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce modèle ?')) {
        router.delete(`/models/delete/${id}`, {
            onSuccess: () => {
                fetchModels(selectedMarque.value.id);
                fetchMarques(); // Refresh count
            }
        });
    }
};

const deleteMarque = (id) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette marque et TOUS ses modèles ?')) {
        router.delete(`/marques/delete/${id}`, {
            onSuccess: () => fetchMarques()
        });
    }
};

</script>

<script>
import Main from '/resources/js/Pages/Main.vue';
export default { layout: Main };
</script>