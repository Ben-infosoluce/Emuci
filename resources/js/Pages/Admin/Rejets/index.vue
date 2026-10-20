<template>
    <div>
        <!-- Titre + bouton création -->
        <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <h4 class="text-2xl font-bold tracking-tight">Liste des rejets</h4>
            <Toaster position="top-right" />

            <Button @click="openCreateModal">
                <Plus class="w-4 h-4 mr-2" /> Ajouter un rejet
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
                                    placeholder="Rechercher un rejet…"
                                    class="w-80 px-3 py-2 text-sm border rounded-md bg-background focus:outline-none focus:border-muted-foreground/50 focus:ring-1 focus:ring-muted-foreground/30" />
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
                                    <TableHead>Motif</TableHead>
                                    <TableHead>Service</TableHead>
                                    <TableHead>Type de service</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="(rejet, index) in paginatedRejets" :key="rejet.id">
                                    <TableCell>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</TableCell>
                                    <TableCell>{{ rejet.motif }}</TableCell>
                                    <TableCell>
                                        <Badge variant="info">{{ getServiceName(rejet.service_id) }}</Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="success">{{ getTypeServiceName(rejet.id_type_services) }}</Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon">
                                                    <MoreVertical class="w-4 h-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent>
                                                <DropdownMenuItem @click="openEditModal(rejet)">Modifier</DropdownMenuItem>
                                                <DropdownMenuItem class="text-red-500"
                                                    @click="openDeleteModal(rejet.id)">
                                                    Supprimer
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="paginatedRejets.length === 0">
                                    <TableCell colspan="5" class="text-center py-8 text-muted-foreground">
                                        Aucun rejet trouvé
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>

                        <!-- Pagination -->
                        <div class="flex items-center justify-between pt-4 border-t">
                            <p class="text-sm text-muted-foreground">
                                {{ filteredRejets.length === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1 }}–{{
                                    Math.min(currentPage * itemsPerPage, filteredRejets.length) }}
                                sur {{ filteredRejets.length }} rejet(s)
                            </p>
                            <div class="flex items-center gap-1">
                                <Button variant="outline" size="icon" :disabled="currentPage === 1"
                                    @click="goToPage(1)" title="Première page">
                                    <ChevronsLeft class="w-4 h-4" />
                                </Button>
                                <Button variant="outline" size="icon" :disabled="currentPage === 1"
                                    @click="goToPage(currentPage - 1)" title="Page précédente">
                                    <ChevronLeft class="w-4 h-4" />
                                </Button>
                                <template v-for="page in pageNumbers" :key="page">
                                    <span v-if="page === '...'" class="px-2 text-muted-foreground">…</span>
                                    <Button v-else :variant="page === currentPage ? 'default' : 'outline'" size="icon"
                                        @click="goToPage(page)">
                                        {{ page }}
                                    </Button>
                                </template>
                                <Button variant="outline" size="icon" :disabled="currentPage === totalPages"
                                    @click="goToPage(currentPage + 1)" title="Page suivante">
                                    <ChevronRight class="w-4 h-4" />
                                </Button>
                                <Button variant="outline" size="icon" :disabled="currentPage === totalPages"
                                    @click="goToPage(totalPages)" title="Dernière page">
                                    <ChevronsRight class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </Card>
            </main>

            <!-- Add/Edit Modal -->
            <Dialog :open="isAddEditModalOpen" @update:open="closeAddEditModal">
                <DialogContent class="sm:max-w-[500px]">
                    <DialogHeader>
                        <DialogTitle>
                            {{ editingId ? 'Modifier le rejet' : 'Ajouter un rejet' }}
                        </DialogTitle>
                        <DialogDescription>
                            {{ editingId ? 'Modifiez les informations du rejet ci-dessous.' :
                                'Remplissez les informations pour créer un nouveau rejet.' }}
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="submit" class="space-y-4 mt-4">
                        <div class="space-y-2">
                            <Label for="motif">Motif</Label>
                            <Input id="motif" v-model="form.motif" placeholder="Entrez le motif du rejet"
                                :class="{ 'border-red-500': form.errors.motif }" />
                            <p v-if="form.errors.motif" class="text-sm text-red-500">{{ form.errors.motif }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="service">Service</Label>
                            <Select v-model="form.service_id">
                                <SelectTrigger :class="{ 'border-red-500': form.errors.service_id }">
                                    <SelectValue placeholder="Sélectionnez un service" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="service in services" :key="service.id"
                                        :value="service.id.toString()">
                                        {{ service.nom_service }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.service_id" class="text-sm text-red-500">{{ form.errors.service_id }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="type_service">Type de Service</Label>
                            <Select v-model="form.id_type_services">
                                <SelectTrigger :class="{ 'border-red-500': form.errors.id_type_services }">
                                    <SelectValue placeholder="Sélectionnez un type de service" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="type in typeServices" :key="type.id" :value="type.id.toString()">
                                        {{ type.nom_type_service }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.id_type_services" class="text-sm text-red-500">
                                {{ form.errors.id_type_services }}
                            </p>
                        </div>

                        <DialogFooter class="mt-6">
                            <Button type="button" variant="outline" @click="closeAddEditModal">
                                Annuler
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Enregistrement...' : (editingId ? 'Modifier' : 'Ajouter') }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Delete Confirmation Modal -->
            <AlertDialog :open="isDeleteModalOpen" @update:open="closeDeleteModal">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Confirmer la suppression</AlertDialogTitle>
                        <AlertDialogDescription>
                            Êtes-vous sûr de vouloir supprimer ce rejet ? Cette action est irréversible.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel @click="closeDeleteModal">Annuler</AlertDialogCancel>
                        <AlertDialogAction @click="confirmDelete" class="bg-red-600 hover:bg-red-700 text-white">
                            Supprimer
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { Toaster, toast } from 'vue-sonner'
import { Badge } from '@/components/ui/badge'
import { Card } from '@/components/ui/card'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog'
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
    Plus,
    Search,
    X,
    MoreVertical,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
} from 'lucide-vue-next'

const props = defineProps({
    rejets: Array,
    services: Array,
    typeServices: Array,
})

// Recherche
const searchInput = ref('')
const activeSearch = ref('')

// Pagination
const currentPage = ref(1)
const itemsPerPage = 10

// Helpers pour afficher les noms
const getServiceName = (id) => {
    const service = props.services.find(
        s => Number(s.id) === Number(id)
    )

    return service?.nom_service ?? 'N/A'
}

const getTypeServiceName = (id) => {
    const type = props.typeServices.find(
        t => Number(t.id) === Number(id)
    )

    return type?.nom_type_service ?? 'N/A'
}

const filteredRejets = computed(() => {
    const all = props.rejets ?? []
    if (!activeSearch.value) return all
    const q = activeSearch.value.toLowerCase()
    return all.filter(rejet =>
        (rejet.motif ?? '').toLowerCase().includes(q) ||
        getServiceName(rejet.service_id).toLowerCase().includes(q) ||
        getTypeServiceName(rejet.id_type_services).toLowerCase().includes(q)
    )
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredRejets.value.length / itemsPerPage)))

watch(totalPages, (total) => {
    if (currentPage.value > total) currentPage.value = total
})

const paginatedRejets = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    return filteredRejets.value.slice(start, start + itemsPerPage)
})

const pageNumbers = computed(() => {
    const total = totalPages.value
    const current = currentPage.value
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
    const pages = [1]
    if (current > 3) pages.push('...')
    for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) pages.push(i)
    if (current < total - 2) pages.push('...')
    pages.push(total)
    return pages
})

const goToPage = (page) => {
    if (page === '...' || page < 1 || page > totalPages.value) return
    currentPage.value = page
}

const applySearch = () => {
    activeSearch.value = searchInput.value.trim()
    currentPage.value = 1
}

const clearSearch = () => {
    searchInput.value = ''
    activeSearch.value = ''
    currentPage.value = 1
}

// Modals state
const isAddEditModalOpen = ref(false)
const isDeleteModalOpen = ref(false)
const editingId = ref(null)
const deletingId = ref(null)

// Form
const form = useForm({
    motif: '',
    service_id: '',
    id_type_services: '',
})

// Open modal for create
const openCreateModal = () => {
    editingId.value = null
    form.reset()
    isAddEditModalOpen.value = true
}

// Open modal for edit
const openEditModal = (rejet) => {
    editingId.value = rejet.id
    form.motif = rejet.motif
    form.service_id = rejet.service_id.toString()
    form.id_type_services = rejet.id_type_services.toString()
    isAddEditModalOpen.value = true
}

// Open delete confirmation
const openDeleteModal = (id) => {
    deletingId.value = id
    isDeleteModalOpen.value = true
}

// Submit (create or update)
const submit = () => {
    const payload = {
        ...form.data(),
        service_id: parseInt(form.service_id),
        id_type_services: parseInt(form.id_type_services),
    }

    if (editingId.value) {
        form.transform(() => payload).put(route('rejets.update', editingId.value), {
            onSuccess: () => {
                isAddEditModalOpen.value = false
                form.reset()
                editingId.value = null
                toast.success('Rejet modifié avec succès.')
            },
        })
    } else {
        form.transform(() => payload).post(route('rejets.store'), {
            onSuccess: () => {
                isAddEditModalOpen.value = false
                form.reset()
                toast.success('Rejet ajouté avec succès.')
            },
        })
    }
}

// Delete
const confirmDelete = () => {
    if (deletingId.value) {
        router.delete(route('rejets.destroy', deletingId.value), {
            onSuccess: () => {
                isDeleteModalOpen.value = false
                deletingId.value = null
                toast.success('Rejet supprimé avec succès.')
            },
        })
    }
}

// Close modal handlers
const closeAddEditModal = () => {
    isAddEditModalOpen.value = false
    form.reset()
    editingId.value = null
}

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false
    deletingId.value = null
}
</script>

<script>
import Main from '/resources/js/Pages/Main.vue';
export default { layout: Main };
</script>
