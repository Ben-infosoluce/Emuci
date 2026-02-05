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


                <h3 class="text-xl font-semibold mt-5 mb-5 text-center">
                    Pièces justificatifs (joindre les documents du dossier)
                </h3>

                <div>
                    <h1 class=" font-semibold mt-4 mb-5">Informations Véhicule</h1>
                    <div class="w-full grid gap-4 md:gap-8 lg:grid-cols-3 xl:grid-cols-3">
                        <div class="space-y-2">
                            <p>Carte grise *</p>
                            <div>
                                <Input type="file" :accept="acceptedImageTypes" @input="
                                    form.carte_grise = $event.target.files[0]
                                    " placeholder="5771905005" class="py-0 h-10" /><!-- Message d'erreur -->
                            </div>
                        </div>

                        <div class="space-y-2">
                            <p>Type de pièce *</p>
                            <div>
                                <select v-model="form.type_document"
                                    class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-gray-200 disabled:opacity-50">
                                    <option value="" disabled selected>Sélectionner un type</option>
                                    <option value="carte_grise">Carte grise</option>
                                    <option value="piece_identite">Pièce d'identité</option>
                                    <option value="passeport">Passeport</option>
                                </select>
                                <!-- Message d'erreur (optionnel) -->
                                <div v-if="form.errors.type_document" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.type_document }}
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <p>Pièce d'identité *</p>
                            <div>
                                <Input type="file" :accept="acceptedImageTypes" @input="
                                    form.piece = $event.target.files[0]
                                    " placeholder="5771905005" class="py-0 h-10" /><!-- Message d'erreur -->
                            </div>
                        </div>

                        <div class="space-y-2">
                            <p>Certificat de visite technique *</p>
                            <div>
                                <Input type="file" :accept="acceptedImageTypes" @input="
                                    form.certificat_visite_technique =
                                    $event.target.files[0]
                                    " placeholder="5771905005" class="py-0 h-10" /><!-- Message d'erreur -->
                            </div>
                        </div>

                        <div class="space-y-2">
                            <p>Assurance en cours de validaité *</p>
                            <div>
                                <Input type="file" :accept="acceptedImageTypes"
                                    @input="form.assurance = $event.target.files[0]" placeholder="5771905005"
                                    class="py-0 h-10" /><!-- Message d'erreur -->
                            </div>
                        </div>

                        <div class="space-y-2">
                            <p>Déclaration de perte *</p>
                            <div>
                                <Input type="file" :accept="acceptedImageTypes" @input="
                                    form.declaration_perte =
                                    $event.target.files[0]
                                    " placeholder="5771905005" class="py-0 h-10" /><!-- Message d'erreur -->
                            </div>
                        </div>

                        <div class="space-y-2">
                            <p>Certificat de résidence*</p>
                            <div>
                                <Input type="file" :accept="acceptedImageTypes" @input="
                                    form.certificat_residence =
                                    $event.target.files[0]
                                    " placeholder="5771905005" class="py-0 h-10" /><!-- Message d'erreur -->
                            </div>
                        </div>
                    </div>
                </div>


                <!-- information entreprise -->
                <div v-if="dossier && dossier.r_dossier_vehicule">
                    <!-- Condition sur physique_morale -->
                    <div v-if="dossier.r_dossier_vehicule.physique_morale === 1">
                        <h1 class=" font-semibold mt-8 mb-5">Informations du Propriétaire</h1>
                        <div class="grid gap-4 md:gap-8 lg:grid-cols-3 xl:grid-cols-3">
                            <!-- Champs spécifiques au client -->
                            <div class="space-y-2">
                                <p>Registre de commerce *</p>
                                <Input type="file" :accept="acceptedImageTypes"
                                    @input="form.registre_commerce = $event.target.files[0]" class="py-0 h-10" />
                            </div>
                            <div class="space-y-2">
                                <p>Pièce d'identité en cours de validité *</p>
                                <Input type="file" :accept="acceptedImageTypes"
                                    @input="form.piece_entreprise_commerce = $event.target.files[0]"
                                    class="py-0 h-10" />
                            </div>
                            <div class="space-y-2">
                                <p>Autorisation de la société de crédit *</p>
                                <Input type="file" :accept="acceptedImageTypes"
                                    @input="form.autorisation_societe_credit = $event.target.files[0]"
                                    class="py-0 h-10" />
                            </div>
                            <div class="space-y-2">
                                <p>Extrait de carte grise *</p>
                                <Input type="file" :accept="acceptedImageTypes"
                                    @input="form.extrait_carte_grise = $event.target.files[0]" class="py-0 h-10" />
                            </div>
                        </div>
                    </div>

                    <div v-else>
                        <h1 class="text-xl font-semibold mt-4">Informations entreprise</h1>
                        <div class="grid gap-4 md:gap-8 lg:grid-cols-3 xl:grid-cols-3">
                            <!-- Champs spécifiques à l'entreprise -->
                            <div class="space-y-2">
                                <p>Registre de commerce *</p>
                                <Input type="file" :accept="acceptedImageTypes"
                                    @input="form.registre_commerce = $event.target.files[0]" class="py-0 h-10" />
                            </div>
                            <div class="space-y-2">
                                <p>Pièce d'identité en cours de validité *</p>
                                <Input type="file" :accept="acceptedImageTypes"
                                    @input="form.piece_entreprise_commerce = $event.target.files[0]"
                                    class="py-0 h-10" />
                            </div>
                            <div class="space-y-2">
                                <p>Autorisation de la société de crédit *</p>
                                <Input type="file" :accept="acceptedImageTypes"
                                    @input="form.autorisation_societe_credit = $event.target.files[0]"
                                    class="py-0 h-10" />
                            </div>
                            <div class="space-y-2">
                                <p>Extrait de carte grise *</p>
                                <Input type="file" :accept="acceptedImageTypes"
                                    @input="form.extrait_carte_grise = $event.target.files[0]" class="py-0 h-10" />
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
    </div>
    <Toaster richColors position="top-right" />
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { Input } from '@/components/ui/input'
import Pagination from '@/components/Pagination.vue'
import { Input_search } from '@/components/ui/Input_search'
import { MoveRight, MoveLeft, Plus } from 'lucide-vue-next'
import { Toaster, toast } from 'vue-sonner'
import BoutonRetour from "/resources/js/components/BoutonRetour.vue";

const acceptedImageTypes = ".jpeg,.png,.jpg,.webp";

const props = defineProps({
    dossier: Object,
    client: Object
});


// Initialisation du formulaire avec les paramètres d'URL
const params = new URLSearchParams(window.location.search)
const form = useForm({
    id_dossier: props.dossier.id,
    // Nouveaux champs pour les pièces justificatives
    carte_grise: null,
    type_document: null,         // ✅ Remplace `type_piece`
    piece: null,                 // ✅ Remplace `piece_identite`
    certificat_visite_technique: null,
    assurance: null,
    declaration_perte: null,
    certificat_residence: null,
    // entreprise ou client (facultatif)
    registre_commerce: null,
    piece_entreprise_commerce: null,
    autorisation_societe_credit: null,
    extrait_carte_grise: null,
    created_at: Date.now(),
    updated_at: Date.now()
});



function sendDocument(formData) {
    return new Promise((resolve, reject) => {
        axios.post('/numerisation/save', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
            .then(response => {
                resolve(response)
            })
            .catch(error => {
                console.log(error);
                reject(error)
            })
    })
}

// Utilisation :
const submitForm = async () => {
    const requiredFields = [
        { value: form.id_dossier, name: "Dossier de numérisation" },
        { value: form.carte_grise, name: "Carte grise" },
        { value: form.type_document, name: "Type de pièce" },
        { value: form.piece, name: "Pièce d'identité" },
        { value: form.certificat_visite_technique, name: "Certificat de visite technique" },
        { value: form.assurance, name: "Assurance en cours de validité" },
        { value: form.declaration_perte, name: "Déclaration de perte" },
        { value: form.certificat_residence, name: "Certificat de résidence" },
    ]

    for (const field of requiredFields) {
        if (!field.value) {
            toast.error(`${field.name} est obligatoire.`);
            return
        }
    }

    const formData = new FormData()
    Object.entries(form.data()).forEach(([key, value]) => {
        if (value !== null && value !== undefined) {
            formData.append(key, value)
        }
    })

    try {
        const response = await sendDocument(formData)
        toast.success("Document enregistré avec succès !");
        form.reset()
        // Redirection vers la liste des numérisations
        router.visit("/numerisation/list")

    } catch (error) {
        toast.error("Une erreur est survenue lors de l'enregistrement");
        console.error("Erreur :", error)

        if (error?.response?.data?.message) {
            toast.error(error.response.data.message);
        } else {
            toast.error("Échec de l'envoi des documents.");
        }
    }
}
</script>

<script>
import { ref, onMounted, watch } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import Main from '/resources/js/Pages/Main.vue'
import { Button } from '@/components/ui/button'
import Pagination from '@/components/Pagination.vue'
import { Input } from '@/components/ui/input'
import { toast } from 'vue-sonner'
import { router } from '@inertiajs/vue3'
export default {
    layout: Main,
}
</script>
