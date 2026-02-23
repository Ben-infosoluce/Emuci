<template>
    <!-- Header sticky -->
    <div
        class="sticky top-[-20px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8  py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <h4 class="text-2xl font-bold tracking-tight">
            Détails du dossiers
        </h4>
        <div class="flex items-center space-x-2">
            <Button @click="returnBack()">
                <MoveLeft class="w-4 h-4 mr-2" /> Retour
            </Button>
        </div>
    </div>

    <!-- Contenu scrollable -->
    <div class="rounded-lg dark:border-gray-700 ">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <div class="grid gap-4 md:gap-8 lg:grid-cols-2 xl:grid-cols-3">
                <Card class="xl:col-span-2">
                    <ScrollArea class="h-full w-full rounded-md border">
                        <!-- Si pas Post-immatriculation -->
                        <div class="m-8" v-if="dossier.id_service != 3">
                            <!-- Service & statut du dossier -->
                            <div
                                class=" flex flex-col space-y-4  py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                                <h2> <strong>Service : </strong>{{ dossier.r_dossier_services.nom_service }} <samp> /{{
                                    dossier_lier ? dossier_lier.r_dossier_services.nom_service : '' }}</samp></h2>
                                <h2>Statut du dossier :
                                    <Badge :variant="dossier?.statut === 1
                                        ? 'warning'
                                        : dossier?.statut === 2
                                            ? 'success'
                                            : dossier?.statut === 3
                                                ? 'error'
                                                : dossier?.statut === 4
                                                    ? 'warning'
                                                    : 'secondary'">
                                        {{
                                            dossier?.statut === 2
                                                ? 'Validé'
                                                : dossier?.statut === 3
                                                    ? 'Refusé'
                                                    : 'En attente de validation'
                                        }}
                                    </Badge>
                                </h2>

                            </div>

                            <!-- Type de Service & motif de rejet -->
                            <div
                                class=" flex flex-col space-y-4  py-2 sm:flex-row sm:items-center justify-between sm:space-y-0">
                                <div>
                                    <h2>
                                        <strong>Type de Service : </strong>
                                        <template v-if="dossier.detail && Array.isArray(dossier.detail)">
                                            {{ dossier.detail.join(', ') }} <samp>{{ dossier_lier ?
                                                dossier_lier.detail.join(', ') : '' }} </samp>
                                        </template>
                                        <template v-else-if="dossier.detail && typeof dossier.detail === 'string'">
                                            {{ JSON.parse(dossier.detail).join(', ') }} <samp>,{{
                                                dossier_lier ? JSON.parse(dossier_lier.detail).join(', ') : '' }}
                                            </samp>
                                        </template>
                                    </h2>
                                </div>
                            </div>

                            <!-- Informations du véhicule -->
                            <h3 class="mt-3"> <strong> Informations du véhicule</strong> </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 mb-6">
                                <p>Châssis (VIN) : {{ dossier.r_dossier_vehicule.vin }}</p>
                                <p>Marque du véhicule : {{ dossier.r_dossier_vehicule.marque }}</p>
                                <p>Modèle du véhicule : {{ dossier.r_dossier_vehicule.modele }}</p>
                                <p>Genre : {{ dossier.r_dossier_vehicule.genre_vehicule }}</p>
                                <p>Poids Total en charge : {{ dossier.r_dossier_vehicule.poids_total_charge }}</p>
                                <p>Poids Utile : {{ dossier.r_dossier_vehicule.poids_utile }}</p>
                                <p>Sources d’énergie : {{ dossier.r_dossier_vehicule.source_energie }}</p>
                                <p>Couleur : {{ dossier.r_dossier_vehicule.couleur }}</p>
                                <p>Carrosserie : {{ dossier.r_dossier_vehicule.carrosserie }}</p>
                                <p>Type technique : {{ dossier.r_dossier_vehicule.type_technique }}</p>
                                <p>Poids à Vide : {{ dossier.r_dossier_vehicule.poids_vide }}</p>
                                <p>Puissance administrative : {{ dossier.r_dossier_vehicule.puissance_administrative
                                    }}
                                </p>
                                <p>Places Assises : {{ dossier.r_dossier_vehicule.places_assises }}</p>
                                <p>Nbre d’Essieux : {{ dossier.r_dossier_vehicule.nombre_essieux }}</p>
                                <p>Usage : {{ dossier.r_dossier_vehicule.usage_vehicule }}</p>
                                <p v-if="dossier.r_dossier_vehicule.num_immatriculation">N° d'immatriculation : {{
                                    dossier.r_dossier_vehicule.num_immatriculation }}</p>
                                <p v-if="dossier.r_dossier_vehicule.num_immatriculation_precedant">N°
                                    d'immatriculation precedant : {{
                                        dossier.r_dossier_vehicule.num_immatriculation_precedant }}</p>
                                <p v-if="dossier.reserverNumero">N°
                                    réservé : {{
                                        dossier.reserverNumero }}</p>
                                <p v-if="dossier.numeroDiplomatique">N°
                                    d'immatriculation diplomatique : {{
                                        dossier.numeroDiplomatique }}</p>
                            </div>
                            <hr />
                            <!-- Informations du propriétaire -->
                            <h3 class="mt-6"> <strong> Informations du propriétaire : </strong>
                                {{ dossier.r_dossier_client.civilite }} , {{ dossier.r_dossier_client.nom
                                }} {{ dossier.r_dossier_client.prenom }}
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 mb-6">
                                <p>Adresse : {{ dossier.r_dossier_client.adresse }}</p>
                                <p>Email : {{ dossier.r_dossier_client.email }}</p>
                                <p>Telephone : {{ dossier.r_dossier_client.telephone }}</p>
                                <p>Date de naissance : {{ dossier.r_dossier_client.date_naissance }}</p>
                                <p>Ville de naissance : {{ dossier.r_dossier_client.ville_naissance }}</p>
                            </div>
                            <hr />
                            <!-- Informations de l'entreprise -->
                            <h3 class="mt-6" v-if="entreprise"> <strong> Informations de l'entreprise </strong> </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 mb-6" v-if="entreprise">
                                <p>Nom Entreprise : {{ entreprise?.nom_entreprise }}</p>
                                <p>N° de compte contribuale : {{ entreprise?.compte_contribuale }}</p>
                                <p>Registre de commerce : {{ entreprise?.registre_commerce }}</p>
                                <p>Nom du représentant légal : {{ entreprise?.nom_representant_legal }}</p>
                                <p>Téléphone du représentant légal : {{ entreprise?.telephone_representant_legal }}</p>
                                <p>Profession représentant légal : {{ entreprise?.profession_representant_legal }}</p>
                                <p>Date de naissance du représentant : {{
                                    entreprise.date_de_naissance_representant_legal
                                    }}</p>

                            </div>
                        </div>


                        <!-- Pour Post-Immatriculation -->
                        <div class="m-8" v-if="dossier.id_service == 3">
                            <div
                                class=" flex flex-col space-y-4  py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                                <h2>Service : {{ dossier.r_dossier_services.nom_service }}</h2>
                                <h2>Statut du dossier :
                                    <Badge :variant="dossier?.statut === 1
                                        ? 'warning'
                                        : dossier?.statut === 2
                                            ? 'success'
                                            : dossier?.statut === 3
                                                ? 'error'
                                                : dossier?.statut === 4
                                                    ? 'warning'
                                                    : 'secondary'">
                                        {{
                                            dossier?.statut === 2
                                                ? 'Validé'
                                                : dossier?.statut === 3
                                                    ? 'Refusé'
                                                    : 'En attente de validation'
                                        }}
                                    </Badge>

                                </h2>
                            </div>
                            <!-- Type de Service & motif de rejet -->
                            <div
                                class=" flex flex-col space-y-4  py-2 sm:flex-row sm:items-center justify-between sm:space-y-0">
                                <div>
                                    <h2>
                                        <strong>Type de Service : </strong>
                                        <template v-if="dossier.detail && Array.isArray(dossier.detail)">
                                            {{ dossier.detail.join(', ') }}
                                        </template>
                                        <template v-else-if="dossier.detail && typeof dossier.detail === 'string'">
                                            {{ JSON.parse(dossier.detail).join(', ') }}
                                        </template>
                                    </h2>
                                </div>
                            </div>
                            <!-- Anciennes Informations -->
                            <div class="mb-6">
                                <h3 class="text-red-600 font-bold uppercase mt-6 mb-6">Anciennes Informations</h3>
                                <!--Informations du véhicule  -->
                                <p class=" font-bold my-4">Informations du véhicule </p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm my-4">
                                    <InfoRow label="Châssis (VIN)" :value="oldData.vehicule.vin" />
                                    <InfoRow label="Couleur" :value="oldData.vehicule.couleur" />
                                    <InfoRow label="Marque du véhicule" :value="oldData.vehicule.marque" />
                                    <InfoRow label="Carrosserie" :value="oldData.vehicule.carrosserie" />
                                    <InfoRow label="Modèle" :value="oldData.vehicule.modele" />
                                    <InfoRow label="Type technique" :value="oldData.vehicule.type_technique" />
                                    <InfoRow label="Genre" :value="oldData.vehicule.genre_vehicule" />
                                    <InfoRow label="Poids à Vide" :value="oldData.vehicule.poids_vide" />
                                    <InfoRow label="Poids Total en charge"
                                        :value="oldData.vehicule.poids_total_charge" />
                                    <InfoRow label="Puissance administrative"
                                        :value="oldData.vehicule.puissance_administrative" />
                                    <InfoRow label="Poids Utile" :value="oldData.vehicule.poids_utile" />
                                    <InfoRow label="Places Assises" :value="oldData.vehicule.places_assises" />
                                    <InfoRow label="Sources d’énergie" :value="oldData.vehicule.source_energie" />
                                    <InfoRow label="Nbre d’Essieux" :value="oldData.vehicule.nombre_essieux" />
                                </div>
                                <hr>
                                <!--Informations du propriétaire  -->
                                <p class=" font-bold my-4">Informations du propriétaire</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm my-4">
                                    <InfoRow label="Civilité" :value="oldData.client.civilite" />
                                    <InfoRow label="Nom" :value="oldData.client.nom" />
                                    <InfoRow label="Prénom" :value="oldData.client.prenom" />
                                    <InfoRow label="Adresse" :value="oldData.client.adresse" />
                                    <InfoRow label="Téléphone" :value="oldData.client.telephone" />
                                    <InfoRow label="Date de Naissance" :value="oldData.client.date_naissance" />
                                    <InfoRow label="Ville de Naissance" :value="oldData.client.ville_naissance" />
                                    <InfoRow label="Email" :value="oldData.client.email" />
                                </div>
                                <hr>
                                <!--Informations de l'entreprise -->
                                <p class="font-bold my-4" v-if="oldData.entreprise">Informations de l'entreprisess</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm my-4"
                                    v-if="oldData.entreprise">
                                    <InfoRow label="Nom de l'entreprise" :value="oldData.entreprise.nom_entreprise" />
                                    <InfoRow label="Registre de commerce"
                                        :value="oldData.entreprise.registre_commerce" />
                                    <InfoRow label="N° de Compte contribuable"
                                        :value="oldData.entreprise.compte_contribuale" />
                                    <InfoRow label="Nom du Representant Legal"
                                        :value="oldData.entreprise.nom_representant_legal" />
                                    <InfoRow label="Téléphone du Representant Legal"
                                        :value="oldData.entreprise.telephone_representant_legal" />
                                    <InfoRow label="Profession du Representant Legal"
                                        :value="oldData.entreprise.profession_representant_legal" />
                                    <InfoRow label="Date de Naissance du Representant Legal"
                                        :value="oldData.entreprise.date_de_naissance_representant_legal" />
                                </div>
                                <hr v-if="oldData.entreprise">
                            </div>
                            <!-- Nouvelles Informations -->
                            <div class="mb-6">
                                <h3 class="text-green-600 font-bold uppercase mt-6 mb-6">Nouvelles Informations</h3>
                                <!-- Informations du véhicule -->
                                <p class="font-bold my-4">Informations du véhicule</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm my-4">
                                    <InfoRow label="Châssis (VIN)" :value="newData.vehicule.vin"
                                        :highlight="isDifferent('vehicule.vin')" />
                                    <InfoRow label="Couleur" :value="newData.vehicule.couleur"
                                        :highlight="isDifferent('vehicule.couleur')" />
                                    <InfoRow label="Marque du véhicule" :value="newData.vehicule.marque"
                                        :highlight="isDifferent('vehicule.marque')" />
                                    <InfoRow label="Carrosserie" :value="newData.vehicule.carrosserie"
                                        :highlight="isDifferent('vehicule.carrosserie')" />
                                    <InfoRow label="Modèle" :value="newData.vehicule.modele"
                                        :highlight="isDifferent('vehicule.modele')" />
                                    <InfoRow label="Type technique" :value="newData.vehicule.type_technique"
                                        :highlight="isDifferent('vehicule.type_technique')" />
                                    <InfoRow label="Genre" :value="newData.vehicule.genre_vehicule"
                                        :highlight="isDifferent('vehicule.genre_vehicule')" />
                                    <InfoRow label="Poids à Vide" :value="newData.vehicule.poids_vide"
                                        :highlight="isDifferent('vehicule.poids_vide')" />
                                    <InfoRow label="Poids Total en charge" :value="newData.vehicule.poids_total_charge"
                                        :highlight="isDifferent('vehicule.poids_total_charge')" />
                                    <InfoRow label="Puissance administrative"
                                        :value="newData.vehicule.puissance_administrative"
                                        :highlight="isDifferent('vehicule.puissance_administrative')" />
                                    <InfoRow label="Poids Utile" :value="newData.vehicule.poids_utile"
                                        :highlight="isDifferent('vehicule.poids_utile')" />
                                    <InfoRow label="Places Assises" :value="newData.vehicule.places_assises"
                                        :highlight="isDifferent('vehicule.places_assises')" />
                                    <InfoRow label="Sources d’énergie" :value="newData.vehicule.source_energie"
                                        :highlight="isDifferent('vehicule.source_energie')" />
                                    <InfoRow label="Nbre d’Essieux" :value="newData.vehicule.nombre_essieux"
                                        :highlight="isDifferent('vehicule.nombre_essieux')" />
                                </div>

                                <hr>
                                <!-- Informations du propriétaire -->
                                <p class="font-bold my-4">Informations du propriétaire</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <InfoRow label="Civilité" :value="newData.client.civilite"
                                        :highlight="isDifferent('client.civilite')" />
                                    <InfoRow label="Nom" :value="newData.client.nom"
                                        :highlight="isDifferent('client.nom')" />
                                    <InfoRow label="Prénom" :value="newData.client.prenom"
                                        :highlight="isDifferent('client.prenom')" />
                                    <InfoRow label="Adresse" :value="newData.client.adresse"
                                        :highlight="isDifferent('client.adresse')" />
                                    <InfoRow label="Téléphone" :value="newData.client.telephone"
                                        :highlight="isDifferent('client.telephone')" />
                                    <InfoRow label="Date de Naissance" :value="newData.client.date_naissance"
                                        :highlight="isDifferent('client.date_naissance')" />
                                    <InfoRow label="Ville de Naissance" :value="newData.client.ville_naissance"
                                        :highlight="isDifferent('client.ville_naissance')" />
                                    <InfoRow label="Email" :value="newData.client.email"
                                        :highlight="isDifferent('client.email')" />
                                </div>

                                <hr v-if="newData.entreprise">
                                <!-- Informations de l'entreprise -->
                                <p class="font-bold my-4" v-if="newData.entreprise">Informations de l'entreprise</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm" v-if="newData.entreprise">
                                    <InfoRow label="Nom de l'entreprise" :value="newData.entreprise.nom_entreprise"
                                        :highlight="isDifferent('entreprise.nom_entreprise')" />
                                    <InfoRow label="Registre de commerce" :value="newData.entreprise.registre_commerce"
                                        :highlight="isDifferent('entreprise.registre_commerce')" />
                                    <InfoRow label="N° de Compte contribuable"
                                        :value="newData.entreprise.compte_contribuable"
                                        :highlight="isDifferent('entreprise.compte_contribuable')" />
                                    <InfoRow label="Nom du représentant légal"
                                        :value="newData.entreprise.nom_representant_legal"
                                        :highlight="isDifferent('entreprise.nom_representant_legal')" />
                                    <InfoRow label="Téléphone du Representant Legal"
                                        :value="newData.entreprise.telephone_representant_legal"
                                        :highlight="isDifferent('entreprise.telephone_representant_legal')" />
                                    <InfoRow label="Profession du Representant Legal"
                                        :value="newData.entreprise.profession_representant_legal"
                                        :highlight="isDifferent('entreprise.profession_representant_legal')" />
                                    <InfoRow label="Date de Naissance du Representant Legal"
                                        :value="newData.entreprise.date_de_naissance_representant_legal"
                                        :highlight="isDifferent('entreprise.date_de_naissance_representant_legal')" />
                                </div>
                                <hr v-if="newData.entreprise">
                            </div>
                        </div>
                    </ScrollArea>
                </Card>

                <Card>
                    <!-- <ScrollArea class="h-full w-full rounded-md border"> iii-->
                    <div class="m-8">
                        <h3 class="mt-6 text-center">DOCUMENTATION</h3>
                        <div class="max-w-xl mx-auto bg-white rounded-lg shadow p-4 mt-6">
                            <div class="space-y-5">

                            </div>
                        </div>
                    </div>
                    <!-- </ScrollArea> -->
                </Card>
            </div>

            <div v-if="dossier.statut_numerisation == 1 && dossier.statut == 1"
                class="sticky top-[-20px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8  py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                <div></div>
                <div
                    class="sticky top-[-20px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8  py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                    <div></div>
                    <div class="flex items-center space-x-2">
                        <!-- <p>{{ props.dossier.r_dossier_services.nom_service }} okok</p> -->

                        <AlertDialog>
                            <Link
                                :href="route('show.modification.numerisation.get.data', { vin: dossier.num_chrono, })">
                            <Button class="bg-red-900">
                                <HandCoins class="w-4 h-4 mr-2" /> Demande de mofication
                            </Button>
                            </Link>
                            <span v-if="props.dossier_lier == null">
                                <Link v-if="props.dossier.r_dossier_services.nom_service === 'Immatriculation spéciale'"
                                    :href="route('show.ops.form.numerisation', { dossier: dossier.id, service: dossier.r_dossier_services.nom_service, detail: dossier.detail, physique_morale: dossier.r_dossier_vehicule.physique_morale })">
                                <Button class="bg-[#068A06]">
                                    <CreditCard class="w-4 h-4 mr-2" /> Ouvrir la numérisation
                                </Button>
                                </Link>
                                <Link v-if="props.dossier.r_dossier_services.nom_service === 'Post-immatriculation'"
                                    :href="route('show.post.form.numerisation', { dossier: dossier.id, service: dossier.r_dossier_services.nom_service, detail: dossier.detail, physique_morale: dossier.r_dossier_vehicule.physique_morale })">
                                <Button class="bg-[#068A06]">
                                    <CreditCard class="w-4 h-4 mr-2" /> Ouvrir la numérisation
                                </Button>
                                </Link>
                                <Link
                                    v-if="props.dossier.r_dossier_services.nom_service != 'Immatriculation spéciale' && props.dossier.r_dossier_services.nom_service != 'Post-immatriculation'"
                                    :href="route('show.form.numerisation', { dossier: dossier.id, service: dossier.r_dossier_services.nom_service, detail: dossier.detail, physique_morale: dossier.r_dossier_vehicule.physique_morale })">
                                <Button class="bg-[#068A06]">
                                    <CreditCard class="w-4 h-4 mr-2" /> Ouvrir la numérisation
                                </Button>
                                </Link>



                            </span>
                            <Link v-if="props.dossier_lier != null"
                                :href="route('show.new.numerisation.get.data.with.post', { dossier: dossier.id, service: dossier.r_dossier_services.nom_service, detail: dossier.detail, physique_morale: dossier.r_dossier_vehicule.physique_morale })">
                            <Button class="bg-[#068A06]">
                                <CreditCard class="w-4 h-4 mr-2" /> Ouvrir la numérisation
                            </Button>
                            </Link>


                            <AlertDialogContent>
                                <AlertDialogHeader>
                                    <!-- <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle> -->
                                    <AlertDialogDescription>
                                        <div class="max-w-md mx-auto p-6 text-center">
                                            <h2 class="text-2xl font-bold mb-6">SUGGESTIONS</h2>

                                            <textarea v-model="message" rows="5"
                                                placeholder="Changer les informations suivantes..."
                                                class="w-full p-4 rounded-xl text-left text-black resize-none mb-6 focus:outline-none "></textarea>


                                        </div>
                                    </AlertDialogDescription>
                                </AlertDialogHeader>
                                <AlertDialogFooter>
                                    <AlertDialogCancel>Annuler</AlertDialogCancel>
                                    <AlertDialogAction class="bg-green-600 hover:bg-green-700 ">
                                        Envoyer</AlertDialogAction>
                                </AlertDialogFooter>
                            </AlertDialogContent>
                        </AlertDialog>
                        <!-- <Button @click="returnBack()">
                        <CreditCard class="w-4 h-4 mr-2" /> Payer en Ligne
                    </Button> -->
                    </div>
                </div>
            </div>
        </main>
        <Toaster richColors position="top-right" />
    </div>
</template>


<script setup>
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { ref, computed, onMounted } from 'vue'
import { returnBack } from "/resources/js/composable/fonction.js"
import { MoveRight, MoveLeft, HandCoins, CreditCard, Eye, CircleAlert } from 'lucide-vue-next'
import {
    Card,
} from '@/components/ui/card'
import { ScrollArea } from '@/components/ui/scroll-area'
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
} from '@/components/ui/alert-dialog'
import InfoRow from './InfoRow.vue';
import axios from 'axios'
import { Toaster, toast } from 'vue-sonner'
import { router } from '@inertiajs/vue3';
import { Checkbox } from "@/components/ui/checkbox";
import { Label } from "@/components/ui/label";
const props = defineProps({
    vin: String,
    dossier: Object,
    dossier_lier: Object,
    client: Object,
    old: Object,
    new: Object
});
console.log('dossier_lier:', props.dossier_lier);

onMounted(() => {
    if (props.dossier.r_dossier_vehicule.entreprise_id) {
        fetchEntreprise(props.dossier.r_dossier_vehicule.entreprise_id);
    }
})
const oldData = computed(() => props.old);
const newData = computed(() => props.new);
const motifRejet = ref('');
const entreprise = ref(null);
function isDifferent(path) {
    const oldVal = getNestedValue(oldData.value, path);
    const newVal = getNestedValue(newData.value, path);
    return oldVal !== newVal;
}

function getNestedValue(obj, path) {
    return path.split('.').reduce((acc, key) => acc?.[key], obj);
}



// Exclusion des champs non pertinents
const keysToExclude = ['id', 'id_dossier', 'type_document', 'created_at', 'updated_at']

// On prend le premier élément du tableau (car un seul objet avec tous les documents)
const documentData = computed(() => {
    return props.dossier.r_dossier_documents?.[0] || {}
})

// Filtrage des documents valides
const filteredDocs = computed(() => {
    const entries = Object.entries(documentData.value)
    return entries.filter(([key, value]) => !keysToExclude.includes(key) && value)
})

// Prévisualisation
const selectedDoc = ref(null)

function formatLabel(key) {
    return key
        .replace(/_/g, ' ')
        .replace(/\b\w/g, l => l.toUpperCase())
}

async function confirmerRejet(id) {
    console.log("Rejet du dossier avec ID:", id);
    if (!motifRejet.value.trim()) {
        toast.error("Veuillez fournir un motif de rejet.");
        return;
    }
    try {
        const response = await axios.post('/minister/mt1/dossiers/rejeter', {
            dossier_id: id,
            motif: motifRejet.value,
        });

        // Afficher le message de Confirmation
        toast.success(response.data.message);
        //
        router.visit(window.location.pathname, {
            only: ['dossier'], // recharge uniquement cette prop
            preserveScroll: true,
            preserveState: true,
        });
        // Réinitialiser le champ de motif
        motifRejet.value = '';
    } catch (error) {
        console.error(error);
        toast.error("Une erreur s'est produite lors du rejet du dossier.");
    }
}

async function confirmerValidation(id) {
    console.log("Validation du dossier avec ID:", id);
    try {
        const response = await axios.post('/minister/mt1/dossiers/valider', {
            dossier_id: id,
        });

        // Afficher le message de Confirmation
        toast.success(response.data.message);
        //
        router.visit(window.location.pathname, {
            only: ['dossier'], // recharge uniquement cette prop
            preserveScroll: true,
            preserveState: true,
        });
    } catch (error) {
        console.error(error);
        toast.error("Une erreur s'est produite lors de la validation du dossier.");
    }
}

// ✅ Fonction pour charger les données
async function fetchEntreprise(id) {
    try {
        const response = await axios.get(`/entreprises/${id}`);
        entreprise.value = response.data.data; // On stocke uniquement la partie data
        console.log("Données de l'entreprise :", entreprise.value);
    } catch (error) {
        console.error("Erreur :", error.response?.data || error);
        entreprise.value = null;
    }
}




//getion rejet dossier
const showRejectModal = ref(false);
const items = ref([]);
const selectedFields = ref([]);

// Toggle d'une case
function toggleField(fieldKey) {
    const index = selectedFields.value.indexOf(fieldKey);
    if (index === -1) {
        selectedFields.value.push(fieldKey);
    } else {
        selectedFields.value.splice(index, 1);
    }
}

// Charger les motifs depuis l'API
const fetchItems = async () => {
    try {
        const res = await fetch(`/minister/mt1/get/rejets/data?id_service=${props.dossier.id_service}`);
        const data = await res.json();
        items.value = data;
    } catch (err) {
        console.error("Erreur de chargement des motifs :", err);
    }
};

// Sauvegarder les sélections
const saveSelection = async () => {
    console.log("✅ iddossier:", props.dossier.id);
    try {
        console.log("✅ Éléments sélectionnés :", selectedFields.value);
        const response = await axios.post(
            "/minister/mt1/save/motif/rejets",
            {
                selected: selectedFields.value,
                id: props.dossier.id,
            },
            { headers: { "Content-Type": "application/json" } }
        );
        showRejectModal.value = false;
        //update prop dossier
        router.visit(window.location.pathname, {
            only: ['dossier'], // recharge uniquement cette prop
            preserveScroll: true,
            preserveState: true,
        });
        toast.success(response.data.message);
    } catch (err) {
        console.error(err);
    }
};

// Réinitialiser la sélection
const clearSelection = () => {
    selectedFields.value = [];
};



const motifDossierRejeter = ref('');

const fetchMotifsRejets = async () => {
    if (!motifDossierRejeter.value) {
        try {
            const response = await axios.get(`/minister/mt1/get/motifs/rejets/${props.dossier.id}`);
            motifDossierRejeter.value = response.data.motifs;
        } catch (err) {
            console.error("Erreur de chargement des motifs sélectionnés:", err);
        }
    }
};
onMounted(fetchItems);
</script>

<script>
import Main from '/resources/js/Pages/Main.vue';

export default {
    layout: Main,
};
</script>

<style scoped>
/* Optionnel pour responsive / animation */
</style>
