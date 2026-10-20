<template>
    <div>
        <!-- Titre + bouton création -->
        <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <h4 class="text-2xl font-bold tracking-tight">Liste des marques</h4>
            <Toaster position="top-right" />

            <Button @click="router.visit('/marques/new')">
                <Plus class="w-4 h-4 mr-2" /> Nouvelle marque
            </Button>
        </div>

        <div class="rounded-lg dark:border-gray-700">
            <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
                <Card class="h-full flex flex-col">
                    <div class="space-y-4 p-8">
                        <!-- Recherche -->
                        <div class="flex justify-start w-full">
                            <div class="flex items-center gap-2">
                                <input v-model="searchInput" @keyup.enter="applySearch" type="text"
                                    placeholder="Rechercher par nom de marque…"
                                    class="w-64 px-3 py-2 text-sm border rounded-md bg-background focus:outline-none focus:border-muted-foreground/50 focus:ring-1 focus:ring-muted-foreground/30" />
                                <button @click="applySearch"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-colors">
                                    <Search class="w-4 h-4" />
                                    Chercher
                                </button>
                                <button v-if="activeSearch" @click="clearSearch" title="Réinitialiser"
                                    class="inline-flex items-center gap-1 px-3 py-2 text-sm rounded-md border hover:bg-muted transition-colors text-muted-foreground">
                                    <X class="w-4 h-4" />
                                    Réinitialiser
                                </button>
                            </div>
                        </div>

                        <!-- Tableau -->
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID</TableHead>
                                    <TableHead>Nom</TableHead>
                                    <TableHead>Modèles</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <template v-if="loading">
                                    <TableRow v-for="i in 10" :key="i">
                                        <TableCell v-for="n in 4" :key="n" class="p-4">
                                            <Skeleton class="h-4 w-20" />
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <template v-else>
                                    <TableRow v-for="marque in marques.data" :key="marque.id">
                                        <TableCell>{{ marque.id }}</TableCell>
                                        <TableCell>{{ marque.nom || '—' }}</TableCell>
                                        <TableCell>{{ marque.models_count || 0 }}</TableCell>
                                        <TableCell class="text-right">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" size="icon">
                                                        <MoreVertical class="w-4 h-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent>
                                                    <DropdownMenuItem @click="openModelsModal(marque)">
                                                        Voir les modèles
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        @click="router.visit(`/marques/edit/${marque.id}`)">
                                                        Modifier
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem class="text-red-500"
                                                        @click="openDeleteModal('marque', marque.id)">
                                                        Supprimer
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="marques.data.length === 0">
                                        <TableCell colspan="4" class="text-center py-8 text-muted-foreground">
                                            Aucune marque trouvée
                                        </TableCell>
                                    </TableRow>
                                </template>
                            </TableBody>
                        </Table>

                        <!-- Pagination -->
                        <div class="flex items-center justify-between pt-4 border-t">
                            <p class="text-sm text-muted-foreground">
                                {{ marques.total === 0 ? 0 : (marques.current_page - 1) * marques.per_page + 1 }}–{{
                                    Math.min(marques.current_page * marques.per_page, marques.total) }}
                                sur {{ marques.total }} marque(s)
                            </p>
                            <div class="flex items-center gap-1">
                                <Button variant="outline" size="icon" :disabled="marques.current_page === 1"
                                    @click="goToPage(1)" title="Première page">
                                    <ChevronsLeft class="w-4 h-4" />
                                </Button>
                                <Button variant="outline" size="icon" :disabled="marques.current_page === 1"
                                    @click="goToPage(marques.current_page - 1)" title="Page précédente">
                                    <ChevronLeft class="w-4 h-4" />
                                </Button>
                                <template v-for="page in pageNumbers" :key="page">
                                    <span v-if="page === '...'" class="px-2 text-muted-foreground">…</span>
                                    <Button v-else :variant="page === marques.current_page ? 'default' : 'outline'"
                                        size="icon" @click="goToPage(page)">
                                        {{ page }}
                                    </Button>
                                </template>
                                <Button variant="outline" size="icon"
                                    :disabled="marques.current_page === marques.last_page"
                                    @click="goToPage(marques.current_page + 1)" title="Page suivante">
                                    <ChevronRight class="w-4 h-4" />
                                </Button>
                                <Button variant="outline" size="icon"
                                    :disabled="marques.current_page === marques.last_page"
                                    @click="goToPage(marques.last_page)" title="Dernière page">
                                    <ChevronsRight class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </Card>
            </main>

            <!-- Modal pour la gestion des modèles -->
            <Dialog v-model:open="modelsModalOpen">
                <DialogContent class="max-w-4xl max-h-[90vh] flex flex-col">
                    <DialogHeader>
                        <DialogTitle class="text-xl">Modèles pour {{ selectedMarque?.nom }}</DialogTitle>
                    </DialogHeader>

                    <div class="flex-1 overflow-y-auto py-4">
                        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-3">
                            <h5 class="font-semibold text-muted-foreground">
                                Liste des modèles ({{ models.length }})
                            </h5>
                            <div class="flex items-center gap-2 w-full md:w-auto">
                                <input v-model="modelSearchQuery" type="text" placeholder="Filtrer les modèles…"
                                    class="w-full md:w-48 px-3 py-2 text-sm border rounded-md bg-background focus:outline-none focus:border-muted-foreground/50 focus:ring-1 focus:ring-muted-foreground/30" />
                                <Button variant="default" size="sm" @click="createOpen = true"
                                    class="whitespace-nowrap">
                                    <Plus class="w-4 h-4 mr-1" /> Ajouter
                                </Button>
                            </div>
                        </div>

                        <Table v-if="!modelsLoading">
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID</TableHead>
                                    <TableHead>Nom</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="m in models" :key="m.id">
                                    <TableCell>{{ m.id }}</TableCell>
                                    <TableCell class="font-semibold">{{ m.nom }}</TableCell>
                                    <TableCell class="text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon">
                                                    <MoreVertical class="w-4 h-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent>
                                                <DropdownMenuItem @click="openEdit(m)">Modifier</DropdownMenuItem>
                                                <DropdownMenuItem class="text-red-500"
                                                    @click="openDeleteModal('model', m.id)">
                                                    Supprimer
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="models.length === 0">
                                    <TableCell colspan="3" class="text-center py-8 text-muted-foreground">
                                        Aucun modèle trouvé pour cette marque.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                        <div v-else class="flex justify-center items-center py-10">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                            <span class="ml-3 text-muted-foreground">Chargement des modèles…</span>
                        </div>
                    </div>

                    <DialogFooter>
                        <DialogClose as-child>
                            <Button variant="outline">Fermer</Button>
                        </DialogClose>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Modal Création de Modèle -->
            <Dialog v-model:open="createOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Créer un modèle pour {{ selectedMarque?.nom }}</DialogTitle>
                    </DialogHeader>
                    <form @submit.prevent="submitCreate">
                        <div class="space-y-4 py-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Nom du modèle</label>
                                <input v-model="createForm.nom" required placeholder="Ex: Corolla, Mustang..."
                                    class="w-full px-3 py-2 text-sm border rounded-md bg-background focus:outline-none focus:border-muted-foreground/50 focus:ring-1 focus:ring-muted-foreground/30" />
                                <div v-if="createForm.errors.nom" class="text-xs text-red-500 mt-1">
                                    {{ createForm.errors.nom }}
                                </div>
                            </div>
                        </div>
                        <DialogFooter>
                            <DialogClose as-child>
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
                        <DialogTitle>Modifier le modèle</DialogTitle>
                    </DialogHeader>
                    <form @submit.prevent="submitEdit">
                        <div class="space-y-4 py-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Nom du modèle</label>
                                <input v-model="editForm.nom" required
                                    class="w-full px-3 py-2 text-sm border rounded-md bg-background focus:outline-none focus:border-muted-foreground/50 focus:ring-1 focus:ring-muted-foreground/30" />
                                <div v-if="editForm.errors.nom" class="text-xs text-red-500 mt-1">
                                    {{ editForm.errors.nom }}
                                </div>
                            </div>
                        </div>
                        <DialogFooter>
                            <DialogClose as-child>
                                <Button variant="outline" type="button">Annuler</Button>
                            </DialogClose>
                            <Button type="submit" :disabled="editForm.processing">
                                {{ editForm.processing ? 'Enregistrement...' : 'Enregistrer' }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Confirmation de suppression -->
            <AlertDialog :open="deleteModal">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            {{ deleteTarget?.type === 'marque' ? 'Supprimer la marque ?' : 'Supprimer le modèle ?' }}
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            Cette action est irréversible.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter class="flex gap-2">
                        <AlertDialogCancel @click="deleteModal = false">Annuler</AlertDialogCancel>
                        <Button variant="destructive" @click="confirmDelete">Supprimer</Button>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { Toaster, toast } from 'vue-sonner';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableHead,
    TableCell,
} from '@/components/ui/table';
import { Skeleton } from '@/components/ui/skeleton';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    DialogClose,
} from '@/components/ui/dialog';
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import {
    Plus,
    Search,
    X,
    MoreVertical,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
} from 'lucide-vue-next';

// État des marques
const marques = ref({ data: [], current_page: 1, last_page: 1, per_page: 15, total: 0 });
const loading = ref(true);
const searchInput = ref('');
const activeSearch = ref('');

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

// Suppression
const deleteModal = ref(false);
const deleteTarget = ref(null);

// Fetch marques (pagination supportée)
const fetchMarques = async (url = '/marques/get/data') => {
    loading.value = true;
    try {
        const separator = url.includes('?') ? '&' : '?';
        const searchParam = activeSearch.value
            ? `${separator}search=${encodeURIComponent(activeSearch.value)}`
            : '';
        const response = await axios.get(`${url}${searchParam}`);
        marques.value = response.data.marques;
    } catch (error) {
        console.error('Erreur lors du chargement des marques:', error);
        toast.error('Erreur lors du chargement des marques.');
    } finally {
        loading.value = false;
    }
};

const applySearch = () => {
    searchInput.value = searchInput.value.trim();
    activeSearch.value = searchInput.value;
    fetchMarques();
};

const clearSearch = () => {
    searchInput.value = '';
    activeSearch.value = '';
    fetchMarques();
};

const goToPage = (page) => {
    if (page === '...' || page < 1 || page > marques.value.last_page) return;
    if (page === marques.value.current_page) return;
    fetchMarques(`/marques/get/data?page=${page}`);
};

const pageNumbers = computed(() => {
    const total = marques.value.last_page || 1;
    const current = marques.value.current_page || 1;
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
    const pages = [1];
    if (current > 3) pages.push('...');
    for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) pages.push(i);
    if (current < total - 2) pages.push('...');
    pages.push(total);
    return pages;
});

// Fetch models
const fetchModels = async () => {
    if (!selectedMarque.value) return;
    modelsLoading.value = true;
    try {
        const searchParam = modelSearchQuery.value
            ? `?search=${encodeURIComponent(modelSearchQuery.value)}`
            : '';
        const response = await axios.get(`/marques/${selectedMarque.value.id}/models${searchParam}`);
        models.value = response.data.models;
    } catch (error) {
        console.error('Erreur lors du chargement des modèles:', error);
        toast.error('Erreur lors du chargement des modèles.');
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
    modelSearchQuery.value = '';
    fetchModels();
    modelsModalOpen.value = true;
};

const openDeleteModal = (type, id) => {
    deleteTarget.value = { type, id };
    deleteModal.value = true;
};

const confirmDelete = async () => {
    if (!deleteTarget.value) return;
    try {
        if (deleteTarget.value.type === 'marque') {
            await axios.delete(`/marques/delete/${deleteTarget.value.id}`);
            toast.success('Marque supprimée avec succès.');
            fetchMarques();
        } else {
            await axios.delete(`/models/delete/${deleteTarget.value.id}`);
            toast.success('Modèle supprimé avec succès.');
            fetchModels();
            fetchMarques();
        }
        deleteModal.value = false;
    } catch (error) {
        console.error('Erreur lors de la suppression:', error);
        toast.error('Une erreur est survenue lors de la suppression.');
    }
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
            fetchModels();
            fetchMarques();
        },
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
            fetchModels();
        },
    });
};
</script>

<script>
import Main from '/resources/js/Pages/Main.vue';
export default { layout: Main };
</script>
