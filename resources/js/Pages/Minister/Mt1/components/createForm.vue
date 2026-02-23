<template>
    <!-- Header sticky -->
    <div
        class="sticky top-[-20px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8 py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <h4 class="text-2xl font-bold tracking-tight">Détails du dossiers</h4>
        <div class="flex items-center space-x-2">
            <Button @click="returnBack()">
                <MoveLeft class="w-4 h-4 mr-2" /> Retour
            </Button>
        </div>
    </div>

    <!-- Contenu scrollable -->
    <div class="rounded-lg dark:border-gray-700">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <div class="grid gap-4 md:gap-8 lg:grid-cols-2 xl:grid-cols-3">
                <Card class="xl:col-span-2">
                    <ScrollArea class="h-full w-full rounded-md border">
                        <!-- Si pas Post-immatriculation -->
                        <div class="m-8" v-if="dossier.id_service != 3">
                            <!-- Service & statut du dossier -->
                            <div
                                class="flex flex-col space-y-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                                <h2>
                                    <strong>Service : </strong>{{
                                        dossier.r_dossier_services.nom_service
                                    }}
                                </h2>
                                <h2>
                                    Statut du dossier :
                                    <Badge :variant="dossier?.statut === 1
                                        ? 'warning'
                                        : dossier?.statut === 2
                                            ? 'success'
                                            : dossier?.statut === 3
                                                ? 'error'
                                                : dossier?.statut === 4
                                                    ? 'warning'
                                                    : 'secondary'
                                        ">
                                        {{
                                            dossier?.statut === 2
                                                ? "Validé"
                                                : dossier?.statut === 3
                                                    ? "Refusé"
                                                    : "En attente de validation"
                                        }}
                                    </Badge>
                                </h2>
                            </div>

                            <!-- Type de Service & motif de rejet -->
                            <div
                                class="flex flex-col space-y-4 py-2 sm:flex-row sm:items-center justify-between sm:space-y-0">
                                <div>
                                    <h2>
                                        <strong>Type de Service : </strong>
                                        <template v-if="
                                            dossier.detail &&
                                            Array.isArray(dossier.detail)
                                        ">
                                            {{ dossier.detail.join(", ") }}
                                        </template>
                                        <template v-else-if="
                                            dossier.detail &&
                                            typeof dossier.detail ===
                                            'string'
                                        ">
                                            {{
                                                JSON.parse(dossier.detail).join(
                                                    ", ",
                                                )
                                            }}
                                        </template>
                                    </h2>
                                </div>
                                <!-- Motif de rejet -->
                                <div v-if="dossier.motif_rejet">
                                    <div class="">
                                        <p class="border border-red-300 rounded-md p-3">
                                            <strong>Motif de rejet : </strong>
                                            <AlertDialog>
                                                <AlertDialogTrigger>
                                                    <Badge @click="
                                                        fetchMotifsRejets()
                                                        " variant="error"
                                                        class="mx-2 flex items-center cursor-pointer">
                                                        <span>Voir les
                                                            motifs</span>
                                                        <MoveRight class="w-4 h-4 ml-2" />
                                                    </Badge>
                                                </AlertDialogTrigger>
                                                <AlertDialogContent class="max-w-4xl w-full">
                                                    <AlertDialogHeader>
                                                        <AlertDialogTitle>Détails du rejet du
                                                            dossier</AlertDialogTitle>
                                                        <AlertDialogDescription>
                                                            <ScrollArea class="h-[400px] w-full rounded-md border p-4">
                                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4 mb-6"
                                                                    v-if="
                                                                        motifDossierRejeter.length
                                                                    ">
                                                                    <div v-for="motif in motifDossierRejeter" :key="motif.id
                                                                        " class="flex items-center space-x-2">
                                                                        <Checkbox checked="true" class="mr-2" :id="'motif-' +
                                                                            motif.id
                                                                            " />
                                                                        <Label :for="'motif-' +
                                                                            motif.id
                                                                            ">{{
                                                                                motif.motif
                                                                            }}</Label>
                                                                    </div>
                                                                </div>
                                                                <p v-else class="text-gray-500 italic">
                                                                    Chargement
                                                                    des
                                                                    données...
                                                                </p>
                                                            </ScrollArea>
                                                        </AlertDialogDescription>
                                                    </AlertDialogHeader>
                                                    <AlertDialogFooter class="flex justify-center gap-4 mt-6">
                                                        <AlertDialogCancel
                                                            class="bg-gray-200 text-gray-800 hover:bg-gray-300 px-4 py-2 rounded-md">
                                                            Fermer
                                                        </AlertDialogCancel>
                                                    </AlertDialogFooter>
                                                </AlertDialogContent>
                                            </AlertDialog>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Informations du véhicule -->
                            <h3><strong> Informations du véhicule</strong></h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 mb-6">
                                <p>
                                    Châssis (VIN) :
                                    {{ dossier.r_dossier_vehicule.vin }}
                                </p>
                                <p>
                                    Marque du véhicule :
                                    {{ dossier.r_dossier_vehicule.marque }}
                                </p>
                                <p>
                                    Modèle du véhicule :
                                    {{ dossier.r_dossier_vehicule.modele }}
                                </p>
                                <p>
                                    Genre :
                                    {{
                                        dossier.r_dossier_vehicule
                                            .genre_vehicule
                                    }}
                                </p>
                                <p>
                                    Poids Total en charge :
                                    {{
                                        dossier.r_dossier_vehicule
                                            .poids_total_charge
                                    }}
                                </p>
                                <p>
                                    Poids Utile :
                                    {{ dossier.r_dossier_vehicule.poids_utile }}
                                </p>
                                <p>
                                    Sources d’énergie :
                                    {{
                                        dossier.r_dossier_vehicule
                                            .source_energie
                                    }}
                                </p>
                                <p>
                                    Couleur :
                                    {{ dossier.r_dossier_vehicule.couleur }}
                                </p>
                                <p>
                                    Carrosserie :
                                    {{ dossier.r_dossier_vehicule.carrosserie }}
                                </p>
                                <p>
                                    Type technique :
                                    {{
                                        dossier.r_dossier_vehicule
                                            .type_technique
                                    }}
                                </p>
                                <p>
                                    Poids à Vide :
                                    {{ dossier.r_dossier_vehicule.poids_vide }}
                                </p>
                                <p>
                                    Puissance administrative :
                                    {{
                                        dossier.r_dossier_vehicule
                                            .puissance_administrative
                                    }}
                                </p>
                                <p>
                                    Places Assises :
                                    {{
                                        dossier.r_dossier_vehicule
                                            .places_assises
                                    }}
                                </p>
                                <p>
                                    Nbre d’Essieux :
                                    {{
                                        dossier.r_dossier_vehicule
                                            .nombre_essieux
                                    }}
                                </p>
                                <p>
                                    Usage :
                                    {{
                                        dossier.r_dossier_vehicule
                                            .usage_vehicule
                                    }}
                                </p>
                                <p v-if="
                                    dossier.r_dossier_vehicule
                                        .num_immatriculation
                                ">
                                    N° d'immatriculation :
                                    {{
                                        dossier.r_dossier_vehicule
                                            .num_immatriculation
                                    }}
                                </p>
                                <p v-if="
                                    dossier.r_dossier_vehicule
                                        .num_immatriculation_precedant
                                ">
                                    N° d'immatriculation precedant :
                                    {{
                                        dossier.r_dossier_vehicule
                                            .num_immatriculation_precedant
                                    }}
                                </p>
                                <p v-if="dossier.reserverNumero">
                                    N° réservé : {{ dossier.reserverNumero }}
                                </p>
                                <p v-if="dossier.numeroDiplomatique">
                                    N° d'immatriculation diplomatique :
                                    {{ dossier.numeroDiplomatique }}
                                </p>
                            </div>
                            <hr />
                            <!-- Informations du propriétaire -->
                            <h3 class="mt-6">
                                <strong>
                                    Informations du propriétaire :
                                </strong>
                                {{ dossier.r_dossier_client.civilite }} ,
                                {{ dossier.r_dossier_client.nom }}
                                {{ dossier.r_dossier_client.prenom }}
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 mb-6">
                                <p>
                                    Adresse :
                                    {{ dossier.r_dossier_client.adresse }}
                                </p>
                                <p>
                                    Email : {{ dossier.r_dossier_client.email }}
                                </p>
                                <p>
                                    Telephone :
                                    {{ dossier.r_dossier_client.telephone }}
                                </p>
                                <p>
                                    Date de naissance :
                                    {{
                                        dossier.r_dossier_client.date_naissance
                                    }}
                                </p>
                                <p>
                                    Ville de naissance :
                                    {{
                                        dossier.r_dossier_client.ville_naissance
                                    }}
                                </p>
                            </div>
                            <hr />
                            <!-- Informations de l'entreprise -->
                            <h3 class="mt-6" v-if="entreprise">
                                <strong> Informations de l'entreprise </strong>
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 mb-6" v-if="entreprise">
                                <p>
                                    Nom Entreprise :
                                    {{ entreprise?.nom_entreprise }}
                                </p>
                                <p>
                                    N° de compte contribuale :
                                    {{ entreprise?.compte_contribuale }}
                                </p>
                                <p>
                                    Registre de commerce :
                                    {{ entreprise?.registre_commerce }}
                                </p>
                                <p>
                                    Nom du représentant légal :
                                    {{ entreprise?.nom_representant_legal }}
                                </p>
                                <p>
                                    Téléphone du représentant légal :
                                    {{
                                        entreprise?.telephone_representant_legal
                                    }}
                                </p>
                                <p>
                                    Profession représentant légal :
                                    {{
                                        entreprise?.profession_representant_legal
                                    }}
                                </p>
                                <p>
                                    Date de naissance du représentant :
                                    {{
                                        entreprise.date_de_naissance_representant_legal
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Pour Post-Immatriculation -->
                        <div class="m-8" v-if="dossier.id_service == 3">
                            <div
                                class="flex flex-col space-y-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                                <h2>
                                    Service :
                                    {{ dossier.r_dossier_services.nom_service }}
                                </h2>
                                <h2>
                                    Statut du dossier :
                                    <Badge :variant="dossier?.statut === 1
                                        ? 'warning'
                                        : dossier?.statut === 2
                                            ? 'success'
                                            : dossier?.statut === 3
                                                ? 'error'
                                                : dossier?.statut === 4
                                                    ? 'warning'
                                                    : 'secondary'
                                        ">
                                        {{
                                            dossier?.statut === 2
                                                ? "Validé"
                                                : dossier?.statut === 3
                                                    ? "Refusé"
                                                    : "En attente de validation"
                                        }}
                                    </Badge>
                                </h2>
                            </div>
                            <!-- Type de Service & motif de rejet -->
                            <div
                                class="flex flex-col space-y-4 py-2 sm:flex-row sm:items-center justify-between sm:space-y-0">
                                <div>
                                    <h2>
                                        <strong>Type de Service : </strong>
                                        <template v-if="
                                            dossier.detail &&
                                            Array.isArray(dossier.detail)
                                        ">
                                            {{ dossier.detail.join(", ") }}
                                        </template>
                                        <template v-else-if="
                                            dossier.detail &&
                                            typeof dossier.detail ===
                                            'string'
                                        ">
                                            {{
                                                JSON.parse(dossier.detail).join(
                                                    ", ",
                                                )
                                            }}
                                        </template>
                                    </h2>
                                </div>
                                <!-- Motif de rejet -->
                                <div v-if="dossier.motif_rejet">
                                    <div class="">
                                        <p class="border border-red-300 rounded-md p-3">
                                            <strong>Motif de rejet : </strong>
                                            <AlertDialog>
                                                <AlertDialogTrigger>
                                                    <Badge @click="
                                                        fetchMotifsRejets()
                                                        " variant="error"
                                                        class="mx-2 flex items-center cursor-pointer">
                                                        <span>Voir les
                                                            motifs</span>
                                                        <MoveRight class="w-4 h-4 ml-2" />
                                                    </Badge>
                                                </AlertDialogTrigger>
                                                <AlertDialogContent class="max-w-4xl w-full">
                                                    <AlertDialogHeader>
                                                        <AlertDialogTitle>Détails du rejet du
                                                            dossier</AlertDialogTitle>
                                                        <AlertDialogDescription>
                                                            <ScrollArea class="h-[400px] w-full rounded-md border p-4">
                                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4 mb-6"
                                                                    v-if="
                                                                        motifDossierRejeter.length
                                                                    ">
                                                                    <div v-for="motif in motifDossierRejeter" :key="motif.id
                                                                        " class="flex items-center space-x-2">
                                                                        <Checkbox checked="true" class="mr-2" :id="'motif-' +
                                                                            motif.id
                                                                            " />
                                                                        <Label :for="'motif-' +
                                                                            motif.id
                                                                            ">{{
                                                                                motif.motif
                                                                            }}</Label>
                                                                    </div>
                                                                </div>
                                                                <p v-else class="text-gray-500 italic">
                                                                    Chargement
                                                                    des
                                                                    données...
                                                                </p>
                                                            </ScrollArea>
                                                        </AlertDialogDescription>
                                                    </AlertDialogHeader>
                                                    <AlertDialogFooter class="flex justify-center gap-4 mt-6">
                                                        <AlertDialogCancel
                                                            class="bg-gray-200 text-gray-800 hover:bg-gray-300 px-4 py-2 rounded-md">
                                                            Fermer
                                                        </AlertDialogCancel>
                                                    </AlertDialogFooter>
                                                </AlertDialogContent>
                                            </AlertDialog>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <!-- Anciennes Informations -->
                            <div class="mb-6">
                                <h3 class="text-red-600 font-bold uppercase mt-6 mb-6">
                                    Anciennes Informations
                                </h3>
                                <!--Informations du véhicule  -->
                                <p class="font-bold my-4">
                                    Informations du véhicule
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm my-4">
                                    <InfoRow label="Châssis (VIN)" :value="oldData.vehicule.vin" />
                                    <InfoRow label="Couleur" :value="oldData.vehicule.couleur" />
                                    <InfoRow label="Marque du véhicule" :value="oldData.vehicule.marque" />
                                    <InfoRow label="Carrosserie" :value="oldData.vehicule.carrosserie" />
                                    <InfoRow label="Modèle" :value="oldData.vehicule.modele" />
                                    <InfoRow label="Type technique" :value="oldData.vehicule.type_technique" />
                                    <InfoRow label="Genre" :value="oldData.vehicule.genre_vehicule" />
                                    <InfoRow label="Poids à Vide" :value="oldData.vehicule.poids_vide" />
                                    <InfoRow label="Poids Total en charge" :value="oldData.vehicule.poids_total_charge
                                        " />
                                    <InfoRow label="Puissance administrative" :value="oldData.vehicule
                                        .puissance_administrative
                                        " />
                                    <InfoRow label="Poids Utile" :value="oldData.vehicule.poids_utile" />
                                    <InfoRow label="Places Assises" :value="oldData.vehicule.places_assises" />
                                    <InfoRow label="Sources d’énergie" :value="oldData.vehicule.source_energie" />
                                    <InfoRow label="Nbre d’Essieux" :value="oldData.vehicule.nombre_essieux" />
                                    <InfoRow label="Code de Région" :value="oldData.vehicule.code_de_region" />
                                </div>
                                <hr />
                                <!--Informations du propriétaire  -->
                                <p class="font-bold my-4">
                                    Informations du propriétaire
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm my-4">
                                    <InfoRow label="Civilité" :value="oldData.client.civilite" />
                                    <InfoRow label="Nom" :value="oldData.client.nom" />
                                    <InfoRow label="Prénom" :value="oldData.client.prenom" />
                                    <InfoRow label="Adresse" :value="oldData.client.adresse" />
                                    <InfoRow label="Téléphone" :value="oldData.client.telephone" />
                                    <InfoRow label="Date de Naissance" :value="oldData.client.date_naissance" />
                                    <InfoRow label="Ville de Naissance" :value="oldData.client.ville_naissance" />
                                    <InfoRow label="Email" :value="oldData.client.email" />
                                </div>
                                <hr />
                                <!--Informations de l'entreprise -->
                                <p class="font-bold my-4" v-if="oldData.entreprise">
                                    Informations de l'entreprisess
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm my-4"
                                    v-if="oldData.entreprise">
                                    <InfoRow label="Nom de l'entreprise" :value="oldData.entreprise.nom_entreprise
                                        " />
                                    <InfoRow label="Registre de commerce" :value="oldData.entreprise.registre_commerce
                                        " />
                                    <InfoRow label="N° de Compte contribuable" :value="oldData.entreprise
                                        .compte_contribuale
                                        " />
                                    <InfoRow label="Nom du Representant Legal" :value="oldData.entreprise
                                        .nom_representant_legal
                                        " />
                                    <InfoRow label="Téléphone du Representant Legal" :value="oldData.entreprise
                                        .telephone_representant_legal
                                        " />
                                    <InfoRow label="Profession du Representant Legal" :value="oldData.entreprise
                                        .profession_representant_legal
                                        " />
                                    <InfoRow label="Date de Naissance du Representant Legal" :value="oldData.entreprise
                                        .date_de_naissance_representant_legal
                                        " />
                                </div>
                                <hr v-if="oldData.entreprise" />
                            </div>
                            <!-- Nouvelles Informations -->
                            <div class="mb-6">
                                <h3 class="text-green-600 font-bold uppercase mt-6 mb-6">
                                    Nouvelles Informations
                                </h3>
                                <!-- Informations du véhicule -->
                                <p class="font-bold my-4">
                                    Informations du véhicule
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm my-4">
                                    <InfoRow label="Châssis (VIN)" :value="newData.vehicule.vin"
                                        :highlight="isDifferent('vehicule.vin')" />
                                    <InfoRow label="Couleur" :value="newData.vehicule.couleur" :highlight="isDifferent('vehicule.couleur')
                                        " />
                                    <InfoRow label="Marque du véhicule" :value="newData.vehicule.marque" :highlight="isDifferent('vehicule.marque')
                                        " />
                                    <InfoRow label="Carrosserie" :value="newData.vehicule.carrosserie" :highlight="isDifferent('vehicule.carrosserie')
                                        " />
                                    <InfoRow label="Modèle" :value="newData.vehicule.modele" :highlight="isDifferent('vehicule.modele')
                                        " />
                                    <InfoRow label="Type technique" :value="newData.vehicule.type_technique" :highlight="isDifferent(
                                        'vehicule.type_technique',
                                    )
                                        " />
                                    <InfoRow label="Genre" :value="newData.vehicule.genre_vehicule" :highlight="isDifferent(
                                        'vehicule.genre_vehicule',
                                    )
                                        " />
                                    <InfoRow label="Poids à Vide" :value="newData.vehicule.poids_vide" :highlight="isDifferent('vehicule.poids_vide')
                                        " />
                                    <InfoRow label="Poids Total en charge" :value="newData.vehicule.poids_total_charge
                                        " :highlight="isDifferent(
                                            'vehicule.poids_total_charge',
                                        )
                                            " />
                                    <InfoRow label="Puissance administrative" :value="newData.vehicule
                                        .puissance_administrative
                                        " :highlight="isDifferent(
                                            'vehicule.puissance_administrative',
                                        )
                                            " />
                                    <InfoRow label="Poids Utile" :value="newData.vehicule.poids_utile" :highlight="isDifferent('vehicule.poids_utile')
                                        " />
                                    <InfoRow label="Places Assises" :value="newData.vehicule.places_assises" :highlight="isDifferent(
                                        'vehicule.places_assises',
                                    )
                                        " />
                                    <InfoRow label="Sources d’énergie" :value="newData.vehicule.source_energie"
                                        :highlight="isDifferent(
                                            'vehicule.source_energie',
                                        )
                                            " />
                                    <InfoRow label="Nbre d’Essieux" :value="newData.vehicule.nombre_essieux" :highlight="isDifferent(
                                        'vehicule.nombre_essieux',
                                    )
                                        " />
                                    <InfoRow label="Code de Région" :value="newData.vehicule.code_de_region" :highlight="isDifferent(
                                        'vehicule.code_de_region',
                                    )
                                        " />
                                </div>

                                <hr />
                                <!-- Informations du propriétaire -->
                                <p class="font-bold my-4">
                                    Informations du propriétaire
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <InfoRow label="Civilité" :value="newData.client.civilite" :highlight="isDifferent('client.civilite')
                                        " />
                                    <InfoRow label="Nom" :value="newData.client.nom"
                                        :highlight="isDifferent('client.nom')" />
                                    <InfoRow label="Prénom" :value="newData.client.prenom" :highlight="isDifferent('client.prenom')
                                        " />
                                    <InfoRow label="Adresse" :value="newData.client.adresse" :highlight="isDifferent('client.adresse')
                                        " />
                                    <InfoRow label="Téléphone" :value="newData.client.telephone" :highlight="isDifferent('client.telephone')
                                        " />
                                    <InfoRow label="Date de Naissance" :value="newData.client.date_naissance"
                                        :highlight="isDifferent('client.date_naissance')
                                            " />
                                    <InfoRow label="Ville de Naissance" :value="newData.client.ville_naissance"
                                        :highlight="isDifferent(
                                            'client.ville_naissance',
                                        )
                                            " />
                                    <InfoRow label="Email" :value="newData.client.email"
                                        :highlight="isDifferent('client.email')" />
                                </div>

                                <hr v-if="newData.entreprise" />
                                <!-- Informations de l'entreprise -->
                                <p class="font-bold my-4" v-if="newData.entreprise">
                                    Informations de l'entreprise
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm" v-if="newData.entreprise">
                                    <InfoRow label="Nom de l'entreprise" :value="newData.entreprise.nom_entreprise
                                        " :highlight="isDifferent(
                                            'entreprise.nom_entreprise',
                                        )
                                            " />
                                    <InfoRow label="Registre de commerce" :value="newData.entreprise.registre_commerce
                                        " :highlight="isDifferent(
                                            'entreprise.registre_commerce',
                                        )
                                            " />
                                    <InfoRow label="N° de Compte contribuable" :value="newData.entreprise
                                        .compte_contribuable
                                        " :highlight="isDifferent(
                                            'entreprise.compte_contribuable',
                                        )
                                            " />
                                    <InfoRow label="Nom du représentant légal" :value="newData.entreprise
                                        .nom_representant_legal
                                        " :highlight="isDifferent(
                                            'entreprise.nom_representant_legal',
                                        )
                                            " />
                                    <InfoRow label="Téléphone du Representant Legal" :value="newData.entreprise
                                        .telephone_representant_legal
                                        " :highlight="isDifferent(
                                            'entreprise.telephone_representant_legal',
                                        )
                                            " />
                                    <InfoRow label="Profession du Representant Legal" :value="newData.entreprise
                                        .profession_representant_legal
                                        " :highlight="isDifferent(
                                            'entreprise.profession_representant_legal',
                                        )
                                            " />
                                    <InfoRow label="Date de Naissance du Representant Legal" :value="newData.entreprise
                                        .date_de_naissance_representant_legal
                                        " :highlight="isDifferent(
                                            'entreprise.date_de_naissance_representant_legal',
                                        )
                                            " />
                                </div>
                                <hr v-if="newData.entreprise" />
                            </div>
                        </div>
                    </ScrollArea>
                </Card>

                <Card>
                    <!-- <ScrollArea class="h-full w-full rounded-md border"> iii-->
                    <!-- <template> -->
                    <div class="m-8" v-if="props.dossier_lier == null">
                        <h3 class="mt-6 text-center font-bold">
                            DOCUMENTATION
                        </h3>

                        <div class="max-w-xl mx-auto bg-white rounded-lg shadow p-4 mt-6">
                            <div class="space-y-5">
                                <!-- Liste des documents -->
                                <div v-if="filteredDocs.length">
                                    <div v-for="(
[key, value], index
                                        ) in filteredDocs" :key="key"
                                        class="flex items-center justify-between border rounded px-4 py-2 my-4">
                                        <span>{{ formatLabel(key) }}</span>

                                        <AlertDialog>
                                            <AlertDialogTrigger as-child>
                                                <button class="flex items-center text-gray-600 hover:text-black" @click="
                                                    selectedIndex = index
                                                    ">
                                                    <Eye class="w-5 h-5" />
                                                </button>
                                            </AlertDialogTrigger>

                                            <!-- MODAL -->
                                            <AlertDialogContent class="max-w-8xl">
                                                <AlertDialogHeader>
                                                    <AlertDialogDescription>
                                                        <div
                                                            class="bg-white rounded-2xl relative flex flex-col md:flex-row gap-6">
                                                            <!-- Bouton fermer -->
                                                            <AlertDialogCancel class="absolute top-4 right-4">
                                                                ✕
                                                            </AlertDialogCancel>

                                                            <!-- COLONNE INFOS (TON CONTENU EXISTANT) -->
                                                            <div class="flex-1 space-y-4">
                                                                <!-- Colonne infos -->
                                                                <div class="flex-1 space-y-3">
                                                                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                                                                        {{
                                                                            modalTitle
                                                                        }}
                                                                    </h2>
                                                                    <div class="m-8" v-if="
                                                                        dossier.id_service !=
                                                                        3
                                                                    ">
                                                                        <h3>
                                                                            Informations
                                                                            du
                                                                            véhicule
                                                                        </h3>
                                                                        <div
                                                                            class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 mb-6">
                                                                            <p>
                                                                                Châssis
                                                                                (VIN)
                                                                                :
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_vehicule
                                                                                        ?.vin
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Marque
                                                                                du
                                                                                véhicule
                                                                                :
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_vehicule
                                                                                        ?.marque
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Modèle
                                                                                du
                                                                                véhicule
                                                                                :
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_vehicule
                                                                                        ?.modele
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Genre
                                                                                :
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_vehicule
                                                                                        ?.genre_vehicule
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Poids
                                                                                Total
                                                                                en
                                                                                charge
                                                                                :
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_vehicule
                                                                                        ?.poids_total
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Poids
                                                                                Utile
                                                                                :
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_vehicule
                                                                                        ?.poids_utile
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Sources
                                                                                d’énergie
                                                                                :
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_vehicule
                                                                                        ?.source_energie
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Couleur
                                                                                :
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_vehicule
                                                                                        ?.couleur
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Carrosserie
                                                                                :
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_vehicule
                                                                                        ?.carrosserie
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Type
                                                                                technique
                                                                                :
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_vehicule
                                                                                        ?.type_technique
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Poids
                                                                                à
                                                                                Vide
                                                                                :
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_vehicule
                                                                                        ?.poids_vide
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Puissance
                                                                                administrative
                                                                                :
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_vehicule
                                                                                        ?.puissance_administrative
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Places
                                                                                Assises
                                                                                :
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_vehicule
                                                                                        ?.places_assises
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Nbre
                                                                                d’Essieux
                                                                                :
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_vehicule
                                                                                        ?.nombre_essieux
                                                                                }}
                                                                            </p>
                                                                        </div>
                                                                        <hr />
                                                                        <h3 class="mt-6">
                                                                            Informations
                                                                            du
                                                                            propriétaire
                                                                            :
                                                                            <strong>{{
                                                                                dossier
                                                                                    ?.r_dossier_client
                                                                                    ?.civilite
                                                                            }},
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_client
                                                                                        ?.nom
                                                                                }}
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_client
                                                                                        ?.prenom
                                                                                }}</strong>
                                                                        </h3>
                                                                        <div
                                                                            class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 mb-6">
                                                                            <p>
                                                                                Adresse
                                                                                :
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_client
                                                                                        ?.adresse
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Email
                                                                                :
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_client
                                                                                        ?.email
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Téléphone
                                                                                :
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_client
                                                                                        ?.telephone
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Date
                                                                                de
                                                                                naissance
                                                                                :
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_client
                                                                                        ?.date_naissance
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Ville
                                                                                de
                                                                                naissance
                                                                                :
                                                                                {{
                                                                                    dossier
                                                                                        ?.r_dossier_client
                                                                                        ?.ville_naissance
                                                                                }}
                                                                            </p>
                                                                        </div>
                                                                        <hr />
                                                                        <!-- Informations de l'entreprise -->
                                                                        <h3 class="mt-6" v-if="
                                                                            entreprise
                                                                        ">
                                                                            <strong>
                                                                                Informations
                                                                                de
                                                                                l'entreprise
                                                                            </strong>
                                                                        </h3>
                                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 mb-6"
                                                                            v-if="
                                                                                entreprise
                                                                            ">
                                                                            <p>
                                                                                Nom
                                                                                Entreprise
                                                                                :
                                                                                {{
                                                                                    entreprise?.nom_entreprise
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Numéro
                                                                                de
                                                                                compte
                                                                                contribuale
                                                                                :
                                                                                {{
                                                                                    entreprise?.compte_contribuale
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Registre
                                                                                de
                                                                                commerce
                                                                                :
                                                                                {{
                                                                                    entreprise?.registre_commerce
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Nom
                                                                                du
                                                                                représentant
                                                                                légal
                                                                                :
                                                                                {{
                                                                                    entreprise?.nom_representant_legal
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Téléphone
                                                                                du
                                                                                représentant
                                                                                légal
                                                                                :
                                                                                {{
                                                                                    entreprise?.telephone_representant_legal
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Profession
                                                                                représentant
                                                                                légal
                                                                                :
                                                                                {{
                                                                                    entreprise?.profession_representant_legal
                                                                                }}
                                                                            </p>
                                                                            <p>
                                                                                Date
                                                                                de
                                                                                naissance
                                                                                du
                                                                                représentant
                                                                                :
                                                                                {{
                                                                                    entreprise.date_de_naissance_representant_legal
                                                                                }}
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="m-8" v-if="
                                                                        dossier.id_service ==
                                                                        3
                                                                    ">
                                                                        <div
                                                                            class="flex flex-col space-y-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                                                                            <h2>
                                                                                Service
                                                                                :
                                                                                {{
                                                                                    dossier
                                                                                        .r_dossier_services
                                                                                        .nom_service
                                                                                }}
                                                                            </h2>
                                                                        </div>
                                                                        <ScrollArea
                                                                            class="h-[900px] w-full rounded-md border p-4">
                                                                            <!-- Anciennes Informations -->
                                                                            <div class="mb-6">
                                                                                <h3
                                                                                    class="text-red-600 font-bold uppercase mt-6 mb-6">
                                                                                    Anciennes
                                                                                    Informations
                                                                                </h3>
                                                                                <!--Informations du véhicule -->
                                                                                <p class="font-bold my-4">
                                                                                    Informations
                                                                                    du
                                                                                    véhicule
                                                                                </p>
                                                                                <div
                                                                                    class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm my-4">
                                                                                    <InfoRow label="Châssis (VIN)"
                                                                                        :value="oldData
                                                                                            .vehicule
                                                                                            .vin
                                                                                            " />
                                                                                    <InfoRow label="Couleur" :value="oldData
                                                                                        .vehicule
                                                                                        .couleur
                                                                                        " />
                                                                                    <InfoRow label="Marque du véhicule"
                                                                                        :value="oldData
                                                                                            .vehicule
                                                                                            .marque
                                                                                            " />
                                                                                    <InfoRow label="Carrosserie" :value="oldData
                                                                                        .vehicule
                                                                                        .carrosserie
                                                                                        " />
                                                                                    <InfoRow label="Modèle" :value="oldData
                                                                                        .vehicule
                                                                                        .modele
                                                                                        " />
                                                                                    <InfoRow label="Type technique"
                                                                                        :value="oldData
                                                                                            .vehicule
                                                                                            .type_technique
                                                                                            " />
                                                                                    <InfoRow label="Genre" :value="oldData
                                                                                        .vehicule
                                                                                        .genre_vehicule
                                                                                        " />
                                                                                    <InfoRow label="Poids à Vide"
                                                                                        :value="oldData
                                                                                            .vehicule
                                                                                            .poids_vide
                                                                                            " />
                                                                                    <InfoRow
                                                                                        label="Poids Total en charge"
                                                                                        :value="oldData
                                                                                            .vehicule
                                                                                            .poids_total_charge
                                                                                            " />
                                                                                    <InfoRow
                                                                                        label="Puissance administrative"
                                                                                        :value="oldData
                                                                                            .vehicule
                                                                                            .puissance_administrative
                                                                                            " />
                                                                                    <InfoRow label="Poids Utile" :value="oldData
                                                                                        .vehicule
                                                                                        .poids_utile
                                                                                        " />
                                                                                    <InfoRow label="Places Assises"
                                                                                        :value="oldData
                                                                                            .vehicule
                                                                                            .places_assises
                                                                                            " />
                                                                                    <InfoRow label="Sources d’énergie"
                                                                                        :value="oldData
                                                                                            .vehicule
                                                                                            .source_energie
                                                                                            " />
                                                                                    <InfoRow label="Nbre d’Essieux"
                                                                                        :value="oldData
                                                                                            .vehicule
                                                                                            .nombre_essieux
                                                                                            " />
                                                                                </div>
                                                                                <hr />
                                                                                <!--Informations du propriétaire -->
                                                                                <p class="font-bold my-4">
                                                                                    Informations
                                                                                    du
                                                                                    propriétaire
                                                                                </p>
                                                                                <div
                                                                                    class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm my-4">
                                                                                    <InfoRow label="Civilité" :value="oldData
                                                                                        .client
                                                                                        .civilite
                                                                                        " />
                                                                                    <InfoRow label="Nom" :value="oldData
                                                                                        .client
                                                                                        .nom
                                                                                        " />
                                                                                    <InfoRow label="Prénom" :value="oldData
                                                                                        .client
                                                                                        .prenom
                                                                                        " />
                                                                                    <InfoRow label="Adresse" :value="oldData
                                                                                        .client
                                                                                        .adresse
                                                                                        " />
                                                                                    <InfoRow label="Téléphone" :value="oldData
                                                                                        .client
                                                                                        .telephone
                                                                                        " />
                                                                                    <InfoRow label="Date de Naissance"
                                                                                        :value="oldData
                                                                                            .client
                                                                                            .date_naissance
                                                                                            " />
                                                                                    <InfoRow label="Ville de Naissance"
                                                                                        :value="oldData
                                                                                            .client
                                                                                            .ville_naissance
                                                                                            " />
                                                                                    <InfoRow label="Email" :value="oldData
                                                                                        .client
                                                                                        .email
                                                                                        " />
                                                                                </div>
                                                                                <hr />
                                                                                <!--Informations de l'entreprise -->
                                                                                <p class="font-bold my-4" v-if="
                                                                                    oldData.entreprise
                                                                                ">
                                                                                    Informations
                                                                                    de
                                                                                    l'entreprise
                                                                                </p>
                                                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm my-4"
                                                                                    v-if="
                                                                                        oldData.entreprise
                                                                                    ">
                                                                                    <InfoRow label="Nom de l'entreprise"
                                                                                        :value="oldData
                                                                                            .entreprise
                                                                                            .nom
                                                                                            " />
                                                                                    <InfoRow label="Adresse" :value="oldData
                                                                                        .entreprise
                                                                                        .adresse
                                                                                        " />
                                                                                    <InfoRow label="Téléphone" :value="oldData
                                                                                        .entreprise
                                                                                        .telephone
                                                                                        " />
                                                                                    <InfoRow label="Email" :value="oldData
                                                                                        .entreprise
                                                                                        .email
                                                                                        " />
                                                                                    <InfoRow label="District" :value="oldData
                                                                                        .entreprise
                                                                                        .district
                                                                                        " />
                                                                                    <InfoRow label="Préfecture" :value="oldData
                                                                                        .entreprise
                                                                                        .prefecture
                                                                                        " />
                                                                                    <InfoRow label="Sous-préfecture"
                                                                                        :value="oldData
                                                                                            .entreprise
                                                                                            .sous_prefecture
                                                                                            " />
                                                                                    <InfoRow label="Code de région"
                                                                                        :value="oldData
                                                                                            .entreprise
                                                                                            .code_de_region
                                                                                            " />
                                                                                </div>
                                                                                <hr v-if="
                                                                                    oldData.entreprise
                                                                                " />
                                                                            </div>
                                                                            <!-- Nouvelles Informations -->
                                                                            <div class="mb-6">
                                                                                <h3
                                                                                    class="text-green-600 font-bold uppercase mt-6 mb-6">
                                                                                    Nouvelles
                                                                                    Informations
                                                                                </h3>
                                                                                <!-- Informations du véhicule -->
                                                                                <p class="font-bold my-4">
                                                                                    Informations
                                                                                    du
                                                                                    véhicule
                                                                                </p>
                                                                                <div
                                                                                    class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm my-4">
                                                                                    <InfoRow label="Châssis (VIN)"
                                                                                        :value="newData
                                                                                            .vehicule
                                                                                            .vin
                                                                                            " :highlight="isDifferent(
                                                                                                'vehicule.vin',
                                                                                            )
                                                                                                " />
                                                                                    <InfoRow label="Couleur" :value="newData
                                                                                        .vehicule
                                                                                        .couleur
                                                                                        " :highlight="isDifferent(
                                                                                            'vehicule.couleur',
                                                                                        )
                                                                                            " />
                                                                                    <InfoRow label="Marque du véhicule"
                                                                                        :value="newData
                                                                                            .vehicule
                                                                                            .marque
                                                                                            " :highlight="isDifferent(
                                                                                                'vehicule.marque',
                                                                                            )
                                                                                                " />
                                                                                    <InfoRow label="Carrosserie" :value="newData
                                                                                        .vehicule
                                                                                        .carrosserie
                                                                                        " :highlight="isDifferent(
                                                                                            'vehicule.carrosserie',
                                                                                        )
                                                                                            " />
                                                                                    <InfoRow label="Modèle" :value="newData
                                                                                        .vehicule
                                                                                        .modele
                                                                                        " :highlight="isDifferent(
                                                                                            'vehicule.modele',
                                                                                        )
                                                                                            " />
                                                                                    <InfoRow label="Type technique"
                                                                                        :value="newData
                                                                                            .vehicule
                                                                                            .type_technique
                                                                                            " :highlight="isDifferent(
                                                                                                'vehicule.type_technique',
                                                                                            )
                                                                                                " />
                                                                                    <InfoRow label="Genre" :value="newData
                                                                                        .vehicule
                                                                                        .genre_vehicule
                                                                                        " :highlight="isDifferent(
                                                                                            'vehicule.genre_vehicule',
                                                                                        )
                                                                                            " />
                                                                                    <InfoRow label="Poids à Vide"
                                                                                        :value="newData
                                                                                            .vehicule
                                                                                            .poids_vide
                                                                                            " :highlight="isDifferent(
                                                                                                'vehicule.poids_vide',
                                                                                            )
                                                                                                " />
                                                                                    <InfoRow
                                                                                        label="Poids Total en charge"
                                                                                        :value="newData
                                                                                            .vehicule
                                                                                            .poids_total_charge
                                                                                            " :highlight="isDifferent(
                                                                                                'vehicule.poids_total_charge',
                                                                                            )
                                                                                                " />
                                                                                    <InfoRow
                                                                                        label="Puissance administrative"
                                                                                        :value="newData
                                                                                            .vehicule
                                                                                            .puissance_administrative
                                                                                            " :highlight="isDifferent(
                                                                                                'vehicule.puissance_administrative',
                                                                                            )
                                                                                                " />
                                                                                    <InfoRow label="Poids Utile" :value="newData
                                                                                        .vehicule
                                                                                        .poids_utile
                                                                                        " :highlight="isDifferent(
                                                                                            'vehicule.poids_utile',
                                                                                        )
                                                                                            " />
                                                                                    <InfoRow label="Places Assises"
                                                                                        :value="newData
                                                                                            .vehicule
                                                                                            .places_assises
                                                                                            " :highlight="isDifferent(
                                                                                                'vehicule.places_assises',
                                                                                            )
                                                                                                " />
                                                                                    <InfoRow label="Sources d’énergie"
                                                                                        :value="newData
                                                                                            .vehicule
                                                                                            .source_energie
                                                                                            " :highlight="isDifferent(
                                                                                                'vehicule.source_energie',
                                                                                            )
                                                                                                " />
                                                                                    <InfoRow label="Nbre d’Essieux"
                                                                                        :value="newData
                                                                                            .vehicule
                                                                                            .nombre_essieux
                                                                                            " :highlight="isDifferent(
                                                                                                'vehicule.nombre_essieux',
                                                                                            )
                                                                                                " />
                                                                                </div>
                                                                                <hr />
                                                                                <!-- Informations du propriétaire -->
                                                                                <p class="font-bold my-4">
                                                                                    Informations
                                                                                    du
                                                                                    propriétaire
                                                                                </p>
                                                                                <div
                                                                                    class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                                                                    <InfoRow label="Civilité" :value="newData
                                                                                        .client
                                                                                        .civilite
                                                                                        " :highlight="isDifferent(
                                                                                            'client.civilite',
                                                                                        )
                                                                                            " />
                                                                                    <InfoRow label="Nom" :value="newData
                                                                                        .client
                                                                                        .nom
                                                                                        " :highlight="isDifferent(
                                                                                            'client.nom',
                                                                                        )
                                                                                            " />
                                                                                    <InfoRow label="Prénom" :value="newData
                                                                                        .client
                                                                                        .prenom
                                                                                        " :highlight="isDifferent(
                                                                                            'client.prenom',
                                                                                        )
                                                                                            " />
                                                                                    <InfoRow label="Adresse" :value="newData
                                                                                        .client
                                                                                        .adresse
                                                                                        " :highlight="isDifferent(
                                                                                            'client.adresse',
                                                                                        )
                                                                                            " />
                                                                                    <InfoRow label="Téléphone" :value="newData
                                                                                        .client
                                                                                        .telephone
                                                                                        " :highlight="isDifferent(
                                                                                            'client.telephone',
                                                                                        )
                                                                                            " />
                                                                                    <InfoRow label="Date de Naissance"
                                                                                        :value="newData
                                                                                            .client
                                                                                            .date_naissance
                                                                                            " :highlight="isDifferent(
                                                                                                'client.date_naissance',
                                                                                            )
                                                                                                " />
                                                                                    <InfoRow label="Ville de Naissance"
                                                                                        :value="newData
                                                                                            .client
                                                                                            .ville_naissance
                                                                                            " :highlight="isDifferent(
                                                                                                'client.ville_naissance',
                                                                                            )
                                                                                                " />
                                                                                    <InfoRow label="Email" :value="newData
                                                                                        .client
                                                                                        .email
                                                                                        " :highlight="isDifferent(
                                                                                            'client.email',
                                                                                        )
                                                                                            " />
                                                                                </div>
                                                                                <hr v-if="
                                                                                    newData.entreprise
                                                                                " />
                                                                                <!-- Informations de l'entreprise -->
                                                                                <p class="font-bold my-4" v-if="
                                                                                    newData.entreprise
                                                                                ">
                                                                                    Informations
                                                                                    de
                                                                                    l'entreprise
                                                                                </p>
                                                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm"
                                                                                    v-if="
                                                                                        newData.entreprise
                                                                                    ">
                                                                                    <InfoRow label="Nom de l'entreprise"
                                                                                        :value="newData
                                                                                            .entreprise
                                                                                            .nom_entreprise
                                                                                            " :highlight="isDifferent(
                                                                                                'entreprise.nom_entreprise',
                                                                                            )
                                                                                                " />
                                                                                    <InfoRow
                                                                                        label="Registre de commerce"
                                                                                        :value="newData
                                                                                            .entreprise
                                                                                            .registre_commerce
                                                                                            " :highlight="isDifferent(
                                                                                                'entreprise.registre_commerce',
                                                                                            )
                                                                                                " />
                                                                                    <InfoRow
                                                                                        label="N° de Compte contribuable"
                                                                                        :value="newData
                                                                                            .entreprise
                                                                                            .compte_contribuable
                                                                                            " :highlight="isDifferent(
                                                                                                'entreprise.compte_contribuable',
                                                                                            )
                                                                                                " />
                                                                                    <InfoRow
                                                                                        label="Nom du représentant légal"
                                                                                        :value="newData
                                                                                            .entreprise
                                                                                            .nom_representant_legal
                                                                                            " :highlight="isDifferent(
                                                                                                'entreprise.nom_representant_legal',
                                                                                            )
                                                                                                " />
                                                                                    <InfoRow
                                                                                        label="Téléphone du Representant Legal"
                                                                                        :value="newData
                                                                                            .entreprise
                                                                                            .telephone_representant_legal
                                                                                            " :highlight="isDifferent(
                                                                                                'entreprise.telephone_representant_legal',
                                                                                            )
                                                                                                " />
                                                                                    <InfoRow
                                                                                        label="Profession du Representant Legal"
                                                                                        :value="newData
                                                                                            .entreprise
                                                                                            .profession_representant_legal
                                                                                            " :highlight="isDifferent(
                                                                                                'entreprise.profession_representant_legal',
                                                                                            )
                                                                                                " />
                                                                                    <InfoRow
                                                                                        label="Date de Naissance du Representant Legal"
                                                                                        :value="newData
                                                                                            .entreprise
                                                                                            .date_de_naissance_representant_legal
                                                                                            " :highlight="isDifferent(
                                                                                                'entreprise.date_de_naissance_representant_legal',
                                                                                            )
                                                                                                " />
                                                                                </div>
                                                                                <hr v-if="
                                                                                    newData.entreprise
                                                                                " />
                                                                            </div>
                                                                        </ScrollArea>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- COLONNE IMAGE + NAVIGATION -->
                                                            <div
                                                                class="flex-1 flex flex-col items-center justify-center gap-4">
                                                                <h2 class="text-xl font-bold text-gray-800">
                                                                    {{
                                                                        docsArray[
                                                                            selectedIndex
                                                                        ]?.label
                                                                    }}
                                                                </h2>
                                                                <img :src="docsArray[
                                                                    selectedIndex
                                                                ]?.src
                                                                    " alt="Document"
                                                                    class="w-[900px] h-[600px] object-contain rounded shadow" />

                                                                <!-- Navigation -->
                                                                <div class="flex items-center gap-4">
                                                                    <button
                                                                        class="px-2 py-1 border rounded-md disabled:opacity-40 cursor-pointer"
                                                                        :disabled="selectedIndex ===
                                                                            0
                                                                            " @click="
                                                                                selectedIndex--
                                                                                ">
                                                                        <ChevronLeft />
                                                                    </button>

                                                                    <span class="text-sm text-gray-500">
                                                                        {{
                                                                            selectedIndex +
                                                                            1
                                                                        }}
                                                                        /
                                                                        {{
                                                                            docsArray.length
                                                                        }}
                                                                    </span>

                                                                    <button
                                                                        class="px-2 py-1 border rounded-md disabled:opacity-40 cursor-pointer"
                                                                        :disabled="selectedIndex ===
                                                                            docsArray.length -
                                                                            1
                                                                            " @click="
                                                                                selectedIndex++
                                                                                ">
                                                                        <ChevronRight />
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                            </AlertDialogContent>
                                        </AlertDialog>
                                    </div>
                                    <!-- </template> -->

                                    <!-- Aucun document -->
                                    <!-- <div  v-else>
                                    <div  class="text-center text-sm text-gray-500 m-6">
                                        Il n'y a aucun document enregistré.
                                    </div>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                        <!-- </template> -->

                        <!-- </ScrollArea>  dossier_lier-->
                    </div>
                </Card>
            </div>

            <div v-if="dossier.statut == 1 || dossier.statut == 4"
                class="sticky top-[-20px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8 py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                <div></div>
                <div class="flex items-center space-x-2">
                    <Link :href="route('show.modification.mt1.get.data', {
                        vin: dossier.num_chrono,
                    })
                        ">
                    <Button class="bg-[#ca7600]">
                        <HandCoins class="w-4 h-4 mr-2" /> Demande de
                        mofication
                    </Button>
                    </Link>
                    <!-- <Link :href="route('show.form.numerisation', { dossier: dossier.id })"> -->
                    <AlertDialog>
                        <AlertDialogTrigger>
                            <Button class="bg-[#068A06]" :disabled="!filteredDocs.length">
                                <CreditCard class="w-4 h-4 mr-2" /> Valider le
                                dossier
                            </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>Confirmer la validation</AlertDialogTitle>
                                <AlertDialogDescription>
                                    Êtes-vous sûr de vouloir valider ce dossier
                                    ?
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter class="flex justify-center gap-4 mt-6">
                                <AlertDialogCancel
                                    class="bg-gray-200 text-gray-800 hover:bg-gray-300 px-4 py-2 rounded-md">
                                    Annuler
                                </AlertDialogCancel>
                                <AlertDialogAction class="bg-green-600 text-white hover:bg-red-700 px-4 py-2 rounded-md"
                                    @click="confirmerValidation(dossier.id)">
                                    Confirmer
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                    <AlertDialog>
                        <!-- <Rejet /> -->
                        <Button class="bg-red-900" @click="showRejectModal = true">
                            <CreditCard class="w-4 h-4 mr-2" /> Rejeter le
                            dossier
                        </Button>
                    </AlertDialog>
                </div>
            </div>
        </main>
        <Toaster richColors position="top-right" />

        <!-- Modal pour rejeter le dossier -->
        <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg w-11/12 md:w-1/2 p-6 relative">
                <!-- Bouton fermer -->
                <button @click="showRejectModal = false"
                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 p-4">
                    ✕
                </button>
                <!-- Contenu du modal (ton code de sélection) -->
                <h2 class="text-lg font-semibold mb-4">
                    Choisissez des motifs de rejet
                </h2>
                <ScrollArea class="h-full w-full rounded-md border p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4 mb-6" v-if="items.length">
                        <div v-for="item in items" :key="item.id" class="flex items-center space-x-2">
                            <Checkbox :checked="selectedFields.includes(item.id)"
                                @update:checked="() => toggleField(item.id)" class="mr-2" :id="'motif-' + item.id" />
                            <Label :for="'motif-' + item.id">{{
                                item.motif
                                }}</Label>
                        </div>
                    </div>

                    <p v-else class="text-gray-500 italic">
                        Chargement des données...
                    </p>
                </ScrollArea>

                <div class="row mt-4">
                    <div></div>
                    <!-- Boutons Valider / Annuler -->
                    <div class="flex space-x-4 mt-4 text-right">
                        <Button v-if="!selectedFields.length"
                            class="bg-red-600 text-white hover:bg-red-700 px-4 py-2 rounded-md"
                            disabled>Valider</Button>
                        <AlertDialog>
                            <AlertDialogTrigger>
                                <Button v-if="selectedFields.length"
                                    class="bg-red-600 text-white hover:bg-red-700 px-4 py-2 rounded-md">Valider</Button>
                            </AlertDialogTrigger>
                            <AlertDialogContent>
                                <AlertDialogHeader>
                                    <AlertDialogTitle>Confirmer la
                                        validation</AlertDialogTitle>
                                    <AlertDialogDescription>
                                        Êtes-vous sûr de vouloir rejeter ce
                                        dossier ?
                                    </AlertDialogDescription>
                                </AlertDialogHeader>
                                <AlertDialogFooter class="flex justify-center gap-4 mt-6">
                                    <AlertDialogCancel
                                        class="bg-gray-200 text-gray-800 hover:bg-gray-300 px-4 py-2 rounded-md">
                                        Annuler
                                    </AlertDialogCancel>
                                    <AlertDialogAction
                                        class="bg-green-600 text-white hover:bg-red-700 px-4 py-2 rounded-md"
                                        @click="saveSelection">
                                        Confirmer
                                    </AlertDialogAction>
                                </AlertDialogFooter>
                            </AlertDialogContent>
                        </AlertDialog>
                        <Button @click="clearSelection"
                            class="bg-gray-200 text-gray-800 hover:bg-gray-300 px-4 py-2 rounded-md">Annuler</Button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import Rejet from "./Rejet.vue";
import { ref, computed, onMounted } from "vue";
import { returnBack } from "/resources/js/composable/fonction.js";
import {
    MoveRight,
    MoveLeft,
    HandCoins,
    CreditCard,
    Eye,
    CircleAlert,
    ChevronLeft,
    ChevronRight,
} from "lucide-vue-next";
import { Card } from "@/components/ui/card";
import { ScrollArea } from "@/components/ui/scroll-area";
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
import InfoRow from "./InfoRow.vue";
import axios from "axios";
import { Toaster, toast } from "vue-sonner";
import { router } from "@inertiajs/vue3";
import { Checkbox } from "@/components/ui/checkbox";
import { Label } from "@/components/ui/label";

const props = defineProps({
    vin: String,
    dossier: Object,
    dossier_lier: Object,
    client: Object,
    old: Object,
    new: Object,
});

let details = props.dossier.detail;

// Si c'est une string JSON → on parse
if (typeof details === "string") {
    try {
        details = JSON.parse(details);
    } catch (e) {
        // Si ça ne marche pas, fallback sur split()
        details = details.split(",").map((v) => v.trim());
    }
}

details = details.map((v) => String(v).trim().toLowerCase());

const search = ["Changement de Couleur", "Usage"].map((v) => v.toLowerCase());

const result = details.some((item) => search.includes(item));

console.log("details normalisés:", details);
console.log("résultat:", result);

onMounted(() => {
    if (props.dossier.r_dossier_vehicule.entreprise_id) {
        fetchEntreprise(props.dossier.r_dossier_vehicule.entreprise_id);
    }
});
const oldData = computed(() => props.old);
const newData = computed(() => props.new);
const entreprise = ref(null);
function isDifferent(path) {
    const oldVal = getNestedValue(oldData.value, path);
    const newVal = getNestedValue(newData.value, path);
    return oldVal !== newVal;
}

function getNestedValue(obj, path) {
    return path.split(".").reduce((acc, key) => acc?.[key], obj);
}

// Exclusion des champs non pertinents
const keysToExclude = [
    "id",
    "id_dossier",
    "type_document",
    "created_at",
    "updated_at",
];

// On prend le premier élément du tableau (car un seul objet avec tous les documents)
const documentData = computed(() => {
    return props.dossier.r_dossier_documents?.[0] || {};
});

// Filtrage des documents valides
const filteredDocs = computed(() => {
    return Object.entries(documentData.value).filter(([key, value]) => {
        // Exclure les clés spécifiques
        if (keysToExclude.includes(key)) return false;

        // Exclure les null
        if (!value) return false;

        // Exclure les nombres (id, id_dossier...)
        if (typeof value === "number") return false;

        // Exclure les "piece-identite"
        if (value === "piece-identite") return false;

        // Garder seulement les chemins formatés comme fichiers
        // (ex: "numerisations/xxxxx.png")
        if (typeof value === "string" && value.startsWith("numerisations/")) {
            return true;
        }

        return false;
    });
});

const selectedIndex = ref(0);
const docsArray = computed(() => {
    return filteredDocs.value.map(([key, value]) => ({
        key,
        label: formatLabel(key),
        src: `/storage/${value}`,
    }));
});

// Prévisualisation
const selectedDoc = ref(null);

function formatLabel(key) {
    return key.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
}

async function confirmerValidation(id) {
    console.log("Validation du dossier avec ID:", id);
    try {
        const response = await axios.post("/minister/mt1/dossiers/valider", {
            dossier_id: id,
            type_service: result,
        });

        // Afficher le message de Confirmation
        toast.success(response.data.message);
        //
        router.visit(window.location.pathname, {
            only: ["dossier"], // recharge uniquement cette prop
            preserveScroll: true,
            preserveState: true,
        });
    } catch (error) {
        console.error(error);
        toast.error(
            "Une erreur s'est produite lors de la validation du dossier.",
        );
    }
}

// ✅ Fonction pour charger les données
async function fetchEntreprise(id) {
    try {
        const response = await axios.get(`/entreprises/${id}`);
        entreprise.value = response.data.data; // On stocke uniquement la partie data
        // console.log("Données de l'entreprise :", entreprise.value);
    } catch (error) {
        console.error("Erreur :", error.response?.data || error);
        entreprise.value = null;
    }
}

//getion rejet dossier
const showRejectModal = ref(false);
const items = ref([]);
const selectedFields = ref([]);

// Toggle d'une case
function toggleField(fieldKey) {
    const index = selectedFields.value.indexOf(fieldKey);
    if (index === -1) {
        selectedFields.value.push(fieldKey);
    } else {
        selectedFields.value.splice(index, 1);
    }
}

// Charger les motifs depuis l'API
const fetchItems = async () => {
    try {
        const res = await fetch(`/minister/mt1/get/rejets/data?id_service=${props.dossier.id_service}`);
        const data = await res.json();
        console.log(" ✅ Données des motifs :", data);
        items.value = data;
    } catch (err) {
        console.error("Erreur de chargement des motifs :", err);
    }
};

// Sauvegarder les sélections
const saveSelection = async () => {
    console.log("✅ iddossier:", props.dossier.id);
    try {
        console.log("✅ Éléments sélectionnés :", selectedFields.value);
        const response = await axios.post(
            "/minister/mt1/save/motif/rejets",
            {
                selected: selectedFields.value,
                id: props.dossier.id,
            },
            { headers: { "Content-Type": "application/json" } },
        );
        showRejectModal.value = false;
        //update prop dossier
        router.visit(window.location.pathname, {
            only: ["dossier"], // recharge uniquement cette prop
            preserveScroll: true,
            preserveState: true,
        });
        toast.success(response.data.message);
    } catch (err) {
        console.error(err);
    }
};

// Réinitialiser la sélection
const clearSelection = () => {
    selectedFields.value = [];
};

const motifDossierRejeter = ref("");

const fetchMotifsRejets = async () => {
    if (!motifDossierRejeter.value) {
        try {
            const response = await axios.get(
                `/minister/mt1/get/motifs/rejets/${props.dossier.id}`,
            );
            motifDossierRejeter.value = response.data.motifs;
        } catch (err) {
            console.error("Erreur de chargement des motifs sélectionnés:", err);
        }
    }
};
onMounted(fetchItems);
</script>

<script>
import Main from "/resources/js/Pages/Main.vue";

export default {
    layout: Main,
};
</script>

<style scoped>
/* Optionnel pour responsive / animation */
</style>
