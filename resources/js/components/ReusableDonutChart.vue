<script setup>
import { ref, watch } from "vue";
import { VueUiDonut } from "vue-data-ui";
import "vue-data-ui/style.css";

const props = defineProps({
    dataset: { type: Array, required: true }, // tes données dynamiques
    title: { type: String, default: "Titre du graphique" },
    // width: { type: String, default: "600px" },
    // height: { type: String, default: "360px" },
    configOverrides: { type: Object, default: () => ({}) }, // pour surcharger la config
    suffix: { type: String, default: "" }, // ✅ nouvelle prop pour le suffix
});

const defaultConfig = ref({
    debug: false,
    type: 'classic',
    loading: false,
    pie: false,
    autoSize: true,
    responsive: false,
    theme: '',
    customPalette: [],
    useCssAnimation: true,
    events: {},
    serieToggleAnimation: { show: true, durationMs: 500 },
    startAnimation: { show: true, durationMs: 1000, staggerMs: 50 },
    useBlurOnHover: true,
    startAnimation: {
        show: false,
        durationMs: 1000,
        staggerMs: 50
    },
    userOptions: {
        show: true,
        position: 'right',
        buttons: {
            tooltip: false,
            pdf: false,
            csv: false,
            img: true,
            table: false,
            labels: false,
            fullscreen: true,
            annotator: true,
        },
    },
    style: {
        chart: {
            width: 500,
            height: 360,
            title: { text: '' },
            layout: {
                labels: {
                    dataLabels: {
                        show: true,
                        useLabelSlots: false,
                        hideUnderValue: 3,
                        prefix: '',
                        suffix: '', // ✅ On initialise vide
                    },
                    hollow: {
                        show: true,
                        total: {
                            show: true
                        },
                        average: {
                            show: false // ✅ Désactive l'affichage de l'Average
                        }
                    }
                }
            }
        }
    }
});


const config = ref({ ...defaultConfig.value, ...props.configOverrides });
config.value.style.chart.width = parseInt(props.width);
config.value.style.chart.height = parseInt(props.height);
config.value.style.chart.title.text = props.title;
config.value.style.chart.layout.labels.dataLabels.suffix = props.suffix;

// Watch pour mettre à jour la config si props changent
watch(() => props.configOverrides, (newVal) => {
    config.value = { ...defaultConfig.value, ...newVal };
}, { deep: true });

// Watch pour mettre à jour la config du suffix
watch(() => props.suffix, (newSuffix) => {
    config.value.style.chart.layout.labels.dataLabels.suffix = newSuffix;
});
</script>

<template>
    <div :style="{ width: width, height: height }">
        <VueUiDonut :config="config" :dataset="dataset" />
    </div>
</template>
