<script setup>
import { ref, watch } from "vue";
import { VueUiHorizontalBar } from "vue-data-ui";
import "vue-data-ui/style.css";

// --- Props ---
const props = defineProps({
    dataset: { type: Array, required: true },
    title: { type: String, default: "Titre du graphique" },
    subtitle: { type: String, default: "" },
    width: { type: String, default: "600px" },
    height: { type: String, default: "400px" },
    suffix: { type: String, default: "" },
    prefix: { type: String, default: "" },
    showLegend: { type: Boolean, default: true },
    showTooltip: { type: Boolean, default: true },
    configOverrides: { type: Object, default: () => ({}) },
});

// --- Config de base ---
const defaultConfig = ref({
    debug: false,
    loading: false,
    autoSize: true,
    responsive: false,
    theme: "",
    customPalette: [],
    useCssAnimation: true,
    events: {},
    style: {
        fontFamily: "inherit",
        chart: {
            backgroundColor: "#FFFFFF",
            color: "#1A1A1A",
            layout: {
                bars: {
                    sort: "desc",
                    useStroke: false,
                    strokeWidth: 2,
                    height: 32,
                    gap: 6,
                    borderRadius: 4,
                    offsetX: 64,
                    useGradient: true,
                    gradientIntensity: 20,
                    fillOpacity: 90,
                    underlayerColor: "#FFFFFF",
                    dataLabels: {
                        color: "#1A1A1A",
                        bold: true,
                        fontSize: 12,
                        value: {
                            show: true,
                            roundingValue: 0,
                            prefix: "",
                            suffix: "",
                            formatter: null,
                        },
                        percentage: {
                            show: false,
                            roundingPercentage: 0,
                        },
                        offsetX: 0,
                    },
                    nameLabels: {
                        show: true,
                        color: "#1A1A1A",
                        bold: false,
                        fontSize: 10,
                        offsetX: 0,
                    },
                },
                separators: {
                    show: true,
                    color: "#e1e5e8",
                    strokeWidth: 1,
                    fullWidth: true,
                },
            },
            title: {
                text: "",
                color: "#1A1A1A",
                fontSize: 18,
                bold: true,
                textAlign: "center",
                subtitle: {
                    text: "",
                    color: "#A1A1A1",
                    fontSize: 14,
                    bold: false,
                },
            },
            legend: {
                show: true,
                bold: true,
                backgroundColor: "#FFFFFF",
                color: "#1A1A1A",
                fontSize: 14,
                position: "top",
                prefix: "",
                suffix: "",
            },
            tooltip: {
                show: true,
                color: "#1A1A1A",
                backgroundColor: "#FFFFFF",
                fontSize: 14,
                borderRadius: 4,
                borderColor: "#e1e5e8",
                borderWidth: 1,
                backgroundOpacity: 30,
                prefix: "",
                suffix: "",
            },
        },
    },
});

// --- Fusion Config + Props ---
const config = ref({ ...defaultConfig.value, ...props.configOverrides });

// --- Application des props dynamiques ---
config.value.style.chart.title.text = props.title;
config.value.style.chart.title.subtitle.text = props.subtitle;
config.value.style.chart.layout.bars.dataLabels.value.prefix = props.prefix;
config.value.style.chart.layout.bars.dataLabels.value.suffix = props.suffix;
config.value.style.chart.legend.show = props.showLegend;
config.value.style.chart.tooltip.show = props.showTooltip;

// --- Watchers (mise à jour dynamique) ---
watch(
    () => props.configOverrides,
    (newVal) => {
        config.value = { ...defaultConfig.value, ...newVal };
    },
    { deep: true }
);

watch(
    [() => props.suffix, () => props.prefix, () => props.title, () => props.subtitle],
    () => {
        config.value.style.chart.title.text = props.title;
        config.value.style.chart.title.subtitle.text = props.subtitle;
        config.value.style.chart.layout.bars.dataLabels.value.prefix = props.prefix;
        config.value.style.chart.layout.bars.dataLabels.value.suffix = props.suffix;
    }
);
</script>

<template>
    <div :style="{ width: width, height: height }">
        <VueUiHorizontalBar :config="config" :dataset="dataset" />
    </div>
</template>
