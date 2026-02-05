<template>
    <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <!-- Titre -->
        <h4 class="text-2xl font-bold tracking-tight">
            Duplicata
        </h4>

        <!-- Bouton Nouveau -->
        <div class="flex items-center space-x-2">
            <Link :href="route('show.pdc.duplicata.select')">
            <BoutonRetour />
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
                        <Select v-model="form.statut" @update:modelValue="onFilterChange">
                            <SelectTrigger class="w-40">
                                <SelectValue placeholder="Statut" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectLabel>Statut</SelectLabel>
                                    <SelectItem value="0">Tous</SelectItem>
                                    <SelectItem value="1">En attente</SelectItem>
                                    <SelectItem value="2">Validé</SelectItem>
                                    <SelectItem value="3">Refusé</SelectItem>
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
                                <TableHead>Changement</TableHead>
                                <TableHead>Num chrono</TableHead>
                                <TableHead>Date</TableHead>
                                <TableHead>Statut</TableHead>
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

                            <!-- Données -->
                            <template v-else>
                                <TableRow v-for="(dossier, index) in dossiers" :key="index">
                                    <TableCell>{{ index + 1 }}</TableCell>
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
                                        <Badge :variant="dossier?.statut === 1
                                            ? 'warning'
                                            : dossier?.statut === 2
                                                ? 'success'
                                                : dossier?.statut === 3
                                                    ? 'error'
                                                    : dossier?.statut === 4
                                                        ? 'info'
                                                        : 'secondary'">
                                            {{
                                                dossier?.statut === 1
                                                    ? 'En attente'
                                                    : dossier?.statut === 2
                                                        ? 'Validé'
                                                        : dossier?.statut === 3
                                                            ? 'Refusé'
                                                            : dossier?.statut === 4
                                                                ? 'En cours de traitement'
                                                                : '—'
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
                                                <!-- <Link :href="route('show.view.pdc.duplicata', dossier.id)">
                                                <DropdownMenuItem>
                                                    <Eye class=" w-4 h-4 " />Détails
                                                </DropdownMenuItem> -->
                                                <!-- </Link> -->
                                                <Link :href="route('show.receipt.pdc.duplicata', dossier.id)">
                                                <DropdownMenuItem>
                                                    <ReceiptText class=" w-4 h-4 " />Recépissé
                                                </DropdownMenuItem>
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






</template>

<script setup>
import { onMounted, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { Input } from '@/components/ui/input'
import Pagination from '@/components/Pagination.vue'
import { Input_search } from '@/components/ui/Input_search'
import { MoveRight, MoveLeft, Plus, Eye, Pen, ReceiptText } from 'lucide-vue-next'

const loading = ref(false)
// Pagination
const dossiers = ref([])
const meta = ref(null)
const page = ref(1)
const perPage = ref(10)

// Initialisation du formulaire avec les paramètres d'URL
function getURLParams() {
    const params = new URLSearchParams(window.location.search)
    return {
        search_data: params.get('search_data') || '',
        statut: params.get('statut') || '',
        date_start: params.get('date_start') || '',
        date_end: params.get('date_end') || '',
        page: parsePageParam(params.get('page')),
    }
}

function parsePageParam(value) {
    const p = parseInt(value)
    return (!isNaN(p) && p > 0) ? p : 1
}

const initialParams = getURLParams()

const form = useForm({
    search_data: initialParams.search_data,
    statut: initialParams.statut,
    date_start: initialParams.date_start,
    date_end: initialParams.date_end,
})

page.value = initialParams.page

// URL dans l'historique
function updateURL() {
    const params = new URLSearchParams()
    if (form.search_data) params.set('search_data', form.search_data)
    if (form.statut) params.set('statut', form.statut)
    if (form.date_start) params.set('date_start', form.date_start)
    if (form.date_end) params.set('date_end', form.date_end)
    if (!isNaN(page.value) && page.value > 0) {
        params.set('page', page.value)
    }
    //history.pushState(null, '', `?${params.toString()}`)
}

// Récupération des données
async function fetchDossiers() {
    loading.value = true

    // // Attendre 2 secondes avant d'exécuter le fetch
    // await new Promise(resolve => setTimeout(resolve, 2000))

    const url = new URL('/pdc/duplicata/data', window.location.origin)
    url.searchParams.set('filtre_per_page', perPage.value)
    url.searchParams.set('page', page.value)
    if (form.search_data) url.searchParams.set('search_data', form.search_data)
    if (form.statut) url.searchParams.set('statut', form.statut)
    if (form.date_start) url.searchParams.set('date_start', form.date_start)
    if (form.date_end) url.searchParams.set('date_end', form.date_end)

    try {
        const res = await fetch(url.toString())
        const json = await res.json()
        dossiers.value = json.dossiers?.data || []
        meta.value = json.dossiers || null
        updateURL()
    } catch (error) {
        console.error('Erreur lors de la récupération des dossiers :', error)
    } finally {
        loading.value = false
    }
}



// Filtres
function onFilterChange() {
    page.value = 1
    fetchDossiers()
}

function changePage(p) {
    const newPage = parseInt(p)
    if (!isNaN(newPage) && newPage > 0) {
        page.value = newPage
        fetchDossiers()
    }
}

// Gestion navigation arrière
window.addEventListener('popstate', () => {
    const params = new URLSearchParams(window.location.search)
    page.value = parsePageParam(params.get('page'))
    form.search_data = params.get('search_data') || ''
    form.statut = params.get('statut') || ''
    form.date_start = params.get('date_start') || ''
    form.date_end = params.get('date_end') || ''
    fetchDossiers()
})

// Initial fetch
onMounted(() => {
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
import { Skeleton } from '@/components/ui/skeleton'
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
import BoutonRetour from '@/components/BoutonRetour.vue'

export default {
    layout: Main,
    components: { Pagination },
};
</script>