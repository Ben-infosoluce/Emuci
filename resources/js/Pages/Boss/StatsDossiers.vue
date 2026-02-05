<template>
    <div class="p-6 space-y-6">
        <h1 class="text-2xl font-semibold mb-4">Tableau de bord - Boss</h1>


        <div class="flex items-center gap-3">
            <label class="font-medium">Polling :</label>

            <!-- ✅ Simple switch stylé -->
            <button @click="isPollingEnabled = !isPollingEnabled"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                :class="isPollingEnabled ? 'bg-green-500' : 'bg-gray-400'">
                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                    :class="isPollingEnabled ? 'translate-x-6' : 'translate-x-1'"></span>
            </button>

            <span class="text-sm text-muted-foreground">
                {{ isPollingEnabled ? "Activé" : "Désactivé" }}
            </span>
        </div>
        <!-- Filtres -->
        <div class="flex gap-4 mb-6">
            <select v-model="filters.range" @change="fetchData"
                class="flex h-9 items-center justify-between whitespace-nowrap rounded-md border border-input bg-white px-3 py-2 text-sm shadow-sm ring-offset-background data-[placeholder]:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50 [&>span]:truncate text-start w-[180px]">
                <option value="day">Aujourd'hui</option>
                <option value="week">Cette semaine</option>
                <option value="month">Ce mois</option>
                <option value="custom">Plage personnalisée</option>
            </select>


            <!-- <Input v-if="filters.range === 'custom'" type="date" v-model="filters.from" @change="fetchData"
                class="inline-flex items-center gap-2 whitespace-nowrap rounded-md text-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 w-[280px] justify-start text-left font-normal" />
            <Input v-if="filters.range === 'custom'" type="date" v-model="filters.to" @change="fetchData"
                class="inline-flex items-center gap-2 whitespace-nowrap rounded-md text-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 w-[280px] justify-start text-left font-normal" /> -->

            <DatePicker v-if="filters.range === 'custom'" type="date" v-model="filters.from" @change="fetchData"
                @update="upadteFrom" />
            <DatePicker v-if="filters.range === 'custom'" type="date" v-model="filters.to" @change="fetchData"
                @update="upadteTo" />
        </div>

        <!-- Montant total -->
        <div class="bg-white shadow rounded-xl p-4 text-center">
            <h2 class="text-lg font-semibold mb-2">Montant total</h2>
            <p class="text-3xl font-bold text-green-600">{{ data.total_montant }} f cfa</p>
        </div>

        <div v-if="!loading">

            <!-- <div :style="{ width: '44%' }" class="border-2 border-gray-200 rounded-lg m-8 p-6 flex-shrink-0 bg-white">
                <ReusableHorizontalBarChart :dataset="siteData" title="Répartition par Site"
                    subtitle="(en valeur monétaire: F cfa)" suffix=" f cfa" :showLegend="true" :showTooltip="true" />
            </div> -->

            <!-- Répartition par site -->
            <!-- <div class="bg-white shadow rounded-xl p-4">
                <h2 class="text-lg font-semibold mb-4">Répartition par site</h2>
                <ul>
                    <li v-for="site in sitesWithData" :key="site.id">
                        {{ site.nom_site }} — {{ getMontantSite(site.id) }} XOF ({{ getDossiersSite(site.id) }}
                        dossiers)
                    </li>
                </ul>
            </div> -->
            <!-- Répartition par type de véhicule -->
            <!-- <div class="bg-white shadow rounded-xl p-4">
                <h2 class="text-lg font-semibold mb-4">Répartition par type de véhicule</h2>
                <ul>
                    <li v-for="type in typesWithData" :key="type.id">
                        {{ type.nom }} — {{ getMontantType(type.id) }} XOF ({{ getDossiersType(type.id) }}
                        dossiers)
                    </li>
                </ul>
            </div> -->

            <!-- Répartition par service -->
            <!-- <div class="bg-white shadow rounded-xl p-4">
                <h2 class="text-lg font-semibold mb-4">Répartition par service</h2>
                <ul>
                    <li v-for="service in servicesWithData" :key="service.id">
                        {{ service.nom_service }} — {{ getMontantService(service.id) }} XOF ({{
                            getDossiersService(service.id) }} dossiers)
                    </li>
                </ul>
            </div> -->
            <Card class="xl:col-span-2">
                <CardHeader class="flex flex-row items-center">
                    <div class="grid gap-2">
                        <!-- <CardTitle>
                                    Graphique 1
                                </CardTitle> -->
                    </div>
                </CardHeader>

                <CardContent>


                    <div class="flex flex-col  sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                        <div :style="{ width: '44%' }"
                            class="border-2 border-gray-200 rounded-lg m-8 p-6 flex-shrink-0">
                            <ReusableDonutChart :dataset="dataset" suffix=" f cfa"
                                title="Répartition par Site (francs CFA)" />
                        </div>
                        <div :style="{ width: '44%' }"
                            class="border-2 border-gray-200 rounded-lg m-8 p-6 flex-shrink-0">
                            <ReusableDonutChart :dataset="dataset1" suffix=" f cfa"
                                title="Répartition par Service (francs CFA)" />
                        </div>
                    </div>
                    <div class="m-8">
                        <div :style="{ width: '100%' }" class="border-2 border-gray-200 rounded-lg p-10 flex-shrink-0">
                            <ReusableDonutChart :dataset="dataset2" suffix=" f cfa"
                                title="Répartition par Type de véhicule (francs CFA)" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- <div v-else>
            Chargement des données...
        </div> -->
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import ReusableDonutChart from "@/components/ReusableDonutChart.vue";
import ReusableHorizontalBarChart from "@/components/ReusableHorizontalBarChart.vue";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import DatePicker from "@/components/DatePicker.vue"
import { usePoll } from '@inertiajs/vue3'




const isPollingEnabled = ref(false)

const data = ref({
    total_montant: '0',
    par_site: [],
    par_service: [],
    par_type_vehicule: []
})

const allSites = ref([])
const allServices = ref([])
const allTypesVehicule = ref([])

const loading = ref(true)

const filters = ref({
    range: 'day',
    site: 'all',
    from: '',
    to: '',
})

const dataset = ref([]);
const dataset1 = ref([]);
const dataset2 = ref([]);
const siteData = ref([]);

function upadteFrom(selectedDate) {
    filters.value.from = selectedDate
    console.log("Date sélectionnée :", selectedDate)
    console.log('filters.from   :', filters)
    fetchData()
}

function upadteTo(selectedDate) {
    filters.value.to = selectedDate
    console.log("Date sélectionnée :", selectedDate)
    fetchData()
}



//pour repartition par site
function transformerDonnees(donnees) {
    const couleurs = getRandomColor();

    return donnees.map((item, index) => ({
        name: `Site ${item.id_site}`,
        value: parseFloat(item.total_montant.replace(',', '.')),
        color: couleurs[index % couleurs.length]
    }));
}


const fetchData = async () => {
    loading.value = true
    try {
        const params = new URLSearchParams(filters.value).toString()
        const res = await fetch(`/boss/statistics/global?${params}`)
        const result = await res.json()

        data.value = {
            total_montant: result.total_montant || '0',
            par_site: result.par_site || [],

            par_service: result.par_service || [],
            par_type_vehicule: result.par_type_vehicule || []
        }
        siteData.value = transformerDonnees(data.value.par_site);

        allSites.value = result.allSites || []
        allServices.value = result.allServices || []
        allTypesVehicule.value = result.allTypesVehicule || []


        //
        // Transformation
        dataset.value = transformToDataset(result.par_site, result.allSites, 'id_site', 'nom_site');
        dataset1.value = transformToDataset(result.par_service, result.allServices, 'id_service', 'nom_service');
        dataset2.value = transformToDataset(result.par_type_vehicule, result.allTypesVehicule, 'id_vehicule', 'nom');



    } catch (e) {
        console.error('Erreur de chargement des données', e)
    } finally {
        loading.value = false
    }
}






// Fonctions pour retourner 0 si la catégorie n’existe pas
const getMontantSite = (id) => {
    const item = data.value.par_site.find(s => s.id_site === id)
    return item ? parseFloat(item.total_montant) : 0
}
const getDossiersSite = (id) => {
    const item = data.value.par_site.find(s => s.id_site === id)
    return item ? item.total_dossiers : 0
}

const getMontantService = (id) => {
    const item = data.value.par_service.find(s => s.id_service === id)
    return item ? parseFloat(item.total_montant) : 0
}
const getDossiersService = (id) => {
    const item = data.value.par_service.find(s => s.id_service === id)
    return item ? item.total_dossiers : 0
}

const getMontantType = (id) => {
    const item = data.value.par_type_vehicule.find(t => t.id_vehicule === id)
    return item ? parseFloat(item.total_montant) : 0
}
const getDossiersType = (id) => {
    const item = data.value.par_type_vehicule.find(t => t.id_vehicule === id)
    return item ? item.total_dossiers : 0
}

// Computed pour filtrer uniquement les éléments avec montant > 0
const sitesWithData = computed(() =>
    allSites.value.filter(site => getMontantSite(site.id) > 0)
)
const servicesWithData = computed(() =>
    allServices.value.filter(service => getMontantService(service.id) > 0)
)
const typesWithData = computed(() =>
    allTypesVehicule.value.filter(type => getMontantType(type.id) > 0)
)

onMounted(fetchData)


function transformToDataset(data, allItems, idKey, nomKey) {
    return data.map(item => {
        // Trouver le nom correspondant dans allItems
        const found = allItems.find(allItem => allItem.id === item[idKey]);
        const name = found ? found[nomKey] : `ID ${item[idKey]}`;

        return {
            name: name,
            values: [
                parseFloat(item.total_montant)
            ],
            color: getRandomColor() // Fonction pour générer une couleur aléatoire
        };
    });
}

// Fonction pour générer une couleur aléatoire
function getRandomColor() {
    const letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

usePoll(3600, {
    onStart() {
        if (isPollingEnabled.value) {
            console.log(isPollingEnabled.value)
            fetchData()
        }

    },
    // onFinish() {
    //     console.log('Polling request finished')
    // }
})
</script>





<style scoped>
body {
    background-color: #f9fafb;
}
</style>

<style scoped>
.text-muted-foreground {
    color: #9aa6b2;
}

.bg-muted\/10 {
    background-color: rgba(255, 255, 255, 0.03);
}
</style>


<script>

import Main from '/resources/js/Pages/Main.vue';
export default {
    layout: Main,
};

</script>