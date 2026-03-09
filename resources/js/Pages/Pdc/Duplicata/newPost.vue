<template>
    <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <!-- Titre -->
        <h4 class="text-2xl font-bold tracking-tight">
            Duplicata / Post Immatriculation
        </h4>

        <!-- Sélecteur de plage de dates -->
        <!-- <Link :href="route('show.pdc.duplicata')"> -->
        <BoutonRetour @click="revenirArriere()" />
        <!-- </Link> -->
    </div>
    <!-- selectionner type de service -->
    <div class="rounded-lg dark:border-gray-700" v-if="showTypeService">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <Card class="h-full flex flex-col">
                <div class="p-6 bg-white rounded-xl  mt-8 m-5">
                    <h2 class="text-lg font-semibold mb-6">Sélectionner les types de services</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div v-for="(option, index) in options" :key="index"
                            class="group rounded-xl border transition-all cursor-pointer hover:shadow-xl shadow p-10"
                            :class="selected.includes(option) ? 'border-gray-300 bg-muted' : 'border-muted hover:border-gray-100'"
                            @click="toggleSelection(option)">
                            <div class="flex items-start space-x-3 p-4 ">
                                <Checkbox :checked="selected.includes(option)" @change="toggleSelection(option)" />
                                <Label class="text-sm font-medium text-gray-900">
                                    {{ option.element_facturation }}
                                </Label>
                            </div>
                        </div>

                    </div>
                    <div class="flex  justify-end mt-5">
                        <Button @click="getTypeService" class="justify-content-end">
                            CONTINUER
                        </Button>
                    </div>
                </div>
            </Card>
        </main>
    </div>
    <!-- Recherche de vin -->
    <div class="rounded-lg dark:border-gray-700" v-if="showRechercheVin">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <Card class="h-full flex flex-col">
                <h5 class="pl-8 pt-8 text-[#ca7600]">Post-immatriculation : </h5>
                <span v-for="i in selected" class="pl-8 pt-3 ">{{ i.element_facturation }},</span>
                <!-- <span v-if="mutation" class="pl-8 pt-3 ">{{ mutation }},</span> -->

                <!-- Formulaire initial -->
                <div v-if="vinStatus === 'initial'" class="space-y-8 p-8">
                    <h6>Rechercher le numéro de châssis (VIN): {{ props.vin }}</h6>
                    <div>
                        <Input v-model="props.vin" placeholder="5771905005" class="w-full md:w-1/4" disabled />
                        <!-- <p v-if="vinError" class="text-red-600 text-sm mt-1">{{ vinError }}</p> -->
                        <!-- Message d'erreur -->
                    </div>

                    <div class="flex items-center space-x-2">
                        <Button @click="findVin" :disabled="isLoading">
                            <template v-if="isLoading">
                                <svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                </svg>
                                Recherche...
                            </template>
                            <template v-else>
                                <Search class="w-4 h-4 mr-2" />
                                Rechercher
                            </template>
                        </Button>
                    </div>
                </div>

                <!-- VIN trouvé -->
                <div v-else-if="vinStatus === 'found'" class="text-center space-y-8 p-8">
                    <p>Le VIN <samp class="text-[#ca7600] whitespace-nowrap">{{ vin }}</samp> est immatriculé</p>
                    <div class="text-center space-x-5 mt-4">
                        <BoutonRetour @click="reset" size="sm">Retour</BoutonRetour>

                        <Link
                            :href="route('show.new.pdc.duplicata.post.immatriculation.add.data', { vin: cleanVin(props.vin), selected: selected, mutation: mutation })">
                        <Button variant="outline" size="sm">
                            <MoveRight />
                            Continuer
                        </Button>
                        </Link>
                    </div>

                </div>

                <!-- VIN non trouvé -->
                <div v-else-if="vinStatus === 'not_found'" class="text-center space-y-8 p-8">
                    <p>Ce VIN n'a pas été trouvé</p>
                    <div class="text-center space-x-2 mt-4">
                        <Button @click="reset">Réessayer</Button>
                    </div>
                </div>
            </Card>
        </main>
    </div>
    <Toaster richColors position="top-right" />
</template>

<script setup>
import { ref, computed, onMounted, onBeforeMount } from 'vue'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import BoutonRetour from "/resources/js/components/BoutonRetour.vue";
import { Toaster, toast } from 'vue-sonner'
import { Search, MoveRight } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import axios from 'axios'

const props = defineProps({
    typeServices: Array,
    vin: String,
})

// IDs des services spéciaux
const MUTATION_ID = 31 // ID de "MUTATION : modification nom du propriétaire"

const options = computed(() => props.typeServices)

// Variables réactives
const showTypeService = ref(true)
const showRechercheVin = ref(false)
const selected = ref([]) // Tableau pour tous les services sélectionnés
const mutation = ref(null) // Stocke l'objet de la mutation si sélectionnée

const vinLocal = ref(props.vin || '')
const vin = computed({
    get: () => vinLocal.value,
    set: (value) => { vinLocal.value = value }
})

const vinStatus = ref('initial')
const isLoading = ref(false)

// Fonction pour vérifier si un service est sélectionné
const isSelected = (option) => {
    return selected.value.some(opt => opt.id === option.id)
}

// Fonction pour basculer la sélection d'un service
const toggleSelection = (option) => {
    const index = selected.value.findIndex(opt => opt.id === option.id)

    if (index !== -1) {
        // Désélectionner le service
        selected.value.splice(index, 1)

        // Si c'est la mutation, réinitialiser mutation.value
        if (option.id === MUTATION_ID) {
            mutation.value = null
        }
    } else {
        // Sélectionner le service
        selected.value.push(option)

        // Si c'est la mutation, mettre à jour mutation.value
        if (option.id === MUTATION_ID) {
            mutation.value = option
        }
    }

    console.log('Services sélectionnés:', selected.value.map(opt => ({
        id: opt.id,
        nom: opt.element_facturation
    })))
    console.log('Mutation:', mutation.value?.element_facturation)
}

function cleanVin(vin) {
    return vin.replace(/\s+/g, '')
}

const getTypeService = () => {
    // Mettre à jour la mutation si elle est dans les services sélectionnés
    const mutationOption = selected.value.find(option => option.id === MUTATION_ID)
    mutation.value = mutationOption || null

    if (selected.value.length > 0) {
        showTypeService.value = false
        showRechercheVin.value = true
    } else {
        toast.error('Sélectionner au moins un type de service')
    }
}

function revenirArriere() {
    window.history.back()
}

const reset = () => {
    vinStatus.value = 'initial'
    selected.value = []
    mutation.value = null
}

const resetForm = () => {
    vinLocal.value = ''
    vinStatus.value = 'initial'
    showTypeService.value = true
    showRechercheVin.value = false
    selected.value = []
    mutation.value = null
}

const findVin = async () => {
    isLoading.value = true
    try {
        const cleanedVin = props.vin.trim()
        const response = await axios.get(`/verifie/vin/${cleanedVin}`)
        vinStatus.value = response.data.exists ? 'found' : 'not_found'
    } catch (error) {
        console.error(error)
    } finally {
        isLoading.value = false
    }
}

// Réinitialisation automatique
onBeforeMount(() => {
    resetForm()
})
</script>






<script>
import Main from '/resources/js/Pages/Main.vue';
import BoutonRetour from '/resources/js/components/BoutonRetour.vue';
import { Search } from 'lucide-vue-next';
export default {
    layout: Main,
};


</script>
