<template>
    <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <!-- Titre -->
        <h4 class="text-2xl font-bold tracking-tight">
            Re-immatriculation
        </h4>

        <!-- Sélecteur de plage de dates -->
        <Link :href="route('show.pdc.re.immatriculation.select')">
        <BoutonRetour @click="resetTypeService()" />
        </Link>
    </div>
    <!-- selectionner type de service -->
    <div class="rounded-lg dark:border-gray-700" v-show="showTypeService">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <Card class="h-full flex flex-col">
                <div class="p-6 bg-white rounded-xl  mt-8 m-5">
                    <h2 class="text-lg font-semibold mb-6">Sélectionner les types de services</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div v-for="(option, index) in filteredOptions" :key="index"
                            class="group rounded-xl border transition-all cursor-pointer hover:shadow-xl shadow p-10"
                            :class="selected === option ? 'border-gray-300 bg-muted' : 'border-muted hover:border-gray-100'"
                            @click="toggleSelection(option)">
                            <div class="flex items-start space-x-3 p-4">
                                <Checkbox :checked="selected === option" @change="toggleSelection(option)" />
                                <Label class="text-sm font-medium text-gray-900">
                                    {{ option.nom_type_service }}
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
    <div class="rounded-lg dark:border-gray-700" v-show="showRechercheVin">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <Card class="h-full flex flex-col">
                <h5 class="pl-8 pt-8 text-[#ca7600]">Re-immatriculation : </h5>
                <span class="pl-8 pt-3 ">{{ selected?.nom_type_service ?? '' }},</span>
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
                    <div class="text-center space-x-5 mt-4">
                        <BoutonRetour @click="reset" size="sm">Retour</BoutonRetour>
                        <Link
                            :href="route('show.new.pdc.re.immatriculation.add.data', { vin: cleanVin(vin), selected: selected, mutation: mutation })">
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
                    <br>
                    <Link
                        :href="route('show.new.pdc.re.immatriculation.add.data', { vin: vin, selected: selected, mutation: mutation })">
                    <Button @click="reset">Nouvel Enregistrement</Button></Link>
                </div>
            </Card>
        </main>
    </div>
    <Toaster richColors position="top-right" />
</template>

<script setup>
import { ref, computed } from 'vue'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import BoutonRetour from "/resources/js/components/BoutonRetour.vue";
import { Toaster, toast } from 'vue-sonner'
import { onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
// gestion vin


const props = defineProps({
    typeServices: Array
})
const options = computed(() => props.typeServices)

const user_permissions = usePage().props.auth_user?.user_permissions ?? [];

const permissionMap = {
    1: 5, // typeService id 1 => permission 5
    2: 6, // typeService id 2 => permission 6
};

const canShow = (option) => {
    if (!option) return false;

    const neededPermission = permissionMap[option.id];
    if (!neededPermission) return false;

    return user_permissions.includes(neededPermission);
};

const filteredOptions = computed(() =>
    (options.value ?? []).filter(option => canShow(option))
);




const showTypeService = ref(true)
const showRechercheVin = ref(false)
const selected = ref(null)
const mutation = ref(null);

// new service// Simuler composants shadcn

const toggleSelection = (option) => {
    selected.value = selected.value === option ? null : option
}

const getTypeService = () => {
    if (selected.value) {
        showTypeService.value = false;
        showRechercheVin.value = true;
    } else {
        toast.error('Sélectionnez un type de service');
    }

    console.log('selected:', selected.value);
};


const resetTypeService = () => {
    showTypeService.value = true
    showRechercheVin.value = false
    selected.value = []
}

const retunBack = () => {
    showTypeService.value = true
    showRechercheVin.value = false
}

onMounted(() => {
    reset();
    // console.log('selected.value:', selected.value);
    selected.value = [] // Réinitialise à vide
})

function cleanVin(vin) {
    return vin.replace(/\s+/g, '');
}



const vin = ref('');
const vinStatus = ref('initial');
const isLoading = ref(false);
const vinError = ref(''); // ← message d’erreur

const findVin = async () => {
    vinError.value = ''; // Réinitialise le message

    const cleanedVin = vin.value.replace(/\s+/g, ''); // Supprimer tous les espaces

    if (!cleanedVin) {
        vinError.value = 'Entrez le numéro de châssis (VIN).';
        return;
    }

    isLoading.value = true;
    try {
        const response = await axios.get(`/verifie/vin/${cleanedVin}`);
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
</script>

<script>
import Main from '/resources/js/Pages/Main.vue';
export default {
    layout: Main,
};
import BoutonRetour from '/resources/js/components/BoutonRetour.vue';
import { Search, MoveRight } from 'lucide-vue-next';


</script>
