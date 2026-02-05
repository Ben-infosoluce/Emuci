<template>
    <div>
        <!-- Bouton pour ouvrir le modal -->
        <!-- <button @click="showModal = true" class="px-4 py-2 bg-red-500 text-white rounded">
            Rejeter le dossier
        </button> -->



    </div>
</template>




<script setup>
import { ref, onMounted } from "vue";
import { Checkbox } from "@/components/ui/checkbox";
import { Label } from "@/components/ui/label";
import { Button } from '@/components/ui/button'
import { CreditCard } from "lucide-vue-next";
import { ScrollArea } from '@/components/ui/scroll-area'
import axios from "axios";
import { watch } from "vue";

const props = defineProps({
    iddossier: {
        type: String,
        required: true,
    },
});

watch(() => props.iddossier, (newId) => {
    console.log("Nouvel ID reçu:", newId);
});

</script>
