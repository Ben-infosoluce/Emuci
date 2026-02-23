<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
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
import { Plus, Pencil, Trash2, ChevronLeft, ChevronRight } from 'lucide-vue-next'

const props = defineProps({
    rejets: Array,
    services: Array,
    typeServices: Array
})

// Pagination
const currentPage = ref(1)
const itemsPerPage = 20

const totalPages = computed(() => Math.ceil(props.rejets.length / itemsPerPage))

const paginatedRejets = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    const end = start + itemsPerPage
    return props.rejets.slice(start, end)
})

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page
    }
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
    id_type_services: ''
})

console.log("Rejets reçus :", props.services);
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
    // Convertir en nombres
    const payload = {
        ...form.data(),
        service_id: parseInt(form.service_id),
        id_type_services: parseInt(form.id_type_services)
    }

    if (editingId.value) {
        form.transform(() => payload).put(route('rejets.update', editingId.value), {
            onSuccess: () => {
                isAddEditModalOpen.value = false
                form.reset()
                editingId.value = null
            }
        })
    } else {
        form.transform(() => payload).post(route('rejets.store'), {
            onSuccess: () => {
                isAddEditModalOpen.value = false
                form.reset()
            }
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
            }
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

<template>
    <div class="p-4 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold text-gray-900">Gestion des Rejets</h1>
            <Button @click="openCreateModal">
                <Plus class="w-4 h-4 mr-2" />
                Ajouter un rejet
            </Button>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden border p-2">
            <Table>
                <TableHeader>
                    <TableRow class="bg-gray-50">
                        <TableHead class="font-semibold text-gray-700">ID</TableHead>
                        <TableHead class="font-semibold text-gray-700">Motif</TableHead>
                        <TableHead class="font-semibold text-gray-700">Service</TableHead>
                        <TableHead class="font-semibold text-gray-700">Type Service</TableHead>
                        <TableHead class="font-semibold text-gray-700 text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="(rejet, index) in paginatedRejets" :key="rejet.id" class="hover:bg-gray-50">
                        <TableCell class="font-medium">{{ index + 1 }}</TableCell>
                        <TableCell>{{ rejet.motif }}</TableCell>
                        <TableCell>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ getServiceName(rejet.service_id) }}
                            </span>
                        </TableCell>
                        <TableCell>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ getTypeServiceName(rejet.id_type_services) }}
                            </span>
                        </TableCell>
                        <TableCell class="text-right space-x-2">
                            <Button variant="outline" size="sm" @click="openEditModal(rejet)"
                                class="border-yellow-500 text-yellow-600 hover:bg-yellow-50">
                                <Pencil class="w-4 h-4" />
                            </Button>
                            <Button variant="outline" size="sm" @click="openDeleteModal(rejet.id)"
                                class="border-red-500 text-red-600 hover:bg-red-50">
                                <Trash2 class="w-4 h-4" />
                            </Button>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="paginatedRejets.length === 0">
                        <TableCell colspan="5" class="text-center py-8 text-gray-500">
                            Aucun rejet trouvé
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between mt-4" v-if="totalPages > 1">
            <div class="text-sm text-gray-600">
                Affichage de {{ (currentPage - 1) * itemsPerPage + 1 }} à
                {{ Math.min(currentPage * itemsPerPage, rejets.length) }} sur {{ rejets.length }} rejets
            </div>
            <div class="flex items-center space-x-2">
                <Button variant="outline" size="sm" @click="goToPage(currentPage - 1)" :disabled="currentPage === 1">
                    <ChevronLeft class="w-4 h-4" />
                </Button>
                <span class="text-sm text-gray-600">
                    Page {{ currentPage }} sur {{ totalPages }}
                </span>
                <Button variant="outline" size="sm" @click="goToPage(currentPage + 1)"
                    :disabled="currentPage === totalPages">
                    <ChevronRight class="w-4 h-4" />
                </Button>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <Dialog :open="isAddEditModalOpen" @update:open="closeAddEditModal">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>
                        {{ editingId ? 'Modifier le rejet' : 'Ajouter un rejet' }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ editingId ? 'Modifiez les informations du rejet ci-dessous.' :
                            'Remplissez les informationspour créer un nouveau rejet.' }}
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
                        <p v-if="form.errors.id_type_services" class="text-sm text-red-500">{{
                            form.errors.id_type_services }}</p>
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
</template>