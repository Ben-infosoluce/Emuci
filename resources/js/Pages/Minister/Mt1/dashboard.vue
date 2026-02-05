<template>
    <div>
        <div class="flex flex-col space-y-4 mx-8  sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <!-- Titre -->
            <h4 class="text-3xl font-bold tracking-tight">
                Dashboard s
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
                            <CardTitle class="text-sm font-medium">En attente de paiement</CardTitle>
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
                        <CardContent>{{ stats['Immatriculation'] ?? 0 }}</CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Re-immatriculations</CardTitle>
                        </CardHeader>
                        <CardContent>{{ stats['Re-immatriculation'] ?? 0 }}</CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Post-immatriculations</CardTitle>
                        </CardHeader>
                        <CardContent>{{ stats['Post-immatriculation'] ?? 0 }}</CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Duplicatas</CardTitle>
                        </CardHeader>
                        <CardContent>{{ stats['Duplicata'] ?? 0 }}</CardContent>
                    </Card>
                </div>


                <div>
                    <!-- class="grid gap-4 md:gap-8 lg:grid-cols-2 xl:grid-cols-3" -->
                    <Card class="xl:col-span-2">
                        <CardHeader class="flex flex-row items-center">
                            <div class="grid gap-2">
                                <CardTitle>
                                    Graphique 1
                                </CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <AreaChart index="name" :data="data" :categories="[
                                'Total',
                                'Terminer',
                                'En attente',
                                'Immatriculation',
                                'Re-immatriculation',
                                'Post-immatriculation',
                                'Duplicata'
                            ]" :colors="[
                                '#4f46e5',
                                '#10b981',
                                '#f59e0b',
                                '#3b82f6',
                                '#ec4899',
                                '#8b5cf6',
                                '#f43f5e'
                            ]" />
                        </CardContent>

                    </Card>
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

// 🛠 Libs & utils
import axios from 'axios'
import { ref, watch, onMounted } from 'vue'
import { DollarSign, Users, Activity } from 'lucide-vue-next'

// ⏰ Props
defineProps({
    currentDateTime: String,
})

// 📊 Références réactives
const stats = ref(null)
const data = ref([]) // pour le graphique
const dateRange = ref({ from: null, to: null })

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

            response = await axios.get('/mt1/dashboard/stats/by/date', {
                params: {
                    start_date: startStr,
                    end_date: endStr,
                },
            })
        } else {
            response = await axios.get('/mt1/dashboard/stats')
        }

        stats.value = aggregate(response.data)
        data.value = response.data // données pour le graphique aussi
    } catch (err) {
        console.error('Erreur lors de la récupération des statistiques', err)
    }
}

// 🔄 Appels automatiques
onMounted(fetchStats)
watch(dateRange, fetchStats, { deep: true })
</script>

<script>

import Main from '/resources/js/Pages/Main.vue';
export default {
    layout: Main,
};




</script>