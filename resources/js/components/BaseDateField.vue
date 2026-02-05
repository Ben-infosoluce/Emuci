<script setup>
import { DateField, DateFieldInput } from "@/components/ui/date-field";
import { Label } from "@/components/ui/label";
import { useId, computed, ref, watch } from "vue";

// ⭐ Props
const props = defineProps({
    modelValue: {
        type: [Date, String, null],
        default: null,
    },
    placeholder: {
        type: String,
        default: "DD/MM/YYYY",
    },
    id: {
        type: String,
        default: null,
    },
});

// ⭐ Emits
const emit = defineEmits(["update:modelValue"]);

// ID auto si non fourni
const fieldId = props.id || useId();

// Convertir la valeur en objet Date pour le composant DateField
const internalValue = ref(props.modelValue ? new Date(props.modelValue) : null);

// Synchroniser avec le parent
watch(
    () => props.modelValue,
    (newVal) => {
        if (newVal instanceof Date) {
            internalValue.value = newVal;
        } else if (typeof newVal === "string") {
            // Parse la chaîne en objet Date (ex: "01/01/1984" -> Date)
            const [day, month, year] = newVal.split("/");
            internalValue.value = new Date(`${year}-${month}-${day}`);
        } else {
            internalValue.value = null;
        }
    }
);

// Formater la valeur pour le parent (objet Date -> chaîne "DD/MM/YYYY")
const formatDateForParent = (date) => {
    if (!date) return null;
    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
};

// Mettre à jour le parent quand la date change
const handleUpdate = (newDate) => {
    internalValue.value = newDate;
    emit("update:modelValue", formatDateForParent(newDate));
};
</script>

<template>
    <div class="*:not-first:mt-2">
        <!-- Date Field -->
        <DateField :id="fieldId" :modelValue="internalValue" @update:modelValue="handleUpdate" v-slot="{ segments }">
            <template v-for="segment in segments" :key="segment.part">
                <DateFieldInput :part="segment.part"
                    :placeholder="segment.part === 'day' ? 'DD' : segment.part === 'month' ? 'MM' : 'YYYY'">
                    {{ segment.value }}
                </DateFieldInput>
            </template>
        </DateField>
    </div>
</template>
