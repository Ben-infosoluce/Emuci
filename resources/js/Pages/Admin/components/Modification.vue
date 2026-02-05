<template>
    <div>
        <!-- Header sticky -->
        <div
            class="sticky top-[-20px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8 py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <h4 class="text-2xl font-bold tracking-tight">
                Demander de correction
            </h4>
            <div class="flex items-center space-x-2">
                <Button @click="returnBack()">
                    <MoveLeft class="w-4 h-4 mr-2" /> Retour
                </Button>
            </div>
        </div>

        <!-- Contenu scrollable -->
        <div class="rounded-lg dark:border-gray-700">
            <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
                <Card class="h-full flex flex-col">
                    <ScrollArea class="h-full w-full rounded-md border">
                        <div class="m-8">
                            <h3>
                                informations du véhicule - VIN:
                                <strong>{{
                                    dossier.r_dossier_vehicule.vin
                                    }}</strong>
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-7 mt-6 mb-6">
                                <Label for="vin">
                                    <Checkbox :checked="selectedFields.includes('vin')"
                                        @update:checked="() => toggleField('vin')" class="mr-2" id="vin" />
                                    Châssis (VIN) :
                                    {{ dossier.r_dossier_vehicule.vin }}
                                </Label>

                                <Label for="marque">
                                    <Checkbox :checked="selectedFields.includes('marque')" @update:checked="
                                        () => toggleField('marque')
                                    " class="mr-2" id="marque" />
                                    Marque du véhicule :
                                    {{ dossier.r_dossier_vehicule.marque }}
                                </Label>

                                <Label for="modele">
                                    <Checkbox :checked="selectedFields.includes('modele')" @update:checked="
                                        () => toggleField('modele')
                                    " class="mr-2" id="modele" />
                                    Modèle du véhicule :
                                    {{ dossier.r_dossier_vehicule.modele }}
                                </Label>
                                <Label for="genre_vehicule">
                                    <Checkbox :checked="selectedFields.includes(
                                        'genre_vehicule'
                                    )
                                        " @update:checked="
                                            () => toggleField('genre_vehicule')
                                        " class="mr-2" id="genre_vehicule" />
                                    Genre :
                                    {{ dossier.r_dossier_vehicule.genre_vehicule }}
                                </Label>
                                <Label for="poids_total">
                                    <Checkbox :checked="selectedFields.includes('poids_total')
                                        " @update:checked="
                                            () => toggleField('poids_total')
                                        " class="mr-2" id="poids_total" />
                                    Poids Total en charge :
                                    {{ dossier.r_dossier_vehicule.poids_total }}
                                </Label>
                                <Label for="poids_utile">
                                    <Checkbox :checked="selectedFields.includes('poids_utile')
                                        " @update:checked="
                                            () => toggleField('poids_utile')
                                        " class="mr-2" id="poids_utile" />
                                    Poids Utile :
                                    {{ dossier.r_dossier_vehicule.poids_utile }}
                                </Label>
                                <Label for="source_energie">
                                    <Checkbox :checked="selectedFields.includes(
                                        'source_energie'
                                    )
                                        " @update:checked="
                                            () => toggleField('source_energie')
                                        " class="mr-2" id="source_energie" />
                                    Sources d’énergie :
                                    {{ dossier.r_dossier_vehicule.source_energie }}
                                </Label>
                                <Label for="couleur">
                                    <Checkbox :checked="selectedFields.includes('couleur')
                                        " @update:checked="
                                            () => toggleField('couleur')
                                        " class="mr-2" id="couleur" />
                                    Couleur :
                                    {{ dossier.r_dossier_vehicule.couleur }}
                                </Label>
                                <Label for="carrosserie">
                                    <Checkbox :checked="selectedFields.includes('carrosserie')
                                        " @update:checked="
                                            () => toggleField('carrosserie')
                                        " class="mr-2" id="carrosserie" />
                                    Carrosserie :
                                    {{ dossier.r_dossier_vehicule.carrosserie }}
                                </Label>
                                <Label for="type_technique">
                                    <Checkbox :checked="selectedFields.includes(
                                        'type_technique'
                                    )
                                        " @update:checked="
                                            () => toggleField('type_technique')
                                        " class="mr-2" id="type_technique" />
                                    Type technique :
                                    {{ dossier.r_dossier_vehicule.type_technique }}
                                </Label>
                                <Label for="poids_vide">
                                    <Checkbox :checked="selectedFields.includes('poids_vide')
                                        " @update:checked="
                                            () => toggleField('poids_vide')
                                        " class="mr-2" id="poids_vide" />
                                    Poids à Vide :
                                    {{ dossier.r_dossier_vehicule.poids_vide }}
                                </Label>
                                <Label for="puissance_administrative">
                                    <Checkbox :checked="selectedFields.includes(
                                        'puissance_administrative'
                                    )
                                        " @update:checked="
                                            () =>
                                                toggleField(
                                                    'puissance_administrative'
                                                )
                                        " class="mr-2" id="puissance_administrative" />
                                    Puissance administrative :
                                    {{
                                        dossier.r_dossier_vehicule
                                            .puissance_administrative
                                    }}
                                </Label>
                                <Label for="places_assises">
                                    <Checkbox :checked="selectedFields.includes(
                                        'places_assises'
                                    )
                                        " @update:checked="
                                            () => toggleField('places_assises')
                                        " class="mr-2" id="places_assises" />
                                    Places Assises :
                                    {{ dossier.r_dossier_vehicule.places_assises }}
                                </Label>
                                <Label for="nombre_essieux">
                                    <Checkbox :checked="selectedFields.includes(
                                        'nombre_essieux'
                                    )
                                        " @update:checked="
                                            () => toggleField('nombre_essieux')
                                        " class="mr-2" id="nombre_essieux" />
                                    Nbre d’Essieux :
                                    {{ dossier.r_dossier_vehicule.nombre_essieux }}
                                </Label>
                            </div>

                            <hr />
                            <h3 class="mt-6">
                                informations du propriétaire :
                                <strong>{{ dossier.r_dossier_client.civilite }} ,
                                    {{ dossier.r_dossier_client.nom }}
                                    {{ dossier.r_dossier_client.prenom }}
                                </strong>
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-7 mt-6 mb-6">
                                <Label for="adresse">
                                    <Checkbox :checked="selectedFields.includes('adresse')
                                        " @update:checked="
                                            () => toggleField('adresse')
                                        " class="mr-2" id="adresse" />
                                    adresse :
                                    {{ dossier.r_dossier_client.adresse }}
                                </Label>
                                <Label for="email">
                                    <Checkbox :checked="selectedFields.includes('email')"
                                        @update:checked="() => toggleField('email')" class="mr-2" id="email" />
                                    email :
                                    {{ dossier.r_dossier_client.email }}
                                </Label>
                                <Label for="telephone">
                                    <Checkbox :checked="selectedFields.includes('telephone')
                                        " @update:checked="
                                            () => toggleField('telephone')
                                        " class="mr-2" id="telephone" />
                                    telephone :
                                    {{ dossier.r_dossier_client.telephone }}
                                </Label>
                                <Label for="date_naissance">
                                    <Checkbox :checked="selectedFields.includes(
                                        'date_naissance'
                                    )
                                        " @update:checked="
                                            () => toggleField('date_naissance')
                                        " id="date_naissance" class="mr-2" />
                                    date de naissance :
                                    {{ dossier.r_dossier_client.date_naissance }}
                                </Label>
                                <Label for="ville_naissance">
                                    <Checkbox id="ville_naissance" :checked="selectedFields.includes(
                                        'ville_naissance'
                                    )
                                        " @update:checked="
                                            () => toggleField('ville_naissance')
                                        " class="mr-2" />
                                    ville de naissance :
                                    {{ dossier.r_dossier_client.ville_naissance }}
                                </Label>
                            </div>

                            <div class="flex justify-end">
                                <AlertDialog>
                                    <AlertDialogTrigger as-child>
                                        <Button>
                                            Continuer
                                        </Button>
                                    </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Veuillez saisir un message </AlertDialogTitle>
                                            <AlertDialogDescription>
                                                <div class="bg-white  rounded-lg w-full max-w-md">
                                                    <Textarea type="texte" v-model="confirmMessage" class=" w-full my-6"
                                                        placeholder="Votre message ici..." required />
                                                </div>
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel>Annuler</AlertDialogCancel>
                                            <AlertDialogAction @click="() => handleModifier(dossier)">Valider
                                            </AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </div>
                        </div>
                    </ScrollArea>
                </Card>
            </main>
        </div>
    </div>
    <div>
        <Toaster richColors position="top-right" />

    </div>

</template>

<script setup>
import { Button } from "@/components/ui/button";
import { ref } from "vue";
import { returnBack } from "/resources/js/composable/fonction.js";
import { Toaster, toast } from 'vue-sonner'
import { MoveLeft } from "lucide-vue-next";
import { Card } from "@/components/ui/card";
import { ScrollArea } from "@/components/ui/scroll-area";
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
} from "@/components/ui/alert-dialog";
import { Checkbox } from "@/components/ui/checkbox";
import { Label } from "@/components/ui/label";
import { router } from '@inertiajs/vue3'
import { storeToRefs } from "pinia";
import { useCaisseStore } from "@/stores/mainStore";

const props = defineProps({
    vin: String,
    dossier: Object,
    client: Object,
});
console.log(props.dossier);

// liste des clés cochées à modifier
const selectedFields = ref([]);
const confirmMessage = ref('');

function toggleField(fieldKey) {
    const index = selectedFields.value.indexOf(fieldKey);
    if (index === -1) {
        selectedFields.value.push(fieldKey);
    } else {
        selectedFields.value.splice(index, 1);
    }
}

// import axios from 'axios'
async function handleModifier(dossier) {
    const vehicule = dossier.r_dossier_vehicule;
    const client = dossier.r_dossier_client;

    const modifications = {};

    selectedFields.value.forEach((key) => {
        if (key in vehicule) {
            modifications[key] = vehicule[key];
        } else if (key in client) {
            modifications[key] = client[key];
        }
    });

    if (Object.keys(modifications).length === 0) {
        toast.error("Aucune donnée sélectionnée pour modification.");
        console.warn("Aucune donnée sélectionnée pour modification.");
        return;
    }

    const payload = {
        dossier_id: dossier.id,
        corrections: modifications,
        origine: "caisse",
    };

    console.log("Payload :", payload);

    try {
        const response = await axios.post("/correction/new", {
            dossier_id: dossier.id,
            corrections: modifications,
            origine: "caisse",
        });

        console.log("Correction envoyée avec succès :", response.data);
        selectedFields.value = [];
        toast.success('Correction envoyée avec succès !');
        setTimeout(() => {
            // Redirection avec l'ID du dossier
            router.visit(`/caisse/get/data/${props.dossier.num_chrono}`);
        }, 1500);
    } catch (error) {
        toast.error("Une erreur est survenue lors de l'enregistrement.");
        console.error(error);
    }
}
</script>

<script>
import Main from "/resources/js/Pages/Main.vue";
import axios from "axios";

export default {
    layout: Main,
};
</script>

<style scoped>
/* Optionnel pour responsive / animation */
</style>
