<template>
    <div>
        <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <div>
                <h4 class="text-2xl font-bold tracking-tight">Demandes de modification</h4>
                <p class="text-sm text-muted-foreground">
                    Dossiers dont la caisse a signalé des corrections à appliquer après validation.
                </p>
            </div>
            <Toaster position="top-right" />
        </div>

        <div class="rounded-lg dark:border-gray-700">
            <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
                <Card class="h-full flex flex-col">
                    <div class="space-y-4 p-8">
                        <div class="flex flex-wrap items-end justify-between gap-4">
                            <div class="flex items-end gap-2">
                                <div class="space-y-1">
                                    <!-- <Label>Recherche</Label> -->
                                    <input v-model="searchInput" @keyup.enter="applySearch" type="text"
                                        placeholder="VIN ou numéro chrono…"
                                        class="w-72 px-3 py-2 text-sm border rounded-md bg-background focus:outline-none focus:border-muted-foreground/50 focus:ring-1 focus:ring-muted-foreground/30" />
                                </div>
                                <Button @click="applySearch">
                                    <Search class="w-4 h-4 mr-2" /> Chercher
                                </Button>
                                <Button v-if="activeSearch" variant="outline" @click="clearSearch">
                                    <X class="w-4 h-4 mr-2" /> Réinitialiser
                                </Button>
                            </div>

                            <div class="w-48 space-y-1">
                                <!-- <Label>Statut</Label> -->
                                <Select v-model="statut" @update:modelValue="applySearch">
                                    <SelectTrigger>
                                        <SelectValue placeholder="À traiter" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="2">À traiter</SelectItem>
                                        <SelectItem value="4">Appliquée</SelectItem>
                                        <SelectItem value="0">Toutes</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>VIN</TableHead>
                                    <TableHead>N° chrono</TableHead>
                                    <TableHead>Marque</TableHead>
                                    <TableHead>Propriétaire</TableHead>
                                    <TableHead>Champs</TableHead>
                                    <TableHead>Validée le</TableHead>
                                    <TableHead>Statut</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="loading">
                                    <TableCell colspan="8" class="py-8 text-center text-muted-foreground">
                                        Chargement…
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else-if="demandes.length === 0">
                                    <TableCell colspan="8" class="py-8 text-center text-muted-foreground">
                                        Aucune demande de modification trouvée.
                                    </TableCell>
                                </TableRow>
                                <template v-else>
                                    <TableRow v-for="demande in demandes" :key="demande.id">
                                        <TableCell class="font-medium">{{ demande.vin || '—' }}</TableCell>
                                        <TableCell>{{ demande.num_chrono || '—' }}</TableCell>
                                        <TableCell>{{ demande.marque || '—' }}</TableCell>
                                        <TableCell>{{ demande.client || '—' }}</TableCell>
                                        <TableCell>
                                            <Badge variant="info">{{ demande.nb_champs }}</Badge>
                                        </TableCell>
                                        <TableCell>{{ demande.date_validation || '—' }}</TableCell>
                                        <TableCell>
                                            <Badge :variant="statutVariant(demande.statut)">
                                                {{ demande.statut_label }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <Button v-if="demande.statut === 2" size="sm"
                                                @click="modifierDemande(demande.dossier_id)">
                                                <Pen class="w-4 h-4 mr-2" /> Modifier
                                            </Button>
                                            <span v-else class="text-xs text-muted-foreground">
                                                {{ demande.date_application ? `Appliquée le ${demande.date_application}`
                                                : '—' }}
                                            </span>
                                        </TableCell>
                                    </TableRow>
                                </template>
                            </TableBody>
                        </Table>

                        <Pagination v-if="meta" :meta="meta" @page-change="changePage" />
                    </div>
                </Card>
            </main>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { Toaster, toast } from 'vue-sonner';
import { Pen, Search, X } from 'lucide-vue-next';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import Pagination from '@/components/Pagination.vue';

const demandes = ref([]);
const meta = ref(null);
const loading = ref(false);
const page = ref(1);
const searchInput = ref('');
const activeSearch = ref('');
const statut = ref('2');

function statutVariant(statutDemande) {
    if (statutDemande === 1) return 'warning';
    if (statutDemande === 2) return 'success';
    if (statutDemande === 3) return 'error';
    if (statutDemande === 4) return 'info';
    return 'secondary';
}

async function fetchDemandes() {
    loading.value = true;
    try {
        const { data } = await axios.get(route('get.pdc.demandes.modification.data'), {
            params: {
                page: page.value,
                filtre_per_page: 10,
                search_data: activeSearch.value || undefined,
                statut: statut.value,
            },
        });
        demandes.value = data.demandes?.data ?? [];
        meta.value = data.demandes ?? null;
    } catch (error) {
        toast.error('Impossible de charger les demandes de modification.');
        console.error(error);
    } finally {
        loading.value = false;
    }
}

function applySearch() {
    activeSearch.value = searchInput.value.trim();
    page.value = 1;
    fetchDemandes();
}

function clearSearch() {
    searchInput.value = '';
    activeSearch.value = '';
    page.value = 1;
    fetchDemandes();
}

function changePage(newPage) {
    page.value = newPage;
    fetchDemandes();
}

function modifierDemande(dossierId) {
    if (!dossierId) {
        toast.error('Dossier introuvable pour cette demande.');
        return;
    }

    router.visit(route('show.pdc.demande.modification.edit', dossierId));
}

onMounted(fetchDemandes);
</script>

<script>
import Main from '/resources/js/Pages/Main.vue';

export default {
    layout: Main,
};
</script>
