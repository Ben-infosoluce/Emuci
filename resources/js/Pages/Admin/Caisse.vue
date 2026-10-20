<template>
    <div>
        <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <h4 class="text-2xl font-bold tracking-tight">
                Liste des caisses
            </h4>
            <Toaster position="top-right" />

            <Button @click="openCreateModal">
                <Plus class="w-4 h-4 mr-2" /> Nouvelle caisse
            </Button>
        </div>

        <div class="rounded-lg dark:border-gray-700">
            <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
                <Card class="h-full flex flex-col">
                    <div class="space-y-4 p-8">
                        <div class="flex justify-start w-full">
                            <div class="flex items-center gap-2">
                                <input v-model="searchInput" @keyup.enter="applySearch" type="text"
                                    placeholder="Rechercher par nom de caisse…"
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

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID</TableHead>
                                    <TableHead>Code</TableHead>
                                    <TableHead>Nom de la caisse</TableHead>
                                    <TableHead>ID du site</TableHead>
                                    <TableHead>Site du site</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <template v-if="loading">
                                    <TableRow v-for="i in 10" :key="i">
                                        <TableCell v-for="n in 6" :key="n" class="p-4">
                                            <Skeleton class="h-4 w-20" />
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <template v-else>
                                    <TableRow v-for="caisse in paginatedCaisses" :key="caisse.id">
                                        <TableCell>{{ caisse.id }}</TableCell>
                                        <TableCell>{{ caisse.code || '—' }}</TableCell>
                                        <TableCell>{{ caisse.libelle || '—' }}</TableCell>
                                        <TableCell>{{ caisse.site_id ?? '—' }}</TableCell>
                                        <TableCell>{{ caisse.site?.nom_site || '—' }}</TableCell>
                                        <TableCell class="text-right">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" size="icon">
                                                        <MoreVertical class="w-4 h-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent>
                                                    <DropdownMenuItem @click="openEditModal(caisse)">Modifier
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem class="text-red-500"
                                                        @click="openDeleteModal(caisse.id)">Supprimer</DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                </template>
                            </TableBody>
                        </Table>

                        <div class="flex items-center justify-between pt-4 border-t">
                            <p class="text-sm text-muted-foreground">
                                {{ filteredCaisses.length === 0 ? 0 : (currentPage - 1) * perPage + 1 }}–{{
                                    Math.min(currentPage * perPage,
                                        filteredCaisses.length) }}
                                sur {{ filteredCaisses.length }} caisse(s)
                            </p>
                            <div class="flex items-center gap-1">
                                <Button variant="outline" size="icon" :disabled="currentPage === 1"
                                    @click="currentPage = 1" title="Première page">
                                    <ChevronsLeft class="w-4 h-4" />
                                </Button>
                                <Button variant="outline" size="icon" :disabled="currentPage === 1"
                                    @click="currentPage--" title="Page précédente">
                                    <ChevronLeft class="w-4 h-4" />
                                </Button>
                                <template v-for="page in pageNumbers" :key="page">
                                    <span v-if="page === '...'" class="px-2 text-muted-foreground">…</span>
                                    <Button v-else :variant="page === currentPage ? 'default' : 'outline'" size="icon"
                                        @click="currentPage = page">
                                        {{ page }}
                                    </Button>
                                </template>
                                <Button variant="outline" size="icon" :disabled="currentPage === totalPages"
                                    @click="currentPage++" title="Page suivante">
                                    <ChevronRight class="w-4 h-4" />
                                </Button>
                                <Button variant="outline" size="icon" :disabled="currentPage === totalPages"
                                    @click="currentPage = totalPages" title="Dernière page">
                                    <ChevronsRight class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </Card>
            </main>

            <AlertDialog :open="createModal || editModal">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>{{ editModal ? 'Modifier' : 'Créer' }} une caisse</AlertDialogTitle>
                        <AlertDialogDescription>
                            <div class="space-y-4 my-4">
                                <div class="space-y-1">
                                    <Label>Code <span class="text-red-500">*</span></Label>
                                    <Input v-model="form.code" placeholder="Ex: C-101" />
                                </div>
                                <div class="space-y-1">
                                    <Label>Nom <span class="text-red-500">*</span></Label>
                                    <Input v-model="form.libelle" placeholder="Ex: Caisse principale" />
                                </div>
                                <div class="space-y-1">
                                    <Label>Site lié <span class="text-red-500">*</span></Label>
                                    <Select v-model="form.site_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Sélectionner un site" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="site in props.sites" :key="site.id" :value="site.id">
                                                {{ site.id }} – {{ site.nom_site }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter class="flex gap-2">
                        <AlertDialogCancel @click="resetModals">Annuler</AlertDialogCancel>
                        <Button @click="editModal ? handleEdit() : handleCreate()">
                            Valider
                            <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                        </Button>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

            <AlertDialog :open="deleteModal">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Supprimer la caisse ?</AlertDialogTitle>
                        <AlertDialogDescription>
                            Cette action est irréversible.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter class="flex gap-2">
                        <AlertDialogCancel @click="resetModals">Annuler</AlertDialogCancel>
                        <Button variant="destructive" @click="handleDelete">Supprimer</Button>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Search, X, ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, Plus, MoreVertical } from 'lucide-vue-next';
import { Skeleton } from '@/components/ui/skeleton';
import { Toaster, toast } from 'vue-sonner';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableHead,
    TableCell,
} from '@/components/ui/table';
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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Label } from '@/components/ui/label';

const props = defineProps({
    caisses: Array,
    sites: Array,
});

const loading = ref(false);
const currentPage = ref(1);
const perPage = 10;
const searchInput = ref('');
const activeSearch = ref('');
const createModal = ref(false);
const editModal = ref(false);
const deleteModal = ref(false);
const currentCaisseId = ref(null);

const form = ref({
    code: '',
    libelle: '',
    site_id: '',
});

const filteredCaisses = computed(() => {
    const all = props.caisses ?? [];
    if (!activeSearch.value) return all;
    const q = activeSearch.value.toLowerCase();
    return all.filter(caisse =>
        (caisse.libelle ?? '').toLowerCase().includes(q) ||
        (caisse.code ?? '').toLowerCase().includes(q)
    );
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredCaisses.value.length / perPage)));

const paginatedCaisses = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    return filteredCaisses.value.slice(start, start + perPage);
});

const pageNumbers = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
    const pages = [1];
    if (current > 3) pages.push('...');
    for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) pages.push(i);
    if (current < total - 2) pages.push('...');
    pages.push(total);
    return pages;
});

const applySearch = () => {
    activeSearch.value = searchInput.value.trim();
    currentPage.value = 1;
};

const clearSearch = () => {
    searchInput.value = '';
    activeSearch.value = '';
    currentPage.value = 1;
};

watch(() => props.caisses, () => {
    currentPage.value = 1;
});

const resetModals = () => {
    createModal.value = false;
    editModal.value = false;
    deleteModal.value = false;
    currentCaisseId.value = null;
    form.value = { code: '', libelle: '', site_id: '' };
};

const openCreateModal = () => {
    resetModals();
    createModal.value = true;
};

const openEditModal = (caisse) => {
    resetModals();
    currentCaisseId.value = caisse.id;
    form.value = {
        code: caisse.code || '',
        libelle: caisse.libelle || '',
        site_id: caisse.site_id ? String(caisse.site_id) : '',
    };
    editModal.value = true;
};

const openDeleteModal = (id) => {
    resetModals();
    currentCaisseId.value = id;
    deleteModal.value = true;
};

const handleCreate = async () => {
    if (!form.value.code || !form.value.libelle || !form.value.site_id) {
        toast.error('Veuillez remplir tous les champs requis.');
        return;
    }

    loading.value = true;
    try {
        await axios.post('/caisses', {
            code: form.value.code,
            libelle: form.value.libelle,
            site_id: Number(form.value.site_id),
        });
        toast.success('Caisse créée avec succès.');
        resetModals();
        router.reload();
    } catch (error) {
        toast.error(error?.response?.data?.message || 'Erreur lors de la création de la caisse.');
    } finally {
        loading.value = false;
    }
};

const handleEdit = async () => {
    if (!form.value.code || !form.value.libelle || !form.value.site_id) {
        toast.error('Veuillez remplir tous les champs requis.');
        return;
    }

    loading.value = true;
    try {
        await axios.put(`/caisses/${currentCaisseId.value}`, {
            code: form.value.code,
            libelle: form.value.libelle,
            site_id: Number(form.value.site_id),
        });
        toast.success('Caisse modifiée avec succès.');
        resetModals();
        router.reload();
    } catch (error) {
        toast.error(error?.response?.data?.message || 'Erreur lors de la modification de la caisse.');
    } finally {
        loading.value = false;
    }
};

const handleDelete = async () => {
    try {
        await axios.delete(`/caisses/${currentCaisseId.value}`);
        toast.success('Caisse supprimée avec succès.');
        resetModals();
        router.reload();
    } catch (error) {
        toast.error(error?.response?.data?.message || 'Erreur lors de la suppression.');
    }
};
</script>

<script>
import { Card } from '@/components/ui/card';
import Main from '/resources/js/Pages/Main.vue';

export default {
    layout: Main,
};
</script>
