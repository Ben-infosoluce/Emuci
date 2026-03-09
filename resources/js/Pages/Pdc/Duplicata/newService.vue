<template>
    <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <!-- Titre -->
        <h4 class="text-2xl font-bold tracking-tight">
            Duplica
        </h4>

        <Link :href="route('show.pdc.duplicata')">
            <BoutonRetour @click="resetTypeService" size="sm">
                Retour
            </BoutonRetour>
        </Link>
    </div>

    <!-- Sélection type de service -->
    <div class="rounded-lg dark:border-gray-700">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <Card class="h-full flex flex-col">
                <div class="p-6 bg-white rounded-xl mt-8 m-5">
                    <h2 class="text-lg font-semibold mb-6">
                        Sélectionner les types de services
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div v-for="(option, index) in filteredTypeServices" :key="index"
                            class="group rounded-xl border transition-all cursor-pointer hover:shadow-xl shadow p-10"
                            :class="selected.includes(option)
                                ? 'border-gray-300 bg-muted'
                                : 'border-muted hover:border-gray-100'" @click="toggleSelection(option)">
                            <div class="flex items-start space-x-3 p-4">
                                <Checkbox :checked="selected.includes(option)" />
                                <Label class="text-sm font-medium text-gray-900">
                                    {{ option.element_facturation }}
                                </Label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-5">
                        <!-- <Link :href="route('show.new.pdc.duplicata.add.data', {
                            vin: props.data.vin,
                            selected: selected.value,
                            postImtData1: JSON.stringify(props.postImtData)
                        })">
                            <Button @click="goNext">
                                CONTINUER
                            </Button>
                        </Link> -->
                        <Button @click="goNext">
                            CONTINUER
                        </Button>
                    </div>
                </div>
            </Card>
        </main>
    </div>

    <Toaster richColors position="top-right" />
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import BoutonRetour from "/resources/js/components/BoutonRetour.vue"
import { Toaster, toast } from 'vue-sonner'
import { router } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'





const props = defineProps({
    typeServices: Array,
    data: Object,          // ✔ corrigé (ce n’est pas un Number)
    postImtData: Object

})
const selected = ref([])

const filteredTypeServices = computed(() => {

    const genre = (props.data.genre || "").toLowerCase()
    const nbPlaque = props.data.nb_plaque

    // cas remorque
    if (genre.includes("remorque")) {
        return props.typeServices.filter(service =>
            service.element_facturation.toLowerCase().includes("remorque")
        )
    }

    // cas 1 plaque
    if (nbPlaque === 1) {
        return props.typeServices.filter(service =>
            service.element_facturation.includes("une (01) plaque") &&
            !service.element_facturation.toLowerCase().includes("remorque")
        )
    }

    // cas 2 plaques
    return props.typeServices.filter(service =>
        service.element_facturation.includes("deux (02) plaques")
    )

})

/**
 * Sélection unique (1 seul service à la fois)
 */
const toggleSelection = (option) => {
    if (selected.value.includes(option)) {
        selected.value = []
    } else {
        selected.value = [option]
    }
}

/**
 * Navigation propre avec Inertia
 */
const goNext = () => {
    if (!selected.value.length) {
        toast.error('Sélectionner au moins un type de service')
        return
    }

    router.get(
        route('show.new.pdc.duplicata.add.data'),
        {
            vin: props.data?.vin,
            selected: selected.value,
            postImtData: props.postImtData
        },
        {
            preserveState: false
        }
    )
}

/**
 * Reset
 */
const resetTypeService = () => {
    selected.value = []
}

onMounted(() => {
    selected.value = []
    console.log("postImtData:", props.postImtData)
    console.log("data:", props.data)
    console.log("typeServices:", props.typeServices)
    console.log("genre:", props.data.genre)
})
</script>

<script>
import Main from '/resources/js/Pages/Main.vue'

export default {
    layout: Main,
}
</script>