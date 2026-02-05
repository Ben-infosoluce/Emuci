<template>
  <!-- Header sticky -->
  <div
    class="sticky top-[-30px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8 py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
    <h4 class="text-2xl font-bold tracking-tight">
      Post Immatriculation
    </h4>
    <div class="flex items-center space-x-2">
      <Link :href="route('show.pdc.post.immatriculation')">
      <BoutonRetour />
      </Link>
    </div>
  </div>

  <!-- Contenu scrollable -->
  <div class="rounded-lg dark:border-gray-700">
    <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
      <Card class="h-full flex flex-col ">
        <ScrollArea class="h-full w-full rounded-md ">
          <div>
            <!-- Pour Post-Immatriculation -->
            <div class="m-8">
              <div class=" flex flex-col space-y-4  py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
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
              <div class=" flex flex-col space-y-4  py-2 sm:flex-row sm:items-center justify-between sm:space-y-0">
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
                <!-- Motif de rejet -->
                <div v-if="dossier.motif_rejet">
                  <div class="">
                    <p class="border border-red-300 rounded-md p-3">
                      <strong>Motif de rejet : </strong>
                      <AlertDialog>
                        <AlertDialogTrigger>

                          <Badge @click="fetchMotifsRejets()" variant="error"
                            class="mx-2 flex items-center cursor-pointer ">
                            <span>Voir les motifs</span>
                            <MoveRight class="w-4 h-4 ml-2" />
                          </Badge>

                        </AlertDialogTrigger>
                        <AlertDialogContent class="max-w-4xl w-full">
                          <AlertDialogHeader>
                            <AlertDialogTitle>Détails du rejet du dossier</AlertDialogTitle>
                            <AlertDialogDescription>
                              <ScrollArea class="h-[400px] w-full rounded-md border p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4 mb-6"
                                  v-if="motifDossierRejeter.length">
                                  <div v-for="motif in motifDossierRejeter" :key="motif.id"
                                    class="flex items-center space-x-2">
                                    <Checkbox checked="true" class="mr-2" :id="'motif-' + motif.id" />
                                    <Label :for="'motif-' + motif.id">{{ motif.motif
                                      }}</Label>
                                  </div>
                                </div>
                                <p v-else class="text-gray-500 italic">
                                  Chargement des données...
                                </p>
                              </ScrollArea>
                            </AlertDialogDescription>
                          </AlertDialogHeader>
                          <AlertDialogFooter class="flex justify-center gap-4 mt-6">
                            <AlertDialogCancel class="bg-gray-200 text-gray-800 hover:bg-gray-300 px-4 py-2 rounded-md">
                              Fermer
                            </AlertDialogCancel>
                          </AlertDialogFooter>
                        </AlertDialogContent>

                      </AlertDialog>
                    </p>
                  </div>
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
                  <InfoRow label="Poids Total en charge" :value="oldData.vehicule.poids_total_charge" />
                  <InfoRow label="Puissance administrative" :value="oldData.vehicule.puissance_administrative" />
                  <InfoRow label="Poids Utile" :value="oldData.vehicule.poids_utile" />
                  <InfoRow label="Places Assises" :value="oldData.vehicule.places_assises" />
                  <InfoRow label="Sources d’énergie" :value="oldData.vehicule.source_energie" />
                  <InfoRow label="Nbre d’Essieux" :value="oldData.vehicule.nombre_essieux" />
                  <InfoRow label="Code de Région" :value="oldData.vehicule.code_de_region" />
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm my-4" v-if="oldData.entreprise">
                  <InfoRow label="Nom de l'entreprise" :value="oldData.entreprise.nom_entreprise" />
                  <InfoRow label="Registre de commerce" :value="oldData.entreprise.registre_commerce" />
                  <InfoRow label="N° de Compte contribuable" :value="oldData.entreprise.compte_contribuale" />
                  <InfoRow label="Nom du Representant Legal" :value="oldData.entreprise.nom_representant_legal" />
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
                  <InfoRow label="Puissance administrative" :value="newData.vehicule.puissance_administrative"
                    :highlight="isDifferent('vehicule.puissance_administrative')" />
                  <InfoRow label="Poids Utile" :value="newData.vehicule.poids_utile"
                    :highlight="isDifferent('vehicule.poids_utile')" />
                  <InfoRow label="Places Assises" :value="newData.vehicule.places_assises"
                    :highlight="isDifferent('vehicule.places_assises')" />
                  <InfoRow label="Sources d’énergie" :value="newData.vehicule.source_energie"
                    :highlight="isDifferent('vehicule.source_energie')" />
                  <InfoRow label="Nbre d’Essieux" :value="newData.vehicule.nombre_essieux"
                    :highlight="isDifferent('vehicule.nombre_essieux')" />
                  <InfoRow label="Code de Région" :value="newData.vehicule.code_de_region"
                    :highlight="isDifferent('vehicule.code_de_region')" />
                </div>

                <hr>
                <!-- Informations du propriétaire -->
                <p class="font-bold my-4">Informations du propriétaire</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                  <InfoRow label="Civilité" :value="newData.client.civilite"
                    :highlight="isDifferent('client.civilite')" />
                  <InfoRow label="Nom" :value="newData.client.nom" :highlight="isDifferent('client.nom')" />
                  <InfoRow label="Prénom" :value="newData.client.prenom" :highlight="isDifferent('client.prenom')" />
                  <InfoRow label="Adresse" :value="newData.client.adresse" :highlight="isDifferent('client.adresse')" />
                  <InfoRow label="Téléphone" :value="newData.client.telephone"
                    :highlight="isDifferent('client.telephone')" />
                  <InfoRow label="Date de Naissance" :value="newData.client.date_naissance"
                    :highlight="isDifferent('client.date_naissance')" />
                  <InfoRow label="Ville de Naissance" :value="newData.client.ville_naissance"
                    :highlight="isDifferent('client.ville_naissance')" />
                  <InfoRow label="Email" :value="newData.client.email" :highlight="isDifferent('client.email')" />
                </div>

                <hr v-if="newData.entreprise">
                <!-- Informations de l'entreprise -->
                <p class="font-bold my-4" v-if="newData.entreprise">Informations de l'entreprise</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm" v-if="newData.entreprise">
                  <InfoRow label="Nom de l'entreprise" :value="newData.entreprise.nom_entreprise"
                    :highlight="isDifferent('entreprise.nom_entreprise')" />
                  <InfoRow label="Registre de commerce" :value="newData.entreprise.registre_commerce"
                    :highlight="isDifferent('entreprise.registre_commerce')" />
                  <InfoRow label="N° de Compte contribuable" :value="newData.entreprise.compte_contribuable"
                    :highlight="isDifferent('entreprise.compte_contribuable')" />
                  <InfoRow label="Nom du représentant légal" :value="newData.entreprise.nom_representant_legal"
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
          </div>
        </ScrollArea>
      </Card>
    </main>
    <Toaster richColors position="top-right" />
  </div>
</template>

<script setup>
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { Toaster } from 'vue-sonner'
import { ScrollArea } from '@/components/ui/scroll-area'
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import InfoRow from './InfoRow.vue';
import { defineProps } from 'vue';
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
import { Checkbox } from "@/components/ui/checkbox";
import { Label } from "@/components/ui/label";
import { Badge } from '@/components/ui/badge'

const props = defineProps({
  dossier: Object,
  old: Object,
  new: Object
});

onMounted(() => {
  if (props.dossier.r_dossier_vehicule.entreprise_id) {
    fetchEntreprise(props.dossier.r_dossier_vehicule.entreprise_id);
  }
})
const oldData = computed(() => props.old);
const newData = computed(() => props.new);
const entreprise = ref(null);
function isDifferent(path) {
  const oldVal = getNestedValue(oldData.value, path);
  const newVal = getNestedValue(newData.value, path);
  return oldVal !== newVal;
}

function getNestedValue(obj, path) {
  return path.split('.').reduce((acc, key) => acc?.[key], obj);
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


</script>

<script>
import Main from '/resources/js/Pages/Main.vue';
import BoutonRetour from "/resources/js/components/BoutonRetour.vue";

export default {
  layout: Main,
};
</script>