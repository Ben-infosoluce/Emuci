<template>
    <!-- Titre + bouton création -->
    <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <h4 class="text-2xl font-bold tracking-tight">Liste des sites</h4>
        <Toaster position="top-right" />

        <div class="flex items-center space-x-2">
            <AlertDialog :open="openCreateSiteModal">
                <AlertDialogTrigger as-child>
                    <Button @click="openCreateSite">
                        <Plus class="w-4 h-4 mr-2" /> Nouveau Site
                    </Button>
                </AlertDialogTrigger>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Nouveau Site</AlertDialogTitle>
                        <AlertDialogDescription>
                            <Input v-model="nom_site" class="my-8" placeholder="Nom du site" />
                            <div class="flex gap-4">
                                <Input v-model="type_site" class="my-8" placeholder="Type de site" />
                                <Input v-model="region" class="my-8" placeholder="Région" />
                            </div>
                            <div class="flex gap-4">
                                <Input v-model="heures_ouverture" class="my-8" type="time"
                                    placeholder="Heure d'ouverture" />
                                <Input v-model="heures_fermeture" class="my-8" type="time"
                                    placeholder="Heure de fermeture" />
                            </div>
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter class="flex flex-row gap-2">
                        <AlertDialogCancel @click="openCreateSiteModal = false">Annuler</AlertDialogCancel>
                        <Button @click="handleCreateSite">Valider <span v-if="loading"
                                class="ml-2 animate-spin">⏳</span></Button>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
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
                        <!-- <Select v-model="form.statut" @update:modelValue="onFilterChange">
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
                        </Select> -->
                    </div>

                    <!-- Tableau -->
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Nom Site</TableHead>
                                <TableHead>Type de site</TableHead>
                                <TableHead>Région</TableHead>
                                <!-- <TableHead>Statut</TableHead> -->
                                <TableHead>Heure d'ouverture</TableHead>
                                <TableHead>Heure de fermeture</TableHead>
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
                                <TableRow v-for="(site, index) in sites" :key="index">
                                    <TableCell>{{ index + 1 }}</TableCell>
                                    <TableCell>{{ site?.nom_site || '—' }}</TableCell>
                                    <TableCell>{{ site?.type_site || '—' }}</TableCell>
                                    <TableCell>{{ site?.region || '—' }}</TableCell>
                                    <TableCell>{{ site?.heures_ouverture || '—' }}</TableCell>
                                    <TableCell>{{ site?.heures_fermeture || '—' }}</TableCell>
                                    <!-- <TableCell>
                                        <Badge
                                            :variant="site?.statut === 1 ? 'warning' : site?.statut === 2 ? 'success' : site?.statut === 3 ? 'error' : 'secondary'">
                                            {{
                                                site?.statut === 1
                                                    ? 'En attente'
                                                    : site?.statut === 2
                                                        ? 'Validé'
                                                        : site?.statut === 3
                                                            ? 'Refusé'
                                                            : 'Inconnu'
                                            }}
                                        </Badge>
                                    </TableCell> -->

                                    <TableCell class="text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon">
                                                    <MoreVertical class="w-4 h-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent>
                                                <DropdownMenuItem @click="openEditSite(site.id)">Modifier
                                                </DropdownMenuItem>
                                                <DropdownMenuItem class="text-red-500" @click="openDeleteSite(site.id)">
                                                    Supprimer</DropdownMenuItem>
                                                <!-- <DropdownMenuItem @click="openStatutModal(site.statut, site.id)">Statut
                                                </DropdownMenuItem> -->
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

        <!-- Edit Modal -->
        <div class="flex items-center space-x-2">
            <AlertDialog :open="openEditModal">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            Modifier le site</AlertDialogTitle>

                        <AlertDialogDescription>
                            <Input v-model="nom_site" class="my-8" placeholder="Nom du site" />
                            <div class="flex gap-4">
                                <Input v-model="type_site" class="my-8" placeholder="Type de site" />
                                <Input v-model="region" class="my-8" placeholder="Region" />
                            </div>
                            <div class="flex gap-4">
                                <Input v-model="heures_ouverture" class="my-8" placeholder="Heure d'ouverture"
                                    type="time" />
                                <Input v-model="heures_fermeture" class="my-8" placeholder="Heure de fermeture"
                                    type="time" />
                            </div>
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter class="flex flex-row gap-2">
                        <AlertDialogCancel @click="openEditModal = false">
                            Annuler
                        </AlertDialogCancel>
                        <div>
                            <Button @click="handleEditSite">
                                Valider
                                <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                            </Button>
                        </div>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>

        <!-- Delete Modal -->
        <div class="flex items-center space-x-2">
            <AlertDialog :open="openDeleteModal">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            Voulez-vous vraiment supprimer ce site ?
                        </AlertDialogTitle>

                        <AlertDialogDescription>
                            <p>Cette action est irréversible.</p>
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter class="flex flex-row gap-2">
                        <AlertDialogCancel @click="openDeleteModal = false">
                            Annuler
                        </AlertDialogCancel>
                        <div>
                            <Button @click="handleDeleteSite" variant="destructive">
                                Supprimer
                                <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                            </Button>
                        </div>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>

        <!-- Statut Modal -->
        <div class="flex items-center space-x-2">
            <AlertDialog :open="statutModal">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            Voulez-vous vraiment changer le statut de cette
                            entité ?
                        </AlertDialogTitle>

                        <AlertDialogDescription>
                            <Select v-model="newStatut" class="my-8" placeholder="Statut">
                                <SelectTrigger>
                                    <SelectValue placeholder="Statut" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Site</SelectLabel>
                                        <SelectItem v-for="statut in statuts" :key="statut.id" :value="statut.id">
                                            {{ statut.nom }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter class="flex flex-row gap-2">
                        <AlertDialogCancel @click="statutModal = false">
                            Annuler
                        </AlertDialogCancel>
                        <div>
                            <Button @click="handleStatut">
                                Changer le statut
                                <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                            </Button>
                        </div>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    </div>








</template>

<script setup>
import { onMounted, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { Input } from '@/components/ui/input'
import Pagination from '@/components/Pagination.vue'
import { Input_search } from '@/components/ui/Input_search'
import { MoveRight, MoveLeft, Plus, Eye, Pen, ReceiptText, MoreVertical } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Toaster, toast } from 'vue-sonner'
import { Skeleton } from '@/components/ui/skeleton'
const loading = ref(false)
// Pagination
const sites = ref([])
const meta = ref(null)
const page = ref(1)
const perPage = ref(10)

//create new
const nom_site = ref('')
const type_site = ref('')
const region = ref('')
const heures_ouverture = ref('')
const heures_fermeture = ref('')
const newStatut = ref(null)
const siteId = ref(null)
//
const openCreateSiteModal = ref(false)
const openEditModal = ref(false)
const openDeleteModal = ref(false)
const statutModal = ref(false)

const statuts = [
    { id: 1, nom: 'Actif' },
    { id: 2, nom: 'Inactif' }
]
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
})

page.value = initialParams.page

// URL dans l'historique
function updateURL() {
    const params = new URLSearchParams()
    if (form.search_data) params.set('search_data', form.search_data)
    if (form.statut) params.set('statut', form.statut)
    if (!isNaN(page.value) && page.value > 0) {
        params.set('page', page.value)
    }
}

// Récupération des données
async function fetchSites() {
    loading.value = true
    const url = new URL('/site/get/data', window.location.origin)
    url.searchParams.set('filtre_per_page', perPage.value)
    url.searchParams.set('page', page.value)
    if (form.search_data) url.searchParams.set('search_data', form.search_data)
    if (form.statut) url.searchParams.set('statut', form.statut)


    try {
        const res = await fetch(url.toString())
        const json = await res.json()
        sites.value = json.sites?.data || []
        console.log('Sites récupérés :', sites.value)
        meta.value = json.sites || null
        updateURL()
    } catch (error) {
        console.error('Erreur lors de la récupération des sites :', error)
    } finally {
        loading.value = false
    }
}

// Filtres
function onFilterChange() {
    page.value = 1
    fetchSites()
}

function changePage(p) {
    const newPage = parseInt(p)
    if (!isNaN(newPage) && newPage > 0) {
        page.value = newPage
        fetchSites()
    }
}

// Gestion navigation arrière
window.addEventListener('popstate', () => {
    const params = new URLSearchParams(window.location.search)
    page.value = parsePageParam(params.get('page'))
    form.search_data = params.get('search_data') || ''
    form.statut = params.get('statut') || ''
    fetchSites()
})


const openCreateSite = () => {
    nom_site.value = ''
    type_site.value = ''
    region.value = ''
    heures_ouverture.value = ''
    heures_fermeture.value = ''
    openCreateSiteModal.value = true
}

const openEditSite = async (id) => {
    const res = await axios.get(`/site/edit/${id}`)
    nom_site.value = res.data.nom_site
    type_site.value = res.data.type_site
    region.value = res.data.region
    heures_ouverture.value = res.data.heures_ouverture
    heures_fermeture.value = res.data.heures_fermeture
    siteId.value = id
    openEditModal.value = true
}

const openDeleteSite = (id) => {
    siteId.value = id
    openDeleteModal.value = true
}

const openStatutModal = (statut, id) => {
    newStatut.value = statut
    siteId.value = id
    statutModal.value = true
}

const handleCreateSite = async () => {
    if (!nom_site.value || !type_site.value || !region.value || !heures_ouverture.value || !heures_fermeture.value) {
        toast.error('Veuillez remplir tous les champs')
        return
    }
    loading.value = true
    try {
        await axios.post('/add/site/new', {
            nom_site: nom_site.value,
            type_site: type_site.value,
            region: region.value,
            heures_ouverture: `${heures_ouverture.value}:00`,
            heures_fermeture: `${heures_fermeture.value}:00`
        })
        toast.success('Site créé avec succès')
        fetchSites()
    } catch (e) {
        toast.error("Erreur lors de la création du site")
    } finally {
        loading.value = false
        openCreateSiteModal.value = false
    }
}


const handleEditSite = async () => {
    if (
        nom_site.value === "" ||
        type_site.value === "" ||
        region.value === "" ||
        heures_ouverture.value === "" ||
        heures_fermeture.value === ""
    ) {
        toast.error("Veuillez remplir tous les champs");
        return;
    }
    loading.value = true;
    try {
        await axios.put(`/site/update/${siteId.value}`, {
            nom_site: nom_site.value,
            type_site: type_site.value,
            region: region.value,
            heures_ouverture: `${heures_ouverture.value}:00`,
            heures_fermeture: `${heures_fermeture.value}:00`,
        });
        toast.success("Site modifié avec succès");
        fetchSites();
    } catch (error) {
        toast.error("Une erreur est survenue lors de la modification du site");
        openEditModal.value = false;
        loading.value = false;
        return;
    } finally {
        openEditModal.value = false;
        loading.value = false;
    }
};



const handleStatut = async () => {
    if (siteId.value) {
        loading.value = true;
        try {
            await axios.put(`/site/update/statut/${siteId.value}`, {
                statut: newStatut.value,
            });
            toast.success("Statut mis à jour avec succès");
            fetchSites();
        } catch (error) {
            toast.error(
                "Une erreur est survenue lors de la mise à jour du statut"
            );
            return;
        } finally {
            statutModal.value = false;
            loading.value = false;
        }
    }
};

// Initial fetch
onMounted(() => {
    fetchSites()
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
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
export default {
    layout: Main,
    components: { Pagination },
};
</script>