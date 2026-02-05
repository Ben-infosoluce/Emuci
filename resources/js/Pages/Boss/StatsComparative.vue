<template>
    <!-- Header fixe en haut de la page -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white shadow-md p-8 flex items-center justify-center">
        <!-- Logo à gauche -->
        <div class="absolute left-4">
            <img src="/public/assets/images/logo_frame.svg" alt="Logo" class="h-10 w-auto" />
        </div>
        <!-- Boutons à droite -->
        <div class="absolute center flex space-x-2 cursor-pointer">
            <Tabs default-value="2" class="w-full">
                <TabsList class="grid w-full grid-cols-3">
                    <Link :href="route('show.boss.new.dashboard.stats')">
                        <TabsTrigger value="">Services / Activités</TabsTrigger>
                    </Link>
                    <Link :href="route('show.boss.statistics.finances')">
                        <TabsTrigger value="1">Stat Financiere</TabsTrigger>
                    </Link>
                    <Link :href="route('show.boss.statistics.comparative')">
                        <TabsTrigger value="2">Stat Technique</TabsTrigger>
                    </Link>
                </TabsList>
            </Tabs>
        </div>
        <div class="absolute right-4 flex space-x-2 ">
            <a @click="handleLogout"
                class="flex flex-row items-center gap-2 text-gray-800 hover:text-amber-600 font-medium cursor-pointer">
                <LogOut />
                Déconnexion
            </a>
        </div>
    </header>

    <!-- Contenu principal avec 2 graphiques par ligne -->
    <div class="p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" style="margin-top: 60px;">
            <!-- JOUR -->
            <div class="bg-white shadow rounded p-4">
                <div ref="dayChart" style="height: 350px;"></div>
            </div>

            <!-- SEMAINE -->
            <div class="bg-white shadow rounded p-4">
                <div ref="weekChart" style="height: 350px;"></div>
            </div>

            <!-- MOIS -->
            <div class="bg-white shadow rounded p-4">
                <div ref="monthChart" style="height: 350px;"></div>
            </div>

            <!-- ANNÉE -->
            <div class="bg-white shadow rounded p-4">
                <div ref="yearChart" style="height: 350px;"></div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import * as echarts from "echarts";
import { Tabs, TabsList, TabsTrigger, TabsContent } from "@/components/ui/tabs";
import { Link } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';
import { usePage, router } from "@inertiajs/vue3";

const handleLogout = async () => {
    try {
        await axios.post("/logout");
        router.visit("/");
    } catch (error) {
        console.error("Erreur lors de la déconnexion:", error);
        router.visit("/");
    }
};

const dayChart = ref(null);
const weekChart = ref(null);
const monthChart = ref(null);
const yearChart = ref(null);

const fetchStats = async () => {
    try {
        const response = await axios.get("/get/comparative/stats");
        return response.data;
    } catch (error) {
        console.error("Erreur lors de la récupération des statistiques:", error);
        return null;
    }
};

const buildChart = (refEl, labels, current, previous, title) => {
    const chart = echarts.init(refEl);
    chart.setOption({
        title: { text: title, left: 'center' },
        tooltip: { trigger: "axis" },
        legend: { data: ["Actuel", "Précédent"] },
        xAxis: { type: "category", data: labels },
        yAxis: { type: "value" },
        series: [
            { name: "Actuel", type: "line", smooth: true, data: current },
            { name: "Précédent", type: "line", smooth: true, data: previous },
        ],
    });
    window.addEventListener("resize", () => chart.resize());
};

onMounted(async () => {
    const statsData = await fetchStats();
    if (statsData) {
        // Graphique journalier
        buildChart(
            dayChart.value,
            statsData.day.labels,
            statsData.day.current,
            statsData.day.previous,
            "Comparatif Journée (Aujourd'hui vs Hier)"
        );

        // Graphique hebdomadaire
        buildChart(
            weekChart.value,
            statsData.week.labels,
            statsData.week.current,
            statsData.week.previous,
            "Comparatif Hebdomadaire"
        );

        // Graphique mensuel
        buildChart(
            monthChart.value,
            statsData.month.labels,
            statsData.month.current,
            statsData.month.previous,
            "Comparatif Mensuel"
        );

        // Graphique annuel
        buildChart(
            yearChart.value,
            statsData.year.labels,
            statsData.year.current,
            statsData.year.previous,
            "Comparatif Annuel"
        );
    }
});
</script>
