<script setup>
import { ref, toRefs } from "vue";
import { VueUiStackbar } from "vue-data-ui";
import "vue-data-ui/style.css";

// ✅ Props pour passer la data et config custom si besoin
const props = defineProps({
    dataset: {
        type: Array,
        required: true
    },
    title: {
        type: String,
        default: "Title"
    },
    subtitle: {
        type: String,
        default: "Subtitle"
    },
    height: {
        type: Number,
        default: 500
    },
    width: {
        type: Number,
        default: 800
    }
});

// ✅ Config de base, on applique les props pour le rendre dynamique
const config = ref({
    loading: false,
    debug: false,
    theme: '',
    responsive: false,
    events: {
        datapointEnter: null,
        datapointLeave: null,
        datapointClick: null
    },
    useCssAnimation: true,
    orientation: 'vertical',
    userOptions: {
        show: true,
        buttons: {
            tooltip: true,
            pdf: true,
            csv: true,
            img: true,
            table: true,
            labels: true,
            fullscreen: true
        }
    },
    style: {
        fontFamily: 'inherit',
        chart: {
            backgroundColor: '#FFFFFFff',
            color: '#2D353Cff',
            height: props.height,
            width: props.width,
            title: {
                text: props.title,
                color: '#1A1A1Aff',
                fontSize: 20,
                bold: true,
                textAlign: 'center',
                subtitle: {
                    text: props.subtitle,
                    color: '#A1A1A1ff',
                    fontSize: 16,
                    bold: false
                }
            },
            legend: {
                show: true,
                position: 'bottom'
            },
            bars: {
                gapRatio: 0.5,
                dataLabels: {
                    show: true,
                    adaptColorToBackground: true
                }
            }
        }
    }
});
</script>

<template>
    <div :style="{ width: width + 'px' }">
        <VueUiStackbar :config="config" :dataset="dataset" />
    </div>
</template>
