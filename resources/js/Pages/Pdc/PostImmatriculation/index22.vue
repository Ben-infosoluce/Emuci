<script setup>
import { ref, onMounted, watch } from 'vue'
import { Input_search } from '@/components/ui/Input_search'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'

const form = ref({
    search_data: '',
    statut: '',
    date_start: '',
    date_end: ''
})

const immatriculations = ref([])
const currentPage = ref(1)
const perPage = ref(10)
const total = ref(0)
const loading = ref(false)

const fetchData = async () => {
    loading.value = true

    const params = new URLSearchParams({
        page: currentPage.value,
        search_data: form.value.search_data,
        statut: form.value.statut,
        date_start: form.value.date_start,
        date_end: form.value.date_end
    })

    const response = await fetch(`/pdc/immatriculation/data?${params.toString()}`)
    const data = await response.json()

    immatriculations.value = data.dossiers.data
    total.value = data.dossiers.total
    perPage.value = data.dossiers.per_page
    currentPage.value = data.dossiers.current_page

    loading.value = false
}

const handlePageChange = (page) => {
    currentPage.value = page
    fetchData()
}

watch(() => form.value.search_data, fetchData)
watch(() => form.value.statut, fetchData)
watch(() => form.value.date_start, fetchData)
watch(() => form.value.date_end, fetchData)

onMounted(() => {
    fetchData()
})
</script>

<template>
    <div class="mx-8 space-y-4 sm:flex sm:items-center sm:justify-between sm:space-y-0">
        <h4 class="text-2xl font-bold tracking-tight">Immatriculations</h4>
        <div class="flex items-center space-x-2">
            <a href="/show/new/pdc/immatriculation">
                <BoutonNouveau />
            </a>
        </div>
    </div>

    <main class="p-4 md:p-8">
        <Card>
            <div class="space-y-4 p-8">
                <!-- Filtres -->
                <div class="flex flex-wrap gap-4 items-center justify-between">
                    <!-- <Input_search v-model="form.search_data" placeholder="Rechercher un véhicule" class="w-full" /> -->
                    <Input_search v-model="form.search_data" placeholder="Rechercher un véhicule" class="w-full "
                        @input="search" />

                    <FormField v-slot="{ componentField }" name="filterstatut">
                        <FormItem>
                            <FormControl>
                                <Select v-model="form.statut" @update:modelValue="search">
                                    <SelectTrigger class="w-40">
                                        <SelectValue placeholder="statut" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Statut</SelectLabel>
                                            <SelectItem value="0">Tous </SelectItem>
                                            <SelectItem value="1">En attente</SelectItem>
                                            <SelectItem value="2">Validé</SelectItem>
                                            <SelectItem value="3">Refusé</SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>
                    <!-- <DateRangePicker /> -->
                    <DateRangePicker v-model="form.dateRange"
                        @update:start="val => { form.date_start = val; fetchData(); }"
                        @update:end="val => { form.date_end = val; fetchData(); }" />

                    <!-- Si tu veux ajouter des dates, utilise un vrai DateRangePicker ou deux champs date -->
                </div>

                <!-- Tableau -->
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Vin</th>
                            <th>Véhicule</th>
                            <th>Type de service</th>
                            <th>Statut</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(data, index) in immatriculations" :key="index">
                            <td>{{ index + 1 + (currentPage - 1) * perPage }}</td>
                            <td>{{ data?.r_dossier_vehicule?.vin || '—' }}</td>
                            <td>{{ data?.r_dossier_vehicule?.marque || '—' }}</td>
                            <td>{{ data?.r_dossier_services?.r_service_types?.[0]?.nom_type_service || '—' }}</td>
                            <td>
                                <Badge :variant="getBadgeVariant(data?.statut)">
                                    {{ getStatutText(data?.statut) }}
                                </Badge>
                            </td>
                            <td class="text-right">
                                <!-- Actions -->
                                <Button variant="ghost" size="icon">•••</Button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="flex justify-end mt-4 space-x-2">
                    <button v-for="page in Math.ceil(total / perPage)" :key="page" @click="handlePageChange(page)"
                        :class="['px-3 py-1 border rounded', { 'bg-blue-500 text-white': currentPage === page }]">
                        {{ page }}
                    </button>
                </div>
            </div>
        </Card>
    </main>
</template>



<script>
import Main from '/resources/js/Pages/Main.vue';
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Card } from '@/components/ui/card'
import DateRangePicker from '@/components/ui/DateRangePicker.vue'
import Pagination from '@/components/Pagination.vue';
import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableHead,
    TableCell,
} from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { MoreVertical } from 'lucide-vue-next'
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import {
    FormControl,
    FormField,
    FormItem,
    FormMessage,
} from '@/components/ui/form'
import BoutonNouveau from "/resources/js/components/BoutonNouveau.vue";

export default {
    layout: Main,
    components: { Pagination },
};




function getStatutText(statut) {
    switch (statut) {
        case 1:
            return 'En attente'
        case 2:
            return 'Validé'
        case 3:
            return 'Refusé'
        default:
            return 'Inconnu'
    }
}

function getBadgeVariant(statut) {
    switch (statut) {
        case 1:
            return 'warning'
        case 2:
            return 'success'
        case 3:
            return 'destructive'
        default:
            return 'secondary'
    }
}
</script>
