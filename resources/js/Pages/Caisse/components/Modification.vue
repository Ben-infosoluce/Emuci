<template>
    <div v-if="isOpen">
        <!-- Header sticky -->
        <div
            class="sticky top-[-20px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8 py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <h4 class="text-2xl font-bold tracking-tight">
                Demande de modification
            </h4>
            <div class="flex items-center space-x-2">
                <Button @click="returnBack()">
                    <MoveLeft class="w-4 h-4 mr-2" /> Retour
                </Button>
            </div>
        </div>

        <!-- Contenu scrollable -->
        <div class="rounded-lg dark:border-gray-700">
            <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
                <Card class="h-full flex flex-col">
                    <ScrollArea class="h-full w-full rounded-md border">
                        <div class="m-8">
                            <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
                                Cochez les informations erronées à faire corriger. La demande sera transmise à
                                l'administration pour validation avant que le PDC ne puisse modifier le dossier.
                            </div>

                            <div class="mb-6">
                                <p class="text-sm text-gray-600">
                                    <strong>VIN :</strong> {{ dossier?.r_dossier_vehicule?.vin || '—' }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    <strong>Numéro chrono :</strong> {{ dossier?.num_chrono || '—' }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    <strong>Propriétaire :</strong>
                                    {{ dossier?.r_dossier_client?.civilite }}
                                    {{ dossier?.r_dossier_client?.nom }}
                                    {{ dossier?.r_dossier_client?.prenom }}
                                </p>
                            </div>

                            <div v-for="section in visibleSections" :key="section.key" class="mb-8">
                                <div class="mb-4 flex items-center justify-between border-b pb-2">
                                    <h3 class="text-lg font-semibold">{{ section.label }}</h3>
                                    <Label class="flex cursor-pointer items-center gap-2 text-sm font-medium">
                                        <Checkbox :checked="isSectionChecked(section)"
                                            @update:checked="() => toggleSection(section)" />
                                        Tout sélectionner
                                    </Label>
                                </div>

                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                                    <Label v-for="field in section.fields" :key="field.key"
                                        class="flex cursor-pointer items-center gap-2 rounded-md border p-3 hover:bg-muted/40"
                                        :class="{ 'border-orange-400 bg-orange-50': isSelected(section.key, field.key) }">
                                        <Checkbox :checked="isSelected(section.key, field.key)"
                                            @update:checked="() => toggleField(section.key, field.key)" />
                                        <span class="flex flex-col">
                                            <span class="text-sm font-medium">{{ field.label }}</span>
                                            <span class="text-xs text-muted-foreground">
                                                {{ displayValue(section.key, field.key) || '—' }}
                                            </span>
                                        </span>
                                    </Label>
                                </div>
                            </div>

                            <div class="mt-6 space-y-2">
                                <Label for="message">Message / précision (facultatif)</Label>
                                <Textarea id="message" v-model="message"
                                    placeholder="Expliquez brièvement la correction souhaitée..." />
                            </div>

                            <div class="mt-8 flex flex-col items-end gap-2">
                                <p class="text-sm text-muted-foreground">
                                    {{ totalSelected }} champ(s) sélectionné(s)
                                </p>
                                <Button :disabled="submitting" @click="submitDemande">
                                    <Send class="w-4 h-4 mr-2" />
                                    {{ submitting ? 'Envoi en cours...' : 'Envoyer la demande' }}
                                </Button>
                            </div>
                        </div>
                    </ScrollArea>
                </Card>
            </main>
        </div>
    </div>
    <div v-else>
        <Control />
    </div>
    <Toaster richColors position="top-right" />
</template>

<script setup>
import { computed, ref } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import { storeToRefs } from "pinia";
import { MoveLeft, Send } from "lucide-vue-next";
import { Toaster, toast } from "vue-sonner";

import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Checkbox } from "@/components/ui/checkbox";
import { Label } from "@/components/ui/label";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Textarea } from "@/components/ui/textarea";
import { returnBack } from "/resources/js/composable/fonction.js";
import Control from "./Control.vue";
import { useCaisseStore } from "@/stores/mainStore";

const store = useCaisseStore();
const { isOpen } = storeToRefs(store);

const props = defineProps({
    vin: String,
    dossier: Object,
    entreprise: Object,
    sections: {
        type: Array,
        default: () => [],
    },
});

const selected = ref({
    vehicule: [],
    client: [],
    entreprise: [],
});
const message = ref('');
const submitting = ref(false);

const vehicule = computed(() => props.dossier?.r_dossier_vehicule ?? {});
const client = computed(() => props.dossier?.r_dossier_client ?? {});

const visibleSections = computed(() => {
    const physiqueMorale = vehicule.value?.physique_morale;
    const aUneEntreprise = !!props.entreprise
        || (physiqueMorale !== undefined
            && physiqueMorale !== null
            && ![1, '1', 'Physique'].includes(physiqueMorale));

    return (props.sections ?? []).filter((section) => {
        if (section.key === 'entreprise') {
            return aUneEntreprise;
        }
        return true;
    });
});

function modelFor(sectionKey) {
    if (sectionKey === 'vehicule') return vehicule.value;
    if (sectionKey === 'client') return client.value;
    if (sectionKey === 'entreprise') return props.entreprise ?? {};
    return {};
}

function displayValue(sectionKey, fieldKey) {
    const value = modelFor(sectionKey)?.[fieldKey];
    return value === null || value === undefined ? '' : value;
}

function isSelected(sectionKey, fieldKey) {
    return (selected.value[sectionKey] ?? []).includes(fieldKey);
}

function toggleField(sectionKey, fieldKey) {
    const list = selected.value[sectionKey] ?? [];
    const index = list.indexOf(fieldKey);
    if (index === -1) {
        list.push(fieldKey);
    } else {
        list.splice(index, 1);
    }
    selected.value[sectionKey] = list;
}

function isSectionChecked(section) {
    const list = selected.value[section.key] ?? [];
    return section.fields.length > 0 && list.length === section.fields.length;
}

function toggleSection(section) {
    const allSelected = isSectionChecked(section);
    selected.value[section.key] = allSelected ? [] : section.fields.map((field) => field.key);
}

const totalSelected = computed(() => Object.values(selected.value).reduce((total, list) => total + list.length, 0));

async function submitDemande() {
    if (totalSelected.value === 0) {
        toast.error("Veuillez sélectionner au moins un champ à modifier.");
        return;
    }

    submitting.value = true;
    try {
        await axios.post(route('caisse.demande.modification.store'), {
            dossier_id: props.dossier.id,
            champs_vehicule: selected.value.vehicule,
            champs_client: selected.value.client,
            champs_entreprise: selected.value.entreprise,
            message: message.value,
        });

        toast.success('Demande de modification envoyée avec succès.');
        selected.value = { vehicule: [], client: [], entreprise: [] };
        message.value = '';

        setTimeout(() => {
            router.visit(`/caisse/get/data/${props.dossier.num_chrono}`);
        }, 1200);
    } catch (error) {
        const messageErreur = error?.response?.data?.message
            || "Une erreur est survenue lors de l'enregistrement de la demande.";
        toast.error(messageErreur);
        console.error(error);
    } finally {
        submitting.value = false;
    }
}
</script>

<script>
import Main from "/resources/js/Pages/Main.vue";

export default {
    layout: Main,
};
</script>
