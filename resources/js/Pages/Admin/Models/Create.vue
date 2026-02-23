<template>
    <h2 class="text-center font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Créer un Modèle
    </h2>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form @submit.prevent="submit">
                        <div class="mb-6">
                            <label for="marque_id" class="block font-semibold mb-2">Marque</label>
                            <select id="marque_id" v-model="form.marque_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600"
                                required>
                                <option value="">-- Sélectionnez une marque --</option>
                                <option v-for="marque in marques" :key="marque.id" :value="marque.id">
                                    {{ marque.nom }}
                                </option>
                            </select>
                            <div v-if="form.errors.marque_id" class="text-red-500 text-sm mt-2">
                                {{ form.errors.marque_id }}
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="nom" class="block font-semibold mb-2">Nom</label>
                            <input id="nom" v-model="form.nom" type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600"
                                required />
                            <div v-if="form.errors.nom" class="text-red-500 text-sm mt-2">
                                {{ form.errors.nom }}
                            </div>
                        </div>


                        <div class="flex gap-4">
                            <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                                :disabled="form.processing">
                                {{ form.processing ? 'Création...' : 'Créer' }}
                            </button>
                            <Link href="/models/data" class="px-6 py-2 bg-gray-300 rounded hover:bg-gray-400">
                                Annuler
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';

defineProps({
    marques: Array,
});

const form = useForm({
    marque_id: '',
    nom: '',
});

const submit = () => {
    form.post('/models/new');
};
</script>

<script>
import Main from '/resources/js/Pages/Main.vue';
export default { layout: Main };
</script>