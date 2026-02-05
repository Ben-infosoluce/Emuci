<template>
    <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <h4 class="text-2xl font-bold tracking-tight">
            Nouvelle Numérisation (Post-immatriculation)
        </h4>
        <Button variant="outline" class="flex items-center" @click="returnBack">
            <MoveLeft class="w-4 h-4 mr-2" /> Retour
        </Button>
    </div>
    <div class="rounded-lg dark:border-gray-700">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <Card class="h-full flex flex-col gap-4 p-10">
                <ScrollArea class="h-full w-full">
                    <!-- En-tête du dossier -->
                    <div
                        class="flex flex-col space-y-4 py-2 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                        <h2>
                            <strong>Service :</strong>
                            {{ dossier?.r_dossier_services?.nom_service || '-' }}
                        </h2>
                        <h2>
                            Statut du dossier :
                            <Badge class="mx-2" :variant="dossier?.statut === 1 ? 'warning' :
                                dossier?.statut === 2 ? 'success' :
                                    dossier?.statut === 3 ? 'error' : 'secondary'
                                ">
                                {{
                                    dossier?.statut === 1 ? 'En attente' :
                                        dossier?.statut === 2 ? 'Validé' :
                                            dossier?.statut === 3 ? 'Refusé' : 'Inconnu'
                                }}
                            </Badge>
                        </h2>
                    </div>

                    <!-- Motif de rejet -->
                    <div v-if="dossier?.motif_rejet" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-1 mb-1">
                        <p class="border border-red-300 rounded-md p-3">
                            <strong>Motif de rejet :</strong> {{ dossier.motif_rejet || '-' }}
                        </p>
                    </div>

                    <!-- Type de service -->
                    <h2>
                        <strong>Type de Service : </strong>
                        <template v-if="Array.isArray(dossier?.detail)">
                            {{ dossier.detail.join(', ') }}
                        </template>
                        <template v-else-if="dossier?.detail">
                            {{ JSON.parse(dossier.detail || '[]').join(', ') }}
                        </template>
                    </h2>

                    <!-- Titre des pièces justificatives -->
                    <h3 class="text-xl font-semibold mt-2 mb-2 text-center">
                        Pièces justificatives (Post-immatriculation)
                    </h3>

                    <!-- Informations Véhicule -->
                    <div>
                        <h1 class="font-semibold mt-4 mb-5">Informations Véhicule</h1>
                        <div class="w-full grid gap-4 md:gap-8 lg:grid-cols-3 xl:grid-cols-3">
                            <!-- Carte grise -->
                            <div class="space-y-2">
                                <p>Carte grise *</p>
                                <CustomFileUpload dropText="Carte grise" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.carte_grise = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails de la carte grise" />
                            </div>
                            <!-- Vignette -->
                            <div class="space-y-2">
                                <p>Vignette *</p>
                                <CustomFileUpload dropText="Vignette" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.vignette = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails de la vignette" />
                            </div>
                            <!-- Assurance -->
                            <div class="space-y-2">
                                <p>Assurance en cours de validité *</p>
                                <CustomFileUpload dropText="Assurance en cours de validité"
                                    previewText="Fichier sélectionné" :accept="acceptedImageTypes"
                                    @input="form.assurance_en_cours_de_validite = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile"
                                    modal-title="Détails de l'assurance en cours de validité" />
                            </div>
                            <!-- Post-immatriculation : RTI signalant la modification -->
                            <div class="space-y-2">
                                <p>RTI signalant la modification (usage, couleur..)*</p>
                                <CustomFileUpload dropText="RTI signalant la modification"
                                    previewText="Fichier sélectionné" :accept="acceptedImageTypes"
                                    @input="form.rti_modification = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile"
                                    modal-title="Détails du RTI signalant la modification" />
                            </div>
                            <!-- Post-immatriculation : Fiche de mutation CGI -->
                            <div class="space-y-2" v-if="
                                (decodedDetail = JSON.parse(dossier?.detail || '[]')[0]) === 'Changement de propriétaire : MUTATION'
                            ">
                                <p>Fiche de mutation CGI*</p>
                                <CustomFileUpload dropText="Fiche de mutation CGI" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.fiche_mutation = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails de la fiche de mutation CGI" />
                            </div>

                            <!-- Certificat de résidence (obligatoire si code_region) -->
                            <div class="space-y-2"
                                v-if="dossier.detail && dossier.detail.includes('Changement de zone (Code région)')">
                                <p>Certificat de résidence *</p>
                                <CustomFileUpload dropText="D3" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes"
                                    @input="form.certificat_residence = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails du D3" />
                            </div>
                        </div>
                    </div>

                    <!-- Changement de propriétaire : Mutation -->
                    <div v-if="
                        (decodedDetail = JSON.parse(dossier?.detail || '[]')[0]) === 'Changement de propriétaire : MUTATION'
                    ">
                        <!-- Informations ancien propriétaire -->
                        <h1 class="font-semibold mt-8 mb-5">Informations de l'ancien propriétaire</h1>
                        <div v-if="oldData?.vehicule">
                            <!-- si Physique -->
                            <div v-if="oldData.vehicule.physique_morale === 'Physique'"
                                class="grid gap-4 md:gap-8 lg:grid-cols-3 xl:grid-cols-3 mt-3">
                                <div class="space-y-2">
                                    <p>Type de pièce *</p>
                                    <Select v-model="form.type_piece_ancien_proprietaire">
                                        <SelectTrigger class="w-[260px]">
                                            <SelectValue placeholder="Veuillez sélectionner une valeur" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectLabel>Veuillez sélectionner une valeur</SelectLabel>
                                                <SelectItem value="CI">Carte consulaire</SelectItem>
                                                <SelectItem value="SI">Sans identifiant</SelectItem>
                                                <SelectItem value="TI">Carte d'identité (non nationaux)</SelectItem>
                                                <SelectItem value="CNI">Carte nationale d'identité</SelectItem>
                                                <SelectItem value="AI">Attestation d'identité</SelectItem>
                                                <SelectItem value="DFE">Déclaration fiscale d'existence</SelectItem>
                                                <SelectItem value="PC">Permis de conduire</SelectItem>
                                                <SelectItem value="NNI">Numéro national d'identité</SelectItem>
                                                <SelectItem value="CIR">Carte d'identité du réfugié</SelectItem>
                                                <SelectItem value="ACCS">ACCORD DE CRÉATION DE SIÈGE</SelectItem>
                                                <SelectItem value="CR">Carte de résident</SelectItem>
                                                <SelectItem value="CD">Lettre diplomatique</SelectItem>
                                                <SelectItem value="CC">Compte contribuable</SelectItem>
                                                <SelectItem value="ACO">ACCORD DE CRÉATION ONG</SelectItem>
                                                <SelectItem value="RDC">Registre du commerce</SelectItem>
                                                <SelectItem value="AT">Attestation d'Admission Temporaire</SelectItem>
                                                <SelectItem value="PAS">Passeport</SelectItem>
                                                <SelectItem value="AUT">Autre</SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-2">
                                    <p>Pièce d’identité de l’ancien propriétaire</p>
                                    <CustomFileUpload dropText="Pièce d’identité de l’ancien propriétaire"
                                        previewText="Fichier sélectionné" :accept="acceptedImageTypes"
                                        @input="form.piece_ancien_proprietaire = $event.target.files[0]"
                                        previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                        @file-selected="handleFile"
                                        modal-title="Détails de Pièce d’identité de l’ancien propriétaire" />
                                </div>
                            </div>
                            <!-- si Morale -->
                            <div v-else class="grid gap-4 md:gap-8 lg:grid-cols-3 xl:grid-cols-3 mt-3">
                                <div class="space-y-2">
                                    <p>Registre de commerce *</p>
                                    <CustomFileUpload dropText="Registre de commerce *"
                                        previewText="Registre de commerce" :accept="acceptedImageTypes"
                                        @input="form.registre_de_commerce = $event.target.files[0]"
                                        previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                        @file-selected="handleFile"
                                        modal-title="Détails du registre de commerce de l'ancienne entreprise" />
                                </div>
                                <div class="space-y-2">
                                    <p>DFE</p>
                                    <CustomFileUpload dropText="DFE *" previewText="DFE" :accept="acceptedImageTypes"
                                        @input="form.dfe = $event.target.files[0]"
                                        previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                        @file-selected="handleFile"
                                        modal-title="Détails du DFE de l'ancienne entreprise" />
                                </div>
                            </div>
                        </div>

                        <!-- Informations nouveau propriétaire -->
                        <h1 class="font-semibold mt-8 mb-5">Informations du nouveau propriétaire</h1>
                        <div v-if="newData?.vehicule">
                            <!-- si Physique -->
                            <div v-if="newData.vehicule.physique_morale === 'Physique'"
                                class="grid gap-4 md:gap-8 lg:grid-cols-3 xl:grid-cols-3 mt-3">
                                <div class="space-y-2">
                                    <p>Type de pièce *</p>
                                    <Select v-model="form.type_piece_nouveau_proprietaire">
                                        <SelectTrigger class="w-[260px]">
                                            <SelectValue placeholder="Veuillez sélectionner une valeur" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectLabel>Veuillez sélectionner une valeur</SelectLabel>
                                                <SelectItem value="CI">Carte consulaire</SelectItem>
                                                <SelectItem value="SI">Sans identifiant</SelectItem>
                                                <SelectItem value="TI">Carte d'identité (non nationaux)</SelectItem>
                                                <SelectItem value="CNI">Carte nationale d'identité</SelectItem>
                                                <SelectItem value="AI">Attestation d'identité</SelectItem>
                                                <SelectItem value="DFE">Déclaration fiscale d'existence</SelectItem>
                                                <SelectItem value="PC">Permis de conduire</SelectItem>
                                                <SelectItem value="NNI">Numéro national d'identité</SelectItem>
                                                <SelectItem value="CIR">Carte d'identité du réfugié</SelectItem>
                                                <SelectItem value="ACCS">ACCORD DE CRÉATION DE SIÈGE</SelectItem>
                                                <SelectItem value="CR">Carte de résident</SelectItem>
                                                <SelectItem value="CD">Lettre diplomatique</SelectItem>
                                                <SelectItem value="CC">Compte contribuable</SelectItem>
                                                <SelectItem value="ACO">ACCORD DE CRÉATION ONG</SelectItem>
                                                <SelectItem value="RDC">Registre du commerce</SelectItem>
                                                <SelectItem value="AT">Attestation d'Admission Temporaire</SelectItem>
                                                <SelectItem value="PAS">Passeport</SelectItem>
                                                <SelectItem value="AUT">Autre</SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-2">
                                    <p>Pièce d’identité du nouveau propriétaire</p>
                                    <CustomFileUpload dropText="Pièce d’identité du nouveau propriétaire"
                                        previewText="Fichier sélectionné" :accept="acceptedImageTypes"
                                        @input="form.piece = $event.target.files[0]"
                                        previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                        @file-selected="handleFile"
                                        modal-title="Détails de Pièce d’identité du nouveau propriétaire" />
                                </div>
                            </div>
                            <!-- si Morale -->
                            <div v-else class="grid gap-4 md:gap-8 lg:grid-cols-3 xl:grid-cols-3 mt-3">
                                <div class="space-y-2">
                                    <p>Registre de commerce *</p>
                                    <CustomFileUpload dropText="Registre de commerce *"
                                        previewText="Registre de commerce" :accept="acceptedImageTypes"
                                        @input="form.registre_de_commerce_nouvelle_entreprise = $event.target.files[0]"
                                        previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                        @file-selected="handleFile"
                                        modal-title="Détails du registre de commerce du nouveau entreprise" />
                                </div>
                                <div class="space-y-2">
                                    <p>DFE</p>
                                    <CustomFileUpload dropText="DFE *" previewText="DFE" :accept="acceptedImageTypes"
                                        @input="form.dfe_nouvelle_entreprise = $event.target.files[0]"
                                        previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                        @file-selected="handleFile"
                                        modal-title="Détails du DFE du nouveau entreprise" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <div class="grid gap-4 md:gap-8 lg:grid-cols-3 xl:grid-cols-3 mt-3">
                            <div class="space-y-2">
                                <p>Type de pièce *</p>
                                <Select v-model="form.type_document">
                                    <SelectTrigger class="w-[260px]">
                                        <SelectValue placeholder="Veuillez sélectionner une valeur" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Veuillez sélectionner une valeur</SelectLabel>
                                            <SelectItem value="CI">Carte consulaire</SelectItem>
                                            <SelectItem value="SI">Sans identifiant</SelectItem>
                                            <SelectItem value="TI">Carte d'identité (non nationaux)</SelectItem>
                                            <SelectItem value="CNI">Carte nationale d'identité</SelectItem>
                                            <SelectItem value="AI">Attestation d'identité</SelectItem>
                                            <SelectItem value="DFE">Déclaration fiscale d'existence</SelectItem>
                                            <SelectItem value="PC">Permis de conduire</SelectItem>
                                            <SelectItem value="NNI">Numéro national d'identité</SelectItem>
                                            <SelectItem value="CIR">Carte d'identité du réfugié</SelectItem>
                                            <SelectItem value="ACCS">ACCORD DE CRÉATION DE SIÈGE</SelectItem>
                                            <SelectItem value="CR">Carte de résident</SelectItem>
                                            <SelectItem value="CD">Lettre diplomatique</SelectItem>
                                            <SelectItem value="CC">Compte contribuable</SelectItem>
                                            <SelectItem value="ACO">ACCORD DE CRÉATION ONG</SelectItem>
                                            <SelectItem value="RDC">Registre du commerce</SelectItem>
                                            <SelectItem value="AT">Attestation d'Admission Temporaire</SelectItem>
                                            <SelectItem value="PAS">Passeport</SelectItem>
                                            <SelectItem value="AUT">Autre</SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <p>Pièce d’identité</p>
                                <CustomFileUpload dropText="Pièce d’identité" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.piece = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails de Pièce d’identité " />
                            </div>
                        </div>
                    </div>

                    <!-- Bouton d'enregistrement -->
                    <div class="w-full flex flex-row justify-between mt-6">
                        <div></div>
                        <div>
                            <Button @click="submitForm" class="bg-amber-800 text-white py-4 px-4 rounded-lg">
                                Enregistrer
                            </Button>
                        </div>
                    </div>
                </ScrollArea>
            </Card>
        </main>
        <!-- Overlay Loader -->
        <div v-if="isLoading" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 shadow-lg flex flex-col items-center">
                <span class="loader mb-3"></span>
                <p class="text-lg font-medium">Traitement et enregistrement en cours...</p>
            </div>
        </div>
    </div>
    <Toaster richColors position="top-right" />
</template>




<script setup>
import { ref, onMounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

// Props
const props = defineProps({
    dossier: { type: Object, required: true },
    oldData: { type: Object, required: true },
    newData: { type: Object, required: true },
});

// État réactif
const isLoading = ref(false);
const selectedTypePiece = ref(null);
const acceptedImageTypes = ".jpeg,.png,.jpg,.webp";
const decodedDetail = ref([]);

// Formulaire
const form = useForm({
    id_dossier: props.dossier.id,
    carte_grise: null,
    vignette: null,
    assurance_en_cours_de_validite: null,
    rti_modification: null,
    fiche_mutation: null,
    certificat_residence: null,
    registre_de_commerce: null,
    dfe: null,
    type_piece_ancien_proprietaire: null,
    piece_ancien_proprietaire: null,
    type_piece_nouveau_proprietaire: null,
    type_document: null,
    piece: null,
    registre_de_commerce_nouvelle_entreprise: null,
    dfe_nouvelle_entreprise: null,
});

// Initialisation
onMounted(() => {
    try {
        let detail = props.dossier.detail;
        if (!detail) return;
        if (Array.isArray(detail)) {
            decodedDetail.value = detail;
        } else if (typeof detail === "string") {
            decodedDetail.value = JSON.parse(detail);
        }
    } catch (e) {
        console.error("Erreur parsing détail :", e, props.dossier.detail);
    }
});

// Gestion des fichiers
const handleFile = (fieldName, file) => {
    form[fieldName] = file;
};

// Récupération des champs obligatoires
const getRequiredFields = () => {
    const commonFields = [
        { value: form.carte_grise, name: "Carte grise" },
        { value: form.vignette, name: "Vignette" },
        { value: form.assurance_en_cours_de_validite, name: "Assurance en cours de validité" },
    ];
    let specificFields = [];

    // Cas Post-immatriculation
    if (props.dossier.r_dossier_services.nom_service === 'Post-immatriculation') {
        // Sans mutation
        if (!decodedDetail.value.includes('Changement de propriétaire : MUTATION')) {
            specificFields = [
                { value: form.type_document, name: "Type de pièce" },
                { value: form.piece, name: "Pièce d'identité" },
            ];
        }
        if (decodedDetail.value.includes('Changement de zone (Code région)')) {
            specificFields = [
                { value: form.certificat_residence, name: "Certificat de résidence" },
            ];
        }
        // Avec mutation
        if (decodedDetail.value.includes('Changement de propriétaire : MUTATION')) {
            specificFields = [
                { value: form.fiche_mutation, name: "Fiche de mutation CGI" },
            ];
            // Ancien propriétaire
            if (props.oldData?.vehicule?.physique_morale === 'Physique') {
                specificFields.push(
                    { value: form.type_piece_ancien_proprietaire, name: "Type de pièce (ancien propriétaire)" },
                    { value: form.piece_ancien_proprietaire, name: "Pièce d'identité de l'ancien propriétaire" },
                );
            } else {
                specificFields.push(
                    { value: form.registre_de_commerce, name: "Registre de commerce (ancien propriétaire)" },
                    { value: form.dfe, name: "DFE (ancien propriétaire)" },
                );
            }
            // Nouveau propriétaire
            if (props.newData?.vehicule?.physique_morale === 'Physique') {
                specificFields.push(
                    { value: form.type_piece_nouveau_proprietaire, name: "Type de pièce (nouveau propriétaire)" },
                    { value: form.piece, name: "Pièce d'identité du nouveau propriétaire" },
                );
            } else {
                specificFields.push(
                    { value: form.registre_de_commerce_nouvelle_entreprise, name: "Registre de commerce (nouveau propriétaire)" },
                    { value: form.dfe_nouvelle_entreprise, name: "DFE (nouveau propriétaire)" },
                );
            }
        }
    }
    return [...commonFields, ...specificFields];
};

// Soumission du formulaire
const submitForm = async () => {
    const requiredFields = getRequiredFields();
    for (const field of requiredFields) {
        if (!field.value) {
            toast.error(`${field.name} est obligatoire.`);
            return;
        }
    }
    isLoading.value = true;
    try {
        const formData = new FormData();
        Object.entries(form).forEach(([key, value]) => {
            if (value !== null) {
                formData.append(key, value);
            }
        });
        await axios.post("/numerisation/post/save", formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });
        toast.success("Documents enregistrés avec succès !");
        form.reset();
        router.visit("/numerisation/list");
    } catch (error) {
        console.error("Erreur lors de l'envoi des documents :", error);
        toast.error("Erreur lors de l'envoi des documents.");
    } finally {
        isLoading.value = false;
    }
};

// Retour en arrière
const returnBack = () => {
    window.history.back();
};
</script>





<script>
import { Card } from '@/components/ui/card'
import Main from '/resources/js/Pages/Main.vue'
import { Button } from '@/components/ui/button'
import { router, useForm } from '@inertiajs/vue3'
import { MoveLeft } from 'lucide-vue-next'
import { Toaster, toast } from 'vue-sonner'
import { Badge } from '@/components/ui/badge'
import CustomFileUpload from "@/components/CustomFileUpload.vue";
import { ScrollArea } from '@/components/ui/scroll-area'
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
export default {
    layout: Main,
}
</script>

<style scoped>
/* Petit spinner CSS */
.loader {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}
</style>