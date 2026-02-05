<template>
    <div class="flex items-center space-x-2">
        <AlertDialog :open="open">
            <AlertDialogTrigger as-child>
                <slot></slot>
            </AlertDialogTrigger>
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle> {{ title }}</AlertDialogTitle>
                    <p
                        v-if="error"
                        class="text-red-600 mt-2 w-full text-center"
                    >
                        ⚠️ {{ error }}
                    </p>
                    <AlertDialogDescription>
                        <Input
                            v-model="nom_site"
                            class="my-8"
                            placeholder="Nom du site"
                            required
                        />
                        <div class="flex gap-4">
                            <Input
                                v-model="type_site"
                                class="my-8"
                                placeholder="Type de site"
                                required
                            />
                            <Input
                                v-model="region"
                                class="my-8"
                                placeholder="Region"
                                required
                            />
                        </div>
                        <div class="flex gap-4">
                            <Input
                                v-model="heures_ouverture"
                                class="my-8"
                                placeholder="Heure d'ouverture"
                                type="time"
                                required
                            />
                            <Input
                                v-model="heures_fermeture"
                                class="my-8"
                                placeholder="Heure de fermeture"
                                type="time"
                                required
                            />
                        </div>
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter class="flex flex-row gap-2">
                    <slot name="action" class="flex flex-row gap-2"></slot>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </div>
</template>

<script setup>
import { ref } from "vue";
import {
    AlertDialog,
    AlertDialogTrigger,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogCancel,
} from "@/components/ui/alert-dialog";
import { Input } from "@/components/ui/input";

const props = defineProps({
    siteId: Number || null,
    open: Boolean,
    title: String,
    error: String,
});

const nom_site = ref("");
const type_site = ref("");
const region = ref("");
const heures_ouverture = ref("");
const heures_fermeture = ref("");

// const openModal = async (id = null) => {
// console.log(id);
if (props.siteId) {
    const res = await axios.get(`/site/edit/${props.siteId}`);
    console.log(res.data, "res.data");
    nom_site.value = res.data.nom_site;
    type_site.value = res.data.type_site;
    region.value = res.data.region;
    heures_ouverture.value = res.data.heures_ouverture;
    heures_fermeture.value = res.data.heures_fermeture;
    open.value = true;
} else {
    // const nom_site = ref(props.site?.nom_site || "");
    // const type_site = ref(props.site?.type_site || "");
    // const region = ref(props.site?.region || "");
    // const heures_ouverture = ref(props.site?.heures_ouverture || "");
    // heures_fermeture = ref(props.site?.heures_fermeture || "");
}
// };
</script>
