<template>
    <!-- Header sticky -->
    <div
        class="sticky top-[-30px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8 py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <h4 class="text-2xl font-bold tracking-tight">Post-immatriculation</h4>
        <div class="flex items-center space-x-2">
            <Link :href="route('show.pdc.post.immatriculation')">
            <BoutonRetour />
            </Link>
        </div>
    </div>

    <!-- Contenu scrollable -->
    <div class="rounded-lg dark:border-gray-700">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <Card class="h-full flex flex-col">
                <ScrollArea class="h-full w-full rounded-md border">
                    <div class="p-8">
                        <form @submit="onSubmit" novalidate>
                            <!-- Informations du véhicule -->
                            <div class="gap-4 md:gap-6 mt-10" v-show="selected.length > 0">
                                <h3 class="text-md font-semibold mt-8 mb-8">
                                    Informations du véhicule
                                    <samp class="text-[#ca7600]">({{ form.vin }})</samp>
                                </h3>

                                <!-- Première grille -->
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3 mb-5">
                                    <!-- Couleur -->
                                    <FormField v-slot="{ componentField }" name="couleurVehicule">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Couleur du véhicule</FormLabel>
                                            <FormControl>
                                                <Select v-bind="componentField" autocomplete="couleurVehicule" v-model="form.couleurVehicule
                                                    " :disabled="!selected.includes('10')">
                                                    <SelectTrigger class="w-full">
                                                        <SelectValue placeholder="Sélectionner une couleur" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            <SelectLabel>Couleurs
                                                                disponibles</SelectLabel>
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
                                            <FormLabel>Carrosserie</FormLabel>
                                            <FormControl>
                                                <Input placeholder="Berline" v-bind="componentField"
                                                    :disabled="!selected.includes('22')" autocomplete="carrosserie"
                                                    v-model="form.carrosserie" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>

                                    <!-- typeTechnique -->
                                    <FormField v-slot="{ componentField }" name="typeTechnique">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Type Techniques</FormLabel>
                                            <FormControl>
                                                <Input placeholder="type Technique" v-bind="componentField"
                                                    :disabled="!selected.includes('19')" autocomplete="typeTechnique"
                                                    v-model="form.typeTechnique" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </div>

                                <!-- Deuxième grille -->
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3 mb-5">
                                    <FormField v-slot="{ componentField }" name="usage">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Usage</FormLabel>
                                            <FormControl>
                                                <Select v-bind="componentField" autocomplete="couleurVehicule"
                                                    v-model="form.usage" :disabled="!selected.includes('25')">
                                                    <SelectTrigger class="w-full">
                                                        <SelectValue placeholder="Sélectionner l'usage" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            <SelectLabel>Usage
                                                            </SelectLabel>
                                                            <SelectItem value="Public">Public</SelectItem>
                                                            <SelectItem value="Privé">Privé</SelectItem>
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>
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
                                                    :disabled="!selected.includes('16')" v-model="form.sourcesEnergie
                                                        ">
                                                    <SelectTrigger class="w-full">
                                                        <SelectValue placeholder="Sélectionner une Source d’énergie" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            <SelectLabel>Sources
                                                                d’énergie</SelectLabel>
                                                            <SelectItem value="ESSENCE">ESSENCE</SelectItem>
                                                            <SelectItem value="GAS-OIL">GAS-OIL</SelectItem>
                                                            <SelectItem value="SANS ENERGIE">SANS
                                                                ENERGIE</SelectItem>
                                                            <SelectItem value="ELECTRICITE">ELECTRICITE</SelectItem>
                                                            <SelectItem value="AUTRE">AUTRE</SelectItem>
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>

                                    <!-- Code de zone -->
                                    <FormField v-slot="{ componentField }" name="codeRegion">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Code de région *</FormLabel>
                                            <FormControl>
                                                <Select v-bind="componentField" v-model="form.codeRegion"
                                                    :disabled="!selected.includes('28')">
                                                    <SelectTrigger class="w-full">
                                                        <SelectValue placeholder="Sélectionner le code de région" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectGroup>
                                                            <SelectLabel>Districts</SelectLabel>
                                                            <SelectItem v-for="item in codes_regions" :key="item.code"
                                                                :value="item.code
                                                                    ">
                                                                {{
                                                                    item.district
                                                                }}
                                                            </SelectItem>
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </div>
                            </div>

                            <!-- a revoir -->
                            <div v-show="mutation">
                                <h1 class="text-md font-semibold mt-14">
                                    Changement de propriétaire : MUTATION
                                </h1>
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3 mt-6">
                                    <!-- type -->
                                    <FormField v-slot="{ componentField }" name="type">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Type de personne</FormLabel>
                                            <FormControl>
                                                <Select v-bind="componentField" v-model="form.typePersonne">
                                                    <SelectTrigger class="w-full">
                                                        <SelectValue placeholder="Sélectionner le type de personne" />
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
                                </div>
                                <!-- Informations du propriétaire -->
                                <h3 class="text-md font-semibold mt-6 mb-6">
                                    Informations du propriétaire
                                </h3>
                                <div class="flex flex-1 flex-col gap-4 md:gap-6">
                                    <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3">
                                        <FormField v-slot="{ componentField }" name="civilite">
                                            <FormItem v-auto-animate>
                                                <FormLabel>Civilité</FormLabel>
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
                                                    <Input placeholder="Ben" v-bind="componentField"
                                                        autocomplete="lastname" v-model="form.lastname" />
                                                </FormControl>
                                                <FormMessage />
                                            </FormItem>
                                        </FormField>
                                    </div>
                                    <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3">
                                        <FormField v-slot="{ componentField }" name="phone">
                                            <FormItem v-auto-animate>
                                                <FormLabel>Numéro de
                                                    téléphone*</FormLabel>
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
                                        <!-- Date de naissance -->
                                        <FormField v-slot="{ componentField }" name="DateNaissance">
                                            <FormItem v-auto-animate>
                                                <FormLabel>Date de
                                                    naissance*</FormLabel>
                                                <FormControl>
                                                    <Input placeholder="01-01-1984" v-bind="componentField"
                                                        autocomplete="DateNaissance" v-model="form.DateNaissance
                                                            " />
                                                </FormControl>
                                                <FormMessage />
                                            </FormItem>
                                        </FormField>
                                    </div>
                                    <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3">
                                        <!-- ville de naissance  -->
                                        <FormField v-slot="{ componentField }" name="villeNaissance">
                                            <FormItem v-auto-animate>
                                                <FormLabel>Ville de
                                                    naissance*</FormLabel>
                                                <FormControl>
                                                    <Input placeholder="Bouaké" v-bind="componentField"
                                                        autocomplete="villeNaissance" v-model="form.villeNaissance
                                                            " />
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
                                                        v-bind="componentField" autocomplete="email"
                                                        v-model="form.email" />
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
                                        <FormField v-slot="{ componentField }" name="prefecture">
                                            <FormItem v-auto-animate>
                                                <FormLabel>Préfecture</FormLabel>
                                                <FormControl>
                                                    <Input placeholder="prefecture 1" v-bind="componentField"
                                                        autocomplete="prefecture" v-model="form.prefecture
                                                            " />
                                                </FormControl>
                                                <FormMessage />
                                            </FormItem>
                                        </FormField>
                                        <!-- Sous Préfecture -->
                                        <FormField v-slot="{ componentField }" name="sousPrefecture">
                                            <FormItem v-auto-animate>
                                                <FormLabel>Sous Préfecture</FormLabel>
                                                <FormControl>
                                                    <Input placeholder="Sous prefecture 1" v-bind="componentField"
                                                        autocomplete="SousPrefecture" v-model="form.sousPrefecture
                                                            " />
                                                </FormControl>
                                                <FormMessage />
                                            </FormItem>
                                        </FormField>
                                    </div>
                                </div>
                            </div>

                            <!-- Informations de l'entreprise -->
                            <div v-show="form.typePersonne != 'Physique' && mutation
                                " class="mt-10">
                                <h3 class="text-md font-semibold mb-6">
                                    Informations de l'entreprise
                                </h3>
                                <div class="grid gap-3 md:grid-cols-2 md:gap-10 lg:grid-cols-3">
                                    <div>
                                        <p class="text-sm font-medium mb-2">
                                            Nom de l'entreprise
                                        </p>
                                        <Input class="py-0 h-9" v-model="form.nomEntreprise" />
                                        <p v-if="nomEntrepriseError" class="text-red-600 text-sm mt-1">
                                            {{ nomEntrepriseError }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2">
                                            Numéro du registre de Commerce
                                        </p>
                                        <Input class="py-0 h-9" v-model="form.registreCommerce" />
                                        <p v-if="registreCommerceError" class="text-red-600 text-sm mt-1">
                                            {{ registreCommerceError }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2">
                                            Nom et Prénom du représentant légal
                                        </p>
                                        <Input class="py-0 h-9" v-model="form.representantLegal" />
                                        <p v-if="representantLegalError" class="text-red-600 text-sm mt-1">
                                            {{ representantLegalError }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2">
                                            Téléphone du représentant légal
                                        </p>
                                        <Input class="py-0 h-9" v-model="form.numeroTelephone" />
                                        <p v-if="numeroTelephoneError" class="text-red-600 text-sm mt-1">
                                            {{ numeroTelephoneError }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2">
                                            Date de naissance du représentant
                                            légal
                                        </p>
                                        <Input class="py-0 h-9" v-model="form.DateNaissanceRepresantant
                                            " />
                                        <p v-if="
                                            DateNaissanceRepresantantError
                                        " class="text-red-600 text-sm mt-1">
                                            {{ DateNaissanceRepresantantError }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2">
                                            Profession du représentant légal
                                        </p>
                                        <Input class="py-0 h-9" v-model="form.ProfessionRepresantant
                                            " />
                                        <p v-if="ProfessionRepresantantError" class="text-red-600 text-sm mt-1">
                                            {{ ProfessionRepresantantError }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2">
                                            Numéro de compte contribuale
                                        </p>
                                        <Input class="py-0 h-9" v-model="form.compteContribuable" />
                                        <p v-if="compteContribuableError" class="text-red-600 text-sm mt-1">
                                            {{ compteContribuableError }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2">
                                            Péfecture
                                        </p>
                                        <Input class="py-0 h-9" v-model="form.prefecture" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2">
                                            Sous-Péfecture
                                        </p>
                                        <Input class="py-0 h-9" v-model="form.sousPrefecture" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium mb-2">
                                            Région
                                        </p>
                                        <Input class="py-0 h-9" v-model="form.region" />
                                    </div>
                                </div>
                            </div>

                            <!-- gage /levée de gage -->
                            <div class="flex items-center gap-10 pt-6 mt-6 mb-8">
                                <!-- Gage -->
                                <div class="flex items-center gap-2">
                                    <input type="radio" id="gage" value="gage" v-model="form.gage" class="h-4 w-4" />
                                    <label for="gage" class="text-sm">Gage sur la carte grise</label>
                                </div>

                                <!-- Levée de gage / Autorisation -->
                                <div class="flex items-center gap-2">
                                    <input type="radio" id="leveGage" value="leveGage" v-model="form.gage"
                                        class="h-4 w-4" />
                                    <label for="leveGage" class="text-sm">Levée de gage / Autorisation</label>
                                </div>
                            </div>

                            <!-- show Summary -->
                            <div v-if="showSummary"
                                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 p-4 overflow-auto">
                                <div
                                    class="bg-white dark:bg-gray-800 rounded-2xl p-8 w-full max-w-4xl shadow-2xl space-y-6">
                                    <h3 class="text-2xl font-bold text-center mb-2">
                                        Résumé des informations
                                    </h3>
                                    <!-- Informations du propriétaire -->
                                    <div>
                                        <h4 class="text-lg font-semibold mb-8 text-center">
                                            --------------- Informations du
                                            propriétaire ---------------
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <!-- <p>toto : {{ formDataSummary }}</p> -->
                                            <!-- Civilité -->
                                            <p>
                                                <strong>
                                                    <span v-if="
                                                        mutation &&
                                                        formDataSummary.sex !==
                                                        data.civilite
                                                    ">Nouvelle</span>
                                                    Civilité :
                                                </strong>
                                                <samp :class="{
                                                    'text-[#ca7600]':
                                                        mutation &&
                                                        formDataSummary.sex !==
                                                        data.civilite,
                                                }">
                                                    {{ formDataSummary.sex }}
                                                </samp>
                                            </p>
                                            <p v-if="
                                                mutation &&
                                                formDataSummary.sex !==
                                                data.civilite
                                            ">
                                                <strong>Ancienne Civilité :</strong>
                                                <samp class="text-[#ca7600]">{{
                                                    data.civilite
                                                }}</samp>
                                            </p>
                                            <!-- Nom -->
                                            <p>
                                                <strong>
                                                    <span v-if="
                                                        mutation &&
                                                        formDataSummary.lastname !==
                                                        data.lastname
                                                    ">Nouveau</span>
                                                    Nom :
                                                </strong>
                                                <samp :class="{
                                                    'text-[#ca7600]':
                                                        mutation &&
                                                        formDataSummary.lastname !==
                                                        data.lastname,
                                                }">
                                                    {{
                                                        formDataSummary.lastname
                                                    }}
                                                </samp>
                                            </p>
                                            <p v-if="
                                                mutation &&
                                                formDataSummary.lastname !==
                                                data.lastname
                                            ">
                                                <strong>Ancien Nom :</strong>
                                                <samp class="text-[#ca7600]">{{
                                                    data.lastname
                                                }}</samp>
                                            </p>
                                            <!-- Prénom -->
                                            <p>
                                                <strong>
                                                    <span v-if="
                                                        mutation &&
                                                        formDataSummary.firstname !==
                                                        data.firstname
                                                    ">Nouveau</span>
                                                    Prénom :
                                                </strong>
                                                <samp :class="{
                                                    'text-[#ca7600]':
                                                        mutation &&
                                                        formDataSummary.firstname !==
                                                        data.firstname,
                                                }">
                                                    {{
                                                        formDataSummary.firstname
                                                    }}
                                                </samp>
                                            </p>
                                            <p v-if="
                                                mutation &&
                                                formDataSummary.firstname !==
                                                data.firstname
                                            ">
                                                <strong>Ancien Prénom :</strong>
                                                <samp class="text-[#ca7600]">{{
                                                    data.firstname
                                                }}</samp>
                                            </p>
                                            <!-- Email -->
                                            <p>
                                                <strong>
                                                    <span v-if="
                                                        mutation &&
                                                        formDataSummary.email !==
                                                        data.email
                                                    ">Nouvel</span>
                                                    Email :
                                                </strong>
                                                <samp :class="{
                                                    'text-[#ca7600]':
                                                        mutation &&
                                                        formDataSummary.email !==
                                                        data.email,
                                                }">
                                                    {{ formDataSummary.email }}
                                                </samp>
                                            </p>
                                            <p v-if="
                                                mutation &&
                                                formDataSummary.email !==
                                                data.email
                                            ">
                                                <strong>Ancien Email :</strong>
                                                <samp class="text-[#ca7600]">{{
                                                    data.email
                                                }}</samp>
                                            </p>
                                            <!-- Adresse -->
                                            <p>
                                                <strong>
                                                    <span v-if="
                                                        mutation &&
                                                        formDataSummary.adresse !==
                                                        data.adresse
                                                    ">Nouvelle</span>
                                                    Adresse :
                                                </strong>
                                                <samp :class="{
                                                    'text-[#ca7600]':
                                                        mutation &&
                                                        formDataSummary.adresse !==
                                                        data.adresse,
                                                }">
                                                    {{
                                                        formDataSummary.adresse
                                                    }}
                                                </samp>
                                            </p>
                                            <p v-if="
                                                mutation &&
                                                formDataSummary.adresse !==
                                                data.adresse
                                            ">
                                                <strong>Ancienne Adresse :</strong>
                                                <samp class="text-[#ca7600]">{{
                                                    data.adresse
                                                }}</samp>
                                            </p>
                                            <!-- Ville de naissance -->
                                            <p>
                                                <strong>
                                                    <span v-if="
                                                        mutation &&
                                                        formDataSummary.villeNaissance !==
                                                        data.villeNaissance
                                                    ">Nouvelle</span>
                                                    Ville de naissance :
                                                </strong>
                                                <samp :class="{
                                                    'text-[#ca7600]':
                                                        mutation &&
                                                        formDataSummary.villeNaissance !==
                                                        data.villeNaissance,
                                                }">
                                                    {{
                                                        formDataSummary.villeNaissance
                                                    }}
                                                </samp>
                                            </p>
                                            <p v-if="
                                                mutation &&
                                                formDataSummary.villeNaissance !==
                                                data.villeNaissance
                                            ">
                                                <strong>Ancienne Ville de naissance
                                                    :</strong>
                                                <samp class="text-[#ca7600]">{{
                                                    data.villeNaissance
                                                }}</samp>
                                            </p>
                                            <!-- date de naissance -->
                                            <p>
                                                <strong>
                                                    <span v-if="
                                                        mutation &&
                                                        formDataSummary.DateNaissance !==
                                                        data.DateNaissance
                                                    ">Nouvelle</span>
                                                    Date de naissance :
                                                </strong>
                                                <samp :class="{
                                                    'text-[#ca7600]':
                                                        mutation &&
                                                        formDataSummary.DateNaissance !==
                                                        data.DateNaissance,
                                                }">
                                                    {{
                                                        formDataSummary.DateNaissance
                                                    }}
                                                </samp>
                                            </p>
                                            <p v-if="
                                                mutation &&
                                                formDataSummary.DateNaissance !==
                                                data.DateNaissance
                                            ">
                                                <strong>Ancienne Date de naissance
                                                    :</strong>
                                                <samp class="text-[#ca7600]">{{
                                                    data.DateNaissance
                                                }}</samp>
                                            </p>
                                            <!-- Téléphone -->
                                            <p>
                                                <strong>
                                                    <span v-if="
                                                        mutation &&
                                                        formDataSummary.phone !==
                                                        data.phone
                                                    ">Nouveau</span>
                                                    Téléphone :
                                                </strong>
                                                <samp :class="{
                                                    'text-[#ca7600]':
                                                        mutation &&
                                                        formDataSummary.phone !==
                                                        data.phone,
                                                }">
                                                    {{ formDataSummary.phone }}
                                                </samp>
                                            </p>
                                            <p v-if="
                                                mutation &&
                                                formDataSummary.phone !==
                                                data.phone
                                            ">
                                                <strong>Ancien Téléphone :</strong>
                                                <samp class="text-[#ca7600]">{{
                                                    data.phone
                                                }}</samp>
                                            </p>
                                        </div>
                                    </div>
                                    <Separator class="my-4" />
                                    <!-- Informations du véhicule -->

                                    <div>
                                        <h4 class="text-lg font-semibold mb-8 text-center">
                                            --------------- Informations du véhicule ---------------
                                        </h4>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

                                            <!-- Marque -->
                                            <p>
                                                <strong>Marque :</strong>
                                                {{ formDataSummary.marqueVehicule?.nom ?? "-" }}
                                            </p>

                                            <!-- Modèle -->
                                            <p>
                                                <strong>Model :</strong>
                                                {{ formDataSummary.modelVehicule?.nom ?? "-" }}
                                            </p>

                                            <!-- Couleur (ID: 4) -->
                                            <p>
                                                <strong>
                                                    <span v-if="selected.includes('10') &&
                                                        formDataSummary.couleurVehicule != data.couleurVehicule">
                                                        Nouvelle
                                                    </span>
                                                    Couleur :
                                                </strong>
                                                <samp :class="{
                                                    'text-[#ca7600]':
                                                        selected.includes('10') &&
                                                        formDataSummary.couleurVehicule != data.couleurVehicule
                                                }">
                                                    {{ formDataSummary.couleurVehicule }}
                                                </samp>
                                            </p>

                                            <p v-if="selected.includes('10') &&
                                                formDataSummary.couleurVehicule != data.couleurVehicule">
                                                <strong>Ancienne Couleur : </strong>
                                                <samp class="text-[#ca7600]">
                                                    {{ data.couleurVehicule }}
                                                </samp>
                                            </p>

                                            <!-- Carrosserie (ID: 8) -->
                                            <p>
                                                <strong>
                                                    <span v-if="selected.includes('22') &&
                                                        formDataSummary.carrosserie != data.carrosserie">
                                                        Nouvelle
                                                    </span>
                                                    Carrosserie :
                                                </strong>
                                                <samp :class="{
                                                    'text-[#ca7600]':
                                                        selected.includes('22') &&
                                                        formDataSummary.carrosserie != data.carrosserie
                                                }">
                                                    {{ formDataSummary.carrosserie }}
                                                </samp>
                                            </p>

                                            <p v-if="selected.includes('22') &&
                                                formDataSummary.carrosserie != data.carrosserie">
                                                <strong>Ancienne Carrosserie :</strong>
                                                <samp class="text-[#ca7600]">
                                                    {{ data.carrosserie }}
                                                </samp>
                                            </p>

                                            <!-- Type Technique (ID: 7) -->
                                            <p>
                                                <strong>
                                                    <span v-if="selected.includes('19') &&
                                                        formDataSummary.typeTechnique != data.typeTechnique">
                                                        Nouveau
                                                    </span>
                                                    Type technique :
                                                </strong>
                                                <samp :class="{
                                                    'text-[#ca7600]':
                                                        selected.includes('19') &&
                                                        formDataSummary.typeTechnique != data.typeTechnique
                                                }">
                                                    {{ formDataSummary.typeTechnique }}
                                                </samp>
                                            </p>

                                            <p v-if="selected.includes('19') &&
                                                formDataSummary.typeTechnique != data.typeTechnique">
                                                <strong>Ancien Type technique :</strong>
                                                <samp class="text-[#ca7600]">
                                                    {{ data.typeTechnique }}
                                                </samp>
                                            </p>

                                            <!-- Usage (ID: 9) -->
                                            <p>
                                                <strong>
                                                    <span v-if="selected.includes('25') &&
                                                        formDataSummary.usage != data.usage">
                                                        Nouvel
                                                    </span>
                                                    Usage :
                                                </strong>
                                                <samp :class="{
                                                    'text-[#ca7600]':
                                                        selected.includes('25') &&
                                                        formDataSummary.usage != data.usage
                                                }">
                                                    {{ formDataSummary.usage }}
                                                </samp>
                                            </p>

                                            <p v-if="selected.includes('25') &&
                                                formDataSummary.usage != data.usage">
                                                <strong>Ancien Usage :</strong>
                                                <samp class="text-[#ca7600]">
                                                    {{ data.usage }}
                                                </samp>
                                            </p>

                                            <!-- Sources d’énergie (ID: 6) -->
                                            <p>
                                                <strong>
                                                    <span v-if="selected.includes('16') &&
                                                        formDataSummary.sourcesEnergie != data.sourcesEnergie">
                                                        Nouvelles
                                                    </span>
                                                    Sources d’énergie :
                                                </strong>
                                                <samp :class="{
                                                    'text-[#ca7600]':
                                                        selected.includes('16') &&
                                                        formDataSummary.sourcesEnergie != data.sourcesEnergie
                                                }">
                                                    {{ formDataSummary.sourcesEnergie }}
                                                </samp>
                                            </p>

                                            <p v-if="selected.includes('16') &&
                                                formDataSummary.sourcesEnergie != data.sourcesEnergie">
                                                <strong>Anciennes Sources d’énergie :</strong>
                                                <samp class="text-[#ca7600]">
                                                    {{ data.sourcesEnergie }}
                                                </samp>
                                            </p>

                                            <!-- Code Région (ID: 10) -->
                                            <p>
                                                <strong>
                                                    <span v-if="selected.includes('28') &&
                                                        formDataSummary.codeRegion != data.codeRegion">
                                                        Nouveau
                                                    </span>
                                                    Code de Région :
                                                </strong>
                                                <samp :class="{
                                                    'text-[#ca7600]':
                                                        selected.includes('28') &&
                                                        formDataSummary.codeRegion != data.codeRegion
                                                }">
                                                    {{ formDataSummary.codeRegion }}
                                                </samp>
                                            </p>

                                            <p v-if="selected.includes('28') &&
                                                formDataSummary.codeRegion != data.codeRegion">
                                                <strong>Ancien Code de Région :</strong>
                                                <samp class="text-[#ca7600]">
                                                    {{ data.codeRegion }}
                                                </samp>
                                            </p>

                                            <!-- Autres infos -->
                                            <p>
                                                <strong>Genre :</strong>
                                                {{ formDataSummary.genre }}
                                            </p>

                                            <p>
                                                <strong>Poids Total en charge (PTAC) :</strong>
                                                {{ formDataSummary.ptac }}
                                            </p>

                                            <p>
                                                <strong>Poids Utile (PU) :</strong>
                                                {{ formDataSummary.pu }}
                                            </p>

                                            <p>
                                                <strong>Poids à Vide (PV) :</strong>
                                                {{ formDataSummary.pv }}
                                            </p>

                                            <p>
                                                <strong>Puissance administrative :</strong>
                                                {{ formDataSummary.puissance }}
                                            </p>

                                            <p>
                                                <strong>Places Assises :</strong>
                                                {{ formDataSummary.placesAssises }}
                                            </p>

                                            <p>
                                                <strong>Nbre d’Essieux :</strong>
                                                {{ formDataSummary.nombreEssieux }}
                                            </p>

                                        </div>
                                    </div>
                                    <Separator class="my-4" />
                                    <!-- Information de l'entreprise -->
                                    <div v-if="
                                        formDataSummary.typePersonne !=
                                        'Physique'
                                    ">
                                        <h4 class="text-lg font-semibold mb-8 text-center">
                                            --------------- Informations de
                                            l'entreprise ---------------
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <!-- Nom de l'entreprise -->
                                            <p>
                                                <strong>
                                                    <span v-if="
                                                        mutation &&
                                                        formDataSummary.nomEntreprise !==
                                                        data.nomEntreprise
                                                    ">Nouveau</span>
                                                    Nom de l'entreprise :
                                                </strong>
                                                <samp :class="{
                                                    'text-[#ca7600]':
                                                        mutation &&
                                                        formDataSummary.nomEntreprise !==
                                                        data.nomEntreprise,
                                                }">
                                                    {{
                                                        formDataSummary.nomEntreprise
                                                    }}
                                                </samp>
                                            </p>
                                            <p v-if="
                                                mutation &&
                                                formDataSummary.nomEntreprise !==
                                                data.nomEntreprise
                                            ">
                                                <strong>Ancien Nom de l'entreprise
                                                    :
                                                </strong>
                                                <samp class="text-[#ca7600]">{{
                                                    data.nomEntreprise
                                                }}</samp>
                                            </p>

                                            <!-- Registre de commerce -->
                                            <p>
                                                <strong>
                                                    <span v-if="
                                                        mutation &&
                                                        formDataSummary.registreCommerce !==
                                                        data.registreCommerce
                                                    ">Nouveau</span>
                                                    Registre de commerce :
                                                </strong>
                                                <samp :class="{
                                                    'text-[#ca7600]':
                                                        mutation &&
                                                        formDataSummary.registreCommerce !==
                                                        data.registreCommerce,
                                                }">
                                                    {{
                                                        formDataSummary.registreCommerce
                                                    }}
                                                </samp>
                                            </p>
                                            <p v-if="
                                                mutation &&
                                                formDataSummary.registreCommerce !==
                                                data.registreCommerce
                                            ">
                                                <strong>Ancien Registre :</strong>
                                                <samp class="text-[#ca7600]">{{
                                                    data.registreCommerce
                                                }}</samp>
                                            </p>

                                            <!-- Représentant légal -->
                                            <p>
                                                <strong>
                                                    <span v-if="
                                                        mutation &&
                                                        formDataSummary.representantLegal !==
                                                        data.representantLegal
                                                    ">Nouveau</span>
                                                    Représentant Légal :
                                                </strong>
                                                <samp :class="{
                                                    'text-[#ca7600]':
                                                        mutation &&
                                                        formDataSummary.representantLegal !==
                                                        data.representantLegal,
                                                }">
                                                    {{
                                                        formDataSummary.representantLegal
                                                    }}
                                                </samp>
                                            </p>
                                            <p v-if="
                                                mutation &&
                                                formDataSummary.representantLegal !==
                                                data.representantLegal
                                            ">
                                                <strong>Ancien Représentant
                                                    :</strong>
                                                <samp class="text-[#ca7600]">{{
                                                    data.representantLegal
                                                }}</samp>
                                            </p>

                                            <!-- Téléphone -->
                                            <p>
                                                <strong>
                                                    <span v-if="
                                                        mutation &&
                                                        formDataSummary.numeroTelephone !==
                                                        data.numeroTelephone
                                                    ">Nouveau</span>
                                                    Numéro de Téléphone :
                                                </strong>
                                                <samp :class="{
                                                    'text-[#ca7600]':
                                                        mutation &&
                                                        formDataSummary.numeroTelephone !==
                                                        data.numeroTelephone,
                                                }">
                                                    {{
                                                        formDataSummary.numeroTelephone
                                                    }}
                                                </samp>
                                            </p>
                                            <p v-if="
                                                mutation &&
                                                formDataSummary.numeroTelephone !==
                                                data.numeroTelephone
                                            ">
                                                <strong>Ancien Téléphone :</strong>
                                                <samp class="text-[#ca7600]">{{
                                                    data.numeroTelephone
                                                }}</samp>
                                            </p>

                                            <!-- Compte contribuable -->
                                            <p>
                                                <strong>
                                                    <span v-if="
                                                        mutation &&
                                                        formDataSummary.compteContribuable !==
                                                        data.compteContribuable
                                                    ">Nouveau</span>
                                                    Compte Contribuable :
                                                </strong>
                                                <samp :class="{
                                                    'text-[#ca7600]':
                                                        mutation &&
                                                        formDataSummary.compteContribuable !==
                                                        data.compteContribuable,
                                                }">
                                                    {{
                                                        formDataSummary.compteContribuable
                                                    }}
                                                </samp>
                                            </p>
                                            <p v-if="
                                                mutation &&
                                                formDataSummary.compteContribuable !==
                                                data.compteContribuable
                                            ">
                                                <strong>Ancien Compte :</strong>
                                                <samp class="text-[#ca7600]">{{
                                                    data.compteContribuable
                                                }}</samp>
                                            </p>
                                            <!-- Profession représentant -->
                                            <p>
                                                <strong>
                                                    <span v-if="
                                                        mutation &&
                                                        formDataSummary.professionRepresantant !==
                                                        data.professionRepresantant
                                                    ">Nouvelle</span>
                                                    Profession du représentant
                                                    légal :
                                                </strong>
                                                <samp :class="{
                                                    'text-[#ca7600]':
                                                        mutation &&
                                                        formDataSummary.professionRepresantant !==
                                                        data.professionRepresantant,
                                                }">
                                                    {{
                                                        formDataSummary.professionRepresantant
                                                    }}
                                                </samp>
                                            </p>
                                            <p v-if="
                                                mutation &&
                                                formDataSummary.professionRepresantant !==
                                                data.professionRepresantant
                                            ">
                                                <strong>Ancienne Profession
                                                    :</strong>
                                                <samp class="text-[#ca7600]">{{
                                                    data.professionRepresantant
                                                }}</samp>
                                            </p>
                                        </div>
                                    </div>
                                    <!-- Actions -->
                                    <div class="mt-6 flex justify-end gap-4">
                                        <Button @click="showSummary = false" variant="outline"
                                            class="px-6 py-2 rounded-lg">Modifier</Button>
                                        <AlertDialog>
                                            <AlertDialogTrigger as-child>
                                                <Button class=" px-6 py-2 rounded-lg primary-color text-white">
                                                    Valider
                                                </Button>
                                            </AlertDialogTrigger>
                                            <AlertDialogContent>
                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>Etes-vous sur de vouloir continuer?
                                                    </AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        En continuant, vous acceptez de valider les données de
                                                        post-immatriculation.
                                                        Vous serez ensuite redirigé vers la page de validation du
                                                        duplicata.
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel>Annuler</AlertDialogCancel>
                                                    <AlertDialogAction @click="submitFinal">Continuer
                                                    </AlertDialogAction>
                                                </AlertDialogFooter>
                                            </AlertDialogContent>
                                        </AlertDialog>
                                    </div>
                                </div>
                            </div>
                            <!-- Bouton continuer -->
                            <div class="mt-8">
                                <Button @click="showSummary = true">
                                    METTRE À JOUR
                                </Button>
                            </div>
                        </form>
                    </div>
                </ScrollArea>
            </Card>
        </main>
        <Toaster richColors position="top-right" />
    </div>
</template>

<script setup>
import { Button } from "@/components/ui/button";
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { Card } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Toaster, toast } from "vue-sonner";
import { vAutoAnimate } from "@formkit/auto-animate/vue";
import { useForm } from "vee-validate";
import * as z from "zod";
import { toTypedSchema } from "@vee-validate/zod"; // Import ajouté
import { ScrollArea } from "@/components/ui/scroll-area";
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from "@/components/ui/select";
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
import { Separator } from "@/components/ui/separator";

// Constante pour l'ID de la mutation
const MUTATION_ID = "31"; // ID de "Changement de propriétaire : MUTATION"

// Props
const props = defineProps({
    immatriculation: Object,
    marques: Array,
    modeles: Array,
    vin: String,
    dossier: Object,
    entreprise: Object,
    data: Object,
    typeServices: Array, // Liste des services avec leurs IDs
});

// Réfs locales
const marques = ref([]);
const modeles = ref([]);
const selectedMarqueId = ref("");
const selectedModeleId = ref("");
const selected = ref([]); // IDs des services sélectionnés
const mutation = ref(null); // ID de la mutation
const showSummary = ref(false);

// Données statiques
const types = ["Physique", "Morale Simple", "Morale Transport", "Morale Diplomatic", "Morale Institution", "Morale Gouv"];

const codes_regions = [
    { district: "Abidjan", code: "01" },
    { district: "Yamoussoukro", code: "07" },
    { district: "Lacs", code: "13" },
    { district: "Comoé", code: "05" },
    { district: "Denguélé", code: "10" },
    { district: "Gôh-Djiboua", code: "11" },
    { district: "Lagunes", code: "12" },
    { district: "Montagnes", code: "06" },
    { district: "Sassandra-Marahoué", code: "02" },
    { district: "Savanes", code: "03" },
    { district: "Bas-Sassandra", code: "09" },
    { district: "Vallée du Bandama", code: "04" },
    { district: "Woroba", code: "14" },
    { district: "Zanzan", code: "08" },
];

// Initialisation du formulaire avec les données existantes
const data = props.data.immatriculation;
const form = ref({
    firstname: data.firstname || "",
    lastname: data.lastname || "",
    email: data.email || "",
    adresse: data.adresse || "",
    phone: data.phone || "",
    typePersonne: data.typePersonne || "",
    district: data.district || "",
    villeNaissance: data.villeNaissance || "",
    civilite: data.civilite || "",
    DateNaissance: data.DateNaissance || "",
    vin: data.vin || props.vin || "",
    couleurVehicule: data.couleurVehicule || "",
    carrosserie: data.carrosserie || "",
    typeTechnique: data.typeTechnique || "",
    genre: data.genre || "",
    ptac: data.ptac || "",
    pu: data.pu || "",
    pv: data.pv || "",
    puissance: data.puissance || "",
    placesAssises: data.placesAssises.toString() || "",
    sourcesEnergie: data.sourcesEnergie || "",
    nombreEssieux: data.nombreEssieux.toString() || "",
    type: data.typePersonne || "",
    usage: data.usage || "",
    codeRegion: data.codeRegion || "",
    DateCirculation: data.DateCirculation || "",
    AnneeProduction: data.AnneeProduction || "",
    nomEntreprise: data.nomEntreprise || null,
    registreCommerce: data.registreCommerce || null,
    representantLegal: data.representantLegal || null,
    numeroTelephone: data.numeroTelephone || null,
    compteContribuable: data.compteContribuable || null,
    DateNaissanceRepresantant: data.DateNaissanceRepresantant || null,
    ProfessionRepresantant: data.ProfessionRepresantant || null,
    prefecture: data.prefecture || null,
    sousPrefecture: data.sousPrefecture || null,
    region: data.region || null,
    marqueVehicule: data.marqueVehicule || null,
    modelVehicule: data.modelVehicule || null,
    gage: data.gage || null,
});

// Variables d'erreur
const typeError = ref("");
const nomEntrepriseError = ref("");
const registreCommerceError = ref("");
const representantLegalError = ref("");
const numeroTelephoneError = ref("");
const ProfessionRepresantantError = ref("");
const compteContribuableError = ref("");
const DateNaissanceRepresantantError = ref("");

// Schéma de validation avec Zod
const formSchema = toTypedSchema(
    z.object({
        firstname: z.string().min(3, "Le nom doit contenir au moins 3 caractères."),
        lastname: z.string().min(3, "Le prénom doit contenir au moins 3 caractères."),
        adresse: z.string().min(3, "L'adresse doit contenir au moins 3 caractères."),
        phone: z.string().min(10, "Le numéro de téléphone doit contenir au moins 10 caractères."),
        DateNaissance: z.string().min(10, "La date de naissance est obligatoire.")
            .regex(/^(0[1-9]|[12][0-9]|3[01])[-](0[1-9]|1[0-2])[-](19|20)\d{2}$/, "Le format doit être jj-mm-aaaa (ex: 01-01-1984)."),
        villeNaissance: z.string().min(3, "La ville de naissance est obligatoire."),
        couleurVehicule: z.string().min(3, "La couleur du véhicule est obligatoire."),
        carrosserie: z.string().min(3, "La carrosserie est obligatoire."),
        typeTechnique: z.string().min(3, "Le type technique est obligatoire."),
        genre: z.string().min(3, "Le genre est obligatoire."),
        ptac: z.string().min(2, "Le PTAC est obligatoire."),
        pu: z.string().min(2, "Le PU est obligatoire."),
        pv: z.string().min(3, "Le PV est obligatoire."),
        puissance: z.string().min(1, "La puissance administrative est obligatoire."),
        placesAssises: z.string().min(1, "Le nombre de places assises est obligatoire."),
        sourcesEnergie: z.string().min(3, "Les sources d'énergie sont obligatoires."),
        nombreEssieux: z.string().min(1, "Le nombre d'essieux est obligatoire."),
    })
);

// Validation avec VeeValidate
const { handleSubmit } = useForm({
    validationSchema: formSchema,
    initialValues: form.value,
});

// Computed pour les objets complets
const selectedMarque = computed(() => marques.value.find((marque) => marque.id === selectedMarqueId.value));
const selectedModele = computed(() => modeles.value.find((modele) => modele.id === selectedModeleId.value));

// Résumé des données
const formDataSummary = computed(() => ({
    firstname: form.value.firstname,
    lastname: form.value.lastname,
    email: form.value.email,
    adresse: form.value.adresse,
    phone: form.value.phone,
    typePersonne: form.value.typePersonne,
    district: form.value.district,
    villeNaissance: form.value.villeNaissance,
    sex: form.value.civilite,
    DateNaissance: form.value.DateNaissance,
    vin: form.value.vin,
    couleurVehicule: form.value.couleurVehicule,
    carrosserie: form.value.carrosserie,
    typeTechnique: form.value.typeTechnique,
    genre: form.value.genre,
    ptac: form.value.ptac,
    pu: form.value.pu,
    pv: form.value.pv,
    puissance: form.value.puissance,
    placesAssises: form.value.placesAssises,
    sourcesEnergie: form.value.sourcesEnergie,
    nombreEssieux: form.value.nombreEssieux,
    type: form.value.type,
    usage: form.value.usage,
    codeRegion: form.value.codeRegion,
    DateCirculation: form.value.DateCirculation,
    AnneeProduction: form.value.AnneeProduction,
    nomEntreprise: form.value.nomEntreprise,
    registreCommerce: form.value.registreCommerce,
    representantLegal: form.value.representantLegal,
    numeroTelephone: form.value.numeroTelephone,
    compteContribuable: form.value.compteContribuable,
    DateNaissanceRepresantant: form.value.DateNaissanceRepresantant,
    professionRepresantant: form.value.ProfessionRepresantant,
    prefecture: form.value.prefecture,
    sousPrefecture: form.value.sousPrefecture,
    region: form.value.region,
    marqueVehicule: selectedMarque.value,
    modelVehicule: selectedModele.value,
}));

// Fonction de soumission du formulaire
const onSubmit = handleSubmit((values) => {
    const errors = [];
    if (!form.value.type) {
        typeError.value = "Sélectionner le Type de personne";
        errors.push(typeError.value);
    }
    if (form.value.type != "Physique") {
        if (!form.value.nomEntreprise) {
            nomEntrepriseError.value = "Entrez le nom de l'entreprise.";
            errors.push(nomEntrepriseError.value);
        }
        if (!form.value.registreCommerce) {
            registreCommerceError.value = "Entrez le numéro de registre de commerce.";
            errors.push(registreCommerceError.value);
        }
        if (!form.value.representantLegal) {
            representantLegalError.value = "Entrez le nom du représentant légal.";
            errors.push(representantLegalError.value);
        }
        if (!form.value.numeroTelephone) {
            numeroTelephoneError.value = "Entrez le numéro de téléphone.";
            errors.push(numeroTelephoneError.value);
        }
        if (!form.value.compteContribuable) {
            compteContribuableError.value = "Entrez le numéro de compte contribuable.";
            errors.push(compteContribuableError.value);
        }
    }
    if (errors.length > 0) {
        toast.error(errors.join("\n"));
        return;
    }
    showSummary.value = true;
});

// Soumission finale au backend
function submitFinal() {
    const data = {
        ...formDataSummary.value,
        id: props.data.immatriculation.id,// Ajouter l'ID pour la mise à jour
        selected: selected.value,
        mutation: mutation.value
    };
    console.log(data)
    //visité show.new.service.pdc.duplicata

    const vinWithGenre = `${props.data.immatriculation.vin}_${props.genreVehicule.nb_plaque}`;

    const donne = {
        postImtData: data,
        vinWithGenre: vinWithGenre
    };

    router.visit(`/pdc/duplicata/new/service/${encodeURIComponent(JSON.stringify(donne))}`);





    // Récupérer le genre associé


    //     /pdc/duplicata/new/service/{data}
    // router.visit(linnk);    
    // Utiliser PUT ou PATCH pour la mise à jour
    // axios.put(`/pdc/postimmatriculation/${data.id}/update`, data)
    //     .then(response => {
    //         const res = response.data;
    //         console.log('Réponse serveur :', res);

    //         if (res.success) {
    //             toast.success(res.message || 'Information mise à jour avec succès !');
    //             setTimeout(() => {
    //                 // Redirection avec l'ID du dossier
    //                 window.location.href = `/pdc/post/immatriculation/receipt/${res.data.dossier.id}`;
    //             }, 1500);
    //         } else {
    //             toast.error(res.message || "Une erreur est survenue lors de la mise à jour.");
    //         }
    //     })
    //     .catch(error => {
    //         console.error('Erreur :', error);
    //         const errMsg = error.response?.data?.message || 'Erreur lors de la mise à jour des données.';
    //         toast.error(errMsg);
    //     });
}

// Récupération des marques et modèles
const fetchMarques = async () => {
    try {
        const res = await axios.get("/pdc/get/marque");
        marques.value = res.data;
    } catch (err) {
        console.error("Erreur lors du chargement des marques :", err);
    }
};

const fetchModeles = async () => {
    if (!selectedMarqueId.value) {
        modeles.value = [];
        return;
    }
    try {
        const res = await axios.get(`/pdc/get/model?marque_id=${selectedMarqueId.value}`);
        modeles.value = res.data;
    } catch (err) {
        console.error("Erreur lors du chargement des modèles :", err);
    }
};

// Initialisation au montage du composant
onMounted(async () => {
    await fetchMarques();
    initializeForm();

    const params = new URLSearchParams(window.location.search);
    const ids = [];

    // Parcourir tous les paramètres
    params.forEach((value, key) => {
        // Vérifier si le paramètre est un ID de service (ex: selected[0][id], selected[1][id], etc.)
        const match = key.match(/^selected\[\d+]\[id]$/);
        if (match) {
            ids.push(decodeURIComponent(value)); // Ajouter l'ID à la liste
        }
    });

    selected.value = ids; // selected.value contient maintenant ["4", "9", "8", "11"]
    getTypeService();
});


// Vérifie si la mutation est sélectionnée
const getTypeService = () => {
    const index = selected.value.findIndex((id) => id === MUTATION_ID);
    if (index !== -1) {
        mutation.value = selected.value.splice(index, 1)[0];
    }
};

// Initialisation du formulaire avec les données existantes
const initializeForm = () => {
    if (props.data && props.data.immatriculation) {
        const data = props.data.immatriculation;
        form.value = {
            firstname: data.firstname || "",
            lastname: data.lastname || "",
            email: data.email || "",
            adresse: data.adresse || "",
            phone: data.phone || "",
            typePersonne: data.typePersonne || "",
            district: data.district || "",
            villeNaissance: data.villeNaissance || "",
            civilite: data.civilite || "",
            DateNaissance: data.DateNaissance || "",
            vin: data.vin || props.vin || "",
            couleurVehicule: data.couleurVehicule || "",
            carrosserie: data.carrosserie || "",
            typeTechnique: data.typeTechnique || "",
            genre: data.genre || "",
            ptac: data.ptac || "",
            pu: data.pu || "",
            pv: data.pv || "",
            puissance: data.puissance || "",
            placesAssises: data.placesAssises.toString() || "",
            sourcesEnergie: data.sourcesEnergie || "",
            nombreEssieux: data.nombreEssieux.toString() || "",
            type: data.typePersonne || "",
            usage: data.usage || "",
            codeRegion: data.codeRegion || "",
            DateCirculation: data.DateCirculation || "",
            AnneeProduction: data.AnneeProduction || "",
            nomEntreprise: data.nomEntreprise || null,
            registreCommerce: data.registreCommerce || null,
            representantLegal: data.representantLegal || null,
            numeroTelephone: data.numeroTelephone || null,
            compteContribuable: data.compteContribuable || null,
            DateNaissanceRepresantant: data.DateNaissanceRepresantant || null,
            ProfessionRepresantant: data.ProfessionRepresantant || null,
            prefecture: data.prefecture || null,
            sousPrefecture: data.sousPrefecture || null,
            region: data.region || null,
            marqueVehicule: data.marqueVehicule || null,
            modelVehicule: data.modelVehicule || null,
            gage: data.gage || null,
        };
        if (data.marqueVehicule && data.marqueVehicule.id) {
            selectedMarqueId.value = data.marqueVehicule.id;
            fetchModeles().then(() => {
                if (data.modelVehicule && data.modelVehicule.id) {
                    selectedModeleId.value = data.modelVehicule.id;
                }
            });
        }
    }
};
</script>



<script>
import Main from "/resources/js/Pages/Main.vue";
import BoutonRetour from "/resources/js/components/BoutonRetour.vue";

export default {
    layout: Main,
};
</script>
