<template>
    <!-- Header sticky -->
    <div
        class="sticky top-[-30px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8  py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <h4 class="text-2xl font-bold tracking-tight">
            Immatriculation Spéciale
        </h4>
        <div class="flex items-center space-x-2">
            <Link :href="route('show.new.pdc.immatriculation')">
            <BoutonRetour />
            </Link>
        </div>
    </div>

    <!-- Contenu scrollable -->
    <div class="rounded-lg dark:border-gray-700 ">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <Card class="h-full flex flex-col ">
                <ScrollArea class="h-full w-full rounded-md border">
                    <div class="p-8">
                        <!-- Type de personne -->
                        <h3 class="text-md font-semibold  mb-6">Type de personne</h3>

                        <div class="flex items-center gap-6 mb-5">
                            <label class="flex items-center gap-2">
                                <input type="radio" value="Morale" v-model="form.type" />
                                Morale

                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" value="Physique" v-model="form.type" />
                                Physique
                            </label>
                            <p v-if="typeError" class="text-red-600 text-sm mt-1">{{ typeError }}</p>
                        </div>

                        <form @submit="onSubmit">
                            <!-- Informations du propriétaire -->
                            <h3 class="text-md font-semibold mt-6 mb-6">Informations du propriétaire</h3>
                            <div class="flex flex-1 flex-col gap-4 md:gap-6">
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3">
                                    <FormField v-slot="{ componentField }" name="firstname">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Nom</FormLabel>
                                            <FormControl>
                                                <Input placeholder="Konaté" v-bind="componentField"
                                                    autocomplete="firstname" v-model="form.firstname" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- Prenom -->
                                    <FormField v-slot="{ componentField }" name="lastname">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Prénom</FormLabel>
                                            <FormControl>
                                                <Input placeholder="Ben" v-bind="componentField" autocomplete="lastname"
                                                    v-model="form.lastname" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- Email -->
                                    <FormField v-slot="{ componentField }" name="email">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Adresse email</FormLabel>
                                            <FormControl>
                                                <Input type="email" placeholder="exemple@email.com"
                                                    v-bind="componentField" autocomplete="email" v-model="form.email" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </div>
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3">
                                    <FormField v-slot="{ componentField }" name="phone">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Numéro de téléphone</FormLabel>
                                            <FormControl>
                                                <Input placeholder="0000000000" v-bind="componentField"
                                                    autocomplete="phone" v-model="form.phone" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- adresse -->
                                    <FormField v-slot="{ componentField }" name="adresse">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Adresse</FormLabel>
                                            <FormControl>
                                                <Input placeholder="Abidjan,Cocody" v-bind="componentField"
                                                    autocomplete="adresse" v-model="form.adresse" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- typePersonne -->
                                    <FormField v-slot="{ componentField }" name="typePersonne">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Type de personne</FormLabel>
                                            <FormControl>
                                                <Input placeholder="exemple de personne" v-bind="componentField"
                                                    autocomplete="typePersonne" v-model="form.typePersonne" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </div>
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3">
                                    <!-- typePersonne -->
                                    <FormField v-slot="{ componentField }" name="district">
                                        <FormItem v-auto-animate>
                                            <FormLabel>District</FormLabel>
                                            <FormControl>
                                                <Input placeholder="exemple de personne" v-bind="componentField"
                                                    autocomplete="district" v-model="form.district" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- ville de naissance  -->
                                    <FormField v-slot="{ componentField }" name="villeNaissance">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Ville de naissance</FormLabel>
                                            <FormControl>
                                                <Input placeholder="exemple de personne" v-bind="componentField"
                                                    autocomplete="villeNaissance" v-model="form.villeNaissance" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </div>
                            </div>

                            <!-- Informations du véhicule -->
                            <div class="gap-4 md:gap-6 mt-10 ">
                                <h3 class="text-md font-semibold mt-8 mb-8">Informations du véhicule</h3>
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3 mb-5">
                                    <FormField v-slot="{ componentField }" name="vin">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Châssis (VIN)</FormLabel>
                                            <FormControl>
                                                <Input placeholder="0GE447" v-bind="componentField" autocomplete="vin"
                                                    v-model="form.vin" disabled />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- marqueVehicule -->
                                    <FormField v-slot="{ componentField }" name="marqueVehicule">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Marque du véhicule</FormLabel>
                                            <FormControl>
                                                <Select v-bind="componentField" autocomplete="marqueVehicule"
                                                    v-model="form.marqueVehicule">
                                                    <SelectTrigger class="w-full">
                                                        <SelectValue placeholder="Selectioner une marque" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            <SelectLabel>Marques</SelectLabel>
                                                            <SelectItem value="toyota">Toyota</SelectItem>
                                                            <SelectItem value="honda">Honda</SelectItem>
                                                            <SelectItem value="bmw">BMW</SelectItem>
                                                            <SelectItem value="mercedes">Mercedes-Benz</SelectItem>
                                                            <SelectItem value="audi">Audi</SelectItem>
                                                            <SelectItem value="ford">Ford</SelectItem>
                                                            <SelectItem value="chevrolet">Chevrolet</SelectItem>
                                                            <SelectItem value="tesla">Tesla</SelectItem>
                                                            <SelectItem value="volkswagen">Volkswagen</SelectItem>
                                                            <SelectItem value="hyundai">Hyundai</SelectItem>
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- Modèle du véhicule -->
                                    <FormField v-slot="{ componentField }" name="modelVehicule">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Modèle du véhicule</FormLabel>
                                            <FormControl>
                                                <Input placeholder="berline" v-bind="componentField"
                                                    autocomplete="modelVehicule" v-model="form.modelVehicule" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </div>
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3 mb-5">
                                    <FormField v-slot="{ componentField }" name="couleurVehicule">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Couleur du véhicule</FormLabel>
                                            <FormControl>
                                                <Input placeholder="noire" v-bind="componentField"
                                                    autocomplete="couleurVehicule" v-model="form.couleurVehicule" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- Carrosserie -->
                                    <FormField v-slot="{ componentField }" name="carrosserie">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Carrosserie</FormLabel>
                                            <FormControl>
                                                <Input placeholder="Abidjan,Cocody" v-bind="componentField"
                                                    autocomplete="carrosserie" v-model="form.carrosserie" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- typeTechnique -->
                                    <FormField v-slot="{ componentField }" name="typeTechnique">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Type Technique</FormLabel>
                                            <FormControl>
                                                <Input placeholder="4*4" v-bind="componentField"
                                                    autocomplete="typeTechnique" v-model="form.typeTechnique" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </div>
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3 mb-5">
                                    <!-- typePersonne -->
                                    <FormField v-slot="{ componentField }" name="genre">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Genre</FormLabel>
                                            <FormControl>
                                                <Input placeholder="exemple de personne" v-bind="componentField"
                                                    autocomplete="genre" v-model="form.genre" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- PTAC -->
                                    <FormField v-slot="{ componentField }" name="ptac">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Poids total en charge (PTAC)</FormLabel>
                                            <FormControl>
                                                <Input placeholder="exemple de personne" v-bind="componentField"
                                                    autocomplete="ptac" v-model="form.ptac" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- PU -->
                                    <FormField v-slot="{ componentField }" name="pu">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Poids Utile (PU)</FormLabel>
                                            <FormControl>
                                                <Input placeholder="exemple de personne" v-bind="componentField"
                                                    autocomplete="pu" v-model="form.pu" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </div>
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3 mb-5">
                                    <!-- PU -->
                                    <FormField v-slot="{ componentField }" name="pv">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Poids à Vide (PV)</FormLabel>
                                            <FormControl>
                                                <Input placeholder="exemple de personne" v-bind="componentField"
                                                    autocomplete="pv" v-model="form.pv" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- Puissance administrative -->
                                    <FormField v-slot="{ componentField }" name="puissance">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Puissance administrative</FormLabel>
                                            <FormControl>
                                                <Input placeholder="exemple de personne" v-bind="componentField"
                                                    autocomplete="puissance" v-model="form.puissance" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- Places assises -->
                                    <FormField v-slot="{ componentField }" name="placesAssises">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Places assises</FormLabel>
                                            <FormControl>
                                                <Input placeholder="exemple de personne" v-bind="componentField"
                                                    autocomplete="placesAssises" v-model="form.placesAssises" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </div>
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3 mb-5">
                                    <!-- Places assises -->
                                    <FormField v-slot="{ componentField }" name="placesAssises">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Places assises</FormLabel>
                                            <FormControl>
                                                <Input placeholder="exemple de personne" v-bind="componentField"
                                                    autocomplete="placesAssises" v-model="form.placesAssises" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- Sources d’énergie -->
                                    <FormField v-slot="{ componentField }" name="sourcesEnergie">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Sources d’énergie</FormLabel>
                                            <FormControl>
                                                <Input placeholder="exemple de personne" v-bind="componentField"
                                                    autocomplete="sourcesEnergie" v-model="form.sourcesEnergie" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- Nombre d’essieux -->
                                    <FormField v-slot="{ componentField }" name="nombreEssieux">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Nombre d’essieux</FormLabel>
                                            <FormControl>
                                                <Input placeholder="exemple de personne" v-bind="componentField"
                                                    autocomplete="nombreEssieux" v-model="form.nombreEssieux" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </div>
                            </div>


                            <!-- Informations de l'entreprise -->
                            <!-- Fichiers (si type != Physique) -->

                            <div v-show="form.type !== 'Physique'" class="mt-10">
                                <h3 class="text-md font-semibold mb-6 ">Informations de l’entreprise</h3>
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3">
                                    <div>
                                        <p class="text-sm font-medium mb-2 ">Registre de Commerce</p>
                                        <Input type="file" @change="onFileChange($event, 'file_1')" class="py-0 h-10" />
                                        <p v-if="file_1Error" class="text-red-600 text-sm mt-1">{{ file_1Error }}</p>
                                    </div>
                                    <div>
                                        <!-- marqueVehicule -->
                                        <p class="text-sm font-medium mb-2 ">Type de pièce</p>
                                        <Select v-model="form.type_piece">
                                            <SelectTrigger class="h-10">
                                                <SelectValue placeholder="Type de pièce" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectItem value="1">CNI</SelectItem>
                                                    <SelectItem value="2">Passport</SelectItem>
                                                    <SelectItem value="3">Permis de conduire</SelectItem>
                                                    <SelectItem value="4">Autre</SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2 ">Pièce d'identité en cours de validité</p>
                                        <Input type="file" @change="onFileChange($event, 'file_2')" class="py-0 h-10" />
                                        <p v-if="file_2Error" class="text-red-600 text-sm mt-1">{{ file_2Error }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2 ">Autorisation de la société de crédit</p>
                                        <Input type="file" @change="onFileChange($event, 'file_3')" class="py-0 h-10" />
                                        <p v-if="file_3Error" class="text-red-600 text-sm mt-1">{{ file_3Error }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2 ">Extrait de carte grise</p>
                                        <Input type="file" @change="onFileChange($event, 'file_4')" class="py-0 h-10" />
                                        <p v-if="file_4Error" class="text-red-600 text-sm mt-1">{{ file_4Error }}</p>
                                    </div>
                                </div>
                            </div>
                            <!-- show Summary -->
                            <div v-if="showSummary"
                                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 p-4 overflow-auto">
                                <div
                                    class="bg-white dark:bg-gray-800 rounded-2xl p-8 w-full max-w-4xl shadow-2xl space-y-6">
                                    <h3 class="text-2xl font-bold text-center mb-2">Résumé des informations</h3>

                                    <!-- Informations du propriétaire -->
                                    <div>
                                        <h4 class="text-lg font-semibold mb-4">Informations du propriétaire :
                                            <samp class="text-[#ca7600]"> {{ formDataSummary.firstname }} {{
                                                formDataSummary.lastname }}</samp>
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <p><strong>Email :</strong> {{ formDataSummary.email }}</p>
                                            <p><strong>Adresse :</strong> {{ formDataSummary.adresse }}</p>
                                            <p><strong>District :</strong> {{ formDataSummary.district }}</p>
                                            <p><strong>Ville de naissance :</strong> {{ formDataSummary.villeNaissance
                                            }}</p>
                                            <!-- <p><strong>Nom :</strong> {{ formDataSummary.firstname }}</p>
                                            <p><strong>Prénom :</strong> {{ formDataSummary.lastname }}</p> -->
                                            <p><strong>Téléphone :</strong> {{ formDataSummary.phone }}</p>
                                            <p><strong>Type de personne :</strong> {{ formDataSummary.typePersonne }}
                                            </p>
                                        </div>
                                    </div>
                                    <Separator class="my-4" />
                                    <!-- Informations de l'entreprise -->
                                    <div>

                                        <h4 class="text-lg font-semibold mb-4">Informations du véhicule :
                                            <samp class="text-[#ca7600]">{{ formDataSummary.marqueVehicule }}</samp>
                                        </h4>


                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <p><strong>Marque :</strong> {{ formDataSummary.marqueVehicule }}</p>
                                            <p><strong>Model :</strong> {{ formDataSummary.modelVehicule }}</p>
                                            <p><strong>Couleur :</strong> {{ formDataSummary.couleurVehicule }}</p>
                                            <p><strong>Carrosserie :</strong> {{ formDataSummary.carrosserie }}
                                            </p>
                                            <p><strong>Type technique :</strong> {{
                                                formDataSummary.typeTechnique
                                                }}</p>
                                            <p><strong>Genre :</strong> {{ formDataSummary.genre }}</p>
                                            <p><strong>Poids Total en charge (PTAC) :</strong> {{ formDataSummary.ptac
                                                }}</p>
                                            <p><strong>Poids Utile (PU) :</strong> {{ formDataSummary.pu }}</p>
                                            <p><strong>Poids à Vide (PV) :</strong> {{ formDataSummary.pv }}</p>
                                            <p><strong>Puissance administrative :</strong> {{ formDataSummary.pa }}</p>
                                            <p><strong>Places Assises :</strong> {{ formDataSummary.placesAssises }}</p>
                                            <p><strong>Sources d’énergie :</strong> {{ formDataSummary.sourcesEnergie }}
                                            </p>
                                            <p><strong>Nbre d’Essieux :</strong> {{ formDataSummary.nombreEssieux }}</p>
                                        </div>
                                    </div>
                                    <Separator class="my-4" />
                                    <!-- Fichiers -->
                                    <div v-if="formDataSummary.files.length && formDataSummary.type == 'Morale'"
                                        class="mt-6">
                                        <h4 class="text-lg font-semibold mb-2">Fichiers ajoutés :</h4>
                                        <ul
                                            class="list-disc list-inside text-sm space-y-1 text-gray-700 dark:text-gray-300">
                                            <li v-for="(file, index) in formDataSummary.files" :key="index">{{ file }}
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Actions -->
                                    <div class="mt-6 flex justify-end gap-4">
                                        <Button @click="showSummary = false" variant="outline"
                                            class="px-6 py-2 rounded-lg">Modifier</Button>
                                        <Button @click="submitFinal"
                                            class="px-6 py-2 rounded-lg primary-color text-white">
                                            <!-- <MoveLeft class=" w-4 h-4 mr-2" />
                                            <Validate class="w-4 h-4 mr-2" /> -->
                                            Valider
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <!-- Bouton continuer -->
                            <div class="mt-8">
                                <Button type="submit">
                                    CONTINUER
                                </Button>
                            </div>
                        </form>
                    </div>
                </ScrollArea>
                <!-- Ton contenu ici -->

                <!-- Modal Résumé -->

            </Card>
        </main>
        <Toaster richColors position="top-right" />

    </div>


</template>



<script setup>
import { Button } from '@/components/ui/button'
import { ref } from 'vue'

import {
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form'
import {
    Card,
} from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Input_search } from '@/components/ui/Input_search'
import { Label } from '@/components/ui/label'
import { Toaster, toast } from 'vue-sonner'
import { toTypedSchema } from '@vee-validate/zod'
import { vAutoAnimate } from '@formkit/auto-animate/vue'
import { useForm } from 'vee-validate'
import { router } from '@inertiajs/vue3'
import * as z from 'zod'
import { useForm as useValidationForm } from 'vee-validate';
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
import { Separator } from '@/components/ui/separator'



const props = defineProps({
    vin: String
})
// Schéma de validation
const formSchema = toTypedSchema(z.object({

    firstname: z
        .string({
            required_error: "Le Nom est obligatoire.",
        })
        .min(3, {
            message: 'Le Nom doit contenir au moins 3 caractères.',
        }),

    lastname: z
        .string({
            required_error: "Le Prénom est obligatoire.",
        })
        .min(3, {
            message: 'Le Prénom doit contenir au moins 3 caractères.',
        }),

    email: z
        .string({
            required_error: "L'email est obligatoire.",
        })
        .email({
            message: 'Adresse email invalide.',
        }),

    adresse: z
        .string({
            required_error: "L'adresse est obligatoire.",
        })
        .min(3, {
            message: "L'adresse doit contenir au moins 3 caractères.",
        }),

    phone: z
        .string({
            required_error: "Le numéro de téléphone est obligatoire.",
        })
        .min(10, {
            message: "Le numéro de téléphone doit contenir au moins 10 caractères.",
        }),

    typePersonne: z
        .string({
            required_error: "Le type de personne est obligatoire.",
        })
        .min(3, {
            message: "Le type de personne doit contenir au moins 3 caractères.",
        }),

    district: z
        .string({
            required_error: "Le district est obligatoire.",
        })
        .min(3, {
            message: "Le district doit contenir au moins 3 caractères.",
        }),

    villeNaissance: z
        .string({
            required_error: "La ville de naissance est obligatoire.",
        })
        .min(3, {
            message: "La ville de naissance doit contenir au moins 3 caractères.",
        }),

    // vin: z
    //     .string({
    //         required_error: "Le châssis (VIN) est obligatoire.",
    //     })
    //     .min(3, {
    //         message: "Le châssis (VIN) doit contenir au moins 3 caractères.",
    //     }),

    marqueVehicule: z
        .string({
            required_error: "La marque du véhicule est obligatoire.",
        })
        .min(3, {
            message: "La marque du véhicule doit contenir au moins 3 caractères.",
        }),

    modelVehicule: z
        .string({
            required_error: "Le modèle du véhicule est obligatoire.",
        })
        .min(3, {
            message: "Le modèle du véhicule doit contenir au moins 3 caractères.",
        }),

    couleurVehicule: z
        .string({
            required_error: "La couleur du véhicule est obligatoire.",
        })
        .min(3, {
            message: "La couleur du véhicule doit contenir au moins 3 caractères.",
        }),

    carrosserie: z
        .string({
            required_error: "La carrosserie est obligatoire.",
        })
        .min(3, {
            message: "La carrosserie doit contenir au moins 3 caractères.",
        }),

    typeTechnique: z
        .string({
            required_error: "Le type technique est obligatoire.",
        })
        .min(3, {
            message: "Le type technique doit contenir au moins 3 caractères.",
        }),

    genre: z
        .string({
            required_error: "Le genre est obligatoire.",
        })
        .min(3, {
            message: "Le genre doit contenir au moins 3 caractères.",
        }),

    ptac: z
        .string({
            required_error: "Le poids total en charge (PTAC) est obligatoire.",
        })
        .min(3, {
            message: "Le poids total en charge (PTAC) doit contenir au moins 3 caractères.",
        }),

    pu: z
        .string({
            required_error: "Le poids utile (PU) est obligatoire.",
        })
        .min(3, {
            message: "Le poids utile (PU) doit contenir au moins 3 caractères.",
        }),

    pv: z
        .string({
            required_error: "Le poids à vide (PV) est obligatoire.",
        })
        .min(3, {
            message: "Le poids à vide (PV) doit contenir au moins 3 caractères.",
        }),

    puissance: z
        .string({
            required_error: "La puissance administrative est obligatoire.",
        })
        .min(3, {
            message: "La puissance administrative doit contenir au moins 3 caractères.",
        }),

    placesAssises: z
        .string({
            required_error: "Le nombre de places assises est obligatoire.",
        })
        .min(3, {
            message: "Le nombre de places assises doit contenir au moins 3 caractères.",
        }),

    sourcesEnergie: z
        .string({
            required_error: "Les sources d'énergie sont obligatoires.",
        })
        .min(3, {
            message: "Les sources d'énergie doivent contenir au moins 3 caractères.",
        }),

    nombreEssieux: z
        .string({
            required_error: "Le nombre d'essieux est obligatoire.",
        })
        .min(3, {
            message: "Le nombre d'essieux doit contenir au moins 3 caractères.",
        }),
    // files: z
    //     .any()
    //     .array(z.instanceof(File))
    //     .nonempty("Un fichier est requis.")
}))

// Valeurs du formulaire
const form = ref({
    firstname: '',
    lastname: '',
    email: '',
    adresse: '',
    phone: '',
    typePersonne: '',
    district: '',
    villeNaissance: '',
    vin: props.vin,
    marqueVehicule: '',
    modelVehicule: '',
    couleurVehicule: '',
    carrosserie: '',
    typeTechnique: '',
    genre: '',
    ptac: '',
    pu: '',
    pv: '',
    puissance: '',
    placesAssises: '',
    sourcesEnergie: '',
    nombreEssieux: '',
    type: '',
    file_1: null,
    file_2: null,
    file_3: null,
    file_4: null,
    file_5: null,
});

//erreur fichier si type == moral
const typeError = ref('');
const type_pieceError = ref('');
const file_1Error = ref('');
const file_2Error = ref('');
const file_3Error = ref('');
const file_4Error = ref('');

// Validation
const { handleSubmit } = useValidationForm({
    validationSchema: formSchema,
    initialValues: form.value,
});

// Modal & résumé
const showSummary = ref(false);
const formDataSummary = ref({
    firstname: '',
    lastname: '',
    email: '',
    adresse: '',
    phone: '',
    typePersonne: '',
    district: '',
    villeNaissance: '',
    vin: '',
    marqueVehicule: '',
    modelVehicule: '',
    couleurVehicule: '',
    carrosserie: '',
    typeTechnique: '',
    genre: '',
    ptac: '',
    pu: '',
    pv: '',
    puissance: '',
    placesAssises: '',
    sourcesEnergie: '',
    nombreEssieux: '',
    type: '',
    type_piece: '',
    files: [],
});

// Gérer les fichiers
function onFileChange(event, key) {
    form.value[key] = event.target.files[0];
}

// Afficher le résumé dans le modal
const onSubmit = handleSubmit((values) => {
    // Reset all errors
    typeError.value = '';
    type_pieceError.value = '';
    file_1Error.value = '';
    file_2Error.value = '';
    file_3Error.value = '';
    file_4Error.value = '';

    const errors = []; // Liste des erreurs

    if (!form.value.type) {
        const msg = 'Sélectionner le Type de personne';
        typeError.value = msg;
        errors.push(msg);
    }

    // if (!form.value.typePiece) {
    //     const msg = 'Sélectionner le Type de pièce';
    //     type_pieceError.value = msg;
    //     errors.push(msg);
    // }

    if (form.value.type != "Physique") {
        if (!form.value.file_1) {
            const msg = 'Sélectionner le registre de commerce.';
            file_1Error.value = msg;
            errors.push(msg);
        }
        if (!form.value.file_2) {
            const msg = "Sélectionner une Pièce d'identité en cours de validité.";
            file_2Error.value = msg;
            errors.push(msg);
        }
        if (!form.value.file_3) {
            const msg = 'Sélectionner une Autorisation de la société de crédit.';
            file_3Error.value = msg;
            errors.push(msg);
        }
        if (!form.value.file_4) {
            const msg = 'Sélectionner un Extrait de carte grise.';
            file_4Error.value = msg;
            errors.push(msg);
        }
    }

    // Si erreurs → on affiche tout dans le toast
    if (errors.length > 0) {
        toast.error(errors.join('\n'));
        return;
    }

    formDataSummary.value = {
        ...values,
        type: form.value.type,
        files: [],
    };

    for (let i = 1; i <= 5; i++) {
        const file = form.value[`file_${i}`];
        if (file) {
            formDataSummary.value.files.push(file.name);
        }
    }

    showSummary.value = true;
});

// Envoi final
function submitFinal() {
    const data = new FormData();
    data.append('adresse', formDataSummary.value.adresse);
    data.append('typePersonne', formDataSummary.value.type);
    for (let i = 1; i <= 5; i++) {
        const file = form.value[`file_${i}`];
        if (file) {
            data.append(`file_${i}`, file);
        }
    }

    // Exemple de log ou envoi réel
    for (let [key, val] of data.entries()) {
        console.log(key + ':', val);
    }

    alert('Formulaire soumis avec succès !');
}

</script>


<script>
import Main from '/resources/js/Pages/Main.vue';
import BoutonRetour from "/resources/js/components/BoutonRetour.vue";

export default {
    layout: Main,
};
</script>

<style scoped>
/* Optionnel pour responsive / animation */
</style>