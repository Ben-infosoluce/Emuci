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
          <div class="p-14">
            <div class="mb-6 ">
              <h2 class="text-lg font-semibold text-center">informations du véhicule – VIN : <i>{{ oldData.vin }}</i>
              </h2>
              <p class="text-center font-semibold mt-8">SERVICE : POST Immatriculation</p>
            </div>

            <!-- Anciennes Informations -->
            <div class="mb-6">
              <h3 class="text-red-600 font-bold uppercase mt-6 mb-6">Anciennes Informations</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <InfoRow label="Châssis (VIN)" :value="oldData.vin" />
                <InfoRow label="Couleur" :value="oldData.couleur" />
                <InfoRow label="Marque du véhicule" :value="oldData.marque" />
                <InfoRow label="Carrosserie" :value="oldData.carrosserie" />
                <InfoRow label="Modèle" :value="oldData.modele" />
                <InfoRow label="Type technique" :value="oldData.type_technique" />
                <InfoRow label="Genre" :value="oldData.genre_vehicule" />
                <InfoRow label="Poids à Vide" :value="oldData.poids_vide" />
                <InfoRow label="Poids Total en charge" :value="oldData.poids_total_charge" />
                <InfoRow label="Puissance administrative" :value="oldData.puissance_administrative" />
                <InfoRow label="Poids Utile" :value="oldData.poids_utile" />
                <InfoRow label="Places Assises" :value="oldData.places_assises" />
                <InfoRow label="Sources d’énergie" :value="oldData.source_energie" />
                <InfoRow label="Nbre d’Essieux" :value="oldData.nombre_essieux" />
              </div>
            </div>

            <!-- Nouvelles Informations -->
            <div class="mb-6">
              <h3 class="text-orange-600 font-bold uppercase mt-6 mb-6">Nouvelles Informations</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <InfoRow label="Châssis (VIN)" :value="newData.vin" :highlight="isDifferent('vin')" />
                <InfoRow label="Couleur" :value="newData.couleur" :highlight="isDifferent('couleur')" />
                <InfoRow label="Marque du véhicule" :value="newData.marque" :highlight="isDifferent('marque')" />
                <InfoRow label="Carrosserie" :value="newData.carrosserie" :highlight="isDifferent('carrosserie')" />
                <InfoRow label="Modèle" :value="newData.modele" :highlight="isDifferent('modele')" />
                <InfoRow label="Type technique" :value="newData.type_technique"
                  :highlight="isDifferent('type_technique')" />
                <InfoRow label="Genre" :value="newData.genre_vehicule" :highlight="isDifferent('genre_vehicule')" />
                <InfoRow label="Poids à Vide" :value="newData.poids_vide" :highlight="isDifferent('poids_vide')" />
                <InfoRow label="Poids Total en charge" :value="newData.poids_total_charge"
                  :highlight="isDifferent('poids_total_charge')" />
                <InfoRow label="Puissance administrative" :value="newData.puissance_administrative"
                  :highlight="isDifferent('puissance_administrative')" />
                <InfoRow label="Poids Utile" :value="newData.poids_utile" :highlight="isDifferent('poids_utile')" />
                <InfoRow label="Places Assises" :value="newData.places_assises"
                  :highlight="isDifferent('places_assises')" />
                <InfoRow label="Sources d’énergie" :value="newData.source_energie"
                  :highlight="isDifferent('source_energie')" />
                <InfoRow label="Nbre d’Essieux" :value="newData.nombre_essieux"
                  :highlight="isDifferent('nombre_essieux')" />
              </div>
            </div>

            <!-- Informations du Propriétaire -->
            <div>
              <h3 class="font-semibold text-base">informations du propriétaire – <b>{{ newData.prenom }} {{ newData.nom
              }}</b></h3>
              <div class="grid grid-cols-2 gap-4 mt-2">
                <InfoRow label="Adresse email" :value="newData.email" :highlight="isDifferent('email')" />
                <InfoRow label="Pays Naissance" :value="newData.date_naissance"
                  :highlight="isDifferent('date_naissance')" />
                <InfoRow label="Adresse" :value="newData.adresse" :highlight="isDifferent('adresse')" />
                <InfoRow label="Type de Personne" :value="newData.physique_morale"
                  :highlight="isDifferent('physique_morale')" />
                <InfoRow label="District" :value="newData.district_client"
                  :highlight="isDifferent('district_client')" />
                <InfoRow label="ville de Naissance" :value="newData.ville_naissance"
                  :highlight="isDifferent('ville_naissance')" />
              </div>
            </div>

            <!-- Informations de l'entreprise si Morale -->
            <div v-if="newData.physique_morale != 'Physique'">
              <h3 class=" font-semibold text-base mt-6">Informations de l'entreprise</h3>
              <div class="grid grid-cols-2 gap-4 mt-4">
                <InfoRow label="Nom de l'entreprise" :value="newData.nom_entreprise"
                  :highlight="isDifferent('nom_entreprise')" />
                <InfoRow label="Nom du représentant légal" :value="newData.nom_representant_legal"
                  :highlight="isDifferent('nom_representant_legal')" />
                <InfoRow label="Téléphone du représentant légal" :value="newData.telephone_representant_legal"
                  :highlight="isDifferent('telephone_representant_legal')" />
                <InfoRow label="RCCM" :value="newData.registre_commerce"
                  :highlight="isDifferent('registre_commerce')" />
                <InfoRow label="Compte Contribuable" :value="newData.compte_contribuale"
                  :highlight="isDifferent('compte_contribuale')" />
                <InfoRow label="Préfecture" :value="newData.prefecture" :highlight="isDifferent('prefecture')" />
                <InfoRow label="Sous-préfecture" :value="newData.sous_prefecture"
                  :highlight="isDifferent('sous_prefecture')" />
                <InfoRow label="Région" :value="newData.region" :highlight="isDifferent('region')" />
                <InfoRow label="Date de naissance du représentant" :value="newData.date_de_naissance_representant_legal"
                  :highlight="isDifferent('date_de_naissance_representant_legal')" />
                <InfoRow label="Profession du représentant" :value="newData.profession_representant_legal"
                  :highlight="isDifferent('profession_representant_legal')" />
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

const props = defineProps({
  old: Object,
  new: Object
});

const oldData = computed(() => props.old);
const newData = computed(() => props.new);

function isDifferent(key) {
  return oldData.value[key] !== newData.value[key];
}



</script>

<script>
import Main from '/resources/js/Pages/Main.vue';
import BoutonRetour from "/resources/js/components/BoutonRetour.vue";

export default {
  layout: Main,
};
</script>