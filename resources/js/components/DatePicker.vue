<script setup>
import { ref, watch, defineEmits } from "vue"
import { getLocalTimeZone } from "@internationalized/date"
import { CalendarIcon } from "lucide-vue-next"
import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button"
import { Calendar } from "@/components/ui/calendar"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"

const emit = defineEmits(["update"])
const value = ref(null)

// Quand la valeur change, on émet la date au format YYYY-MM-DD
watch(value, (newVal) => {
    if (newVal) {
        const date = newVal.toDate(getLocalTimeZone())
        const formatted = date.toISOString().split("T")[0] // "YYYY-MM-DD"
        emit("update", formatted)
    }
})
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button variant="outline" :class="cn(
                'w-[280px] justify-start text-left font-normal',
                !value && 'text-muted-foreground',
            )">
                <CalendarIcon class="mr-2 h-4 w-4" />
                {{ value ? value.toDate(getLocalTimeZone()).toISOString().split("T")[0] : "Pick a date" }}
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-0">
            <Calendar v-model="value" initial-focus />
        </PopoverContent>
    </Popover>
</template>
