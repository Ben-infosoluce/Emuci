<template>
    <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <h4 class="text-2xl font-bold tracking-tight">Nouvelle Numérisation</h4>
        <Link :href="route('show.numerisation.list')">
        <Button>
            <MoveLeft class="w-4 h-4 mr-2" /> Retour
        </Button>
        </Link>
    </div>
    <div class="rounded-lg dark:border-gray-700">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <Card class="h-full flex flex-col gap-4 p-10">
                <ScrollArea class="h-full w-full">
                    <!-- En-tête -->
                    <div
                        class="flex flex-col space-y-4 py-2 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                        <h2><strong>Service :</strong> {{ dossier.r_dossier_services.nom_service }}</h2>
                        <h2>
                            Statut du dossier :
                            <Badge class="mx-2"
                                :variant="dossier?.statut === 1 ? 'warning' : dossier?.statut === 2 ? 'success' : dossier?.statut === 3 ? 'error' : 'secondary'">
                                {{
                                    dossier?.statut === 1 ? 'En attente' :
                                        dossier?.statut === 2 ? 'Validé' :
                                            dossier?.statut === 3 ? 'Refusé' : 'Inconnu'
                                }}
                            </Badge>
                        </h2>
                    </div>
                    <!-- Motif de rejet -->
                    <div v-if="dossier.motif_rejet" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-1 mb-1">
                        <p class="border border-red-300 rounded-md p-3">
                            <strong>Motif de rejet :</strong> {{ dossier.motif_rejet }}
                        </p>
                    </div>
                    <!-- Type de service -->
                    <h2>
                        <strong>Type de Service :</strong>
                        <template v-if="dossier.detail && Array.isArray(dossier.detail)">
                            {{ dossier.detail.join(', ') }}
                        </template>
                        <template v-else-if="dossier.detail && typeof dossier.detail === 'string'">
                            {{ JSON.parse(dossier.detail).join(', ') }}
                        </template>
                    </h2>
                    <!-- Titre des pièces justificatives -->
                    <h3 class="text-xl font-semibold mt-2 mb-2 text-center">
                        Pièces justificatifs (joindre les documents du dossier)
                    </h3>
                    <!-- Informations véhicule -->
                    <div>
                        <h1 class="font-semibold mt-4 mb-5">Informations Véhicule</h1>
                        <div class="w-full grid gap-4 md:gap-8 lg:grid-cols-3 xl:grid-cols-3">
                            <!-- Reçu d’achat (obligatoire pour "Immatriculation spéciale") -->
                            <div class="space-y-2"
                                v-if="dossier.r_dossier_services.nom_service == 'Immatriculation spéciale'">
                                <p>Reçu d’achat *</p>
                                <CustomFileUpload dropText="Reçu d’achat" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.recu_achat = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails du reçu d’achat" />
                            </div>
                            <!-- Vignette (obligatoire) -->
                            <div class="space-y-2">
                                <p>Vignette *</p>
                                <CustomFileUpload dropText="Vignette" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.vignette = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails de la vignette" />
                            </div>
                            <!-- Assurance en cours de validité (obligatoire) -->
                            <div class="space-y-2">
                                <p>Assurance en cours de validité *</p>
                                <CustomFileUpload dropText="Assurance en cours de validité"
                                    previewText="Fichier sélectionné" :accept="acceptedImageTypes"
                                    @input="form.assurance_en_cours_de_validite = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile"
                                    modal-title="Détails de l'assurance en cours de validité" />
                            </div>
                            <!-- D3 (obligatoire pour "Immatriculation spéciale") -->
                            <div class="space-y-2"
                                v-if="dossier.r_dossier_services.nom_service == 'Immatriculation spéciale'">
                                <p>D3 *</p>
                                <CustomFileUpload dropText="D3" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.d3 = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails du D3" />
                            </div>
                            <!-- Quitance de paiement (obligatoire pour "Immatriculation spéciale") -->
                            <div class="space-y-2"
                                v-if="dossier.r_dossier_services.nom_service == 'Immatriculation spéciale'">
                                <p>Quitance de paiement *</p>
                                <CustomFileUpload dropText="Quitance de paiement" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes"
                                    @input="form.quittance_paiement = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails de la quittance de paiement" />
                            </div>
                            <!-- Bon à enlever (obligatoire pour "Immatriculation spéciale") -->
                            <div class="space-y-2"
                                v-if="dossier.r_dossier_services.nom_service == 'Immatriculation spéciale'">
                                <p>Bon à enlever *</p>
                                <CustomFileUpload dropText="Bon à enlever" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.bon_a_enlever = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails du bon à enlever" />
                            </div>
                            <!-- Liste de colisage (obligatoire pour "Immatriculation spéciale") -->
                            <div class="space-y-2"
                                v-if="dossier.r_dossier_services.nom_service == 'Immatriculation spéciale'">
                                <p>Liste de colisage *</p>
                                <CustomFileUpload dropText="Liste de colisage" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.liste_colisage = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails de la liste de colisage" />
                            </div>
                            <!-- DCG (obligatoire) -->
                            <div class="space-y-2">
                                <p>DCG *</p>
                                <CustomFileUpload dropText="DCG" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.dcg = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails du DCG" />
                            </div>
                            <!-- RTI (obligatoire) -->
                            <div class="space-y-2">
                                <p>RTI *</p>
                                <CustomFileUpload dropText="RTI" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.rti = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails du RTI" />
                            </div>
                            <!-- Type de pièce (obligatoire) -->
                            <div class="space-y-2 mt-16">
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
                            <!-- Pièce d’identité du propriétaire (obligatoire) -->
                            <div class="space-y-2">
                                <p>Pièce d’identité du propriétaire *</p>
                                <CustomFileUpload dropText="Pièce d’identité du nouveau propriétaire"
                                    previewText="Fichier sélectionné" :accept="acceptedImageTypes"
                                    @input="form.piece = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile"
                                    modal-title="Détails de la pièce d’identité du nouveau propriétaire" />
                            </div>
                        </div>
                    </div>
                    <!-- Informations entreprise (obligatoire uniquement si "morale") -->
                    <div
                        v-if="dossier && dossier.r_dossier_vehicule && dossier.r_dossier_vehicule.physique_morale != 'Physique'">
                        <h1 class="font-semibold mt-8 mb-5">Informations entreprise</h1>
                        <div class="grid gap-4 md:gap-8 lg:grid-cols-3 xl:grid-cols-3 mt-3">
                            <!-- Registre de commerce (obligatoire si "morale") -->
                            <div class="space-y-2">
                                <p>Registre de commerce *</p>
                                <CustomFileUpload dropText="Registre de commerce" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes"
                                    @input="form.registre_de_commerce = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails du registre de commerce" />
                            </div>
                            <!-- DFE (obligatoire si "morale") -->
                            <div class="space-y-2">
                                <p>DFE *</p>
                                <CustomFileUpload dropText="DFE" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.dfe = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails du DFE" />
                            </div>
                        </div>
                    </div>
                    <!-- Bouton Enregistrer -->
                    <div class="w-full flex flex-row justify-between">
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
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

const acceptedImageTypes = ".jpeg,.png,.jpg,.webp";
const props = defineProps({
    dossier: Object,
    client: Object
});

const isLoading = ref(false);
const form = useForm({
    id_dossier: props.dossier.id,
    // Champs communs
    recu_achat: null,
    type_document: null,
    piece: null,
    vignette: null,
    assurance_en_cours_de_validite: null,
    // Nouveaux champs obligatoires
    dcg: null,
    rti: null,
    // Champs spécifiques à "Immatriculation spéciale"
    d3: null,
    quittance_paiement: null,
    bon_a_enlever: null,
    liste_colisage: null,
    // Champs entreprise (obligatoires si "morale")
    registre_de_commerce: null,
    dfe: null,
    // Timestamps
    created_at: Date.now(),
    updated_at: Date.now()
});

const submitForm = async () => {
    try {
        const requiredFields = [
            { value: form.type_document, name: "Type de pièce" },
            { value: form.piece, name: "Pièce d’identité du propriétaire" },
            { value: form.vignette, name: "Vignette" },
            { value: form.assurance_en_cours_de_validite, name: "Assurance en cours de validité" },
            { value: form.dcg, name: "DCG" },
            { value: form.rti, name: "RTI" },
        ];

        // Champs obligatoires pour "Immatriculation spéciale"
        if (props.dossier.r_dossier_services.nom_service === "Immatriculation spéciale") {
            requiredFields.push(
                { value: form.recu_achat, name: "Reçu d’achat" },
                { value: form.d3, name: "D3" },
                { value: form.quittance_paiement, name: "Quitance de paiement" },
                { value: form.bon_a_enlever, name: "Bon à enlever" },
                { value: form.liste_colisage, name: "Liste de colisage" }
            );
        }

        // Champs obligatoires si "morale"
        if (props.dossier.r_dossier_vehicule && props.dossier.r_dossier_vehicule.physique_morale != 'Physique') {
            requiredFields.push(
                { value: form.registre_de_commerce, name: "Registre de commerce" },
                { value: form.dfe, name: "DFE" }
            );
        }

        // Vérification des champs obligatoires
        for (const field of requiredFields) {
            if (!field.value) {
                toast.error(`${field.name} est obligatoire.`);
                return;
            }
        }

        isLoading.value = true;
        const formData = new FormData();
        Object.entries(form).forEach(([key, value]) => {
            if (value !== null && value !== undefined) {
                formData.append(key, value);
            }
        });

        await axios.post("/numerisation/ops/save", formData, {
            headers: { "Content-Type": "multipart/form-data" },
        });

        toast.success("Documents enregistrés avec succès !");
        form.reset();
        router.visit("/numerisation/list");
    } catch (error) {
        console.error(error);
        toast.error("Erreur lors de l'envoi des documents");
    } finally {
        isLoading.value = false;
    }
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