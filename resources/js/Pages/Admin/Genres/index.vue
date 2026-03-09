<script setup>
import { ref, computed } from 'vue'
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
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Plus, Pencil, Trash2, ChevronLeft, ChevronRight } from 'lucide-vue-next'

const props = defineProps({
    genres: Array
})

// Pagination
const currentPage = ref(1)
const itemsPerPage = 20

const totalPages = computed(() => Math.ceil(props.genres.length / itemsPerPage))

const paginatedGenres = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    const end = start + itemsPerPage
    return props.genres.slice(start, end)
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
    nom: '',
    nb_plaque: ''
})

// Open modal for create
const openCreateModal = () => {
    editingId.value = null
    form.reset()
    isAddEditModalOpen.value = true
}

// Open modal for edit
const openEditModal = (genre) => {
    editingId.value = genre.id
    form.nom = genre.nom
    form.nb_plaque = genre.nb_plaque.toString()
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
        nb_plaque: parseInt(form.nb_plaque)
    }

    if (editingId.value) {
        form.transform(() => payload).put(route('genres.update', editingId.value), {
            onSuccess: () => {
                isAddEditModalOpen.value = false
                form.reset()
                editingId.value = null
            }
        })
    } else {
        form.transform(() => payload).post(route('genres.store'), {
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
        router.delete(route('genres.destroy', deletingId.value), {
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
            <h1 class="text-xl font-bold text-gray-900">Gestion des Genres</h1>
            <Button @click="openCreateModal">
                <Plus class="w-4 h-4 mr-2" />
                Ajouter un genre
            </Button>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden border p-2">
            <Table>
                <TableHeader>
                    <TableRow class="bg-gray-50">
                        <TableHead class="font-semibold text-gray-700">ID</TableHead>
                        <TableHead class="font-semibold text-gray-700">Nom</TableHead>
                        <TableHead class="font-semibold text-gray-700">Nombre de Plaques</TableHead>
                        <TableHead class="font-semibold text-gray-700 text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="(genre, index) in paginatedGenres" :key="genre.id" class="hover:bg-gray-50">
                        <TableCell class="font-medium">{{ index + 1 }}</TableCell>
                        <TableCell>{{ genre.nom }}</TableCell>
                        <TableCell>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                :class="genre.nb_plaque === 2 ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                            >
                                {{ genre.nb_plaque }} plaque(s)
                            </span>
                        </TableCell>
                        <TableCell class="text-right space-x-2">
                            <Button variant="outline" size="sm" @click="openEditModal(genre)"
                                class="border-yellow-500 text-yellow-600 hover:bg-yellow-50">
                                <Pencil class="w-4 h-4" />
                            </Button>
                            <Button variant="outline" size="sm" @click="openDeleteModal(genre.id)"
                                class="border-red-500 text-red-600 hover:bg-red-50">
                                <Trash2 class="w-4 h-4" />
                            </Button>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="paginatedGenres.length === 0">
                        <TableCell colspan="4" class="text-center py-8 text-gray-500">
                            Aucun genre trouvé
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between mt-4" v-if="totalPages > 1">
            <div class="text-sm text-gray-600">
                Affichage de {{ (currentPage - 1) * itemsPerPage + 1 }} à
                {{ Math.min(currentPage * itemsPerPage, genres.length) }} sur {{ genres.length }} genres
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
                        {{ editingId ? 'Modifier le genre' : 'Ajouter un genre' }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ editingId ? 'Modifiez les informations du genre ci-dessous.' :
                            'Remplissez les informations pour créer un nouveau genre.' }}
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submit" class="space-y-4 mt-4">
                    <div class="space-y-2">
                        <Label for="nom">Nom</Label>
                        <Input id="nom" v-model="form.nom" placeholder="Entrez le nom du genre"
                            :class="{ 'border-red-500': form.errors.nom }" />
                        <p v-if="form.errors.nom" class="text-sm text-red-500">{{ form.errors.nom }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="nb_plaque">Nombre de plaques</Label>
                        <Input id="nb_plaque" v-model="form.nb_plaque" type="number" min="1" max="2"
                            placeholder="Entrez le nombre de plaques (1 ou 2)"
                            :class="{ 'border-red-500': form.errors.nb_plaque }" />
                        <p v-if="form.errors.nb_plaque" class="text-sm text-red-500">{{ form.errors.nb_plaque }}</p>
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
                        Êtes-vous sûr de vouloir supprimer ce genre ? Cette action est irréversible.
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
