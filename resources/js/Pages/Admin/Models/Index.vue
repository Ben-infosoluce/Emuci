<template>
    <div>
        <!-- Titre + bouton création -->
        <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <h4 class="text-2xl font-bold tracking-tight">Liste des modèles</h4>
            <Toaster position="top-right" />

            <Button @click="router.visit('/models/new')">
                <Plus class="w-4 h-4 mr-2" /> Nouveau modèle
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
                                    placeholder="Rechercher par modèle ou marque…"
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
                                    <TableHead>Marque</TableHead>
                                    <TableHead>Nom</TableHead>
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
                                    <TableRow v-for="model in modelsData.data" :key="model.id">
                                        <TableCell>{{ model.id }}</TableCell>
                                        <TableCell>{{ model.marque?.nom || '—' }}</TableCell>
                                        <TableCell>{{ model.nom || '—' }}</TableCell>
                                        <TableCell class="text-right">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" size="icon">
                                                        <MoreVertical class="w-4 h-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent>
                                                    <DropdownMenuItem
                                                        @click="router.visit(`/models/edit/${model.id}`)">
                                                        Modifier
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem class="text-red-500"
                                                        @click="openDeleteModal(model.id)">
                                                        Supprimer
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="modelsData.data.length === 0">
                                        <TableCell colspan="4" class="text-center py-8 text-muted-foreground">
                                            Aucun modèle trouvé
                                        </TableCell>
                                    </TableRow>
                                </template>
                            </TableBody>
                        </Table>

                        <!-- Pagination -->
                        <div class="flex items-center justify-between pt-4 border-t">
                            <p class="text-sm text-muted-foreground">
                                {{ modelsData.total === 0 ? 0 : (modelsData.current_page - 1) * modelsData.per_page + 1 }}–{{
                                    Math.min(modelsData.current_page * modelsData.per_page, modelsData.total) }}
                                sur {{ modelsData.total }} modèle(s)
                            </p>
                            <div class="flex items-center gap-1">
                                <Button variant="outline" size="icon" :disabled="modelsData.current_page === 1"
                                    @click="goToPage(1)" title="Première page">
                                    <ChevronsLeft class="w-4 h-4" />
                                </Button>
                                <Button variant="outline" size="icon" :disabled="modelsData.current_page === 1"
                                    @click="goToPage(modelsData.current_page - 1)" title="Page précédente">
                                    <ChevronLeft class="w-4 h-4" />
                                </Button>
                                <template v-for="page in pageNumbers" :key="page">
                                    <span v-if="page === '...'" class="px-2 text-muted-foreground">…</span>
                                    <Button v-else :variant="page === modelsData.current_page ? 'default' : 'outline'"
                                        size="icon" @click="goToPage(page)">
                                        {{ page }}
                                    </Button>
                                </template>
                                <Button variant="outline" size="icon"
                                    :disabled="modelsData.current_page === modelsData.last_page"
                                    @click="goToPage(modelsData.current_page + 1)" title="Page suivante">
                                    <ChevronRight class="w-4 h-4" />
                                </Button>
                                <Button variant="outline" size="icon"
                                    :disabled="modelsData.current_page === modelsData.last_page"
                                    @click="goToPage(modelsData.last_page)" title="Dernière page">
                                    <ChevronsRight class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </Card>
            </main>

            <!-- Confirmation de suppression -->
            <AlertDialog :open="deleteModal">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Supprimer le modèle ?</AlertDialogTitle>
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
import { computed, onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
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

const modelsData = ref({ data: [], current_page: 1, last_page: 1, per_page: 15, total: 0 });
const loading = ref(true);
const searchInput = ref('');
const activeSearch = ref('');

const deleteModal = ref(false);
const deletingId = ref(null);

const fetchModels = async (url = '/models/get/data') => {
    loading.value = true;
    try {
        const separator = url.includes('?') ? '&' : '?';
        const searchParam = activeSearch.value
            ? `${separator}search=${encodeURIComponent(activeSearch.value)}`
            : '';
        const response = await axios.get(`${url}${searchParam}`);
        modelsData.value = response.data.models;
    } catch (error) {
        console.error('Erreur lors du chargement des modèles:', error);
        toast.error('Erreur lors du chargement des modèles.');
    } finally {
        loading.value = false;
    }
};

const applySearch = () => {
    searchInput.value = searchInput.value.trim();
    activeSearch.value = searchInput.value;
    fetchModels();
};

const clearSearch = () => {
    searchInput.value = '';
    activeSearch.value = '';
    fetchModels();
};

const goToPage = (page) => {
    if (page === '...' || page < 1 || page > modelsData.value.last_page) return;
    if (page === modelsData.value.current_page) return;
    fetchModels(`/models/get/data?page=${page}`);
};

const pageNumbers = computed(() => {
    const total = modelsData.value.last_page || 1;
    const current = modelsData.value.current_page || 1;
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
    const pages = [1];
    if (current > 3) pages.push('...');
    for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) pages.push(i);
    if (current < total - 2) pages.push('...');
    pages.push(total);
    return pages;
});

const openDeleteModal = (id) => {
    deletingId.value = id;
    deleteModal.value = true;
};

const confirmDelete = async () => {
    if (!deletingId.value) return;
    try {
        await axios.delete(`/models/delete/${deletingId.value}`);
        toast.success('Modèle supprimé avec succès.');
        deleteModal.value = false;
        fetchModels();
    } catch (error) {
        console.error('Erreur lors de la suppression:', error);
        toast.error('Une erreur est survenue lors de la suppression.');
    }
};

onMounted(() => {
    fetchModels();
});
</script>

<script>
import Main from '/resources/js/Pages/Main.vue';
export default { layout: Main };
</script>
