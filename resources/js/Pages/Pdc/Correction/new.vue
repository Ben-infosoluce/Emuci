<template>
    <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <h4 class="text-2xl font-bold tracking-tight">Immatriculation Spéciale</h4>
        <div class="flex items-center space-x-2">
            <Link :href="route('show.pdc.immatriculation')">
            <BoutonRetour />
            </Link>
        </div>
    </div>

    <div class="rounded-lg dark:border-gray-700">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <Card class="h-full flex flex-col">
                <h5 class="pl-8 pt-8">Immatriculation : Spéciale</h5>
                <!-- Formulaire initial -->
                <div v-if="vinStatus === 'initial'" class="space-y-8 p-8">
                    <h6>Entrez le numéro de châssis (VIN)</h6>
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
                    <p>Le VIN <samp class="text-[#ca7600] whitespace-nowrap">{{ vin }}</samp> est déjà immatriculé</p>
                    <!-- <Link :href="route('show.new.pdc.immatriculation.add.data', { vin: vin })">
                    Voir les détails</Link> -->
                    <div class="text-center space-x-2 mt-4">
                        <BoutonRetour @click="reset">Retour</BoutonRetour>
                    </div>
                </div>

                <!-- VIN non trouvé -->
                <div v-else-if="vinStatus === 'not_found'" class="text-center space-y-8 p-8">
                    <div class="text-center space-x-2 m-9">
                        <p>Le VIN <samp class="text-[#ca7600] whitespace-nowrap">{{ vin }}</samp> n'est pas encore
                            immatriculé
                        </p>
                        <br>
                        <div class="text-center space-x-2 mt-4 m-4">
                            <Link :href="route('show.new.pdc.immatriculation.add.data', { vin: vin })">
                            <Button>
                                <Plus class=" w-4 h-4 mr-2" /> Nouvelle immatriculation
                            </Button>
                            </Link>
                        </div>
                    </div>
                </div>
            </Card>
        </main>
    </div>
</template>


<script setup>
import { ref } from 'vue';
import axios from 'axios';
import Main from '/resources/js/Pages/Main.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card } from '@/components/ui/card';
import BoutonRetour from '/resources/js/components/BoutonRetour.vue';
import { Search } from 'lucide-vue-next';
import { MoveRight, MoveLeft, Plus } from 'lucide-vue-next'
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
        const response = await axios.get(`/verifie/vin/${vin.value}`);
        if (response.data.exists) {
            vinStatus.value = 'found';
        } else {
            vinStatus.value = 'not_found';
            // window.location.href = `/pdc/immatriculation/new/add/data/${vin.value}`;
            // `/pdc/immatriculation/new/add/data/${vin.value}`

        }
        // console.log(response.data.exists)
        // vinStatus.value = response.data.exists ? 'found' : 'not_found';
    } catch (error) {
        console.error('Erreur lors de la vérification du VIN', error);
    } finally {
        isLoading.value = false;
    }
};

const reset = () => {
    vin.value = '';
    vinStatus.value = 'initial';
    vinError.value = ''; // ← Réinitialise aussi ici
};
</script>


<script>
import Main from '/resources/js/Pages/Main.vue';
export default {
    layout: Main,
};

</script>
