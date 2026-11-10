<template>
    <div>
        <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <div>
                <h4 class="text-2xl font-bold tracking-tight">Demandes de modification</h4>
                <p class="text-sm text-muted-foreground">
                    Corrections signalées par la caisse après pré-enrôlement des dossiers.
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
                                        <SelectValue placeholder="Tous les statuts" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="0">Tous</SelectItem>
                                        <SelectItem value="1">En attente</SelectItem>
                                        <SelectItem value="2">Validée</SelectItem>
                                        <SelectItem value="3">Rejetée</SelectItem>
                                        <SelectItem value="4">Appliquée</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID</TableHead>
                                    <TableHead>VIN</TableHead>
                                    <TableHead>N° chrono</TableHead>
                                    <TableHead>Propriétaire</TableHead>
                                    <TableHead>Demandeur</TableHead>
                                    <TableHead>Champs</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Statut</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="loading">
                                    <TableCell colspan="9" class="py-8 text-center text-muted-foreground">
                                        Chargement…
                                    </TableCell>
                                </TableRow>
                                <TableRow v-else-if="demandes.length === 0">
                                    <TableCell colspan="9" class="py-8 text-center text-muted-foreground">
                                        Aucune demande trouvée.
                                    </TableCell>
                                </TableRow>
                                <template v-else>
                                    <TableRow v-for="demande in demandes" :key="demande.id">
                                        <TableCell>{{ demande.id }}</TableCell>
                                        <TableCell class="font-medium">{{ demande.vin || '—' }}</TableCell>
                                        <TableCell>{{ demande.num_chrono || '—' }}</TableCell>
                                        <TableCell>{{ demande.client || '—' }}</TableCell>
                                        <TableCell>{{ demande.demandeur }}</TableCell>
                                        <TableCell>
                                            <Badge variant="info">{{ demande.nb_champs }}</Badge>
                                        </TableCell>
                                        <TableCell>{{ demande.date_creation }}</TableCell>
                                        <TableCell>
                                            <Badge :variant="statutVariant(demande.statut)">
                                                {{ demande.statut_label }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" size="icon">
                                                        <MoreVertical class="w-4 h-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent>
                                                    <DropdownMenuItem @click="openDetails(demande.id)">
                                                        <Eye class="w-4 h-4" /> Ouvrir
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem v-if="demande.statut === 1" class="text-green-600"
                                                        @click="demanderValidation(demande.id)">
                                                        <Check class="w-4 h-4" /> Valider
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem v-if="demande.statut === 1" class="text-red-500"
                                                        @click="demanderRejet(demande.id)">
                                                        <X class="w-4 h-4" /> Rejeter
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
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

        <!-- Détail de la demande -->
        <Dialog :open="detailsOpen" @update:open="detailsOpen = $event">
            <DialogContent class="max-w-5xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Demande de modification #{{ selectedDemande?.id }}</DialogTitle>
                    <DialogDescription>
                        Les champs surlignés en orange sont ceux que la caisse souhaite faire corriger.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="detailLoading" class="py-10 text-center text-muted-foreground">
                    Chargement du dossier…
                </div>

                <div v-else-if="selectedDemande" class="space-y-6">
                    <div class="grid grid-cols-1 gap-2 rounded-lg bg-muted/40 p-4 text-sm md:grid-cols-2">
                        <p><strong>VIN :</strong> {{ detailVehicule?.vin || '—' }}</p>
                        <p><strong>N° chrono :</strong> {{ detailDossier?.num_chrono || '—' }}</p>
                        <p>
                            <strong>Propriétaire :</strong>
                            {{ detailClient?.civilite }} {{ detailClient?.nom }} {{ detailClient?.prenom }}
                        </p>
                        <p><strong>Statut :</strong> {{ selectedDemande.statut_label }}</p>
                        <p v-if="selectedDemande.message" class="md:col-span-2">
                            <strong>Message :</strong> {{ selectedDemande.message }}
                        </p>
                    </div>

                    <div v-for="section in detailSections" :key="section.key" class="space-y-3">
                        <h3 class="border-b pb-2 text-base font-semibold">{{ section.label }}</h3>
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-2 xl:grid-cols-3">
                            <div v-for="field in section.fields" :key="field.key" class="rounded-md border p-3 text-sm"
                                :class="isFieldSelected(section.key, field.key)
                                    ? 'border-orange-400 bg-orange-100 text-orange-900'
                                    : 'bg-background'">
                                <p class="text-xs font-medium text-muted-foreground">{{ field.label }}</p>
                                <p class="font-medium">{{ valueFor(section.key, field.key) || '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <DialogFooter class="mt-6 flex-row justify-between">
                    <Button variant="outline" @click="detailsOpen = false">Fermer</Button>
                    <div class="flex gap-2">
                        <Button v-if="selectedDemande?.statut === 1" variant="outline" class="text-red-500"
                            @click="demanderRejet(selectedDemande.id)">
                            <X class="w-4 h-4 mr-2" /> Rejeter
                        </Button>
                        <Button v-if="selectedDemande?.statut === 1" class="bg-green-700 hover:bg-green-800"
                            @click="demanderValidation(selectedDemande.id)">
                            <Check class="w-4 h-4 mr-2" /> Valider la demande
                        </Button>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Confirmation de validation / rejet -->
        <AlertDialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>
                        {{ confirmAction === 'valider' ? 'Valider la demande' : 'Rejeter la demande' }}
                        #{{ confirmDemandeId }}
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                        <template v-if="confirmAction === 'valider'">
                            Valider cette demande ? Le PDC pourra alors modifier les champs concernés.
                        </template>
                        <template v-else>
                            Confirmer le rejet de cette demande. Le PDC ne pourra pas appliquer les modifications.
                        </template>
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <div v-if="confirmAction === 'rejeter'" class="space-y-2 py-2">
                    <Label for="motif-rejet">Motif du rejet (facultatif)</Label>
                    <Textarea id="motif-rejet" v-model="rejetMotif" rows="3"
                        placeholder="Expliquez la raison du rejet…" />
                </div>

                <AlertDialogFooter>
                    <Button variant="outline" :disabled="processing" @click="confirmOpen = false">
                        Annuler
                    </Button>
                    <Button :disabled="processing" :class="confirmAction === 'valider'
                        ? 'bg-green-700 hover:bg-green-800 text-white'
                        : 'bg-red-600 hover:bg-red-700 text-white'" @click="confirmerAction">
                        {{ processing ? 'Traitement…' : (confirmAction === 'valider' ? 'Valider' : 'Rejeter') }}
                    </Button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import { Toaster, toast } from 'vue-sonner';
import {
    Check,
    Eye,
    MoreVertical,
    Search,
    X,
} from 'lucide-vue-next';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Textarea } from '@/components/ui/textarea';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
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
const statut = ref('0');

const detailsOpen = ref(false);
const detailLoading = ref(false);
const selectedDemande = ref(null);
const detailSections = ref([]);
const detailEntreprise = ref(null);

const confirmOpen = ref(false);
const confirmAction = ref('valider');
const confirmDemandeId = ref(null);
const rejetMotif = ref('');
const processing = ref(false);

const detailDossier = computed(() => selectedDemande.value?.dossier ?? {});
const detailVehicule = computed(() => detailDossier.value?.r_dossier_vehicule ?? {});
const detailClient = computed(() => detailDossier.value?.r_dossier_client ?? {});

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
        const { data } = await axios.get(route('get.admin.demandes.modification.data'), {
            params: {
                page: page.value,
                filtre_per_page: 10,
                search_data: activeSearch.value || undefined,
                statut: statut.value && statut.value !== '0' ? statut.value : undefined,
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

async function openDetails(id) {
    detailsOpen.value = true;
    detailLoading.value = true;
    selectedDemande.value = null;
    try {
        const { data } = await axios.get(route('show.admin.demande.modification', id));
        selectedDemande.value = data.demande;
        detailSections.value = data.sections;
        detailEntreprise.value = data.entreprise;
    } catch (error) {
        toast.error('Impossible de charger le détail de la demande.');
        detailsOpen.value = false;
        console.error(error);
    } finally {
        detailLoading.value = false;
    }
}

function isFieldSelected(sectionKey, fieldKey) {
    const key = {
        vehicule: 'champs_vehicule',
        client: 'champs_client',
        entreprise: 'champs_entreprise',
    }[sectionKey];

    return (selectedDemande.value?.[key] ?? []).includes(fieldKey);
}

function valueFor(sectionKey, fieldKey) {
    const model = sectionKey === 'vehicule'
        ? detailVehicule.value
        : sectionKey === 'client'
            ? detailClient.value
            : detailEntreprise.value ?? {};

    const value = model?.[fieldKey];
    return value === null || value === undefined ? '' : value;
}

function demanderValidation(id) {
    confirmAction.value = 'valider';
    confirmDemandeId.value = id;
    rejetMotif.value = '';
    confirmOpen.value = true;
}

function demanderRejet(id) {
    confirmAction.value = 'rejeter';
    confirmDemandeId.value = id;
    rejetMotif.value = '';
    confirmOpen.value = true;
}

async function confirmerAction() {
    const id = confirmDemandeId.value;
    if (!id) {
        return;
    }

    processing.value = true;

    try {
        if (confirmAction.value === 'valider') {
            await axios.post(route('valider.admin.demande.modification', id));
            toast.success('Demande validée. Le PDC peut maintenant modifier le dossier.');
        } else {
            await axios.post(route('rejeter.admin.demande.modification', id), {
                motif_rejet: rejetMotif.value,
            });
            toast.success('Demande rejetée.');
        }

        confirmOpen.value = false;

        if (selectedDemande.value?.id === id) {
            await openDetails(id);
        }
        await fetchDemandes();
    } catch (error) {
        toast.error(error?.response?.data?.message || "Impossible de traiter la demande.");
        console.error(error);
    } finally {
        processing.value = false;
    }
}

onMounted(fetchDemandes);
</script>

<script>
import Main from '/resources/js/Pages/Main.vue';

export default {
    layout: Main,
};
</script>
