<template>
    <div class="flex flex-col space-y-4 mx-8  sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <!-- Titre -->
        <h4 class="text-3xl font-bold tracking-tight">
            Gestion utilisateur
        </h4>

        <!-- Sélecteur de plage de dates -->
        <!-- <div class="flex items-center space-x-2">
            <DateRangePicker />
        </div> -->
    </div>
    <div class="    rounded-lg dark:border-gray-700">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <Card class="h-full flex flex-col">

            </Card>
        </main>

    </div>
</template>

<script setup>
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import DateRangePicker from '@/components/ui/DateRangePicker.vue'
import UserNav from '@/components/ui/UserNav.vue'
</script>

<script>

import Main from '/resources/js/Pages/Main.vue';
export default {
    layout: Main,
};</script>