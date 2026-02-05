<template>
    <div>
        <div class="flex flex-col space-y-4 mx-8  sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <!-- Titre -->
            <h4 class="text-3xl font-bold tracking-tight">
                Dashboard
            </h4>

            <!-- Sélecteur de plage de dates -->
            <div class="flex items-center space-x-2">
                <DateRangePicker v-model="dateRange" />
            </div>
        </div>
        <div class="    rounded-lg dark:border-gray-700">
            <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
                <div class="grid gap-4 md:grid-cols-2 md:gap-8 lg:grid-cols-3">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Total Enrolement</CardTitle>
                            <DollarSign class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats?.Total ?? '...' }}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Enrolement Terminer</CardTitle>
                            <Users class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats?.['Terminer'] ?? '...' }}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">En attente de traitement</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats?.['En attente'] ?? '...' }}</div>
                        </CardContent>
                    </Card>
                </div>
                <div v-if="stats" class="grid gap-4 md:grid-cols-2 md:gap-8 lg:grid-cols-4">

                    <Card>
                        <CardHeader>
                            <CardTitle>Immatriculations</CardTitle>
                        </CardHeader>
                        <CardContent>{{ stats['Immatriculation-Special'] ?? 0 }} (En attente de pose de plaques)
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Re-immatriculations</CardTitle>
                        </CardHeader>
                        <CardContent>{{ stats['Re-immatriculation'] ?? 0 }} (En attente de pose de plaques)
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Post-immatriculations</CardTitle>
                        </CardHeader>
                        <CardContent>{{ stats['Post-immatriculation'] ?? 0 }} (En attente de pose de plaques)
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Duplicatas</CardTitle>
                        </CardHeader>
                        <CardContent>{{ stats['Duplicata'] ?? 0 }} (En attente de pose de plaques)</CardContent>
                    </Card>
                </div>


                <div>
                    <!-- class="grid gap-4 md:gap-8 lg:grid-cols-2 xl:grid-cols-3" -->
                    <Card class="xl:col-span-2">
                        <CardHeader class="flex flex-row items-center">
                            <div class="grid gap-2">
                                <!-- <CardTitle>
                                    Graphique 1
                                </CardTitle> -->
                            </div>
                        </CardHeader>
                        <CardContent>
                            <h1 class="text-2xl font-bold mx-8 mb-8 text-center">Satatistiques du jour</h1>
                            <div
                                class="flex flex-col space-y-4 mx-8  sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                                <div :style="{ width: '600px' }"
                                    class="border-2 border-gray-200 rounded-lg p-4 flex-shrink-0">
                                    <!-- <VueUiDonut :config="config" :dataset="dataset" /> -->
                                    <ReusableDonutChart :dataset="dataset1" title="Services" />
                                </div>
                                <div :style="{ width: '600px' }"
                                    class="border-2 border-gray-200 rounded-lg p-4 flex-shrink-0">
                                    <ReusableDonutChart :dataset="dataset2" title="Status Services" />
                                    <!-- <ReusableDonutChart :dataset="dataset" title="Statistiques mensuelles" /> -->
                                </div>
                            </div>
                            <h1 class="text-2xl font-bold mx-8 my-8 text-center">Satatistiques de la semaine</h1>
                            <div
                                class="flex flex-col space-y-4 mx-8  sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                                <div :style="{ width: '600px' }"
                                    class="border-2 border-gray-200 rounded-lg p-4 flex-shrink-0">
                                    <!-- <VueUiDonut :config="config" :dataset="dataset" /> -->
                                    <ReusableDonutChart :dataset="dataset1Week" title="Services" />
                                </div>
                                <div :style="{ width: '600px' }"
                                    class="border-2 border-gray-200 rounded-lg p-4 flex-shrink-0">
                                    <ReusableDonutChart :dataset="dataset2Week" title="Status Services" />
                                    <!-- <ReusableDonutChart :dataset="dataset" title="Statistiques mensuelles" /> -->
                                </div>
                            </div>
                            <h1 class="text-2xl font-bold mx-8 my-8 text-center">Satatistiques des 30 derniers jours
                            </h1>
                            <div
                                class="flex flex-col space-y-4 mx-8  sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                                <div :style="{ width: '600px' }"
                                    class="border-2 border-gray-200 rounded-lg p-4 flex-shrink-0">
                                    <!-- <VueUiDonut :config="config" :dataset="dataset" /> -->
                                    <ReusableDonutChart :dataset="dataset1Month" title="Services" />
                                </div>
                                <div :style="{ width: '600px' }"
                                    class="border-2 border-gray-200 rounded-lg p-4 flex-shrink-0">
                                    <ReusableDonutChart :dataset="dataset2Month" title="Status Services" />
                                    <!-- <ReusableDonutChart :dataset="dataset" title="Statistiques mensuelles" /> -->
                                </div>
                            </div>
                        </CardContent>

                        <!-- <DonutChart index="name" category="total" :data="data1" type="pie" /> -->
                        <!-- <DonutChart :data="data" /> -->


                    </Card>
                    <!-- <StatsDossiers /> -->
                    <!-- <Card>
                        <CardHeader>
                            <CardTitle>Graphique 2</CardTitle>
                        </CardHeader>
                        <CardContent class="grid gap-8">
                            <div class="h-[360px] bg-gray-200">
                                Contenu de la div
                            </div>

                        </CardContent>
                    </Card> -->
                </div>
            </main>

        </div>

        <div>

            <!-- :custom-tooltip="CustomChartTooltip" -->
        </div>
    </div>

</template>

<script setup>

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { AreaChart } from '@/components/ui/chart-area'
import DateRangePicker from '@/components/ui/DateRangePicker.vue'
// import { DonutChart } from "@/components/ui/chart-donut";
import { VueUiDonut } from "vue-data-ui";
import "vue-data-ui/style.css";
import ReusableDonutChart from "@/components/ReusableDonutChart.vue";
import StackbarChart from "@/components/StackbarChart.vue";
// 🛠 Libs & utils
import axios from 'axios'
import { ref, watch, onMounted } from 'vue'
import { DollarSign, Users, Activity } from 'lucide-vue-next'
import StatsDossiers from './StatsDossiers.vue'

// ⏰ Props
defineProps({
    currentDateTime: String,
})

// 📊 Références réactives
const stats = ref(null)
const data = ref([]) // pour le graphique
const pieData = ref([]) // pour le graphique en donut
const dateRange = ref({ from: null, to: null })
const dataset1 = ref([]);
const dataset2 = ref([]);

const dataset1Week = ref([]);
const dataset2Week = ref([]);
const dataset1Month = ref([]);
const dataset2Month = ref([]);

// Définir les couleurs pour chaque clé
const colors = {
    Total: "#4f46e5",
    Terminer: "#10b981",
    "En attente": "#f59e0b",
    "En cours de traitement": "#3b82f6",
    Duplicata: "#ec4899",
    "Immatriculation-Special": "#8b5cf6",
    "Post-immatriculation": "#14b8a6",
    "Re-immatriculation": "#f43f5e",
    Rejeter: "#ef4444",
};

// const dataset1 = [
//     { name: "Total", values: [120], color: "#4f46e5" },
//     { name: "Terminé", values: [80], color: "#10b981" },
//     { name: "En attente", values: [50], color: "#f59e0b" },
// ];
// 🔁 Agrégation
function aggregate(data) {
    return data.reduce((acc, item) => {
        for (const key in item) {
            if (key !== 'name') {
                acc[key] = (acc[key] || 0) + item[key]
            }
        }
        return acc
    }, {})
}

// 📥 Récupération des statistiques (avec ou sans date)
async function fetchStats() {
    try {
        const { start, end } = dateRange.value

        let response
        if (start && end) {
            const startDate = new Date(start.year, start.month - 1, start.day)
            const endDate = new Date(end.year, end.month - 1, end.day)

            const startStr = startDate.toISOString().slice(0, 10)
            const endStr = endDate.toISOString().slice(0, 10)

            response = await axios.get('/boss/dashboard/stats/by/date', {
                params: {
                    start_date: startStr,
                    end_date: endStr,
                },
            })
        } else {
            response = await axios.get('/boss/dashboard/stats')
        }
        console.log('Réponse des statistiques', response.data);
        pieData.value = response.data
        stats.value = aggregate(response.data)
        console.log('Statistiques agrégées', stats.value);
        data.value = response.data // données pour le graphique aussi
    } catch (err) {
        console.error('Erreur lors de la récupération des statistiques', err)
    }
}

//get stats by day
async function fetchStatsByDay() {
    try {
        const response = await axios.get('/stats/day');
        console.log('Stats by day:', response.data);
    } catch (error) {
        console.error('Error fetching stats by day:', error);
    }
}

//get stats by week
async function fetchStatsByWeek() {
    try {
        const response = await axios.get('/stats/week');
        console.log('Stats by week:', response.data);
    } catch (error) {
        console.error('Error fetching stats by week:', error);
    }
}

//get stats by month
async function fetchStatsByMonth() {
    try {
        const response = await axios.get('/stats/month');
        console.log('Stats by month:', response.data);
    } catch (error) {
        console.error('Error fetching stats by month:', error);
    }
}

async function fetchGlobalStats() {
    try {
        const response = await axios.get("/boss/dashboard/global/stats");
        console.log('Réponse des statistiques globales', response.data);
        //  Définition des couleurs une seule fois
        const colors = {
            "Duplicata": "#ec4899",
            "En attente": "#f59e0b",
            "En cours de traitement": "#3b82f6",
            "Immatriculation-Special": "#8b5cf6",
            "Post-immatriculation": "#14b8a6",
            "Re-immatriculation": "#f43f5e",
            "Rejeter": "#ef4444",
            "Terminer": "#10b981",
        };
        console.log('Statistiques globales', response.data);

        //  Catégories qui vont dans le premier dataset
        const categories1 = ["Duplicata", "Immatriculation-Special", "Post-immatriculation", "Re-immatriculation"];

        //  Fonction utilitaire pour transformer un objet brut en datasets
        function buildDatasets(source) {
            const data = source?.[0] ?? {
                Duplicata: 0,
                "Immatriculation-Special": 0,
                "Post-immatriculation": 0,
                "Re-immatriculation": 0,
                "En attente": 0,
                "En cours de traitement": 0,
                "Rejeter": 0,
                "Terminer": 0,
                Total: 0,
                day: null,
            };

            return {
                dataset1: Object.keys(data)
                    .filter(key => categories1.includes(key) && key !== "Total" && key !== "week_number" && key !== "month")
                    .map(key => ({
                        name: key,
                        values: [data[key]],
                        color: colors[key]
                    })),
                dataset2: Object.keys(data)
                    .filter(key => !categories1.includes(key) && key !== "day" && key !== "Total" && key !== "week_number" && key !== "month")
                    .map(key => ({
                        name: key,
                        values: [data[key]],
                        color: colors[key]
                    }))
            };
        }

        //  Construction des 3 jeux de données
        const dayStats = buildDatasets(response.data.par_jour);
        const weekStats = buildDatasets(response.data.par_semaine);
        const monthStats = buildDatasets(response.data.par_mois);

        //  Affectation aux variables réactives (Vue)
        dataset1.value = dayStats.dataset1;
        dataset2.value = dayStats.dataset2;

        dataset1Week.value = weekStats.dataset1;
        dataset2Week.value = weekStats.dataset2;

        dataset1Month.value = monthStats.dataset1;
        dataset2Month.value = monthStats.dataset2;

        console.log("dataset1", dataset1.value);

    } catch (error) {
        console.error("Erreur lors du chargement des stats :", error);
    }
}









// 🔄 Appels automatiques
onMounted(fetchStats(),
    fetchGlobalStats(),
    // fetchStatsByDay,
    // fetchStatsByWeek,
    // fetchStatsByMonth
)
watch(dateRange, fetchStats, { deep: true })
</script>

<script>

import Main from '/resources/js/Pages/Main.vue';
export default {
    layout: Main,
};




</script>