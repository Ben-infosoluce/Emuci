<script setup>
import { computed } from "vue";
import {
    Stepper,
    StepperIndicator,
    StepperItem,
    StepperSeparator,
    StepperTitle,
} from "@/components/ui/stepper";

/* ================= PROPS ================= */
const props = defineProps({
    service: { type: String, required: true },
    requestDate: { type: String, required: true },
    vehicle: { type: String, required: true },
    plate: { type: String, required: false },
    client: { type: String, required: true },

    statusStep: {
        type: Number,
        required: true,
        validator: (v) => v >= 1 && v <= 4,
    },

    statusState: {
        type: String,
        required: true, // pending | success | error
    },

    hasPlateStep: {
        type: Boolean,
        default: false,
    },
});

/* ================= COMPUTED ================= */
const steps = computed(() => {
    const base = [{ step: 1 }, { step: 2 }];

    if (props.hasPlateStep) {
        base.push({ step: 3 }, { step: 4 });
    } else {
        base.push({ step: 3 });
    }

    return base;
});
console.log(props);

const lastStep = computed(() => steps.value.at(-1)?.step);

/* 👉 dossier validé = source de vérité */
const isValidated = computed(() => props.statusState === "success");
const isRejected = computed(() => props.statusState === "error");
</script>

<template>
    <div class="rounded-3xl p-8 max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold text-center mb-6">Suivi de prestation</h1>

        <!-- Infos dossier -->
        <div class="text-center space-y-4">
            <p class="text-xl">
                <span class="text-muted-foreground">Service : </span>
                <span class="font-[700]">{{ service }}</span>
            </p>
            <p class="text-xl">
                <span class="text-muted-foreground">Date de la demande : </span>
                <span class="font-[700]">{{ requestDate }}</span>
            </p>
            <p class="text-xl">
                <span class="text-muted-foreground">Nom du véhicul : </span>
                <span class="font-[700]">{{ vehicle }}</span>
            </p>
            <p class="text-xl">
                <span class="text-muted-foreground">N° d’immatriculation :
                </span>
                <span class="font-[700]">{{ plate }}</span>
            </p>

            <p class="mt-4 text-xl font-bold">
                {{ client }}
            </p>
        </div>

        <!-- ✅ ÉCRAN VALIDATION -->
        <div v-if="isValidated" class="flex flex-col items-center justify-center mt-10 text-center md:flex-row">
            <div class="flex items-center justify-center">
                <img src="/public/assets/images/ok.gif" alt="Validé" class="w-full" />
            </div>

            <p class="mt-4 max-w-sm text-base">
                Le processus de validation de votre demande est terminé, notre
                équipe vous contactera pour la suite.
            </p>
        </div>

        <!-- ❌ ÉCRAN REJET -->
        <div v-else-if="isRejected" class="flex flex-col items-center justify-center mt-10 text-center md:flex-row">
            <div class="flex items-center justify-center">
                <span class="text-[50px] font-bold text-red-600 animate-pulse">❌</span>
            </div>

            <p class="mt-4 max-w-sm text-base">
                Le processus de validation de votre demande a été refusé.
            </p>
        </div>

        <!-- 🔒 STEPPER (READ ONLY) -->
        <div v-else class="mt-10 w-full">
            <!-- Desktop: Horizontal Stepper -->
            <Stepper class="hidden md:flex w-full items-start gap-2 pointer-events-none" :model-value="statusStep">
                <StepperItem v-for="item in steps" :key="item.step" :step="item.step"
                    class="relative flex w-full flex-col items-center">
                    <StepperIndicator
                        class="bg-muted group-data-[state=completed]:bg-green-700 group-data-[state=active]:bg-green-700">
                        <span class="text-white text-sm font-bold">
                            {{ item.step }}
                        </span>
                    </StepperIndicator>

                    <StepperSeparator v-if="item.step !== lastStep"
                        class="absolute left-[calc(50%+18px)] right-[calc(-50%+10px)] top-5 h-0.5 rounded-full bg-muted group-data-[state=completed]:bg-green-700" />

                    <StepperTitle class="mt-2 text-sm font-semibold text-center">
                        Étape {{ item.step }}
                    </StepperTitle>
                    <!-- <StepperDescription
                        class="text-sm font-semibold text-center"
                    >
                        {{ props.statusState }}
                    </StepperDescription> -->
                </StepperItem>
            </Stepper>

            <!-- Mobile: Vertical Stepper -->
            <div class="md:hidden w-full">
                <Stepper class="flex flex-col items-start gap-0 pointer-events-none" :model-value="statusStep">
                    <StepperItem v-for="item in steps" :key="item.step" :step="item.step"
                        class="flex items-start gap-3 w-full pb-4 last:pb-0">
                        <!-- Indicator + Vertical Line -->
                        <div class="flex flex-col items-center shrink-0">
                            <StepperIndicator
                                class="bg-muted group-data-[state=completed]:bg-green-700 group-data-[state=active]:bg-green-700">
                                <span class="text-white text-sm font-bold">
                                    {{ item.step }}
                                </span>
                            </StepperIndicator>

                            <!-- Vertical separator -->
                            <StepperSeparator v-if="item.step !== lastStep"
                                class="w-0.5 h-8 bg-muted group-data-[state=completed]:bg-green-700 mt-2" />
                        </div>

                        <!-- Title -->
                        <!-- <StepperTitle class="text-sm font-semibold pt-2.5">
                            Étape {{ item.step }}
                        </StepperTitle> -->
                    </StepperItem>
                </Stepper>
            </div>
        </div>
    </div>
</template>
