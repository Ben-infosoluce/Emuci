<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl  text-gray-900">Historique des Validations</h1>
        <p class="text-sm text-gray-500">Consultez l'historique des clôtures de caisse et les écarts détectés.</p>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2 bg-white p-1.5 rounded-lg border border-gray-200">
          <input type="date" v-model="filters.date_start" class="text-xs border-none focus:ring-0" />
          <span class="text-gray-400">à</span>
          <input type="date" v-model="filters.date_end" class="text-xs border-none focus:ring-0" />
        </div>

        <select v-model="filters.site_id"
          class="text-xs border-gray-200 rounded-lg focus:ring-amber-500 focus:border-amber-500">
          <option value="tout">Tous les sites</option>
          <option v-for="site in sites" :key="site.id" :value="site.id">{{ site.nom_site }}</option>
        </select>

        <select v-model="filters.status"
          class="text-xs border-gray-200 rounded-lg focus:ring-amber-500 focus:border-amber-500">
          <option value="tout">Tous les statuts</option>
          <option value="equilibre">Équilibré</option>
          <option value="perte">Pertes</option>
          <option value="surplus">Surplus</option>
        </select>

        <Button @click="fetchHistory" variant="default" size="sm" class="bg-amber-600 hover:bg-amber-700">
          <RefreshCcw v-if="loading" class="w-4 h-4 animate-spin mr-2" />
          Filtrer
        </Button>
      </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
              <th class="px-6 py-3 text-[10px]  text-gray-500 uppercase tracking-wider">Date Clôture</th>
              <th class="px-6 py-3 text-[10px]  text-gray-500 uppercase tracking-wider">Caisse</th>
              <!-- <th class="px-6 py-3 text-[10px]  text-gray-500 uppercase tracking-wider">Caissier</th> -->
              <th class="px-6 py-3 text-[10px]  text-gray-500 uppercase tracking-wider">Nb. Dossiers</th>
              <th class="px-6 py-3 text-[10px]  text-gray-500 uppercase tracking-wider">Fond de Caisse</th>
              <th class="px-6 py-3 text-[10px]  text-gray-500 uppercase tracking-wider">Total Caissière</th>
              <th class="px-6 py-3 text-[10px]  text-gray-500 uppercase tracking-wider">Montant Attendu</th>
              <th class="px-6 py-3 text-[10px]  text-gray-500 uppercase tracking-wider">Total Contrôleur</th>
              <th class="px-6 py-3 text-[10px]  text-gray-500 uppercase tracking-wider text-right">Écart</th>
              <th class="px-6 py-3 text-[10px]  text-gray-500 uppercase tracking-wider">Justification</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="loading">
              <td colspan="10" class="px-6 py-12 text-center text-gray-400">Chargement...</td>
            </tr>
            <tr v-else-if="history.length === 0">
              <td colspan="10" class="px-6 py-12 text-center text-gray-400">Aucun historique trouvé.</td>
            </tr>
            <tr v-for="row in history" :key="row.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-col">
                  <span class="text-sm font-semibold text-gray-900">{{ formatDate(row.date_fermeture) }}</span>
                  <span class="text-[10px] text-gray-500">{{ formatTime(row.date_fermeture) }}</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <Badge variant="outline" class="bg-blue-50 text-blue-700 text-sm border-blue-100">{{ row.nom_caisse }}
                </Badge>
              </td>
              <!-- <td class="px-6 py-4 text-sm text-gray-600">{{ row.nom_caissier }}</td> -->
              <td class="px-6 py-4">
                <Badge variant="secondary" class=" text-xs">{{ row.nb_dossiers || 0 }} </Badge>
              </td>
              <td class="px-6 py-4  text-sm text-gray-500">{{ formatMoney(row.montant_ouverture) }} F</td>
              <td class="px-6 py-4  text-sm">{{ formatMoney(row.montant_fermeture) }} F</td>
              <td class="px-6 py-4  text-sm  text-blue-700 bg-blue-50/50">
                {{ formatMoney(calculateMontantAttendu(row)) }} F
              </td>
              <td class="px-6 py-4  text-sm text-amber-700 ">
                {{ formatMoney(row.montant_controlleur || calculateControllerTotal(row)) }} F
              </td>
              <td class="px-6 py-4 text-right">
                <div v-if="row.perte > 0" class="flex flex-col items-end">
                  <span class="text-sm  text-red-600">- {{ formatMoney(row.perte) }} F</span>
                  <Badge variant="destructive" class="text-[8px] px-1 py-0 h-4">PERTE</Badge>
                </div>
                <div v-else-if="row.surplus > 0" class="flex flex-col items-end">
                  <span class="text-sm  text-green-600">+ {{ formatMoney(row.surplus) }} F</span>
                  <Badge class="bg-green-100 text-green-700 border-green-200 text-[8px] px-1 py-0 h-4 ">SURPLUS
                  </Badge>
                </div>
                <div v-else class="flex flex-col items-end">
                  <span class="text-sm  text-gray-400">0 F</span>
                  <Badge variant="outline" class="text-[8px] px-1 py-0 h-4 border-gray-200 text-gray-400">OK</Badge>
                </div>
              </td>
              <td class="px-6 py-4">
                <p v-if="row.commentaire" class="text-xs text-gray-500 italic max-w-xs truncate"
                  :title="row.commentaire">
                  "{{ row.commentaire }}"
                </p>
                <span v-else class="text-[10px] text-gray-300">Aucun commentaire</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1"
        class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
        <span class="text-xs text-gray-500">Page {{ pagination.current_page }} sur {{ pagination.last_page }}</span>
        <div class="flex gap-2">
          <Button variant="outline" size="sm" :disabled="pagination.current_page === 1"
            @click="goToPage(pagination.current_page - 1)"> Précédent </Button>
          <Button variant="outline" size="sm" :disabled="pagination.current_page === pagination.last_page"
            @click="goToPage(pagination.current_page + 1)"> Suivant </Button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { RefreshCcw } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import axios from 'axios';

const history = ref([]);
const loading = ref(false);
const pagination = ref({ current_page: 1, last_page: 1 });

const filters = reactive({
  date_start: '',
  date_end: '',
  status: 'tout',
  site_id: 'tout'
});

defineProps({
  sites: Array
});

const fetchHistory = async (page = 1) => {
  loading.value = true;
  try {
    const { data } = await axios.get(route('get.boss.validations.history'), {
      params: {
        ...filters,
        page
      }
    });
    history.value = data.data;
    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page
    };
  } catch (error) {
    console.error('Erreur fetch history:', error);
  } finally {
    loading.value = false;
  }
};

const goToPage = (page) => {
  fetchHistory(page);
};

const calculateControllerTotal = (row) => {
  return Number(row.montant_fermeture) - Number(row.perte) + Number(row.surplus);
};

const calculateMontantAttendu = (row) => {
  return (Number(row.total_ventes) || 0) + Number(row.montant_ouverture || 0);
};

const formatDate = (dateStr) => {
  if (!dateStr) return "-";
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  });
};

const formatTime = (dateStr) => {
  if (!dateStr) return "";
  return new Date(dateStr).toLocaleTimeString('fr-FR', {
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatMoney = (amount) => {
  return Number(amount || 0).toLocaleString();
};

onMounted(() => {
  fetchHistory();
});
</script>

<script>
import Main from '/resources/js/Pages/Main.vue';
export default {
  layout: Main,
};
</script>
