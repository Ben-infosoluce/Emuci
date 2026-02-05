<template>
    <!-- Header fixe en haut de la page -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white shadow-md p-8 flex items-center justify-center">
        <!-- Logo à gauche -->
        <div class="absolute left-4">
            <img src="/public/assets/images/logo_frame.svg" alt="Logo" class="h-10 w-auto" />
        </div>



        <!-- Boutons à droite -->
        <div class="absolute center flex space-x-2 cursor-pointer">
            <Tabs default-value="" class="w-full">
                <TabsList class="grid w-full grid-cols-3">
                    <Link :href="route('show.boss.new.dashboard.stats')">
                        <TabsTrigger value="">Services / Statuts</TabsTrigger>
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

    <!-- Contenu principal avec marge pour éviter le chevauchement avec le header -->
    <div class="mt-20 p-4"> <!-- mt-20 pour éviter le header fixe -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- 4 graphiques -->
            <Card v-for="(i, index) in titre" :key="index">
                <CardHeader>
                    <CardTitle> {{ i }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div :ref="(el) => setChartRef(el, index)" style="width: 100%; height: 300px;"></div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, onUnmounted } from "vue"
import * as echarts from "echarts"
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { usePage, router } from "@inertiajs/vue3";
import {
    Tabs,
    TabsList,
    TabsTrigger,
    TabsContent,
} from "@/components/ui/tabs"
import { Link } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';
import axios from "axios"
import { usePoll } from '@inertiajs/vue3'

const stats = ref(null)
const loading = ref(true)

const chartRefs = ref([])
const charts = ref([])

const statusKeys = [
    "Valider",
    "En attente",
    "Refuser",
    "En cours"
]

const serviceKeys = [
    "Immatriculation-Special",
    "Re-immatriculation",
    "Post-immatriculation",
    "Duplicata"
]

const colors = ["#91CC75", "#FAC858", "#EE6666", "#5470C6"] // pour les statuts et services
const titre = [
    "Statuts des dossiers du jour",
    "Statistiques des services du jour",
    "Statuts des dossiers de la semaine",
    "Statistiques des services de la semaine",
    "Statuts des dossiers du mois",
    "Statistiques des services du mois",
    "Statuts des dossiers de l'année",
    "Statistiques des services de l'année"
]
const setChartRef = (el, index) => {
    if (el) chartRefs.value[index] = el
}

/* =========================
   FETCH STATS
========================= */
const fetchStats = async () => {
    try {
        const response = await axios.get("/boss/dashboard/global/stats")
        stats.value = response.data
        console.log("Stats fetched:", stats.value)
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
}

/* =========================
   FORMAT DATA
========================= */
const formatPieData = (row, keys) => {
    const allZero = keys.every(k => !row?.[k])
    return keys.map((k, i) => ({
        name: k,
        value: row?.[k] || (allZero ? 1 : 0), // si tout est zero, mettre 1 pour afficher une part
        itemStyle: {
            color: row?.[k] ? colors[i] : "#d3d3d3", // gris si 0
            opacity: 1
        },
        label: { show: row?.[k] > 0 || allZero } // montrer étiquette si >0 ou tout zero
    }))
}

/* =========================
   BUILD DATASETS
========================= */

const aggregateYearData = (months) => {
    if (!Array.isArray(months) || months.length === 0) return {}

    return months.reduce((acc, month) => {
        Object.keys(month).forEach((key) => {
            if (key === 'month') return

            acc[key] = (acc[key] || 0) + Number(month[key] || 0)
        })

        return acc
    }, {})
}

const datasets = () => {
    const p = stats.value
    const yearData = aggregateYearData(p.par_annee)

    return [
        { title: "Statuts (Jour)", data: formatPieData(p.par_jour[0], statusKeys) },
        { title: "Services (Jour)", data: formatPieData(p.par_jour[0], serviceKeys) },

        { title: "Statuts (Semaine)", data: formatPieData(p.par_semaine[0], statusKeys) },
        { title: "Services (Semaine)", data: formatPieData(p.par_semaine[0], serviceKeys) },

        { title: "Statuts (Mois)", data: formatPieData(p.par_mois[0], statusKeys) },
        { title: "Services (Mois)", data: formatPieData(p.par_mois[0], serviceKeys) },

        { title: "Statuts (Année)", data: formatPieData(yearData, statusKeys) },
        { title: "Services (Année)", data: formatPieData(yearData, serviceKeys) }
    ]
}


/* =========================
   INIT CHART
========================= */
const initChart = (el, dataset) => {
    const chart = echarts.init(el)
    charts.value.push(chart)

    chart.setOption({
        // animation: false,
        tooltip: {
            trigger: 'item',
            formatter: (params) => {
                // params.value = valeur, params.percent = pourcentage, params.name = nom
                return `${params.name} : ${params.value} (${params.percent}%)`
            }
        },
        legend: {
            orient: "vertical",
            left: "left",
            textStyle: { fontSize: 12 },
            formatter: (name) => name // légende montre tous les noms
        },
        series: [
            {
                type: "pie",
                radius: "65%",
                center: ["50%", "50%"],
                data: dataset.data,
                label: {
                    show: true,
                    formatter: (params) => {
                        if (params.value == null) return ''
                        return `${params.name} (${params.value})`
                    }
                }
                ,
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            }
        ]
    })
}


/* =========================
   INIT ALL CHARTS
========================= */
const initCharts = () => {
    charts.value.forEach(c => c.dispose())
    charts.value = []

    datasets().forEach((dataset, index) => {
        const el = chartRefs.value[index]
        if (el) initChart(el, dataset)
    })
}

/* =========================
   LIFECYCLE
========================= */
onMounted(async () => {
    await fetchStats()
    initCharts()
})

onUnmounted(() => {
    charts.value.forEach(c => c.dispose())
})


const handleLogout = async () => {
    try {
        await axios.post("/logout");
        router.visit("/");
    } catch (error) {
        console.error("Erreur lors de la déconnexion:", error);
        // Forcer la redirection même en cas d'erreur
        router.visit("/");
    }
};


usePoll(3600 * 10, {
    onStart() {
        fetchStats().then(() => {
            initCharts()
        })
    },
})
</script>
