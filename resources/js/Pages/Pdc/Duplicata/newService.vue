<template>
    <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <!-- Titre -->
        <h4 class="text-2xl font-bold tracking-tight">
            Duplica
        </h4>

        <!-- Sélecteur de plage de dates -->
        <!-- <Link :href="route('show.pdc.post.immatriculation')">
        <BoutonRetour @click="resetTypeService()" />
        </Link> -->
        <Link :href="route('show.pdc.duplicata')">
        <BoutonRetour @click="resetTypeService" size="sm">Retour</BoutonRetour>
        </Link>
    </div>
    <!-- selectionner type de service -->
    <div class="rounded-lg dark:border-gray-700">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <Card class="h-full flex flex-col">
                <div class="p-6 bg-white rounded-xl  mt-8 m-5">
                    <h2 class="text-lg font-semibold mb-6">Sélectionner les types de services</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div v-for="(option, index) in filteredOptions" :key="index"
                            class="group rounded-xl border transition-all cursor-pointer hover:shadow-xl shadow p-10"
                            :class="selected.includes(option) ? 'border-gray-300 bg-muted' : 'border-muted hover:border-gray-100'"
                            @click="toggleSelection(option)">
                            <div class="flex items-start space-x-3 p-4 ">
                                <Checkbox :checked="selected.includes(option)" @change="toggleSelection(option)" />
                                <Label class="text-sm font-medium text-gray-900">
                                    {{ option.nom_type_service }}
                                </Label>
                            </div>
                        </div>
                    </div>
                    <div class="flex  justify-end mt-5">

                        <!-- <Link
                            :href="route('show.new.pdc.duplicata.add.data', { vin: props.data.vin, selected: selected, postImtData1: props.postImtData })"> -->
                        <Button @click="getTypeService" class="justify-content-end">
                            CONTINUER
                        </Button>
                        <!-- </Link> -->
                    </div>
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
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Card } from '@/components/ui/card'
import BoutonRetour from "/resources/js/components/BoutonRetour.vue";
import { Toaster, toast } from 'vue-sonner'
import { onMounted } from 'vue';
import { router } from '@inertiajs/vue3'
import { Search } from 'lucide-vue-next';


const props = defineProps({
    typeServices: Array,
    data: Number,
    postImtData: Object
})


const filteredOptions = computed(() => {
    if (props.data.nb_plaque === 1) {
        return props.typeServices.filter(opt => opt.nom_type_service === 'Plaque Arrière')
    }
    return props.typeServices
})


const showTypeService = ref(true)
const showRechercheVin = ref(false)
const selected = ref([])
const mutation = ref(null);

// new service// Simuler composants shadcn

const toggleSelection = (option) => {
    if (selected.value.includes(option)) {
        selected.value = [];
    } else {
        selected.value = [option];
    }
}



const getTypeService = () => {

    //vérifier l'éligigilité au duplicata

    // Vérifie si "Changement de propriétaire : MUTATION" est dans selected
    if (selected.value.includes('Changement de propriétaire : MUTATION')) {
        mutation.value = 'Changement de propriétaire : MUTATION';
    }
    if (selected.value.length) {
        showTypeService.value = false;
        showRechercheVin.value = true;
    } else {
        toast.error('Sélectionner au moins un type de service');
    }

    const donne = {
        vin: props.data.vin,
        selected: selected.value,
        postImtData: props.postImtData
    };

    router.visit(`/pdc/duplicata/new/add/data/${encodeURIComponent(JSON.stringify(donne))}`);

    console.log('selected:', selected.value);
    console.log('mutation:', mutation.value);
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
    console.log("postImtData : ", props.postImtData);
})
// console.log('selected.value:', selected.value);

// gestion vin


const vin = ref('');
const vinStatus = ref('initial');
const isLoading = ref(false);
const vinError = ref(''); // ← message d’erreur




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



</script>
