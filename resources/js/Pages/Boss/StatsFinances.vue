<template>
    <!-- Header fixe en haut de la page -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white shadow-md p-8 flex items-center justify-center">
        <!-- Logo à gauche -->
        <div class="absolute left-4">
            <img src="/public/assets/images/logo_frame.svg" alt="Logo" class="h-10 w-auto" />
        </div>

        <!-- Boutons à droite -->
        <div class="absolute center flex space-x-2 cursor-pointer">
            <Tabs default-value="1" class="w-full">
                <TabsList class="grid w-full grid-cols-3">
                    <Link :href="route('show.boss.new.dashboard.stats')">
                        <TabsTrigger value="">Services / Activités</TabsTrigger>
                    </Link>
                    <Link :href="route('show.boss.statistics.finances')">
                        <TabsTrigger value="1"> Stat Financiere</TabsTrigger>
                    </Link>
                    <Link :href="route('show.boss.statistics.comparative')">
                        <TabsTrigger value="2">Stat Technique</TabsTrigger>
                    </Link>
                </TabsList>
            </Tabs>
        </div>

        <div class="absolute right-4">
            <UserAccountNav />
        
        </div>
    </header>

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
                <h2 class="text-xl font-semibold mb-2">Montants par Site</h2>
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
import UserAccountNav from "@/components/Caisse/UserAccountNav.vue";
// Références DOM
const siteChart = ref(null);
const serviceChart = ref(null);
const vehiculeChart = ref(null);

// Palette de couleurs
const colors = ["#A2B296", "#B17A50", "#A47764", "#F7E8D3", "#8F3D37"];

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
        const response = await axios.get(`/get/boss/paiement/global/stats?periode=${periode.value}`);

        // Mapper les données
        const mappedServices = response.data.services
            .filter(service => service.nom_service !== "Immatriculation spéciale")
            .map(service => ({
                name: service.nom_service,
                montant: parseFloat(service.montant)
            }));

        // Ajouter les deux versions de l'immatriculation spéciale
        if (response.data.montantServiceNonFDS !== undefined) {
            mappedServices.push({
                name: "Immatriculation Spéciale",
                montant: parseFloat(response.data.montantServiceNonFDS) || 0
            });
        }
        if (response.data.montantServiceFDS !== undefined) {
            mappedServices.push({
                name: "Opération FDS",
                montant: parseFloat(response.data.montantServiceFDS) || 0
            });
        }

        stats.value = {
            sites: response.data.sites.map(site => ({
                name: site.nom_site,
                montant: parseFloat(site.montant)
            })),
            services: mappedServices,
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
    initChart(siteChart.value, "Montants par Site", stats.value.sites);
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
                rotate: isWide ? 45 : 0,
                formatter: function (value) {
                    if (!isWide && value.length > 15) {
                        return value.substring(0, 15) + '..';
                    }
                    return value;
                }
            }
        },
        yAxis: { type: "value" },
        series: [
            {
                type: "bar",
                data: data.map((d) => d.montant),
                label: {
                    show: true,
                    position: 'top',
                    formatter: (params) => {
                        return new Intl.NumberFormat('fr-FR').format(params.value);
                    },
                    fontSize: 14,
                    fontWeight: 'bold',
                    color: '#374151'
                },
                itemStyle: {
                    color: function (params) {
                        return colors[params.dataIndex % colors.length];
                    },
                    borderRadius: [4, 4, 0, 0]
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

const handleLogout = async () => {
    try {
        await axios.post("/logout");
        router.visit("/");
    } catch (error) {
        console.error("Erreur lors de la déconnexion:", error);
        router.visit("/");
    }
};


usePoll(3600 * 10, {
    onStart() {
        fetchStats().then(() => {
            initCharts()
        })
    }
})

</script>