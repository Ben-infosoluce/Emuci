<template>
    <div class="">
        <!-- Titre -->
        <!-- <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <h4 class="text-2xl font-bold tracking-tight">
                Documents du dossier : {{ props.dossier.num_chrono }}
            </h4>
        </div> -->
        <div
            class="sticky top-[-20px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8  py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <h4 class="text-2xl font-bold tracking-tight">
                Numerisation
            </h4>
            <div class="flex items-center space-x-2">
                <Link :href="route('show.numerisation.list')">
                <Button>
                    <MoveLeft class="w-4 h-4 mr-2" /> Retour
                </Button>
                </Link>
            </div>
        </div>

        <!-- Informations générales -->
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

        <!-- Galerie d'images -->
        <Card class="mt-6 p-6 bg-white dark:bg-gray-900 mx-8 ">
            <div>
                <div v-for="(doc, index) in cleanedDocuments" :key="index" class=" p-4 space-y-4">
                    <!-- Optionnel : Titre du groupe -->
                    <h5 class="font-semibold text-gray-600 dark:text-gray-300 text-center mt-4 mb-8">
                        Liste des documents
                    </h5>

                    <!-- Images avec grid max 5 par ligne -->
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mt-8">
                        <template v-for="(value, key) in doc" :key="key">
                            <div v-if="!keysToExcludeView.includes(key)"
                                class="relative z-40 cursor-pointer flex flex-col items-center m-6">
                                <!-- <Button
                                    class="absolute -top-4 right-6 z-50 bg-red-900 text-white rounded-full p-1.5 m-1"
                                    @click="() => openEditModal(`${value}`, doc.id, key)">
                                    <Pencil class="w-4 h-4" />
                                </Button> -->
                                <span
                                    class="absolute -top-3 right-6 z-50 bg-white border-2 text-black inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md font-medium  focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none  shadow  h-8 px-4 py-2 absolute top-2 right-2 text-gray-500  text-2xl "
                                    @click="() => openEditModal(`${value}`, doc.id, key)">
                                    <Pencil class="w-4 h-4" />
                                </span>

                                <img :src="`${value}`" :alt="formatLabel(key)" @click="openPreview(`${value}`)"
                                    class="w-[160px] h-[160px] object-cover rounded shadow-md hover:scale-105 transition duration-300" />

                                <p class="mt-2 text-center text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ formatLabel(key) }}
                                </p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </Card>


        <!-- Modal -->
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
        <!-- Modal Edit -->
        <div v-if="showModalEdit" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-3xl w-full max-h-[80vh] overflow-auto relative">
                <Button @click="showModalEdit = false"
                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 text-2xl">
                    &times;
                </Button>
                <h5 class="text-xl font-semibold mb-4 text-center">Modifier {{ formatLabel(currentKey) }}</h5>



                <!-- Champ de mise à jour -->
                <div class="w-full flex flex-col justify-items-center py-10">
                    <img :src="fileUrl" v-if="fileUrl" class="mx-auto rounded shadow-md object-contain"
                        style="max-width: 600px; max-height: 400px;" />
                    <Input type="file" :accept="acceptedImageTypes" @input="handleFileUpload" class="mt-5" />
                </div>

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
import { ref, computed, watch } from 'vue'
import { MoveRight, MoveLeft, Plus, Eye, Pen, ReceiptText, Pencil } from 'lucide-vue-next'

const props = defineProps({
    dossier: {
        type: Object,
        required: true
    },
    documents: {
        type: Array,
        required: true
    }
})

const acceptedImageTypes = "image/*";

const keysToExclude = ['id_dossier', 'type_document', 'created_at', 'updated_at']
const keysToExcludeView = ['id', 'created_at', 'updated_at']

const cleanedDocuments = computed(() =>
    props.documents.map(doc => {
        const cleanedDoc = {}
        for (const key in doc) {
            if (!keysToExclude.includes(key)) {
                cleanedDoc[key] = doc[key]
                console.log(doc[key])
            }
        }
        // console.log(props.documents)
        console.log(cleanedDoc)
        return cleanedDoc
    })
)
console.log(props.documents[0])
const showModal = ref(false)
const previewUrl = ref(null)
const showModalEdit = ref(false)
const currentKey = ref(null) // Le champ à mettre à jour (ex: 'carte_grise')
const currentDocumentId = ref(null) // L'id du document à éditer
const fileToUpload = ref(null)
const fileUrl = ref(null); // Pour stocker l'URL Cloudinary



const cloudinaryCloudName = 'dt6ammifo'; // Remplace par ton cloud name
const cloudinaryUploadPreset = 'preset_dt6ammifo'; // Remplace par ton upload preset

const uploadToCloudinary = async (file) => {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('upload_preset', cloudinaryUploadPreset);
    formData.append('cloud_name', cloudinaryCloudName);

    const response = await fetch(`https://api.cloudinary.com/v1_1/${cloudinaryCloudName}/image/upload`, {
        method: 'POST',
        body: formData,
    });

    const data = await response.json();
    return data.secure_url; // URL de l'image sur Cloudinary
};


const handleFileUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Optionnel : vérifier la taille
    if (file.size > 1 * 1024 * 1024) {
        toast.error('Fichier trop volumineux. Taille maximale : 1 Mo.');
        return;
    }

    // Upload vers Cloudinary
    try {
        const imageUrl = await uploadToCloudinary(file);
        fileUrl.value = imageUrl;
        toast.success('Fichier uploadé avec succès !');
    } catch (error) {
        toast.error('Échec de l’upload');
        console.error("Erreur d’upload Cloudinary :", error);
    }
}



async function submitUpdate() {
    if (!fileUrl.value || !currentDocumentId.value || !currentKey.value) {
        toast.error("Veuillez sélectionner un fichier");
        return;
    }

    const formatDataSingle = {
        document_id: currentDocumentId.value,
        [currentKey.value]: fileUrl.value,
        field_name: currentKey.value,
    };
    console.log(formatDataSingle);

    try {
        const response = await axios.post('/numerisation/document/update/single', formatDataSingle, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        toast.success(response?.data?.message || "Document mis à jour avec succès.");
        showModalEdit.value = false;
        fileUrl.value = null;
        setTimeout(() => window.location.reload(), 1000);
    } catch (error) {
        console.error("Erreur lors de la mise à jour", error);
        toast.error(error?.response?.data?.message || "Échec de la mise à jour");
    }
}




function openPreview(url) {
    previewUrl.value = url
    showModal.value = true
}

function openEditModal(url, documentId, key) {
    previewUrl.value = url
    currentDocumentId.value = documentId
    currentKey.value = key
    console.log({ previewUrl, currentDocumentId, currentKey })
    showModalEdit.value = true

    console.log({ url, documentId, key })
}

function formatDate(date) {
    if (!date) return '—'
    const options = { year: 'numeric', month: 'long', day: 'numeric' }
    return new Date(date).toLocaleDateString('fr-FR', options)
}

// Convertit les clés en labels lisibles
function formatLabel(key) {
    return key
        .replace(/_/g, ' ') // underscore → espace
        .replace(/\b(de|la|le|et|en|du|des|aux|au|dans|sur|avec|à)\b/g, match => match.toLowerCase()) // mots courants
        .replace(/\b\w/g, l => l.toUpperCase()) // première lettre majuscule
}




</script>
<script>
import { Card, CardContent } from '@/components/ui/card'

import Main from '/resources/js/Pages/Main.vue';
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableHead,
    TableCell,
} from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { MoreVertical } from 'lucide-vue-next'
import { useForm } from 'vee-validate';
import { toast, Toaster } from 'vue-sonner';


export default {
    layout: Main,
    components: {
        Card,
        Table,
        TableHeader,
        TableBody,
        TableRow,
        TableHead,
        TableCell,
        Button,
        Badge
    }
}
</script>
