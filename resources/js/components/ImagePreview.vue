<template>
    <div>
        <!-- Image principale qui ouvre l'aperçu -->
        <div class="image-container cursor-pointer" :style="{ width: width + 'px', height: height + 'px' }"
            @click="openPreview">
            <img :src="src" :style="{ objectFit: fit }" :alt="alt" class="w-full h-full" />
        </div>

        <!-- Modal d'aperçu d'image -->
        <teleport to="body">
            <transition name="fade">
                <div v-if="showPreview"
                    class="preview-container fixed inset-0 z-50 bg-black bg-opacity-90 flex items-center justify-center"
                    @mousedown="handleMouseDown" @mousemove="handleMouseMove" @mouseup="handleMouseUp"
                    @mouseleave="handleMouseUp" @wheel="handleWheel">
                    <div class="image-wrapper absolute transform-gpu" :style="{
                        transform: `translate(${position.x}px, ${position.y}px) scale(${scale}) rotate(${rotation}deg)`,
                        transition: isDragging ? 'none' : 'transform 0.3s ease'
                    }">
                        <img :src="previewSrcList[currentIndex]" :alt="alt" class="max-w-none"
                            :style="{ cursor: isDragging ? 'grabbing' : 'grab' }" />
                    </div>

                    <!-- Contrôles -->
                    <div
                        class="absolute bottom-5 left-1/2 transform -translate-x-1/2 flex items-center gap-4 bg-black bg-opacity-50 rounded-full px-4 py-2">
                        <button @click="zoomOut" class="text-white p-2 hover:bg-gray-700 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-zoom-out">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                <line x1="8" y1="11" x2="14" y2="11"></line>
                            </svg>
                        </button>
                        <button @click="zoomIn" class="text-white p-2 hover:bg-gray-700 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-zoom-in">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                <line x1="11" y1="8" x2="11" y2="14"></line>
                                <line x1="8" y1="11" x2="14" y2="11"></line>
                            </svg>
                        </button>
                        <button @click="rotate" class="text-white p-2 hover:bg-gray-700 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-rotate-cw">
                                <path d="M21 2v6h-6"></path>
                                <path d="M21 13a9 9 0 1 1-3-7.7L21 8"></path>
                            </svg>
                        </button>
                        <div class="text-white px-2">
                            {{ currentIndex + 1 }} / {{ previewSrcList.length }}
                        </div>
                    </div>

                    <!-- Flèches de navigation -->
                    <template v-if="previewSrcList.length > 1">
                        <button @click="prevImage"
                            class="absolute left-5 top-1/2 transform -translate-y-1/2 text-white p-2 bg-black bg-opacity-50 rounded-full hover:bg-opacity-70">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-chevron-left">
                                <path d="m15 18-6-6 6-6"></path>
                            </svg>
                        </button>
                        <button @click="nextImage"
                            class="absolute right-5 top-1/2 transform -translate-y-1/2 text-white p-2 bg-black bg-opacity-50 rounded-full hover:bg-opacity-70">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-chevron-right">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </button>
                    </template>

                    <!-- Bouton de fermeture -->
                    <button @click="closePreview"
                        class="absolute top-5 right-5 text-white p-2 bg-black bg-opacity-50 rounded-full hover:bg-opacity-70">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-x">
                            <path d="M18 6 6 18"></path>
                            <path d="m6 6 12 12"></path>
                        </svg>
                    </button>
                </div>
            </transition>
        </teleport>
    </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';

export default {
    props: {
        src: {
            type: String,
            required: true
        },
        width: {
            type: Number,
            default: 100
        },
        height: {
            type: Number,
            default: 100
        },
        alt: {
            type: String,
            default: ''
        },
        fit: {
            type: String,
            default: 'cover',
            validator: (value) => ['fill', 'contain', 'cover', 'none', 'scale-down'].includes(value)
        },
        previewSrcList: {
            type: Array,
            default: () => []
        },
        initialIndex: {
            type: Number,
            default: 0
        },
        zoomRate: {
            type: Number,
            default: 1.2
        },
        minScale: {
            type: Number,
            default: 0.2
        },
        maxScale: {
            type: Number,
            default: 7
        }
    },
    setup(props) {
        const showPreview = ref(false);
        const currentIndex = ref(props.initialIndex);
        const scale = ref(1);
        const rotation = ref(0);
        const position = ref({ x: 0, y: 0 });
        const isDragging = ref(false);
        const dragStart = ref({ x: 0, y: 0 });

        const sources = computed(() => {
            return props.previewSrcList.length > 0 ? props.previewSrcList : [props.src];
        });

        const openPreview = () => {
            showPreview.value = true;
            currentIndex.value = props.initialIndex;
            resetView();
            document.body.style.overflow = 'hidden';
        };

        const closePreview = () => {
            showPreview.value = false;
            document.body.style.overflow = '';
        };

        const nextImage = () => {
            resetView();
            currentIndex.value = (currentIndex.value + 1) % sources.value.length;
        };

        const prevImage = () => {
            resetView();
            currentIndex.value = (currentIndex.value - 1 + sources.value.length) % sources.value.length;
        };

        const zoomIn = () => {
            scale.value = Math.min(scale.value * props.zoomRate, props.maxScale);
        };

        const zoomOut = () => {
            scale.value = Math.max(scale.value / props.zoomRate, props.minScale);
        };

        const rotate = () => {
            rotation.value = (rotation.value + 90) % 360;
        };

        const resetView = () => {
            scale.value = 1;
            rotation.value = 0;
            position.value = { x: 0, y: 0 };
        };

        const handleMouseDown = (e) => {
            if (e.button !== 0) return; // Seulement clic gauche
            isDragging.value = true;
            dragStart.value = {
                x: e.clientX - position.value.x,
                y: e.clientY - position.value.y
            };
        };

        const handleMouseMove = (e) => {
            if (!isDragging.value) return;
            position.value = {
                x: e.clientX - dragStart.value.x,
                y: e.clientY - dragStart.value.y
            };
        };

        const handleMouseUp = () => {
            isDragging.value = false;
        };

        const handleWheel = (e) => {
            e.preventDefault();
            if (e.deltaY < 0) {
                zoomIn();
            } else {
                zoomOut();
            }
        };

        const handleKeyDown = (e) => {
            if (!showPreview.value) return;

            switch (e.key) {
                case 'Escape':
                    closePreview();
                    break;
                case 'ArrowLeft':
                    prevImage();
                    break;
                case 'ArrowRight':
                    nextImage();
                    break;
                case '+':
                    zoomIn();
                    break;
                case '-':
                    zoomOut();
                    break;
                case 'r':
                    rotate();
                    break;
            }
        };

        onMounted(() => {
            window.addEventListener('keydown', handleKeyDown);
        });

        onUnmounted(() => {
            window.removeEventListener('keydown', handleKeyDown);
        });

        return {
            showPreview,
            currentIndex,
            scale,
            rotation,
            position,
            isDragging,
            sources,
            openPreview,
            closePreview,
            nextImage,
            prevImage,
            zoomIn,
            zoomOut,
            rotate,
            handleMouseDown,
            handleMouseMove,
            handleMouseUp,
            handleWheel
        };
    }
};
</script>

<style scoped>
.image-container {
    overflow: hidden;
    display: inline-block;
}

.image-container img {
    width: 100%;
    height: 100%;
    transition: transform 0.3s;
}

.preview-container {
    z-index: 2000;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>