<template>
    <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <!-- Titre -->
        <h4 class="text-2xl font-bold tracking-tight">
            Modification — Nouveau Dossier
        </h4>

        <!-- Bouton Retour -->
        <div class="flex items-center space-x-2">
            <Link :href="route('show.pdc.modification.select')">
            <BoutonRetour />
            </Link>
        </div>
    </div>

    <div class="rounded-lg dark:border-gray-700">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <Card class="h-full flex flex-col">
                <div class="space-y-8 p-8">
                    <h5 class="text-lg font-semibold">Informations du dossier</h5>

                    <form @submit.prevent="submitForm" class="space-y-6">

                        <!-- Ligne 1 : Nom & Prénom -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="nom">Nom <span class="text-red-500">*</span></Label>
                                <Input
                                    id="nom"
                                    v-model="form.nom"
                                    placeholder="Nom du client"
                                    :class="{ 'border-red-500': errors.nom }"
                                />
                                <p v-if="errors.nom" class="text-red-500 text-sm">{{ errors.nom }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="prenom">Prénom <span class="text-red-500">*</span></Label>
                                <Input
                                    id="prenom"
                                    v-model="form.prenom"
                                    placeholder="Prénom du client"
                                    :class="{ 'border-red-500': errors.prenom }"
                                />
                                <p v-if="errors.prenom" class="text-red-500 text-sm">{{ errors.prenom }}</p>
                            </div>
                        </div>

                        <!-- Ligne 2 : Châssis / VIN & N° Chrono -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="vin">Numéro de Châssis / VIN <span class="text-red-500">*</span></Label>
                                <Input
                                    id="vin"
                                    v-model="form.vin"
                                    placeholder="Ex: 5771905005"
                                    :class="{ 'border-red-500': errors.vin }"
                                />
                                <p v-if="errors.vin" class="text-red-500 text-sm">{{ errors.vin }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="num_chrono">N° Chrono <span class="text-red-500">*</span></Label>
                                <Input
                                    id="num_chrono"
                                    v-model="form.num_chrono"
                                    placeholder="Ex: CHR-2024-001"
                                    :class="{ 'border-red-500': errors.num_chrono }"
                                />
                                <p v-if="errors.num_chrono" class="text-red-500 text-sm">{{ errors.num_chrono }}</p>
                            </div>
                        </div>

                        <!-- Ligne 3 : Genre de véhicule & Statut plaque -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="genre">Genre de véhicule <span class="text-red-500">*</span></Label>
                                <Select v-model="form.genre">
                                    <SelectTrigger
                                        id="genre"
                                        class="w-full"
                                        :class="{ 'border-red-500': errors.genre }"
                                    >
                                        <SelectValue placeholder="Sélectionner un genre de véhicule" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Genres disponibles</SelectLabel>
                                            <SelectItem
                                                v-for="genre in genres"
                                                :key="genre.id"
                                                :value="genre.nom"
                                            >
                                                {{ genre.nom }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <p v-if="errors.genre" class="text-red-500 text-sm">{{ errors.genre }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="statut_plaque">Statut plaque <span class="text-red-500">*</span></Label>
                                <Select v-model="form.statut_plaque">
                                    <SelectTrigger
                                        id="statut_plaque"
                                        class="w-full"
                                        :class="{ 'border-red-500': errors.statut_plaque }"
                                    >
                                        <SelectValue placeholder="Sélectionner le statut de la plaque" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Statut</SelectLabel>
                                            <SelectItem value="1">Plaque posé</SelectItem>
                                            <SelectItem value="2">Plaque non posé</SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                                <p v-if="errors.statut_plaque" class="text-red-500 text-sm">{{ errors.statut_plaque }}</p>
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                            <Link :href="route('show.pdc.modification.select')">
                                <Button type="button" variant="outline">
                                    Annuler
                                </Button>
                            </Link>
                            <Button type="submit" :disabled="isSubmitting">
                                <template v-if="isSubmitting">
                                    <svg class="animate-spin h-4 w-4 mr-2 text-white" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                    </svg>
                                    Enregistrement...
                                </template>
                                <template v-else>
                                    <Save class="w-4 h-4 mr-2" />
                                    Enregistrer le dossier
                                </template>
                            </Button>
                        </div>

                    </form>
                </div>
            </Card>
        </main>
    </div>

    <Toaster richColors position="top-right" />
</template>

<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { Save } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card } from '@/components/ui/card'
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import BoutonRetour from '@/components/BoutonRetour.vue'
import { Toaster, toast } from 'vue-sonner'

const props = defineProps({
    genres: {
        type: Array,
        default: () => []
    }
})

const form = ref({
    nom: '',
    prenom: '',
    vin: '',
    num_chrono: '',
    genre: '',
    statut_plaque: '',
})

const errors = ref({})
const isSubmitting = ref(false)

function validate() {
    errors.value = {}
    if (!form.value.nom.trim()) errors.value.nom = 'Le nom est obligatoire.'
    if (!form.value.prenom.trim()) errors.value.prenom = 'Le prénom est obligatoire.'
    if (!form.value.vin.trim()) errors.value.vin = 'Le numéro de châssis est obligatoire.'
    if (!form.value.num_chrono.trim()) errors.value.num_chrono = 'Le N° Chrono est obligatoire.'
    if (!form.value.genre) errors.value.genre = 'Le genre de véhicule est obligatoire.'
    if (!form.value.statut_plaque) errors.value.statut_plaque = 'Le statut de la plaque est obligatoire.'
    return Object.keys(errors.value).length === 0
}

function submitForm() {
    if (!validate()) {
        toast.error('Veuillez remplir tous les champs obligatoires.')
        return
    }

    isSubmitting.value = true

    router.post(route('save.pdc.modification.new'), form.value, {
        onSuccess: () => {
            toast.success('Dossier créé avec succès.')
            form.value = { nom: '', prenom: '', vin: '', num_chrono: '', genre: '', statut_plaque: '' }
            errors.value = {}
        },
        onError: (errs) => {
            errors.value = errs
            toast.error('Une erreur est survenue. Vérifiez les champs.')
        },
        onFinish: () => {
            isSubmitting.value = false
        }
    })
}
</script>

<script>
import Main from '/resources/js/Pages/Main.vue'
export default {
    layout: Main,
}
</script>
