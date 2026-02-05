<template>
    <div class="p-6 space-y-8">
        <!-- Sélecteur de période -->
        <div class="bg-white  rounded p-4">
            <h2 class="text-xl font-semibold my-8">Filtrer par période</h2>
            <Tabs default-value="today" v-model="periode">
                <TabsList>
                    <TabsTrigger value="today">Aujourd'hui</TabsTrigger>
                    <TabsTrigger value="week">Cette semaine</TabsTrigger>
                    <TabsTrigger value="month">Ce mois</TabsTrigger>
                    <TabsTrigger value="year">Cette année</TabsTrigger>
                </TabsList>
            </Tabs>
        </div>

        <!-- Grille pour les deux premiers graphiques -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Par Site -->
            <div class="bg-white shadow rounded p-4">
                <h2 class="text-xl font-semibold mb-2">Montants du Site</h2>
                <div ref="siteChart" style="height: 350px;"></div>
            </div>

            <!-- Par Service -->
            <div class="bg-white shadow rounded p-4">
                <h2 class="text-xl font-semibold mb-2">Montants par Service</h2>
                <div ref="serviceChart" style="height: 350px;"></div>
            </div>
        </div>

        <!-- Par Type de Véhicule (pleine largeur) -->
        <div class="bg-white shadow rounded p-4">
            <h2 class="text-xl font-semibold mb-2">Montants par Type de Véhicule</h2>
            <div ref="vehiculeChart" style="height: 400px;"></div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import * as echarts from "echarts";
import { usePage, router } from "@inertiajs/vue3";
import { Tabs, TabsList, TabsTrigger, TabsContent } from "@/components/ui/tabs";
import { Link } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';
import { usePoll } from '@inertiajs/vue3'
// Références DOM
const siteChart = ref(null);
const serviceChart = ref(null);
const vehiculeChart = ref(null);

// État pour la période sélectionnée
const periode = ref("today");

// État pour les statistiques
const stats = ref({
    sites: [],
    services: [],
    vehicules: [],
});

// État de chargement
const loading = ref(true);

// Fonction pour récupérer les statistiques
const fetchStats = async () => {
    try {
        loading.value = true;
        const response = await axios.get(`/get/controller/paiement/global/stats?periode=${periode.value}`);
        // Mapper les données
        stats.value = {
            sites: response.data.sites.map(site => ({
                name: site.nom_site,
                montant: parseFloat(site.montant)
            })),
            services: response.data.services.map(service => ({
                name: service.nom_service,
                montant: parseFloat(service.montant)
            })),
            vehicules: response.data.vehicules.map(vehicule => ({
                name: vehicule.genre_vehicule || "Inconnu",
                montant: parseFloat(vehicule.montant) || 0
            }))
        };

        console.log("Stats fetched:", stats.value);
    } catch (e) {
        console.error("Erreur lors de la récupération des statistiques:", e);
    } finally {
        loading.value = false;
    }
};

// Fonction pour initialiser les graphiques
const initCharts = () => {
    // Détruire les graphiques existants s'ils existent
    if (siteChart.value && echarts.getInstanceByDom(siteChart.value)) {
        echarts.getInstanceByDom(siteChart.value).dispose();
    }
    if (serviceChart.value && echarts.getInstanceByDom(serviceChart.value)) {
        echarts.getInstanceByDom(serviceChart.value).dispose();
    }
    if (vehiculeChart.value && echarts.getInstanceByDom(vehiculeChart.value)) {
        echarts.getInstanceByDom(vehiculeChart.value).dispose();
    }

    // Initialiser les graphiques
    initChart(siteChart.value, "Montants du Site", stats.value.sites);
    initChart(serviceChart.value, "Montants par Service", stats.value.services);
    initChart(
        vehiculeChart.value,
        "Montants par Type de Véhicule",
        stats.value.vehicules,
        true
    );
};

// Fonction réutilisable pour créer un graphe
function initChart(el, title, data, isWide = false) {
    const chart = echarts.init(el);

    chart.setOption({
        title: { text: title, left: 'center' },
        tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'shadow' }
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '10%',
            containLabel: true
        },
        xAxis: {
            type: "category",
            data: data.map((d) => d.name),
            axisLabel: {
                interval: 0,
                rotate: isWide ? 45 : 0
            }
        },
        yAxis: { type: "value" },
        series: [
            {
                type: "bar",
                data: data.map((d) => d.montant),
                itemStyle: {
                    color: '#4f46e5'
                }
            },
        ],
    });

    window.addEventListener("resize", () => chart.resize());
}

// Charger les statistiques au montage
onMounted(async () => {
    await fetchStats();
    initCharts();
});

// Recharger les statistiques lorsque la période change
watch(periode, async () => {
    await fetchStats();
    initCharts();
});

usePoll(3600 * 10, {
    onStart() {
        fetchStats().then(() => {
            initCharts()
        })
    }
});
</script>


<script>

import Main from '/resources/js/Pages/Main.vue';
export default {
    layout: Main,
};

</script>