import { defineStore } from "pinia";
import axios from "axios";

export const useCaisseStore = defineStore("caisseOpened", {
    state: () => ({
        caisseOpened: null, // données de la caisse actuelle
        lastClosure: null, // données de la dernière fermeture
        loading: false, // état de chargement
        error: null, // message d’erreur
    }),

    getters: {
        isOpen(state) {
            return (
                !!state.caisseOpened && state.caisseOpened.statut === "ouverte"
            );
        },
    },

    actions: {
        // 🔄 Récupérer le statut actuel de la caisse
        async fetchCurrent() {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await axios.get("/caisse/statut");
                this.caisseOpened = data;
                return data;
            } catch (err) {
                this.caisseOpened = null;
                this.error = err.response?.data?.message || "Erreur lors du chargement de la caisse";
                return null;
            } finally {
                this.loading = false;
            }
        },

        // 🔄 Récupérer la dernière fermeture
        async fetchLastClosure() {
            this.loading = true;
            try {
                const { data } = await axios.get("/caisse/last-closure");
                this.lastClosure = data;
                return data;
            } catch (err) {
                console.error("Erreur last closure:", err);
                return null;
            } finally {
                this.loading = false;
            }
        },

        // ✅ Ouvrir la caisse
        async open(fondDeCaisse) {
            this.loading = true;
            this.error = null;
            try {
                await axios.post("/caisses/ouvertures", { fond_de_caisse: fondDeCaisse });
                await this.fetchCurrent();
            } catch (err) {
                this.error = err.response?.data?.message || "Erreur lors de l'ouverture de la caisse";
            } finally {
                this.loading = false;
            }
        },

        // ✅ Fermer la caisse
        async close(payload) {
            this.loading = true;
            this.error = null;
            try {
                await axios.put(`/caisses/ouvertures/${this.caisseOpened.caisse_id}/close`, payload);
                await this.fetchCurrent();
                await this.fetchLastClosure(); // Rafraîchir pour l'édition éventuelle
            } catch (err) {
                this.error = err.response?.data?.message || "Erreur lors de la fermeture de la caisse";
            } finally {
                this.loading = false;
            }
        },

        // ✅ Mettre à jour la billetterie
        async updateBilletterie(id, payload) {
            this.loading = true;
            try {
                const res = await axios.put(`/caisses/ouvertures/${id}/update-billetterie`, payload);
                await this.fetchLastClosure();
                return res.data;
            } catch (err) {
                throw err;
            } finally {
                this.loading = false;
            }
        }
    },
});
