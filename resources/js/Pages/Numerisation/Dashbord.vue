<template>
    <div>
        <div class="flex flex-col space-y-4 mx-8  sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <!-- Titre -->
            <h4 class="text-3xl font-bold tracking-tight">
                Dashboard
            </h4>

            <!-- Sélecteur de plage de dates -->
            <div class="flex items-center space-x-2">
                <DateRangePicker />
            </div>
        </div>
        <div class="    rounded-lg dark:border-gray-700">
            <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
                <div class="grid gap-4 md:grid-cols-2 md:gap-8 lg:grid-cols-4">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">
                                Total Enrolement
                            </CardTitle>
                            <DollarSign class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                55
                            </div>
                            <!-- <p class="text-xs text-muted-foreground">
                                        +20.1% from last month
                                    </p> -->
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">
                                Enrolement Terminer
                            </CardTitle>
                            <Users class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                20
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">
                                En attente de paiement
                            </CardTitle>
                            <!-- <CreditCard class="h-4 w-4 text-muted-foreground" /> -->
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                35
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">
                                Enrolement Annuler
                            </CardTitle>
                            <Activity class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                0
                            </div>
                        </CardContent>
                    </Card>
                </div>
                <div class="grid gap-4 md:gap-8 lg:grid-cols-2 xl:grid-cols-3">
                    <Card class="xl:col-span-2">
                        <CardHeader class="flex flex-row items-center">
                            <div class="grid gap-2">
                                <CardTitle>Graphique 1</CardTitle>
                                <!-- <CardDescription>
                                            Recent transactions from your store.
                                        </CardDescription> -->
                            </div>
                            <!-- <Button as-child size="sm" class="ml-auto gap-1">
                                        <a href="#">
                                            View All
                                            <ArrowUpRight class="h-4 w-4" />
                                        </a>
                                    </Button> -->
                        </CardHeader>
                        <CardContent>
                            <div class="h-[360px] bg-gray-200">
                                Contenu de la div
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>Graphique 2</CardTitle>
                        </CardHeader>
                        <CardContent class="grid gap-8">
                            <div class="h-[360px] bg-gray-200">
                                Contenu de la div
                            </div>

                        </CardContent>
                    </Card>
                </div>
            </main>

        </div>
    </div>



</template>

<script setup>
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import DateRangePicker from '@/components/ui/DateRangePicker.vue'
import UserNav from '@/components/ui/UserNav.vue'
import { usePoll } from '@inertiajs/vue3'


defineProps({
    currentDateTime: String,
})
// usePoll(2000, {
//     onStart() {
//         console.log(currentDateTime)
//     },
//     onFinish() {
//         console.log('Polling request finished')
//     }
// })

</script>

<script>

import Main from '/resources/js/Pages/Main.vue';
export default {
    layout: Main,
};




</script>