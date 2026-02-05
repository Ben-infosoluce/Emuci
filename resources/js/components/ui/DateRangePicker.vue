<template>
    <div :class="cn('grid gap-2', $attrs.class ?? '')">
        <!-- Popover pour le calendrier -->
        <Popover v-model:open="isPopoverOpen">
            <PopoverTrigger as-child>
                <Button id="date" variant="outline" :class="cn(
                    'w-[300px] justify-start text-left font-normal',
                    !value.start && 'text-muted-foreground',
                )">
                    <CalendarIcon class="mr-2 h-4 w-4" />

                    <template v-if="value.start">
                        <template v-if="value.end">
                            {{ df.format(value.start.toDate(getLocalTimeZone())) }} - {{
                                df.format(value.end.toDate(getLocalTimeZone())) }}
                        </template>

                        <template v-else>
                            {{ df.format(value.start.toDate(getLocalTimeZone())) }}
                        </template>
                    </template>
                    <template v-else>
                        Sélectionner une plage de date
                    </template>
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-auto p-0" align="end">
                <!-- RangeCalendar pour sélectionner une plage de dates -->
                <RangeCalendar v-model="value" weekday-format="short" :number-of-months="2" initial-focus
                    @update:modelValue="handleDateSelect" />

                <!-- Bouton pour effacer la sélection -->
                <div class="p-2 border-t">
                    <Button variant="ghost" class="w-full justify-center" @click="clearSelection">
                        Effacer la sélection
                    </Button>
                </div>
            </PopoverContent>
        </Popover>

        <!-- Affichage des messages d'erreur -->
        <!-- <div v-if="errorMessage" class="text-red-500 text-sm mt-2">
            {{ errorMessage }}
        </div> -->
    </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import Button from '@/components/ui/button/Button.vue';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover'
import { RangeCalendar, } from '@/components/ui/range-calendar'
import { getLocalTimeZone, DateFormatter } from '@internationalized/date'
import { Calendar as CalendarIcon } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

// Props pour accepter une valeur externe si nécessaire
const props = defineProps({
    modelValue: Object, // pour liaison v-model
});

// Déclarer les événements émis
const emit = defineEmits(['update:modelValue', 'update:start', 'update:end'])

const df = new DateFormatter('fr-FR', {
    dateStyle: 'medium',
});

const value = ref(props.modelValue ?? { start: null, end: null });
const isPopoverOpen = ref(false);
const errorMessage = ref('');

const handleDateSelect = (newValue) => {
    errorMessage.value = '';

    if (!newValue.start || !newValue.end) {
        errorMessage.value = 'Veuillez sélectionner une plage de dates valide.';
        return;
    }

    if (newValue.start.compare(newValue.end) > 0) {
        errorMessage.value = 'La date de début doit être avant la date de fin.';
        return;
    }

    value.value = newValue;
    isPopoverOpen.value = false;

    // Émettre les dates au parent
    emit('update:modelValue', newValue);
    emit('update:start', newValue.start.toString());
    emit('update:end', newValue.end.toString());
};

// Méthode pour effacer la sélection
const clearSelection = () => {
    value.value = { start: null, end: null };
    emit('update:modelValue', value.value);
    emit('update:start', 0);
    emit('update:end', 0);
    isPopoverOpen.value = false;
};
</script>
