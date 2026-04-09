<template>
    <div>
        <div class="flex flex-col space-y-4 mx-8  sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <!-- Titre -->
            <h4 class="text-3xl font-bold tracking-tight">
                Dashboard
            </h4>
        </div>
        <div class="    rounded-lg dark:border-gray-700">
            <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
                <div class="grid gap-4 md:grid-cols-2 md:gap-8 lg:grid-cols-3">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Sites</CardTitle>
                            <DollarSign class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats?.sites ?? '...' }}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Eléments de facturation</CardTitle>
                            <Users class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats?.facturation ?? '...' }}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Entités</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats?.entites ?? '...' }}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Utilisateurs</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats?.users ?? '...' }}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Clients</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats?.clients ?? '...' }}</div>
                        </CardContent>
                    </Card>

                </div>
            </main>
        </div>
        <div>
        </div>
    </div>

</template>

<script setup>

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import axios from 'axios'
import { ref, watch, onMounted } from 'vue'
import { DollarSign, Users, Activity } from 'lucide-vue-next'

defineProps({
    currentDateTime: String,
})

// 📊 Références réactives
const stats = ref(null)

// 📥 Récupération des statistiques (avec ou sans date)
async function fetchStats() {
    let response
    try {
        response = await axios.get('/get/admin/stats')
        stats.value = response.data // données pour le graphique aussi
        console.log('Stats:', stats.value)
    } catch (err) {
        console.error('Erreur lors de la récupération des statistiques', err)
    }
}

// 🔄 Appels automatiques
onMounted(fetchStats)
watch(fetchStats, { deep: true })
</script>

<script>

import Main from '/resources/js/Pages/Main.vue';
export default {
    layout: Main,
};

</script>