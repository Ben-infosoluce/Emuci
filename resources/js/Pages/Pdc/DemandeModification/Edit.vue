<template>
    <div>
        <div
            class="sticky top-[-20px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8 py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <div>
                <h4 class="text-2xl font-bold tracking-tight">Modifier le dossier</h4>
                <p class="text-sm text-muted-foreground">
                    Demande validée le {{ dateValidation }} — seuls les champs signalés sont modifiables.
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <Button variant="outline" @click="retour">
                    <MoveLeft class="w-4 h-4 mr-2" /> Retour
                </Button>
            </div>
        </div>

        <div class="rounded-lg dark:border-gray-700">
            <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
                <Card class="h-full flex flex-col">
                    <ScrollArea class="h-full w-full rounded-md border max-h-[calc(100vh-220px)]">
                        <div class="m-8">
                            <div class="mb-6 rounded-lg border border-orange-300 bg-orange-50 p-4 text-sm text-orange-800">
                                <p>
                                    <strong>Dossier :</strong>
                                    {{ dossier?.num_chrono || '—' }}
                                    — VIN {{ vehicule?.vin || '—' }}
                                </p>
                                <p v-if="demande?.message" class="mt-1">
                                    <strong>Message de la caisse :</strong> {{ demande.message }}
                                </p>
                            </div>

                            <div v-for="section in selectedSections" :key="section.key" class="mb-8">
                                <h3 class="mb-4 border-b pb-2 text-lg font-semibold">{{ section.label }}</h3>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div v-for="field in section.fields" :key="field.key" class="space-y-2">
                                        <Label :for="field.key" class="text-orange-700">
                                            {{ field.label }}
                                        </Label>
                                        <Input :id="field.key" v-model="formValues[field.key]" :type="field.type || 'text'"
                                            class="border-orange-400 bg-orange-50/40 focus-visible:ring-orange-400" />
                                        <p class="text-xs text-muted-foreground">
                                            Valeur actuelle : {{ currentValue(section.key, field.key) || '—' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div v-if="selectedSections.length === 0"
                                class="rounded-md border border-dashed p-8 text-center text-muted-foreground">
                                Aucun champ n'a été sélectionné dans cette demande.
                            </div>

                            <div class="mt-8 flex justify-end">
                                <Button :disabled="submitting || selectedSections.length === 0" @click="enregistrer">
                                    <Save class="w-4 h-4 mr-2" />
                                    {{ submitting ? 'Enregistrement...' : 'Enregistrer les modifications' }}
                                </Button>
                            </div>
                        </div>
                    </ScrollArea>
                </Card>
            </main>
        </div>

        <Toaster richColors position="top-right" />
    </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { Toaster, toast } from 'vue-sonner';
import { MoveLeft, Save } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';

const props = defineProps({
    demande: Object,
    sections: {
        type: Array,
        default: () => [],
    },
    entreprise: Object,
    listeRoute: {
        type: String,
        default: 'show.pdc.immatriculation',
    },
});

const dossier = computed(() => props.demande?.dossier ?? {});
const vehicule = computed(() => dossier.value?.r_dossier_vehicule ?? {});
const client = computed(() => dossier.value?.r_dossier_client ?? {});

const champsSelectionnes = computed(() => ({
    vehicule: props.demande?.champs_vehicule ?? [],
    client: props.demande?.champs_client ?? [],
    entreprise: props.demande?.champs_entreprise ?? [],
}));

function modelFor(sectionKey) {
    if (sectionKey === 'vehicule') return vehicule.value;
    if (sectionKey === 'client') return client.value;
    if (sectionKey === 'entreprise') return props.entreprise ?? {};
    return {};
}

function currentValue(sectionKey, fieldKey) {
    const value = modelFor(sectionKey)?.[fieldKey];
    return value === null || value === undefined ? '' : value;
}

const selectedSections = computed(() => {
    return (props.sections ?? [])
        .map((section) => {
            const keys = champsSelectionnes.value[section.key] ?? [];
            return {
                ...section,
                fields: section.fields.filter((field) => keys.includes(field.key)),
            };
        })
        .filter((section) => section.fields.length > 0);
});

const formValues = reactive({});

for (const section of props.sections ?? []) {
    const keys = champsSelectionnes.value[section.key] ?? [];
    for (const field of section.fields) {
        if (keys.includes(field.key)) {
            formValues[field.key] = currentValue(section.key, field.key);
        }
    }
}

const submitting = ref(false);

const dateValidation = computed(() => {
    const date = props.demande?.traite_le;
    if (!date) return '—';
    try {
        return new Date(date).toLocaleString('fr-FR');
    } catch (error) {
        return date;
    }
});

function retour() {
    router.visit(route(props.listeRoute));
}

async function enregistrer() {
    submitting.value = true;

    try {
        const response = await axios.put(
            route('update.pdc.demande.modification', dossier.value.id),
            { values: { ...formValues } }
        );

        toast.success(response?.data?.message || 'Modifications enregistrées avec succès.');
        setTimeout(() => {
            router.visit(route(props.listeRoute));
        }, 1200);
    } catch (error) {
        const data = error?.response?.data;
        const firstError = data?.errors ? Object.values(data.errors)[0]?.[0] : null;
        toast.error(firstError || data?.message || "Une erreur est survenue lors de l'enregistrement.");
        console.error(error);
    } finally {
        submitting.value = false;
    }
}
</script>

<script>
import Main from '/resources/js/Pages/Main.vue';

export default {
    layout: Main,
};
</script>
