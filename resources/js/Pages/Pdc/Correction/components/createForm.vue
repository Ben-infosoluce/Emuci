<template>
    <!-- Header sticky -->
    <div
        class="sticky top-[-30px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8  py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <h4 class="text-2xl font-bold tracking-tight">
            Immatriculation Spéciale
        </h4>
        <div class="flex items-center space-x-2">
            <Link :href="route('show.new.pdc.immatriculation')">
            <BoutonRetour />
            </Link>
        </div>
    </div>

    <!-- Contenu scrollable -->
    <div class="rounded-lg dark:border-gray-700 ">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <Card class="h-full flex flex-col ">
                <ScrollArea class="h-full w-full rounded-md border">
                    <div class="p-8">
                        <!-- Type de personne -->

                        <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3">
                            <FormField v-slot="{ componentField }" name="type">
                                <FormItem v-auto-animate>
                                    <FormLabel>Type de personne *</FormLabel>
                                    <FormControl>
                                        <Select v-bind="componentField" v-model="form.type">
                                            <SelectTrigger class="w-full">
                                                <SelectValue placeholder="Sélectionner le type de personne " />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectLabel>Types</SelectLabel>
                                                    <SelectItem v-for="type in types" :key="type" :value="type">
                                                        {{ type }}
                                                    </SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <!-- <label class="flex items-center gap-2">
                                <input type="radio" value="Morale" v-model="form.type" />
                                Morale

                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" value="Physique" v-model="form.type" />
                                Physique
                            </label>
                            <p v-if="typeError" class="text-red-600 text-sm mt-1">{{ typeError }}</p> -->
                        </div>

                        <form @submit="onSubmit">
                            <!-- Informations du propriétaire -->
                            <h3 class="text-md font-semibold mt-6 mb-6">Informations du propriétaire</h3>
                            <div class="flex flex-1 flex-col gap-4 md:gap-6">
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3">
                                    <FormField v-slot="{ componentField }" name="civilite">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Civilité *</FormLabel>
                                            <FormControl>
                                                <Select v-bind="componentField" autocomplete="genre"
                                                    v-model="form.civilite">
                                                    <SelectTrigger class="w-full">
                                                        <SelectValue placeholder="Selectioner la civilité " />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            <SelectLabel>Civilité</SelectLabel>
                                                            <SelectItem value="Monsieur">M</SelectItem>
                                                            <SelectItem value="Madame">Mme</SelectItem>
                                                            <SelectItem value="Mademoiselle">Mlle</SelectItem>
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <FormField v-slot="{ componentField }" name="firstname">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Nom*</FormLabel>
                                            <FormControl>
                                                <Input placeholder="Konaté" v-bind="componentField"
                                                    autocomplete="firstname" v-model="form.firstname" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- Prenom -->
                                    <FormField v-slot="{ componentField }" name="lastname">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Prénom*</FormLabel>
                                            <FormControl>
                                                <Input placeholder="Ben" v-bind="componentField" autocomplete="lastname"
                                                    v-model="form.lastname" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </div>
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3">
                                    <FormField v-slot="{ componentField }" name="phone">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Numéro de téléphone*</FormLabel>
                                            <FormControl>
                                                <Input placeholder="0701020304" v-bind="componentField"
                                                    autocomplete="phone" v-model="form.phone" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- adresse -->
                                    <FormField v-slot="{ componentField }" name="adresse">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Adresse*</FormLabel>
                                            <FormControl>
                                                <Input placeholder="Abidjan,Cocody" v-bind="componentField"
                                                    autocomplete="adresse" v-model="form.adresse" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- typePersonne -->
                                    <!-- Date de naissance -->
                                    <FormField v-slot="{ componentField }" name="DateNaissance">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Date de naissance*</FormLabel>
                                            <FormControl>
                                                <Input placeholder="01-01-1984" v-bind="componentField"
                                                    autocomplete="DateNaissance" v-model="form.DateNaissance" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </div>
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3">
                                    <!-- ville de naissance  -->
                                    <FormField v-slot="{ componentField }" name="villeNaissance">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Ville de naissance*</FormLabel>
                                            <FormControl>
                                                <Input placeholder="Bouaké" v-bind="componentField"
                                                    autocomplete="villeNaissance" v-model="form.villeNaissance" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- Email -->
                                    <FormField v-slot="{ componentField }" name="email">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Adresse email</FormLabel>
                                            <FormControl>
                                                <Input type="email" placeholder="exemple@email.com"
                                                    v-bind="componentField" autocomplete="email" v-model="form.email" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- district -->
                                    <FormField v-slot="{ componentField }" name="district">
                                        <FormItem v-auto-animate>
                                            <FormLabel>District</FormLabel>
                                            <FormControl>
                                                <Input placeholder="District 1" v-bind="componentField"
                                                    autocomplete="district" v-model="form.district" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- Préfecture -->
                                    <FormField v-slot="{ componentField }" name="prefecture_client">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Préfecture</FormLabel>
                                            <FormControl>
                                                <Input placeholder="prefecture 1" v-bind="componentField"
                                                    autocomplete="prefecture_client" v-model="form.prefecture_client" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- Sous Préfecture -->
                                    <FormField v-slot="{ componentField }" name="sousPrefectureClient">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Sous Préfecture</FormLabel>
                                            <FormControl>
                                                <Input placeholder="Sous prefecture 1" v-bind="componentField"
                                                    autocomplete="SousPrefectureClient"
                                                    v-model="form.sousPrefectureClient" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>


                                </div>
                            </div>

                            <!-- Informations du véhicule -->
                            <div class="gap-4 md:gap-6 mt-10 ">
                                <h3 class="text-md font-semibold mt-8 mb-8">Informations du véhicule</h3>
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3 mb-5">
                                    <FormField v-slot="{ componentField }" name="vin">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Châssis (VIN)</FormLabel>
                                            <FormControl>
                                                <Input placeholder="0GE447" v-bind="componentField" autocomplete="vin"
                                                    v-model="form.vin" disabled />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- marqueVehicule -->
                                    <div>
                                        <label for="marque">Marque *</label>
                                        <select v-model="selectedMarqueId" @change="fetchModeles"
                                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-gray-200 disabled:opacity-50">
                                            <option disabled value="">Sélectionner une marque</option>
                                            <option v-for="marque in marques" :key="marque.id" :value="marque.id">
                                                {{ marque.nom }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Modèle du véhicule -->
                                    <div class="">
                                        <label for="modele">Modèle *</label>
                                        <select v-model="selectedModeleId" :disabled="modeles.length === 0"
                                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3  text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-gray-200 disabled:opacity-50">
                                            <option disabled value="">Sélectionner un modèle</option>
                                            <option v-for="modele in modeles" :key="modele.id" :value="modele.id">
                                                {{ modele.nom }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3 mb-5">
                                    <FormField v-slot="{ componentField }" name="couleurVehicule">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Couleur du véhicule</FormLabel>
                                            <FormControl>
                                                <Select v-bind="componentField" autocomplete="couleurVehicule"
                                                    v-model="form.couleurVehicule">
                                                    <SelectTrigger class="w-full">
                                                        <SelectValue placeholder="Sélectionner une couleur" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            <SelectLabel>Couleurs disponibles</SelectLabel>
                                                            <SelectItem value="Vert">Vert</SelectItem>
                                                            <SelectItem value="Bleu">Bleu</SelectItem>
                                                            <SelectItem value="Beige">Beige</SelectItem>
                                                            <SelectItem value="Rouge">Rouge</SelectItem>
                                                            <SelectItem value="Marron">Marron</SelectItem>
                                                            <SelectItem value="Orange">Orange</SelectItem>
                                                            <SelectItem value="Jaune">Jaune</SelectItem>
                                                            <SelectItem value="Rose">Rose</SelectItem>
                                                            <SelectItem value="Violet">Violet</SelectItem>
                                                            <SelectItem value="Noir">Noir</SelectItem>
                                                            <SelectItem value="Blanc">Blanc</SelectItem>
                                                            <SelectItem value="Gris">Gris</SelectItem>

                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>

                                    <!-- Carrosserie -->
                                    <FormField v-slot="{ componentField }" name="carrosserie">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Carrosserie *</FormLabel>
                                            <FormControl>
                                                <Input placeholder="Abidjan,Cocody" v-bind="componentField"
                                                    autocomplete="carrosserie" v-model="form.carrosserie" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- typeTechnique -->

                                    <FormField v-slot="{ componentField }" name="typeTechnique">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Type Technique *</FormLabel>
                                            <FormControl>
                                                <Input placeholder="Type Technique " v-bind="componentField"
                                                    autocomplete="typeTechnique" v-model="form.typeTechnique" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>


                                </div>
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3 mb-5">
                                    <!-- genre -->
                                    <FormField v-slot="{ componentField }" name="genre">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Genre *</FormLabel>
                                            <FormControl>
                                                <Select v-bind="componentField" v-model="form.genre">
                                                    <SelectTrigger class="w-full">
                                                        <SelectValue placeholder="Sélectionner le genre" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            <SelectLabel>Catégories disponibles</SelectLabel>

                                                            <SelectItem v-for="genre in genres" :key="genre"
                                                                :value="genre">
                                                                {{ genre }}
                                                            </SelectItem>
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>

                                    <!-- PTAC -->
                                    <FormField v-slot="{ componentField }" name="ptac">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Poids total en charge (PTAC) *</FormLabel>
                                            <FormControl>
                                                <Input placeholder="500 (KG)" v-bind="componentField"
                                                    autocomplete="ptac" v-model="form.ptac" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- PU -->
                                    <FormField v-slot="{ componentField }" name="pu">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Poids Utile (PU) *</FormLabel>
                                            <FormControl>
                                                <Input placeholder="200 (KG)" v-bind="componentField" autocomplete="pu"
                                                    v-model="form.pu" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </div>
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3 mb-5">
                                    <!-- PU -->
                                    <FormField v-slot="{ componentField }" name="pv">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Poids à Vide (PV) *</FormLabel>
                                            <FormControl>
                                                <Input placeholder="150 (KG)" v-bind="componentField" autocomplete="pv"
                                                    v-model="form.pv" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- Puissance administrative -->
                                    <FormField v-slot="{ componentField }" name="puissance">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Puissance administrative *</FormLabel>
                                            <FormControl>
                                                <Input placeholder="100 (CV)" v-bind="componentField"
                                                    autocomplete="puissance" v-model="form.puissance" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- Places assises -->
                                    <FormField v-slot="{ componentField }" name="placesAssises">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Places assises *</FormLabel>
                                            <FormControl>
                                                <Input placeholder="6" v-bind="componentField"
                                                    autocomplete="placesAssises" v-model="form.placesAssises" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </div>
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3 mb-5">
                                    <!-- Places assises -->
                                    <FormField v-slot="{ componentField }" name="usage">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Usage *</FormLabel>
                                            <FormControl>
                                                <Input placeholder="transport" v-bind="componentField"
                                                    autocomplete="usage" v-model="form.usage" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- Sources d’énergie -->
                                    <FormField v-slot="{ componentField }" name="sourcesEnergie">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Sources d’énergie</FormLabel>
                                            <FormControl>
                                                <Select v-bind="componentField" autocomplete="sourcesEnergie"
                                                    v-model="form.sourcesEnergie">
                                                    <SelectTrigger class="w-full">
                                                        <SelectValue placeholder="Sélectionner une Source d’énergie" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            <SelectLabel>Sources d’énergie</SelectLabel>
                                                            <SelectItem value="ESSENCE">ESSENCE</SelectItem>
                                                            <SelectItem value="GAS-OIL">GAS-OIL</SelectItem>
                                                            <SelectItem value="SANS ENERGIE">SANS ENERGIE</SelectItem>
                                                            <SelectItem value="ELECTRICITE">ELECTRICITE</SelectItem>
                                                            <SelectItem value="AUTRE">AUTRE</SelectItem>
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- Nombre d’essieux -->
                                    <FormField v-slot="{ componentField }" name="nombreEssieux">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Nombre d’essieux *</FormLabel>
                                            <FormControl>
                                                <Input placeholder="5" v-bind="componentField"
                                                    autocomplete="nombreEssieux" v-model="form.nombreEssieux" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </div>
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3 mb-5">
                                    <!-- Places assises -->
                                    <FormField v-slot="{ componentField }" name="codeRegion">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Code de région *</FormLabel>
                                            <FormControl>
                                                <Input placeholder="225" v-bind="componentField"
                                                    autocomplete="codeRegion" v-model="form.codeRegion" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- Date de naissance -->
                                    <FormField v-slot="{ componentField }" name="DateCirculation">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Date de mise en circulation *</FormLabel>
                                            <FormControl>
                                                <Input placeholder="01-01-1984" v-bind="componentField"
                                                    autocomplete="DateCirculation" v-model="form.DateCirculation" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                    <!-- Année de production -->
                                    <FormField v-slot="{ componentField }" name="AnneeProduction">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Année de production *</FormLabel>
                                            <FormControl>
                                                <Input placeholder="01-01-1984" v-bind="componentField"
                                                    autocomplete="AnneeProduction" v-model="form.AnneeProduction" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </div>
                            </div>


                            <!-- Informations de l'entreprise -->
                            <div v-show="form.type !== 'Physique'" class="mt-10">
                                <h3 class="text-md font-semibold mb-6 ">Informations de l’entreprise
                                </h3>
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3">
                                    <div>
                                        <p class="text-sm font-medium mb-2 ">Nom de l'entreprise *</p>
                                        <Input class="py-0 h-9" v-model="form.nomEntreprise" />
                                        <p v-if="nomEntrepriseError" class="text-red-600 text-sm mt-1">{{
                                            nomEntrepriseError }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2 ">Numéro du registre de Commerce *</p>
                                        <Input class="py-0 h-9" v-model="form.registreCommerce" />
                                        <p v-if="registreCommerceError" class="text-red-600 text-sm mt-1">{{
                                            registreCommerceError }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2 ">Nom et Prénom du représentant légal *</p>
                                        <Input class="py-0 h-9" v-model="form.representantLegal" />
                                        <p v-if="representantLegalError" class="text-red-600 text-sm mt-1">{{
                                            representantLegalError }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2 ">Téléphone du représentant légal *</p>
                                        <Input class="py-0 h-9" v-model="form.numeroTelephone" />
                                        <p v-if="numeroTelephoneError" class="text-red-600 text-sm mt-1">{{
                                            numeroTelephoneError }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2 ">Date de naissance du représentant légal *
                                        </p>
                                        <Input class="py-0 h-9" v-model="form.DateNaissanceRepresantant" />
                                        <p v-if="DateNaissanceRepresantantError" class="text-red-600 text-sm mt-1">{{
                                            DateNaissanceRepresantantError }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2 ">Profession du représentant légal *</p>
                                        <Input class="py-0 h-9" v-model="form.ProfessionRepresantant" />
                                        <p v-if="ProfessionRepresantantError" class="text-red-600 text-sm mt-1">{{
                                            ProfessionRepresantantError }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2 ">Numéro de compte contribuale *</p>
                                        <Input class="py-0 h-9" v-model="form.compteContribuable" />
                                        <p v-if="compteContribuableError" class="text-red-600 text-sm mt-1">{{
                                            compteContribuableError }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2 ">Péfecture</p>
                                        <Input class="py-0 h-9" v-model="form.prefecture" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2 ">Sous-Péfecture</p>
                                        <Input class="py-0 h-9" v-model="form.sousPrefecture" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2 ">Région</p>
                                        <Input class="py-0 h-9" v-model="form.region" />
                                    </div>
                                </div>
                            </div>
                            <!-- show Summary -->
                            <div v-if="showSummary"
                                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 p-4 overflow-auto">
                                <div
                                    class="bg-white dark:bg-gray-800 rounded-2xl p-8 w-full max-w-4xl shadow-2xl space-y-6">
                                    <h3 class="text-2xl font-bold text-center mb-2">Résumé des informations</h3>
                                    <!-- Informations du propriétaire -->
                                    <div>
                                        <h4 class="text-lg font-semibold mb-4">Informations du propriétaire :
                                            <samp class="text-[#ca7600]"> {{ formDataSummary.sex }} {{
                                                formDataSummary.firstname }} {{
                                                    formDataSummary.lastname }}</samp>
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <p><strong>Email :</strong> {{ formDataSummary.email }}</p>
                                            <p><strong>Adresse :</strong> {{ formDataSummary.adresse }}</p>
                                            <p><strong>District :</strong> {{ formDataSummary.district }}</p>
                                            <p><strong>Ville de naissance :</strong> {{ formDataSummary.villeNaissance
                                                }}</p>
                                            <!-- <p><strong>Nom :</strong> {{ formDataSummary.firstname }}</p>
                                            <p><strong>Prénom :</strong> {{ formDataSummary.lastname }}</p> -->
                                            <p><strong>Téléphone :</strong> {{ formDataSummary.phone }}</p>
                                            <p><strong>Type de personne :</strong> {{ formDataSummary.typePersonne }}
                                            </p>
                                        </div>
                                    </div>
                                    <Separator class="my-4" />
                                    <!-- Informations de l'entreprise -->
                                    <div>
                                        <h4 class="text-lg font-semibold mb-4">Informations du véhicule :
                                            <samp class="text-[#ca7600]">{{ formDataSummary.marqueVehicule.nom }}</samp>
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <p><strong>Marque :</strong> {{ formDataSummary.marqueVehicule.nom }}</p>
                                            <p><strong>Model :</strong> {{ formDataSummary.modelVehicule.nom }}</p>
                                            <p><strong>Couleur :</strong> {{ formDataSummary.couleurVehicule }}</p>
                                            <p><strong>Carrosserie :</strong> {{ formDataSummary.carrosserie }}
                                            </p>
                                            <p><strong>Type technique :</strong> {{
                                                formDataSummary.typeTechnique
                                            }}</p>
                                            <p><strong>Genre :</strong> {{ formDataSummary.genre }}</p>
                                            <p><strong>Poids Total en charge (PTAC) :</strong> {{ formDataSummary.ptac
                                            }}</p>
                                            <p><strong>Poids Utile (PU) :</strong> {{ formDataSummary.pu }}</p>
                                            <p><strong>Poids à Vide (PV) :</strong> {{ formDataSummary.pv }}</p>
                                            <p><strong>Puissance administrative :</strong> {{ formDataSummary.puissance
                                            }}</p>
                                            <p><strong>Places Assises :</strong> {{ formDataSummary.placesAssises }}</p>
                                            <p><strong>Sources d’énergie :</strong> {{ formDataSummary.sourcesEnergie }}
                                            </p>
                                            <p><strong>Nbre d’Essieux :</strong> {{ formDataSummary.nombreEssieux }}</p>

                                        </div>
                                    </div>
                                    <Separator class="my-4" />
                                    <div v-if="formDataSummary.type != 'Physique'">
                                        <!-- Fichiers -->
                                        <h4 class="text-lg font-semibold mb-4">Informations de l'entreprise :
                                            <samp class="text-[#ca7600]">{{ formDataSummary.nomEntreprise }}</samp>
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

                                            <p><strong>Registre de commerce :</strong> {{
                                                formDataSummary.registreCommerce
                                            }}</p>
                                            <p><strong>Representant Legal :</strong> {{
                                                formDataSummary.representantLegal }}
                                            </p>
                                            <p><strong>Numéro de Telephone :</strong> {{ formDataSummary.numeroTelephone
                                            }}
                                            </p>
                                            <p><strong>Compte Contribuable :</strong> {{
                                                formDataSummary.compteContribuable
                                            }}</p>
                                            <p><strong>Prefecture :</strong> {{ formDataSummary.prefecture
                                            }}</p>
                                            <p><strong>Sous Prefecture :</strong> {{ formDataSummary.sousPrefecture
                                            }}</p>
                                            <p><strong>Region :</strong> {{ formDataSummary.region
                                            }}</p>

                                            <p><strong>Profession du représentant légal
                                                    :</strong> {{
                                                        formDataSummary.professionRepresantant }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="mt-6 flex justify-end gap-4">
                                        <Button @click="showSummary = false" variant="outline"
                                            class="px-6 py-2 rounded-lg">Modifier</Button>
                                        <Button @click="submitFinal"
                                            class="px-6 py-2 rounded-lg primary-color text-white">
                                            <!-- <MoveLeft class=" w-4 h-4 mr-2" />
                                            <Validate class="w-4 h-4 mr-2" /> -->
                                            Valider
                                        </Button>
                                    </div>
                                </div>
                            </div>
                            <!-- Bouton continuer -->
                            <div class="mt-8">
                                <Button type="submit">
                                    CONTINUER
                                </Button>
                            </div>
                        </form>
                    </div>
                </ScrollArea>
                <!-- Modal Résumé -->
            </Card>
        </main>
        <Toaster richColors position="top-right" />
    </div>



</template>



<script setup>
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

import {
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form'
import {
    Card,
} from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Input_search } from '@/components/ui/Input_search'
import { Label } from '@/components/ui/label'
import { Toaster, toast } from 'vue-sonner'
import { toTypedSchema } from '@vee-validate/zod'
import { vAutoAnimate } from '@formkit/auto-animate/vue'
import { useForm } from 'vee-validate'
import { router } from '@inertiajs/vue3'
import * as z from 'zod'
import { useForm as useValidationForm } from 'vee-validate';
import { ScrollArea } from '@/components/ui/scroll-area'
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Separator } from '@/components/ui/separator'
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { MoveRight, EyeOff, Printer, KeyRound, CheckCircle } from 'lucide-vue-next'



// Réfs locales
const marques = ref([])
const modeles = ref([])
const selectedMarqueId = ref('')



const genres = [
    'AUTOCAR-BUS',
    'CAMIONNETTE',
    'MOTOCYCLETTE',
    'CAMION',
    'TRACTEUR ROUTIER',
    'SEMI-REMORQUE ROUTIER',
    'SEMI REMORQUE ROUTIER',
    'SEMI-REMORQUE',
    'VEHICULE À USAGE SPECIAL',
    'VEHICULE UTILITAIRE',
    'TRACTEUR AGRICOLE',
    'MINI CAR',
    'AUTOCAR',
    'POUR TRAVAUX-INDUSTRIELS',
    'CHARIOT ELEVATEUR',
    'CAMION GRUE',
    'REMORQUE ROUTIERE',
    'REMORQUE AGRICOLE',
    'VOITURE PARTICULIERE',
    'REMORQUE',
    'REMORQUE 3 ESSIEUX',
    'CAMION PORTEUR',
    'CHARIOT-ELEVATEUR',
    'VEHICULE A USAGE SPECIAL',
    'FOURGON',
    'QUADRICYCLE',
    'VEHICULE  USAGE SPECIAL',
    'CAR'
]

const types = [
    'Physique',
    'Morale Simple',
    'Morale Transport',
    'Morale Diplomatic',
    'Morale Institution',
    'Morale Gouv'
];


//  Récupère l'objet marque complet
const selectedMarque = computed(() =>
    marques.value.find(marque => marque.id === selectedMarqueId.value)
)

// Récupère l'objet modèle complet
const selectedModele = computed(() =>
    modeles.value.find(modele => modele.id === selectedModeleId.value)
)


const props = defineProps({
    vin: String
})

const selectedModeleId = ref('')
// API fetch
const fetchMarques = async () => {
    try {
        const res = await axios.get('/pdc/get/marque')
        marques.value = res.data
    } catch (err) {
        console.error('Erreur lors du chargement des marques :', err)
    }
}

const fetchModeles = async () => {
    if (!selectedMarqueId.value) {
        modeles.value = []
        return
    }
    try {
        const res = await axios.get(`/pdc/get/model?marque_id=${selectedMarqueId.value}`)
        modeles.value = res.data
    } catch (err) {
        console.error('Erreur lors du chargement des modèles :', err)
    }
}

onMounted(fetchMarques)

// Schéma de validation
const formSchema = toTypedSchema(z.object({
    type: z
        .string({
            required_error: "Le type de personne est obligatoire.",
        })
        .min(3, {
            message: "Le type de personne doit contenir au moins 3 caractères.",
        }),

    firstname: z
        .string({
            required_error: "Le Nom est obligatoire.",
        })
        .min(3, {
            message: 'Le Nom doit contenir au moins 3 caractères.',
        }),

    lastname: z
        .string({
            required_error: "Le Prénom est obligatoire.",
        })
        .min(3, {
            message: 'Le Prénom doit contenir au moins 3 caractères.',
        }),
    adresse: z
        .string({
            required_error: "L'adresse est obligatoire.",
        })
        .min(3, {
            message: "L'adresse doit contenir au moins 3 caractères.",
        }),

    phone: z
        .string({
            required_error: "Le numéro de téléphone est obligatoire.",
        })
        .min(10, {
            message: "Le numéro de téléphone doit contenir au moins 10 caractères.",
        }),
    DateNaissance: z
        .string()
        .min(10, 'La date de naissance est obligatoire.')
        .regex(
            /^(0[1-9]|[12][0-9]|3[01])[-](0[1-9]|1[0-2])[-](19|20)\d{2}$/,
            'Le format doit être jj-mm-aaaa (ex: 01-01-1984).'
        ),
    villeNaissance: z
        .string({
            required_error: "La ville de naissance est obligatoire.",
        })
        .min(3, {
            message: "La ville de naissance doit contenir au moins 3 caractères.",
        }),

    couleurVehicule: z
        .string({
            required_error: "La couleur du véhicule est obligatoire.",
        })
        .min(3, {
            message: "La couleur du véhicule doit contenir au moins 3 caractères.",
        }),

    carrosserie: z
        .string({
            required_error: "La carrosserie est obligatoire.",
        })
        .min(3, {
            message: "La carrosserie doit contenir au moins 3 caractères.",
        }),

    typeTechnique: z
        .string({
            required_error: "Le type technique est obligatoire.",
        })
        .min(3, {
            message: "Le type technique doit contenir au moins 3 caractères.",
        }),

    genre: z
        .string({
            required_error: "Le genre est obligatoire.",
        })
        .min(3, {
            message: "Le genre doit contenir au moins 3 caractères.",
        }),

    ptac: z
        .string({
            required_error: "Le poids total en charge (PTAC) est obligatoire.",
        })
        .min(2, {
            message: "Le poids total en charge (PTAC) doit contenir au moins 2 caractères.",
        })
        .refine((val) => !isNaN(Number(val)), {
            message: "Le poids total en charge (PTAC) doit être un nombre.",
        })
        .transform((val) => Number(val)),

    pu: z
        .string({
            required_error: "Le poids utile (PU) est obligatoire.",
        })
        .min(2, {
            message: "Le poids utile utile (PU) doit contenir au moins 2 caractères.",
        })
        .refine((val) => !isNaN(Number(val)), {
            message: "Le poids utile (PU) doit être un nombre.",
        })
        .transform((val) => Number(val)),
    pv: z
        .string({
            required_error: "Le poids à vide (PV) est obligatoire.",
        })
        .min(3, {
            message: "Le poids à vide (PV) doit contenir au moins 3 caractères.",
        }),

    puissance: z
        .string({
            required_error: "La puissance administrative est obligatoire.",
        })
        .min(1, {
            message: "La puissance administrative doit contenir au moins 1 caractères.",
        }),

    placesAssises: z
        .string({
            required_error: "Le nombre de places assises est obligatoire.",
        })
        .min(1, {
            message: "Le nombre de places assises doit contenir au moins 1 caractères.",
        })
        .refine((val) => !isNaN(Number(val)), {
            message: "Le nombre de places assises doit être un nombre.",
        })
        .transform((val) => Number(val)),

    sourcesEnergie: z
        .string({
            required_error: "Les sources d'énergie sont obligatoires.",
        })
        .min(3, {
            message: "Les sources d'énergie doivent contenir au moins 3 caractères.",
        }),

    nombreEssieux: z
        .string({
            required_error: "Le nombre d'essieux est obligatoire.",
        })
        .min(1, {
            message: "Le nombre d'essieux doit contenir au moins 1 caractères.",
        }),
    // files: z
    //     .any()
    //     .array(z.instanceof(File))
    //     .nonempty("Un fichier est requis.")
}))

// Valeurs du formulaire
const form = ref({
    firstname: '',
    lastname: '',
    email: '',
    adresse: '',
    phone: '',
    typePersonne: '',
    district: '',
    villeNaissance: '',
    vin: props.vin,
    marqueVehicule: '',
    modelVehicule: '',
    couleurVehicule: '',
    carrosserie: '',
    typeTechnique: '',
    genre: '',
    ptac: '',
    pu: '',
    pv: '',
    puissance: '',
    placesAssises: '',
    sourcesEnergie: '',
    nombreEssieux: '',
    type: '',
    nomEntreprise: null,
    registreCommerce: null,
    representantLegal: null,
    numeroTelephone: null,
    compteContribuable: null,
    DateNaissanceRepresantant: null,
    ProfessionRepresantant: null,
    prefecture: null,
    prefecture_client: null,
    sousPrefecture: null,
    sousPrefectureClient: null,
    region: null,

});

//erreur fichier si type == moral
const typeError = ref('');
const type_pieceError = ref('');
const nomEntrepriseError = ref('');
const registreCommerceError = ref('');
const representantLegalError = ref('');
const numeroTelephoneError = ref('');
const ProfessionRepresantantError = ref('');
const compteContribuableError = ref('');
const DateNaissanceRepresantantError = ref('');
const marqueVehiculeError = ref('');
const modelVehiculeError = ref('');




// Validation
const { handleSubmit } = useValidationForm({
    validationSchema: formSchema,
    initialValues: form.value,
});

// Modal & résumé
const showSummary = ref(false);
const formDataSummary = ref({
    sex: '',
    firstname: '',
    lastname: '',
    email: '',
    adresse: '',
    phone: '',
    typePersonne: '',
    district: '',
    villeNaissance: '',
    vin: '',
    marqueVehicule: '',
    modelVehicule: '',
    couleurVehicule: '',
    carrosserie: '',
    typeTechnique: '',
    genre: '',
    ptac: '',
    pu: '',
    pv: '',
    puissance: '',
    placesAssises: '',
    sourcesEnergie: '',
    nombreEssieux: '',
    type: '',
    type_piece: '',
    DateCirculation: '',
    AnneeProduction: '',
    DateNaissanceRepresantant: '',
    ProfessionRepresantant: '',
    usage: '',
    codeRegion: '',
});



// Afficher le résumé dans le modal
const onSubmit = handleSubmit((values) => {
    // Reset all errors
    // typeError.value = '';
    type_pieceError.value = '';
    nomEntrepriseError.value = '';
    nomEntrepriseError.value = '';
    registreCommerceError.value = '';
    representantLegalError.value = '';
    numeroTelephoneError.value = '';
    DateNaissanceRepresantantError.value = '';
    ProfessionRepresantantError.value = '';
    compteContribuableError.value = '';
    marqueVehiculeError.value = '';
    modelVehiculeError.value = '';

    const errors = []; // Liste des erreurs

    if (form.value.type != "Physique") {
        if (!form.value.nomEntreprise) {
            const msg = 'Entrez le nom de l\'entreprise.';
            nomEntrepriseError.value = msg;
            errors.push(msg);
        }
        if (!form.value.registreCommerce) {
            const msg = 'Entrez le numéro de registre de commerce.';
            registreCommerceError.value = msg;
            errors.push(msg);
        }
        if (!form.value.representantLegal) {
            const msg = 'Entrez le nom du representant legal.';
            representantLegalError.value = msg;
            errors.push(msg);
        }
        if (!form.value.numeroTelephone) {
            const msg = 'Entrez le numéro de telephone.';
            numeroTelephoneError.value = msg;
            errors.push(msg);
        }
        if (!form.value.ProfessionRepresantant) {
            const msg = 'Entrez le numéro de telephone du representant legal.';
            ProfessionRepresantantError.value = msg;
            errors.push(msg);
        }
        if (!form.value.DateNaissanceRepresantant) {
            const msg = 'Entrez la date de naissance du representant legal.';
            DateNaissanceRepresantantError.value = msg;
            errors.push(msg);
        }


        if (!form.value.compteContribuable) {
            const msg = 'Entrez le numéro de compte contribuable.';
            compteContribuableError.value = msg;
            errors.push(msg);
        }

    }

    // Si erreurs → on affiche tout dans le toast
    if (errors.length > 0) {
        toast.error(errors.join('\n'));
        return;
    }

    formDataSummary.value = {
        ...values,
        type: form.value.type,
        nomEntreprise: form.value.nomEntreprise,
        registreCommerce: form.value.registreCommerce,
        representantLegal: form.value.representantLegal,
        numeroTelephone: form.value.numeroTelephone,
        compteContribuable: form.value.compteContribuable,
        prefecture: form.value.prefecture,
        sousPrefecture: form.value.sousPrefecture,
        prefecture_client: form.value.prefecture_client,
        sousPrefectureClient: form.value.sousPrefectureClient,
        region: form.value.region,
        email: form.value.email,
        district: form.value.district,
        typePersonne: form.value.type,
        sex: form.value.civilite,
        vin: form.value.vin,
        dateCirculation: form.value.DateCirculation,
        anneeProduction: form.value.AnneeProduction,
        dateNaissanceRepresantant: form.value.DateNaissanceRepresantant,
        professionRepresantant: form.value.ProfessionRepresantant,
        usage: form.value.usage,
        codeRegion: form.value.codeRegion,
        marqueVehicule: selectedMarque.value,
        modelVehicule: selectedModele.value,
    };
    console.log(formDataSummary.value);
    showSummary.value = true;
});

// Envoi final

function submitFinal() {
    const data = formDataSummary.value;
    axios.post('/pdc/immatriculation/save/data', data)
        .then(response => {
            const res = response.data;
            console.log('Réponse serveur :', res);

            if (res.success) {
                toast.success(res.message || 'Information enregistré avec succès !');

                setTimeout(() => {
                    // Redirection avec l'ID du dossier
                    window.location.href = `/pdc/immatriculation/receipt/${res.data.id}`;
                }, 1500);
            } else {
                toast.error(res.message || "Une erreur est survenue lors de l'enregistrement.");
            }
        })
        .catch(error => {
            console.error('Erreur :', error);

            const errMsg = error.response?.data?.message || 'Erreur lors de l’enregistrement des données.';
            toast.error(errMsg);
        });

}







</script>


<script>
import Main from '/resources/js/Pages/Main.vue';
import BoutonRetour from "/resources/js/components/BoutonRetour.vue";

export default {
    layout: Main,
};
</script>
