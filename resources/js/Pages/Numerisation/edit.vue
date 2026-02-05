<template>
    <div class="">
        <!-- Header -->
        <div
            class="sticky top-[-20px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8  py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <h4 class="text-2xl font-bold tracking-tight">Numerisation</h4>
            <div class="flex items-center space-x-2">
                <Link :href="route('show.numerisation.list')">
                <Button>
                    <MoveLeft class="w-4 h-4 mr-2" /> Retour
                </Button>
                </Link>
            </div>
        </div>

        <!-- Infos générales -->
        <Card class="mt-6 p-6 bg-white dark:bg-gray-900 mx-8 ">
            <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                <div>
                    <h5 class="text-lg font-semibold">Dossier : {{ props.dossier.num_chrono }}</h5>
                </div>
                <div>
                    <strong>Date de création :</strong> {{ formatDate(props.dossier.date_creation) }}
                </div>
            </div>
        </Card>

        <!-- Galerie -->
        <Card class="mt-6 p-6 bg-white dark:bg-gray-900 mx-8 ">
            <div>
                <h5 class="font-semibold text-gray-600 dark:text-gray-300 text-center mt-4 mb-8">
                    Liste des documents
                </h5>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mt-8">
                    <template v-for="(doc, index) in cleanedDocuments" :key="index">
                        <template v-for="(value, key) in doc" :key="key">
                            <div v-if="!keysToExcludeView.includes(key)"
                                class="relative z-40 flex flex-col items-center m-6">

                                <span
                                    class="cursor-pointer absolute -top-3 right-6 z-50 bg-white border-2 text-black inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md font-medium  focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none  shadow  h-8 px-4 py-2 absolute top-2 right-2 text-gray-500  text-2xl "
                                    @click="openEditModal(doc.id, key, value)">
                                    <Pencil class="w-4 h-4" />
                                </span>
                                <!-- Image -->
                                <img :src="`/storage/${value}`" :alt="formatLabel(key)"
                                    @click="openPreview(`/storage/${value}`)"
                                    class="w-[160px] h-[160px] object-cover rounded shadow-md hover:scale-105 transition duration-300 cursor-pointer" />

                                <!-- Label -->
                                <p class="mt-2 text-center text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ formatLabel(key) }}
                                </p>

                                <!-- Bouton Modifier -->

                            </div>
                        </template>
                    </template>
                </div>
            </div>
        </Card>

        <!-- Modal Preview -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-3xl w-full max-h-[80vh] overflow-auto relative">
                <Button @click="showModal = false"
                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 text-2xl">
                    &times;
                </Button>
                <h5 class="text-xl font-semibold mb-8 mt-4 text-center">Prévisualisation du document</h5>
                <img :src="previewUrl" alt="Document" class="mx-auto rounded shadow-md object-contain"
                    style="max-width: 600px; max-height: 600px;" />

                <div class="mt-8 mb-4 flex justify-center">
                    <a :href="previewUrl" target="_blank"
                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2 bg-[#ca7600]">
                        Ouvrir dans un nouvel onglet
                    </a>
                </div>
            </div>
        </div>

        <!-- Modal Edit -->
        <div v-if="showModalEdit" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-3xl w-full max-h-[80vh] overflow-auto relative">
                <Button @click="showModalEdit = false"
                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 text-2xl">
                    &times;
                </Button>
                <h5 class="text-xl font-semibold mb-4 text-center">Modifier {{ formatLabel(currentKey) }}</h5>

                <!-- Upload -->
                <div class="w-full flex flex-col justify-items-center py-10">
                    <img :src="fileUrl" v-if="fileUrl" class="mx-auto rounded shadow-md object-contain"
                        style="max-width: 600px; max-height: 400px;" />
                    <Input type="file" :accept="acceptedImageTypes" @input="handleFileUpload" class="mt-5" />
                </div>

                <!-- Actions -->
                <div class="mt-6 flex justify-center space-x-4">
                    <Button @click="submitUpdate" class="bg-amber-900 hover:bg-amber-800 text-white px-4 py-2">
                        Enregistrer
                    </Button>
                    <Button @click="showModalEdit = false" variant="outline">
                        Annuler
                    </Button>
                </div>
            </div>
        </div>
    </div>

    <Toaster richColors position="top-right" />
</template>

<script setup>
import { ref, computed } from 'vue'
import { MoveLeft, Pencil } from 'lucide-vue-next'
import { toast, Toaster } from 'vue-sonner'

// Props
const props = defineProps({
    dossier: { type: Object, required: true },
    documents: { type: Array, required: true }
})

const acceptedImageTypes = "image/*"
const keysToExclude = ['id_dossier', 'type_document', 'created_at', 'updated_at']
const keysToExcludeView = ['id', 'created_at', 'updated_at']

// Nettoyage docs (on garde uniquement champs non nuls et non vides)
const cleanedDocuments = computed(() =>
    props.documents.map(doc => {
        const cleanedDoc = {}
        for (const key in doc) {
            if (
                !keysToExclude.includes(key) &&
                doc[key] !== null &&
                doc[key] !== undefined &&
                doc[key] !== ''
            ) {
                cleanedDoc[key] = doc[key]
            }
        }
        return cleanedDoc
    })
)

// États
const showModal = ref(false)
const previewUrl = ref(null)
const showModalEdit = ref(false)
const currentKey = ref(null)
const currentDocumentId = ref(null)
const fileToUpload = ref(null)
const fileUrl = ref(null)

// Preview
function openPreview(url) {
    previewUrl.value = url
    showModal.value = true
}

// Format date
function formatDate(date) {
    if (!date) return '—'
    const options = { year: 'numeric', month: 'long', day: 'numeric' }
    return new Date(date).toLocaleDateString('fr-FR', options)
}

// Label
function formatLabel(key) {
    return key.replace(/_/g, ' ')
        .replace(/\b\w/g, l => l.toUpperCase())
}

// Modal Edit
function openEditModal(docId, key, value) {
    currentDocumentId.value = docId
    currentKey.value = key
    fileUrl.value = `/storage/${value}`
    fileToUpload.value = null
    showModalEdit.value = true
}

// Upload local
function handleFileUpload(event) {
    const file = event.target.files[0]
    if (file) {
        fileToUpload.value = file
        fileUrl.value = URL.createObjectURL(file)
    }
}

// Submit update
async function submitUpdate() {
    if (!fileToUpload.value) {
        toast.error("Veuillez sélectionner un fichier.")
        return
    }

    const formData = new FormData()
    formData.append('document_id', currentDocumentId.value)
    formData.append('field', currentKey.value)
    formData.append('file', fileToUpload.value)

    try {
        const response = await axios.post('/documents/update', formData, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            withCredentials: true
        })

        if (response.data.success) {
            toast.success("Document mis à jour ✅")

            const updatedDoc = props.documents.find(d => d.id === currentDocumentId.value)
            if (updatedDoc) updatedDoc[currentKey.value] = response.data.path

            showModalEdit.value = false
        } else {
            toast.error(response.data.message || "Erreur lors de la mise à jour ❌")
        }
    } catch (error) {
        console.error(error)
        toast.error("Erreur serveur ❌")
    }
}

</script>




<script>
import { Card } from '@/components/ui/card'
import Main from '/resources/js/Pages/Main.vue'
import { Button } from '@/components/ui/button'
import axios from 'axios'

export default {
    layout: Main,
    components: { Card, Button }
}
</script>
