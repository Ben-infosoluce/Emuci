import { defineStore } from "pinia";
import axios from "axios";

export const useCaisseStore = defineStore("caisseOpened", {
    state: () => ({
        caisseOpened: null, // données de la caisse actuelle
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
                // console.log("Caisse actuelle :", data);
                return data;
            } catch (err) {
                this.caisseOpened = null;

                this.error =
                    err.response?.data?.message ||
                    "Erreur lors du chargement de la caisse";
                return null;
            } finally {
                this.loading = false;
            }
        },

        // ✅ Ouvrir la caisse (POST /caisses/ouvertures)
        async open(fondDeCaisse) {
            this.loading = true;
            this.error = null;

            const valeur = String(fondDeCaisse).trim();
            if (valeur === '' || isNaN(Number(valeur))) {
                this.error = "Veuillez entrer un fond de caisse valide";
                this.loading = false;
                return;
            }
            // Ensuite, vous pouvez utiliser Number(valeur) pour obtenir la valeur numérique.

            try {
                const res = await axios.post("/caisses/ouvertures", {
                    fond_de_caisse: fondDeCaisse, // 👈 ajout du montant initial
                });
                console.log(res);

                await this.fetchCurrent(); // mise à jour après ouverture
            } catch (err) {
                console.log(err.response);

                this.error =
                    err.response?.data?.message ||
                    "Erreur lors de l'ouverture de la caisse";
            } finally {
                this.loading = false;
            }
        },

        // ✅ Fermer la caisse (PUT /caisses/ouvertures/:id/close)
        async close(payload) {
            this.loading = true;
            this.error = null;
            console.log(this.caisseOpened);

            if (!this.caisseOpened?.caisse_id) {
                this.error =
                    "Aucune ouverture active trouvée pour cet utilisateur";
                this.loading = false;
                return;
            }

            if (!payload) {
                this.error = "Veuillez entrer un montant de fermeture";
                this.loading = false;
                return;
            }

            try {
                const res = await axios.put(
                    `/caisses/ouvertures/${this.caisseOpened.caisse_id}/close`,
                    {
                        montant_fermeture: payload.montant_fermeture,
                        montant_saisie_caisse: payload.montant_saisie_caisse,
                        billetterie: payload.billetterie,
                    }
                );
                console.log(res);

                await this.fetchCurrent();
            } catch (err) {
                this.error =
                    err.response?.data?.message ||
                    "Erreur lors de la fermeture de la caisse";
            } finally {
                this.loading = false;
            }
        },
    },
});
