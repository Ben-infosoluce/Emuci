<template>
    <div>
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

        <main class="p-4 md:p-8">
            <Card>
                <div class="space-y-4 p-8">
                    <!-- Filtres -->
                    <div class="flex flex-wrap gap-4 items-center justify-between">
                        <Input_search v-model="form.search_data" @update:modelValue="onFilterChange"
                            placeholder="Rechercher par nom..." class="w-full" />
                        <Select v-model="form.statut" @update:modelValue="onFilterChange">
                            <SelectTrigger class="w-40">
                                <SelectValue placeholder="Statut" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectLabel>Statut</SelectLabel>
                                    <SelectItem value="">Tous</SelectItem>
                                    <SelectItem value="1">Actif</SelectItem>
                                    <SelectItem value="2">Inactif</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Tableau -->
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Nom</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Région</TableHead>
                                <TableHead>Ouverture</TableHead>
                                <TableHead>Fermeture</TableHead>
                                <TableHead>Statut</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="loading">
                                <TableRow v-for="i in 10" :key="i">
                                    <TableCell>
                                        <Skeleton class="h-4 w-8" />
                                    </TableCell>
                                    <TableCell>
                                        <Skeleton class="h-4 w-24" />
                                    </TableCell>
                                    <TableCell>
                                        <Skeleton class="h-4 w-20" />
                                    </TableCell>
                                    <TableCell>
                                        <Skeleton class="h-4 w-28" />
                                    </TableCell>
                                    <TableCell>
                                        <Skeleton class="h-4 w-24" />
                                    </TableCell>
                                    <TableCell>
                                        <Skeleton class="h-4 w-24" />
                                    </TableCell>
                                    <TableCell>
                                        <Skeleton class="h-4 w-20" />
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Skeleton class="h-4 w-10 ml-auto" />
                                    </TableCell>
                                </TableRow>
                            </template>
                            <template v-else>
                                <TableRow v-for="site in props.sites.data" :key="site.id">
                                    <TableCell>{{ site.id }}</TableCell>
                                    <TableCell>{{ site.nom_site || '—' }}</TableCell>
                                    <TableCell>{{ site.type_site || '—' }}</TableCell>
                                    <TableCell>{{ site.region || '—' }}</TableCell>
                                    <TableCell>{{ site.heures_ouverture || '—' }}</TableCell>
                                    <TableCell>{{ site.heures_fermeture || '—' }}</TableCell>
                                    <TableCell>
                                        <Badge
                                            :variant="site.statut === 1 ? 'success' : site.statut === 2 ? 'error' : 'secondary'">
                                            {{ site.statut === 1 ? 'Actif' : site.statut === 2 ? 'Inactif' : 'Inconnu'
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
                                                <DropdownMenuItem @click="openEditSite(site.id)">Modifier
                                                </DropdownMenuItem>
                                                <DropdownMenuItem class="text-red-500" @click="openDeleteSite(site.id)">
                                                    Supprimer</DropdownMenuItem>
                                                <DropdownMenuItem @click="openStatutModal(site.statut, site.id)">Statut
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                            </template>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div class="mt-4 flex justify-center">
                        <!-- <nav v-if="props.sites.links.length > 3" class="inline-flex -space-x-px rounded-md shadow-sm">
                            <template v-for="(link, key) in props.sites.links" :key="key">
                                <button v-if="link.url"
                                    @click="router.get(link.url, {}, { preserveState: true, preserveScroll: true })"
                                    class="px-3 py-1 border text-sm"
                                    :class="{ 'bg-blue-600 text-white': link.active, 'text-gray-500': !link.active }"
                                    v-html="link.label" />
                                <span v-else v-html="link.label" class="px-3 py-1 border text-sm text-gray-400" />
                            </template>
                        </nav> -->
                    </div>
                </div>
            </Card>
        </main>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { Input_search } from '@/components/ui/Input_search'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Toaster, toast } from 'vue-sonner'
import { Skeleton } from '@/components/ui/skeleton'
import { Plus, MoreVertical } from 'lucide-vue-next'

const props = defineProps({
    sites: Object,
    filters: Object
})

const loading = ref(false)

const nom_site = ref('')
const type_site = ref('')
const region = ref('')
const heures_ouverture = ref('')
const heures_fermeture = ref('')
const newStatut = ref(null)
const siteId = ref(null)

const openCreateSiteModal = ref(false)
const openEditModal = ref(false)
const openDeleteModal = ref(false)
const statutModal = ref(false)

const statuts = [
    { id: 1, nom: 'Actif' },
    { id: 2, nom: 'Inactif' }
]

const form = reactive({
    search_data: props.filters?.name || '',
    statut: props.filters?.status || ''
})

function onFilterChange() {
    loading.value = true
    router.get(route('admin.sites'), {
        name: form.search_data,
        status: form.statut
    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => loading.value = false
    })
}

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
        await axios.post('/site/new', {
            nom_site: nom_site.value,
            type_site: type_site.value,
            region: region.value,
            heures_ouverture: `${heures_ouverture.value}:00`,
            heures_fermeture: `${heures_fermeture.value}:00`
        })
        toast.success('Site créé avec succès')
        router.reload()
    } catch (e) {
        toast.error("Erreur lors de la création du site")
    } finally {
        loading.value = false
        openCreateSiteModal.value = false
    }
}


</script>


<script>
import { ref, onMounted, watch } from "vue";
import { Card, CardContent } from "@/components/ui/card";

import Main from "/resources/js/Pages/Main.vue";
import { Button } from "@/components/ui/button";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import DateRangePicker from "@/components/ui/DateRangePicker.vue";
import Pagination from "@/components/Pagination.vue";
import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableHead,
    TableCell,
} from "@/components/ui/table";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { MoreVertical } from "lucide-vue-next";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
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
    // components: { Pagination },
};
</script>
