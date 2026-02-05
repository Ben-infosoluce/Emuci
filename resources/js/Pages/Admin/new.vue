<template>

    <div v-if="isOpen">
        <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <!-- Titre -->
            <h4 class="text-2xl font-bold tracking-tight">
                Nouveau Paiement
            </h4>

            <!-- Sélecteur de plage de dates -->
            <!-- <Link :href="route('show.pdc.post.immatriculation')"> -->
            <BoutonRetour @click="boutonRetour()" />
            <!-- </Link> -->
        </div>
        <!-- Recherche de vin -->
        <div class="rounded-lg dark:border-gray-700">
            <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
                <Card class="h-full flex flex-col">
                    <h5 class="pl-8 pt-8 text-[#ca7600]">Recherche une demande : </h5>
                    <span v-for="i in selected" class="pl-8 pt-3 ">{{ i }},</span>
                    <!-- <span v-if="mutation" class="pl-8 pt-3 ">{{ mutation }},</span> -->

                    <!-- Formulaire initial -->
                    <div v-if="vinStatus === 'initial'" class="space-y-8 p-8">
                        <h6>Entrez le numéro chrono</h6>
                        <div>
                            <Input v-model="vin" placeholder="5771905005" class="w-full md:w-1/4" />
                            <p v-if="vinError" class="text-red-600 text-sm mt-1">{{ vinError }}</p>
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
                        <p>Le Numero chrono <samp class="text-[#ca7600] whitespace-nowrap">{{ vin }}</samp> existe</p>
                        <Link :href="route('show.new.caisse.get.data', { vin: vin })">
                        <br>
                        Voir les détails</Link>
                        <div class="text-center space-x-2 mt-4">
                            <BoutonRetour @click="reset">Retour</BoutonRetour>
                        </div>
                    </div>

                    <!-- VIN non trouvé -->
                    <div v-else-if="vinStatus === 'not_found'" class="text-center space-y-8 p-8">
                        <p>Ce numéro chrono n'a pas été trouvé</p>
                        <div class="text-center space-x-2 mt-4">
                            <Button @click="reset">Réessayer</Button>
                        </div>
                    </div>
                </Card>
            </main>
        </div>

    </div>


    <div v-else>
        <Control />
    </div>

    <Toaster richColors position="top-right" />
</template>

<script setup>
import { ref, computed } from 'vue'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'
// import { returnBack } from "../../../composable/fonction"
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Card } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Select, SelectTrigger, SelectContent, SelectItem } from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import { MoreVertical } from 'lucide-vue-next'
import BoutonRetour from "/resources/js/components/BoutonRetour.vue";
import { Toaster, toast } from 'vue-sonner'
import { onMounted } from 'vue';
import { storeToRefs } from "pinia";
import { useCaisseStore } from "@/stores/mainStore";


const store = useCaisseStore();
const { isOpen } = storeToRefs(store);
</script>

<script>
import Main from '/resources/js/Pages/Main.vue';
export default {
    layout: Main,
};



const selected = ref([])
// new service// Simuler composants shadcn

const toggleSelection = (option) => {
    if (selected.value.includes(option)) {
        selected.value = selected.value.filter(o => o !== option)
    } else {
        selected.value.push(option)

    }
}


onMounted(() => {
    reset();
    // console.log('selected.value:', selected.value);
    selected.value = [] // Réinitialise à vide
})
// console.log('selected.value:', selected.value);

// gestion vin
import BoutonRetour from '/resources/js/components/BoutonRetour.vue';
import { Search } from 'lucide-vue-next';

const vin = ref('');
const vinStatus = ref('initial');
const isLoading = ref(false);
const vinError = ref(''); // ← message d’erreur

const findVin = async () => {
    vinError.value = ''; // ← Réinitialise le message

    if (!vin.value.trim()) {
        vinError.value = 'Entrez le numéro de châssis (VIN).';
        return;
    }

    isLoading.value = true;
    try {
        const response = await axios.get(`/verifie/chrono/${vin.value}`);
        vinStatus.value = response.data.exists ? 'found' : 'not_found';
    } catch (error) {
        console.error('Erreur lors de la vérification du VIN', error);
    } finally {
        isLoading.value = false;
    }
};

const reset = () => {
    vin.value = '';
    vinStatus.value = 'initial';
    vinError.value = '';
};

const boutonRetour = () => {
    window.history.back();
}
</script>
