<template>
    <div v-if="isOpen">
        <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <!-- Titre -->
            <h4 class="text-2xl font-bold tracking-tight">
                Dossiers Paiements
            </h4>

            <!-- Bouton Nouveau -->
            <div class="flex items-center space-x-2">
                <Link :href="route('show.new.caisse')">
                <Button>
                    <Plus class=" w-4 h-4 mr-2" /> Nouveau Paiement
                </Button>
                </Link>
            </div>
        </div>

        <div class="rounded-lg dark:border-gray-700">
            <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
                <Card class="h-full flex flex-col">
                    <div class="space-y-4 p-8">
                        <!-- Filtres -->
                        <div class="flex flex-wrap gap-4 items-center justify-between">
                            <!-- Recherche -->
                            <Input_search v-model="form.search_data" @update:modelValue="onFilterChange"
                                placeholder="Rechercher par VIN..." class="w-full " />
                            <!-- Statut -->
                            <Select v-model="form.statut_paiement" @update:modelValue="onFilterChange">
                                <SelectTrigger class="w-40">
                                    <SelectValue placeholder="Statut" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Statut</SelectLabel>
                                        <SelectItem value="0">Tous</SelectItem>
                                        <SelectItem value="1">En attente</SelectItem>
                                        <SelectItem value="2">Paiement validé</SelectItem>
                                        <!-- <SelectItem value="3">Refusé</SelectItem> -->
                                    </SelectGroup>
                                </SelectContent>
                            </Select>

                            <!-- Dates -->
                            <!-- <DateRangePicker /> -->
                            <DateRangePicker v-model="form.dateRange"
                                @update:start="val => { form.date_start = val; onFilterChange(); }"
                                @update:end="val => { form.date_end = val; onFilterChange(); }" />
                        </div>

                        <!-- Tableau -->
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID</TableHead>
                                    <TableHead>Vin</TableHead>
                                    <TableHead>Véhicule</TableHead>
                                    <TableHead>Type de service</TableHead>
                                    <TableHead>Num chrono</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Statut Paiement</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <!-- Skeleton -->
                                <template v-if="loading">
                                    <TableRow v-for="i in 10" :key="i">
                                        <TableCell class="p-4">
                                            <Skeleton class="h-4 w-8 " />
                                        </TableCell>
                                        <TableCell>
                                            <Skeleton class="h-4 w-24 " />
                                        </TableCell>
                                        <TableCell>
                                            <Skeleton class="h-4 w-20 " />
                                        </TableCell>
                                        <TableCell>
                                            <Skeleton class="h-4 w-28 " />
                                        </TableCell>
                                        <TableCell>
                                            <Skeleton class="h-4 w-24 " />
                                        </TableCell>
                                        <TableCell>
                                            <Skeleton class="h-4 w-24 " />
                                        </TableCell>
                                        <TableCell>
                                            <Skeleton class="h-4 w-20 " />
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <Skeleton class="h-4 w-10 ml-auto" />
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <template v-else>
                                    <TableRow v-for="(dossier, index) in dossiers" :key="index">
                                        <TableCell>{{ dossier.id }}</TableCell>
                                        <TableCell>{{ dossier?.r_dossier_vehicule?.vin || '—' }}</TableCell>
                                        <TableCell>{{ dossier?.r_dossier_vehicule?.marque || '—' }}</TableCell>
                                        <TableCell>
                                            <template v-if="dossier.detail && Array.isArray(dossier.detail)">
                                                {{ dossier.detail.slice(0, 2).join(', ') }}<span
                                                    v-if="dossier.detail.length > 2">...</span>
                                            </template>

                                            <template v-else-if="dossier.detail && typeof dossier.detail === 'string'">
                                                {{
                                                    JSON.parse(dossier.detail).slice(0, 2).join(', ')
                                                }}<span v-if="JSON.parse(dossier.detail).length > 2">...</span>
                                            </template>
                                        </TableCell>
                                        <TableCell>{{ dossier?.num_chrono || '—' }}</TableCell>
                                        <TableCell>{{ dossier?.date_creation || '—' }}</TableCell>

                                        <TableCell>
                                            <Badge
                                                :variant="dossier?.statut_paiement === 1 ? 'warning' : dossier?.statut_paiement === 2 ? 'success' : dossier?.statut_paiement === 3 ? 'error' : 'secondary'">
                                                {{
                                                    dossier?.statut_paiement === 1
                                                        ? 'En attente de paiement'
                                                        : dossier?.statut_paiement === 2
                                                            ? 'Paiement validé'
                                                            : dossier?.statut_paiement === 3
                                                                ? 'Refusé'
                                                                : 'Inconnu'
                                                }}
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
                                                    <Link v-if="dossier?.statut_paiement == 1"
                                                        :href="route('show.new.caisse.get.data', dossier.num_chrono)">
                                                    <DropdownMenuItem>Nouveau paiement</DropdownMenuItem>
                                                    </Link>
                                                    <Link v-if="dossier?.statut_paiement != 1"
                                                        :href="route('paiement.receipt', dossier.num_chrono)">
                                                    <DropdownMenuItem>Reçu de paiement</DropdownMenuItem>
                                                    </Link>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                </template>
                            </TableBody>
                        </Table>
                        <!-- Pagination -->
                        <Pagination v-if="meta" :meta="meta" @page-change="changePage" />

                    </div>
                </Card>
            </main>
        </div>

    </div>


    <div v-else>
        <Control />
    </div>



</template>

<script setup>
import { onMounted, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { Input } from '@/components/ui/input'
import Pagination from '@/components/Pagination.vue'
import { Input_search } from '@/components/ui/Input_search'
import { MoveRight, MoveLeft, Plus } from 'lucide-vue-next'
import Control from "../Caisse/components/Control.vue";
import { Skeleton } from '@/components/ui/skeleton'

import { storeToRefs } from "pinia";
import { useCaisseStore } from "@/stores/mainStore";


const loading = ref(false)
const store = useCaisseStore();
const { isOpen } = storeToRefs(store);
// Pagination
const dossiers = ref([])
const meta = ref(null)
const page = ref(1)
const perPage = ref(10)

// Initialisation du formulaire avec les paramètres d'URL
const params = new URLSearchParams(window.location.search)
const form = useForm({
    search_data: params.get('search_data') || '',
    statut_paiement: params.get('statut_paiement') || '',
    date_start: params.get('date_start') || '',
    date_end: params.get('date_end') || ''
})

// URL dans l'historique
function updateURL() {
    const params = new URLSearchParams()
    if (form.search_data) params.set('search_data', form.search_data)
    if (form.statut_paiement) params.set('statut_paiement', form.statut_paiement)
    if (form.date_start) params.set('date_start', form.date_start)
    if (form.date_end) params.set('date_end', form.date_end)
    if (page.value) params.set('page', page.value)
    // history.pushState(null, '', `?${params.toString()}`)
}

// Récupération des données
async function fetchDossiers() {
    loading.value = true
    const url = new URL('/caisse/get/data', window.location.origin)
    url.searchParams.set('filtre_per_page', perPage.value)
    url.searchParams.set('page', page.value)
    if (form.search_data) url.searchParams.set('search_data', form.search_data)
    if (form.statut_paiement) url.searchParams.set('statut_paiement', form.statut_paiement)
    if (form.date_start) url.searchParams.set('date_start', form.date_start)
    if (form.date_end) url.searchParams.set('date_end', form.date_end)

    const res = await fetch(url.toString())
    const json = await res.json()
    dossiers.value = json.dossiers.data
    meta.value = json.dossiers
    updateURL()
    loading.value = false
}

// Filtres
function onFilterChange() {
    page.value = 1
    fetchDossiers()
}

function changePage(p) {
    page.value = p
    fetchDossiers()
}

// Gestion navigation arrière
window.addEventListener('popstate', () => {
    const params = new URLSearchParams(window.location.search)
    // page.value = parseInt(params.get('page')) || 1
    form.search_data = params.get('search_data') || ''
    form.statut_paiement = params.get('statut_paiement') || ''
    form.date_start = params.get('date_start') || ''
    form.date_end = params.get('date_end') || ''
    fetchDossiers()
})

// Initial fetch
onMounted(() => {
    // page.value = parseInt(params.get('page')) || 1
    fetchDossiers()
})
</script>

<script>
import { ref, onMounted, watch } from 'vue'
import { Card, CardContent } from '@/components/ui/card'

import Main from '/resources/js/Pages/Main.vue';
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import DateRangePicker from '@/components/ui/DateRangePicker.vue'
import Pagination from '@/components/Pagination.vue';
import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableHead,
    TableCell,
} from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { MoreVertical } from 'lucide-vue-next'
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import BoutonNouveau from "/resources/js/components/BoutonNouveau.vue";

export default {
    layout: Main,
    components: { Pagination },
};
</script>