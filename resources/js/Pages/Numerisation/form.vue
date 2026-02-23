<template>
    <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <!-- Titre -->
        <h4 class="text-2xl font-bold tracking-tight">
            Nouvelle Numérisation
        </h4>

        <!-- Bouton Nouveau -->
        <Link :href="route('show.numerisation.list')">
        <Button>
            <MoveLeft class="w-4 h-4 mr-2" /> Retour
        </Button>
        </Link>
    </div>

    <div class="rounded-lg dark:border-gray-700">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <Card class="h-full flex flex-col gap-4 p-10">
                <!-- Pièce justificatifs -->
                <div class=" flex flex-col space-y-4  py-2 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                    <h2> <strong>Service : </strong>{{ dossier.r_dossier_services.nom_service }}</h2>
                    <h2>Statut du dossier : <Badge class="mx-2"
                            :variant="dossier?.statut === 1 ? 'warning' : dossier?.statut === 2 ? 'success' : dossier?.statut === 3 ? 'error' : 'secondary'">
                            {{
                                dossier?.statut === 1
                                    ? 'En attente'
                                    : dossier?.statut === 2
                                        ? 'Validé'
                                        : dossier?.statut === 3
                                            ? 'Refusé'
                                            : 'Inconnu'
                            }}
                        </Badge>
                    </h2>

                </div>
                <!-- ici -->
                <div v-if="dossier.motif_rejet">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-1 mb-1">
                        <p class="border border-red-300 rounded-md p-3">
                            <strong>Motif de rejet :</strong> {{ dossier.motif_rejet }}
                        </p>
                    </div>
                </div>
                <h2> <strong>Type de Service : </strong>
                    <template v-if="dossier.detail && Array.isArray(dossier.detail)">
                        {{ dossier.detail.join(', ') }}
                    </template>

                    <template v-else-if="dossier.detail && typeof dossier.detail === 'string'">
                        {{ JSON.parse(dossier.detail).join(', ') }}
                    </template>
                </h2>
                <h3 class="text-xl font-semibold mt-2 mb-2 text-center">
                    Pièces justificatifs (joindre les documents du dossier)
                </h3>

                <div>
                    <h1 class="font-semibold mt-4 mb-5">Informations Véhicule</h1>
                    <div class="w-full grid gap-4 md:gap-8 lg:grid-cols-3 xl:grid-cols-3">
                        <!-- carte grise  -->
                        <div class="space-y-2"
                            v-if="dossier.r_dossier_services.nom_service != 'Immatriculation spéciale'">
                            <p>Carte grise *</p>
                            <div>
                                <CustomFileUpload dropText="Carte grise" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.carte_grise = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails de la carte grise" />
                            </div>
                        </div>
                        <!-- Reçu d’achat  -->
                        <div class="space-y-2"
                            v-if="dossier.r_dossier_services.nom_service == 'Immatriculation spéciale'">
                            <p>Reçu d’achat *</p>
                            <div>
                                <CustomFileUpload dropText="Reçu d’achat" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.recu_achat = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails du reçu d’achat" />
                            </div>
                        </div>

                        <!-- Type de pièce * -->
                        <div class="space-y-2">
                            <!-- <p>Type de pièce *</p>
                            <div>
                                <div class="flex justify-start">
                                    <div class="flex flex-col items-start space-y-2">
                                        <div v-for="(option, index) in options" :key="index"
                                            class="group rounded-xl border transition-all cursor-pointer hover:shadow-xl shadow py-2 px-2 mb-2 flex-shrink-0"
                                            :class="{
                                                'border-gray-300 bg-muted': selected === option,
                                                'border-muted hover:border-gray-100': selected !== option
                                            }" @click="toggleSelection(option)" style="width: 250px;">
                                            <div class="flex items-start space-x-2 px-4 py-6">
                                                <Checkbox :id="'option-' + index" :checked="selected === option" />
                                                <Label class="text-sm font-medium text-gray-900 cursor-pointer"
                                                    :for="'option-' + index">
                                                    {{ option }}
                                                </Label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
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
                        </div>

                        <!-- Pièce d'identité -->
                        <div class="space-y-2">
                            <p>Pièce d'identité *</p>
                            <div>
                                <CustomFileUpload dropText="Pièce d'identité" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes"
                                    @input="form.piece_identite_en_cours_de_validite = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails de la pièce d'identité" />
                            </div>
                        </div>

                        <!-- Vignette  -->
                        <div class="space-y-2">
                            <p>Vignette *</p>
                            <div>
                                <CustomFileUpload dropText="Vignette" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.vignette = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails de la vignette" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <p>Assurance en cours de validité *</p>
                            <div>
                                <CustomFileUpload dropText="Assurance en cours de validité"
                                    previewText="Fichier sélectionné" :accept="acceptedImageTypes"
                                    @input="form.assurance_en_cours_de_validite = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile"
                                    modal-title="Détails de l'assurance en cours de validité" />
                            </div>
                        </div>

                        <!-- D3V -->
                        <div class="space-y-2"
                            v-if="dossier.r_dossier_services.nom_service == 'Immatriculation spéciale'">
                            <p>D3V *</p>
                            <div>
                                <CustomFileUpload dropText="D3V" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.d3v = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails du D3V" />
                            </div>
                        </div>

                        <!-- Quitance de paiement -->
                        <div class="space-y-2"
                            v-if="dossier.r_dossier_services.nom_service == 'Immatriculation spéciale'">
                            <p>Quitance de paiement *</p>
                            <div>
                                <CustomFileUpload dropText="Quitance de paiement" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes"
                                    @input="form.quittance_paiement = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails de la quittance de paiement" />
                            </div>
                        </div>

                        <!-- Bon à enlever -->
                        <div class="space-y-2"
                            v-if="dossier.r_dossier_services.nom_service == 'Immatriculation spéciale'">
                            <p>Bon à enlever *</p>
                            <div>
                                <CustomFileUpload dropText="Bon à enlever" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.bon_a_enlever = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails du bon à enlever" />
                            </div>
                        </div>
                        <!-- Liste de colisage -->
                        <div class="space-y-2"
                            v-if="dossier.r_dossier_services.nom_service == 'Immatriculation spéciale'">
                            <p>Liste de colisage *</p>
                            <div>
                                <CustomFileUpload dropText="Liste de colisage" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.liste_colisage = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails de la liste de colisage" />
                            </div>
                        </div>

                        <!-- si Re-immatriculation -->
                        <div class="space-y-2" v-if="dossier.r_dossier_services.nom_service == 'Re-immatriculation'">
                            <p>RTI de re-immat *</p>
                            <div>
                                <CustomFileUpload dropText="RTI de re-immat" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.rit_reimmat = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails du RTI de re-immat" />
                            </div>
                        </div>
                        <!-- si Duplicata -->
                        <div class="space-y-2" v-if="dossier.r_dossier_services.nom_service == 'Duplicata'">
                            <p>Déclaration de perte *</p>
                            <div>
                                <CustomFileUpload dropText="Déclaration de perte" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes"
                                    @input="form.declaration_de_perte = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails de la déclaration de perte" />
                            </div>
                        </div>

                        <div class="space-y-2" v-if="dossier.r_dossier_services.nom_service == 'Duplicata'">
                            <p>RTI de chute de plaque *</p>
                            <div>
                                <CustomFileUpload dropText="RTI de chute de plaque" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.rti_chute_plaque = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails du RTI de chute de plaque" />
                            </div>
                        </div>
                        <!-- si Duplicata -->
                        <!-- <div class="space-y-2" v-if="dossier.r_dossier_services.nom_service == 'Duplicata'">
                            <p>Pièce d’identité de l’ancien propriétaire</p>
                            <div>
                                <CustomFileUpload dropText="Pièce d’identité de l’ancien propriétaire"
                                    previewText="Fichier sélectionné" :accept="acceptedImageTypes"
                                    @input="form.piece_ancien_proprietaire = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile"
                                    modal-title="Détails de la pièce d’identité de l’ancien propriétaire" />
                            </div>
                        </div> -->
                        <!--Fin si Duplicata -->
                        <!-- si Post-immatriculation -->
                        <div class="space-y-2" v-if="dossier.r_dossier_services.nom_service == 'Post-immatriculation'">
                            <p>RTI signalant la modification (usage, couleur..)*</p>
                            <div>
                                <CustomFileUpload dropText="RTI signalant la modification"
                                    previewText="Fichier sélectionné" :accept="acceptedImageTypes"
                                    @input="form.rti_modification = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile"
                                    modal-title="Détails du RTI signalant la modification" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Changement de propriétaire  si Mutation dans Post-immatriculation-->
                <div
                    v-if="dossier.r_dossier_services.nom_service === 'Post-immatriculation' && (dossier.detail && dossier.detail.includes('Changement de propriétaire : MUTATION'))">
                    <div>
                        <h1 class=" font-semibold mt-8 mb-5">Changement de propriétaire : Mutation</h1>
                        <div class="grid gap-4 md:gap-8 lg:grid-cols-3 xl:grid-cols-3 mt-3">
                            <div class="space-y-2 ">
                                <p>FICHE DE MUTATION CGI*</p>
                                <CustomFileUpload dropText="FICHE DE MUTATION CGI" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes" @input="form.fiche_mutation = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails de la FICHE DE MUTATION CGI" />
                            </div>
                            <div class="space-y-2">
                                <p>Pièce d’identité de l’ancien propriétaire *</p>
                                <CustomFileUpload dropText="Pièce d’identité de l’ancien propriétaire"
                                    previewText="Fichier sélectionné" :accept="acceptedImageTypes"
                                    @input="form.piece_ancien_proprietaire = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile"
                                    modal-title="Détails de la pièce d’identité de l’ancien propriétaire" />
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Changement de propriétaire  si Mutation Re-immatriculation -->
                <div v-if="dossier.r_dossier_services.nom_service === 'Re-immatriculation'">
                    <div>
                        <h1 class=" font-semibold mt-8 mb-5">Mutation (Replire uniquement s'il y a une mutation)
                        </h1>
                        <div class="grid gap-4 md:gap-8 lg:grid-cols-3 xl:grid-cols-3 mt-3">
                            <div class="space-y-2 ">
                                <p class="text-md">Facture de CIE ou SODECI</p>
                                <CustomFileUpload dropText="Facture de CIE ou SODECI (au nom du nouveau propriétaire)"
                                    previewText="Fichier sélectionné" :accept="acceptedImageTypes"
                                    @input="form.facture_cie_sodeci = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails de la facture de CIE ou SODECI" />
                            </div>
                            <div class="space-y-2">
                                <p>FICHE DE MUTATION CGI</p>
                                <CustomFileUpload dropText="FICHE DE MUTATION CGI" previewText="Fichier sélectionné"
                                    :accept="acceptedImageTypes"
                                    @input="form.fiche_mutation_cgi = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile" modal-title="Détails de la FICHE DE MUTATION CGI" />
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
                    </div>
                </div>
                <!-- <p>{{ dossier }}</p> -->
                <!-- information entreprise si Morale -->
                <div v-if="dossier && dossier.r_dossier_vehicule">
                    <!-- Condition sur physique_morale -->
                    <div v-if="dossier.r_dossier_vehicule.physique_morale != 'Physique'">
                        <h1 class=" font-semibold mt-8 mb-5">Informations entreprise</h1>
                        <div class="grid gap-4 md:gap-8 lg:grid-cols-3 xl:grid-cols-3 mt-3">
                            <!-- Champs spécifiques à l'entreprise -->
                            <div class="space-y-2 ">
                                <p>Registre de commerce *</p>
                                <CustomFileUpload dropText="Registre de commerce *" previewText="Registre de commerce"
                                    :accept="acceptedImageTypes"
                                    @input="form.registre_de_commerce = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile"
                                    modal-title="Détails du RTI signalant la modification" />
                            </div>
                            <div class="space-y-2">
                                <p>DFE </p>
                                <CustomFileUpload dropText="DFE *" previewText="DFE" :accept="acceptedImageTypes"
                                    @input="form.dfe = $event.target.files[0]"
                                    previewPlaceholder="Aucune image sélectionnée" :dossier="dossier"
                                    @file-selected="handleFile"
                                    modal-title="Détails du RTI signalant la modification" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="w-full flex flex-row justify-between">
                    <div></div>
                    <div>
                        <Button @click="submitForm"
                            class="bg-amber-800 text-wite py-4 px-4 rounded-lg text-white">Enregistrer</Button>
                    </div>
                </div>
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

const acceptedImageTypes = ".jpeg,.png,.jpg,.webp";

const props = defineProps({
    dossier: Object,
    client: Object
});

const isLoading = ref(false);
const detail = ref(null)
const physiqueMorale = ref(null)
const service = ref(null)
const decodedDetail = ref([])

const options = ["Pièce d'identité", "Passeport"]
const selected = ref(null)
const toggleSelection = (option) => {
    selected.value = selected.value === option ? null : option
}
onMounted(() => {
    const params = new URLSearchParams(window.location.search)

    detail.value = params.get("detail")
    physiqueMorale.value = params.get("physique_morale")
    service.value = params.get("service")

    if (detail.value) {
        decodedDetail.value = JSON.parse(detail.value)
    }
    // console.log(detail.value, physiqueMorale.value, service.value)
    // console.log("detail : ", detail.value)
    // console.log("physiqueMorale : ", physiqueMorale.value)
    // console.log("service : ", service.value)

})
// Initialisation du formulaire avec les paramètres d'URL
const params = new URLSearchParams(window.location.search)

const form = useForm({
    id_dossier: props.dossier.id,
    // Champs communs
    carte_grise: null,
    type_document: null,
    piece_identite_en_cours_de_validite: null,
    vignette: null,
    assurance_en_cours_de_validite: null,
    // Champs spécifiques à certains services
    declaration_de_perte: null,
    rti_chute_plaque: null,
    rit_reimmat: null,
    rti_modification: null,
    fiche_mutation: null,
    fiche_mutation_cgi: null,
    piece_ancien_proprietaire: null,
    facture_cie_sodeci: null,
    // Champs entreprise (si morale)
    registre_de_commerce: null,
    dfe: null,
    // Autres champs optionnels ou non utilisés dans le template mais présents dans votre exemple
    autorisation_societe_credit: null,
    extrait_carte_grise: null,
    // Timestamps
    created_at: Date.now(),
    updated_at: Date.now()
});

const submitForm = async () => {
    console.log("Submitting form with data:", form);

    //form.type_document = selected.value;

    try {
        const requiredFields = [
            { value: form.carte_grise, name: "Carte grise" },
            { value: form.type_document, name: "Type de pièce" },
            { value: form.piece_identite_en_cours_de_validite, name: "Pièce d'identité" },
            { value: form.vignette, name: "Vignette" },
            { value: form.assurance_en_cours_de_validite, name: "Assurance en cours de validité" },
        ];

        for (const field of requiredFields) {
            if (!field.value) {
                toast.error(`${field.name} est obligatoire.`);
                return;
            }
        }
        isLoading.value = true;
        // Création FormData
        const formData = new FormData();
        Object.entries(form.data()).forEach(([key, value]) => {
            if (value !== null) {
                formData.append(key, value);
            }
        });

        await axios.post("/numerisation/save", formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });

        toast.success("Documents enregistrés avec succès !");
        form.reset();
        // window.location.href = "/numerisation/list";
        router.visit("/numerisation/list");
    } catch (error) {
        console.error(error);
        toast.error("Erreur lors de l'envoi des documents");
    } finally {
        isLoading.value = false; // ✅ Désactive le loader même en cas d'erreur
    }
};

</script>

<script>
import { ref, onMounted, watch } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import Main from '/resources/js/Pages/Main.vue'
import { Button } from '@/components/ui/button'
import Pagination from '@/components/Pagination.vue'
import { Input } from '@/components/ui/input'
import { router, useForm } from '@inertiajs/vue3'
import { Input_search } from '@/components/ui/Input_search'
import { MoveRight, MoveLeft, Plus } from 'lucide-vue-next'
import { Toaster, toast } from 'vue-sonner'
import BoutonRetour from "/resources/js/components/BoutonRetour.vue";
import { Badge } from '@/components/ui/badge'
import CustomFileUpload from "@/components/CustomFileUpload.vue";
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'
import imageCompression from "browser-image-compression";
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