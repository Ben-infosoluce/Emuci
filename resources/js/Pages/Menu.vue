<template>
    <div class="h-full px-3 py-4 overflow-y-auto  dark:bg-gray-800 bg-[#1C2434]">
        <!-- Pool de Controle -->
        <template v-if="user_type == 'PoolControle'">
            <ul class="space-y-4 font-medium mt-6" v-if="true">
                <li :class="{ 'active-page': $page.component == 'Pdc/Dashbord' }">
                    <Link :href="route('show.pdc.dashboard')">
                        <a
                            class="flex items-center p-2 text-white  hover:text-black  rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <LayoutDashboard />
                            <span class="ms-3">Dashboard</span>
                        </a>
                    </Link>
                </li>
                <li :class="{ 'active-page': $page.component.startsWith('Pdc/Immatriculation') }"
                    v-if="user_permissions.includes(1)">
                    <!-- <Link :href="route('show.pdc.immatriculation')"> -->
                    <Link :href="route('show.pdc.immatriculation.select')">
                        <a
                            class="flex items-center p-2 text-white  hover:text-black  rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <svg class="flex-shrink-0 w-4 h-4 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 18 18">
                                <path
                                    d="M6.143 0H1.857A1.857 1.857 0 0 0 0 1.857v4.286C0 7.169.831 8 1.857 8h4.286A1.857 1.857 0 0 0 8 6.143V1.857A1.857 1.857 0 0 0 6.143 0Zm10 0h-4.286A1.857 1.857 0 0 0 10 1.857v4.286C10 7.169 10.831 8 11.857 8h4.286A1.857 1.857 0 0 0 18 6.143V1.857A1.857 1.857 0 0 0 16.143 0Zm-10 10H1.857A1.857 1.857 0 0 0 0 11.857v4.286C0 17.169.831 18 1.857 18h4.286A1.857 1.857 0 0 0 8 16.143v-4.286A1.857 1.857 0 0 0 6.143 10Zm10 0h-4.286A1.857 1.857 0 0 0 10 11.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 18 16.143v-4.286A1.857 1.857 0 0 0 16.143 10Z" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Opération-Spécial
                            </span>
                        </a>
                    </Link>
                </li>
                <li :class="{ 'active-page': $page.component.startsWith('Pdc/ReImmatriculation') }"
                    v-if="user_permissions.includes(2)">
                    <Link :href="route('show.pdc.re.immatriculation.select')">
                        <a
                            class="flex items-center p-2 text-white  hover:text-black  rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 16 16">
                                <path clip-rule="evenodd"
                                    d="M16 8C16 3.58172 12.4183 -1.93129e-07 8 0C3.58172 1.93129e-07 -1.93129e-07 3.58172 0 8C1.93129e-07 12.4183 3.58172 16 8 16C12.4183 16 16 12.4183 16 8ZM8 4V7H3V9H8V12H9L13 8L9 4H8Z"
                                    fill-rule="evenodd" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Re-immatriculation</span>
                        </a>
                    </Link>
                </li>
                <li :class="{ 'active-page': $page.component.startsWith('Pdc/PostImmatriculation') }"
                    v-if="user_permissions.includes(3)">
                    <Link :href="route('show.pdc.post.immatriculation.select')">
                        <a
                            class="flex items-center p-2 text-white  hover:text-black  rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="m17.418 3.623-.018-.008a6.713 6.713 0 0 0-2.4-.569V2h1a1 1 0 1 0 0-2h-2a1 1 0 0 0-1 1v2H9.89A6.977 6.977 0 0 1 12 8v5h-2V8A5 5 0 1 0 0 8v6a1 1 0 0 0 1 1h8v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-4h6a1 1 0 0 0 1-1V8a5 5 0 0 0-2.582-4.377ZM6 12H4a1 1 0 0 1 0-2h2a1 1 0 0 1 0 2Z" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Post-immatriculation</span>
                        </a>
                    </Link>
                </li>
                <li :class="{ 'active-page': $page.component.startsWith('Pdc/Duplicata') }"
                    v-if="user_permissions.includes(4)">
                    <Link :href="route('show.pdc.duplicata.select')">
                        <a
                            class="flex items-center p-2 text-white  hover:text-black  rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="m17.418 3.623-.018-.008a6.713 6.713 0 0 0-2.4-.569V2h1a1 1 0 1 0 0-2h-2a1 1 0 0 0-1 1v2H9.89A6.977 6.977 0 0 1 12 8v5h-2V8A5 5 0 1 0 0 8v6a1 1 0 0 0 1 1h8v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-4h6a1 1 0 0 0 1-1V8a5 5 0 0 0-2.582-4.377ZM6 12H4a1 1 0 0 1 0-2h2a1 1 0 0 1 0 2Z" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Duplicata</span>
                        </a>
                    </Link>
                </li>
            </ul>
        </template>


        <!-- Numerisation -->
        <template v-if="user_type == 'Numerisation'">
            <ul class="space-y-4 font-medium mt-6" v-if="true">
                <li :class="{ 'active-page': $page.component == 'Numerisation/Dashbord' }">
                    <Link :href="route('show.numerisation.dashboard')">
                        <a
                            class="flex items-center p-2 text-white  hover:text-black  rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <LayoutDashboard />
                            <span class="ms-3">Dashboard</span>
                        </a>
                    </Link>
                </li>
                <li
                    :class="{ 'active-page': $page.component.startsWith('Numerisation/') && $page.component != 'Numerisation/Dashbord' }">
                    <Link :href="route('show.numerisation.list')">
                        <a
                            class="flex items-center p-2 text-white  hover:text-black  rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                width="24" height="24" viewBox="0 0 24 24" fill="currentColor"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M13 9H18L13 4V9ZM6 22C5.45 22 4.97933 21.8043 4.588 21.413C4.19667 21.0217 4.00067 20.5507 4 20V17H20V20C20 20.55 19.8043 21.021 19.413 21.413C19.0217 21.805 18.5507 22.0007 18 22H6ZM1 15V13H23V15H1ZM4 11V4C4 3.45 4.196 2.97933 4.588 2.588C4.98 2.19667 5.45067 2.00067 6 2H14L20 8V11H4Z" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Numérisation</span>
                        </a>
                    </Link>
                </li>


            </ul>
        </template>


        <!-- Caisse -->
        <template v-if="user_type == 'Caisse'">
            <ul class="space-y-4 font-medium mt-6" v-if="true">
                <li :class="{ 'active-page': $page.component == 'Caisse/Dashbord' }">
                    <Link :href="route('show.caisse.dashboard')">
                        <a
                            class="flex items-center p-2 text-white  hover:text-black  rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <LayoutDashboard />
                            <span class="ms-3">Dashboard</span>
                        </a>
                    </Link>
                </li>
                <li
                    :class="{ 'active-page': $page.component.startsWith('Caisse/') && $page.component != 'Caisse/Dashbord' }">
                    <Link :href="route('show.caisse.data')">
                        <a
                            class="flex items-center p-2 text-white  hover:text-black  rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <RectangleEllipsis />
                            <span class="flex-1 ms-3 whitespace-nowrap">Paiement</span>
                        </a>
                    </Link>
                </li>

            </ul>
        </template>
        <!-- Minister -->


        <!-- Mt1 -->
        <template v-if="user_type == 'MT1'">
            <ul class="space-y-4 font-medium mt-6" v-if="true">
                <li :class="{
                    'active-page': $page.component == 'Minister/Mt1/Dashbord'
                }">
                    <Link :href="route('show.mt1.dashboard')">
                        <a
                            class="flex items-center p-2 text-white  hover:text-black  rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <LayoutDashboard />
                            <span class="ms-3">Dashboard</span>
                        </a>
                    </Link>
                </li>
                <li
                    :class="{ 'active-page': $page.component.startsWith('Minister/Mt1/') && $page.component != 'Minister/Mt1/Dashbord' }">
                    <Link :href="route('show.mt1.list')">
                        <a
                            class="flex items-center p-2 text-white  hover:text-black  rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                width="24" height="24" viewBox="0 0 24 24" fill="currentColor"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M4.11436 0.931152L2.65826 6.9955H0V8.74841H0.911816V20.069H3.52939V18.2159H19.4706V20.069H22.0868V8.74841H23V6.9955H20.3417L18.8856 0.931152H4.11436ZM4.89011 2.11029H18.1096L19.2824 6.99562H3.71707L4.8898 2.11029H4.89011ZM1.66919 10.2312L5.31929 11.5792V13.3307L1.66919 11.9827V10.2312ZM21.329 10.2312V11.9827L17.6804 13.3307V11.5778L21.329 10.2312ZM7.97013 14.8133H15.0272V16.5648H7.97013V14.8133Z"
                                    fill="white" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Enrolements</span>
                        </a>
                    </Link>
                </li>
            </ul>
        </template>


        <!-- Mt2 -->
        <template v-if="user_type == 'MT2'">
            <ul class="space-y-4 font-medium mt-6" v-if="true">
                <li :class="{
                    'active-page': $page.component == 'Minister/Mt2/Dashbord'
                }">
                    <Link :href="route('show.mt1.dashboard')">
                        <a
                            class="flex items-center p-2 text-white  hover:text-black  rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <LayoutDashboard />
                            <span class="ms-3">Dashboard</span>
                        </a>
                    </Link>
                </li>
                <li
                    :class="{ 'active-page': $page.component.startsWith('Minister/Mt2/') && $page.component != 'Minister/Mt2/Dashbord' }">
                    <Link :href="route('show.mt2.list')">
                        <a
                            class="flex items-center p-2 text-white  hover:text-black  rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                width="24" height="24" viewBox="0 0 24 24" fill="currentColor"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M4.11436 0.931152L2.65826 6.9955H0V8.74841H0.911816V20.069H3.52939V18.2159H19.4706V20.069H22.0868V8.74841H23V6.9955H20.3417L18.8856 0.931152H4.11436ZM4.89011 2.11029H18.1096L19.2824 6.99562H3.71707L4.8898 2.11029H4.89011ZM1.66919 10.2312L5.31929 11.5792V13.3307L1.66919 11.9827V10.2312ZM21.329 10.2312V11.9827L17.6804 13.3307V11.5778L21.329 10.2312ZM7.97013 14.8133H15.0272V16.5648H7.97013V14.8133Z"
                                    fill="white" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Enrolements</span>
                        </a>
                    </Link>
                </li>
            </ul>
        </template>

        <!-- Admin -->


        <!-- Site -->
        <template v-if="user_type == 'Admin'">
            <ul class="space-y-4 font-medium mt-6" v-if="true">
                <li :class="{
                    'active-page': $page.component == 'Admin/Dashbord',
                }">
                    <Link :href="route('show.admin.dashboard')">
                        <a
                            class="flex items-center p-2 text-white hover:text-black rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <LayoutDashboard />
                            <span class="ms-3">Dashboard</span>
                        </a>
                    </Link>
                </li>
                <li :class="{
                    'active-page':
                        $page.component.startsWith('Admin/') &&
                        $page.component == 'Admin/site',
                }">
                    <Link :href="route('show.admin.data')">
                        <a
                            class="flex items-center p-2 text-white hover:text-black rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <ArrowsUpFromLine />
                            <span class="flex-1 ms-3 whitespace-nowrap">Sites</span>
                        </a>
                    </Link>
                </li>
                <li :class="{
                    'active-page':
                        $page.component.startsWith('Admin/') &&
                        $page.component == 'Admin/ElementFacturation',
                }">
                    <Link :href="route('show.element.facturation.list')">
                        <a
                            class="flex items-center p-2 text-white hover:text-black rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <Layers2 />
                            <span class="flex-1 ms-3 whitespace-nowrap">Elements à facturer</span>
                        </a>
                    </Link>
                </li>
                <li :class="{
                    'active-page':
                        $page.component.startsWith('Admin/') &&
                        $page.component == 'Admin/entite',
                }">
                    <Link :href="route('show.entite.list')">
                        <a
                            class="flex items-center p-2 text-white hover:text-black rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <SquareStack />
                            <span class="flex-1 ms-3 whitespace-nowrap">Entités</span>
                        </a>
                    </Link>
                </li>
                <li :class="{
                    'active-page':
                        $page.component.startsWith('Admin/') &&
                        $page.component == 'Admin/user',
                }">
                    <Link :href="route('user.index')">
                        <a
                            class="flex items-center p-2 text-white hover:text-black rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <User />
                            <span class="flex-1 ms-3 whitespace-nowrap">Utilisateurs</span>
                        </a>
                    </Link>
                </li>

                <li :class="{
                    'active-page':
                        $page.component.startsWith('Admin/') &&
                        $page.component == 'Admin/client',
                }">
                    <Link :href="route('client.liste')">
                        <a
                            class="flex items-center p-2 text-white hover:text-black rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <UsersRound />
                            <span class="flex-1 ms-3 whitespace-nowrap">Clients</span>
                        </a>
                    </Link>
                </li>
                <li :class="{
                    'active-page': $page.component.startsWith('Admin/Marques/'),
                }">
                    <Link :href="route('show.marques.data')">
                        <a
                            class="flex items-center p-2 text-white hover:text-black rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <Car />
                            <span class="flex-1 ms-3 whitespace-nowrap">Marques</span>
                        </a>
                    </Link>
                </li>
                <li :class="{
                    'active-page': $page.component.startsWith('Admin/Models/'),
                }">
                    <Link :href="route('show.models.data')">
                        <a
                            class="flex items-center p-2 text-white hover:text-black rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <Columns />
                            <span class="flex-1 ms-3 whitespace-nowrap">Modèles</span>
                        </a>
                    </Link>
                </li>
                <li :class="{
                    'active-page': $page.component.startsWith('Admin/Rejets/'),
                }">
                    <Link :href="route('rejets.index')">
                        <a
                            class="flex items-center p-2 text-white hover:text-black rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <ChartColumnStacked />
                            <span class="flex-1 ms-3 whitespace-nowrap">Rejets</span>
                        </a>
                    </Link>
                </li>
            </ul>
        </template>

        <!-- Boss -->
        <template v-if="user_type == 'Boss'">
            <ul class="space-y-4 font-medium mt-6" v-if="true">
                <li :class="{
                    'active-page': $page.component == 'Boss/Dashbord'
                }">
                    <Link :href="route('show.boss.dashboard')">
                        <a
                            class="flex items-center p-2 text-white  hover:text-black  rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <LayoutDashboard />
                            <span class="ms-3">Dashboard</span>
                        </a>
                    </Link>
                </li>
                <li :class="{
                    'active-page': $page.component == 'Boss/StatsDossiers'
                }">
                    <Link :href="route('show.boss.statistics.dossiers')">
                        <a
                            class="flex items-center p-2 text-white  hover:text-black  rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <LayoutDashboard />
                            <span class="ms-3">Dashboard caisse</span>
                        </a>
                    </Link>
                </li>
                <li :class="{
                    'active-page': $page.component == 'Boss/StatsCaisses'
                }">
                    <Link :href="route('show.boss.statistics.caisses')">
                        <a
                            class="flex items-center p-2 text-white  hover:text-black  rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <LayoutDashboard />
                            <span class="ms-3">Stats Caisse</span>
                        </a>
                    </Link>
                </li>
            </ul>
        </template>

        <!-- CaisseController -->
        <template v-if="user_type == 'CaisseController'">
            <ul class="space-y-4 font-medium mt-6" v-if="true">
                <li :class="{
                    'active-page': $page.component == 'ControlleurCaisse/Dashbord'
                }">
                    <Link :href="route('show.caisse.controller.dashboard')">
                        <a
                            class="flex items-center p-2 text-white  hover:text-black  rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <LayoutDashboard />
                            <span class="ms-3">Dashboard caisse</span>
                        </a>
                    </Link>
                </li>
                <li :class="{
                    'active-page': $page.component == 'ControlleurCaisse/StatsCaisses'
                }">
                    <Link :href="route('show.caisse.controller.statistics.caisses')">
                        <a
                            class="flex items-center p-2 text-white  hover:text-black  rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <ChartSpline />
                            <span class="ms-3">Stats Caisse</span>
                        </a>
                    </Link>
                </li>
            </ul>
        </template>

        <!-- Raf -->
        <template v-if="user_type == 'Raf'">
            <ul class="space-y-4 font-medium mt-6" v-if="true">
                <li :class="{
                    'active-page': $page.component == 'RafCaisse/Dashbord'
                }">
                    <Link :href="route('show.raf.dashboard')">
                        <a
                            class="flex items-center p-2 text-white  hover:text-black  rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <LayoutDashboard />
                            <span class="ms-3">Dashboard </span>
                        </a>
                    </Link>
                </li>
                <li :class="{
                    'active-page': $page.component == 'RafCaisse/RafStatsCaisses'
                }">
                    <Link :href="route('show.raf.caisse.controller.statistics.caisses')">
                        <a
                            class="flex items-center p-2 text-white  hover:text-black  rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <ChartColumnStacked />
                            <span class="ms-3">Gestion Caisses</span>
                        </a>
                    </Link>
                </li>
            </ul>
        </template>
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { storeToRefs } from "pinia";
import { useCaisseStore } from "@/stores/mainStore";
import {
    User, UsersRound, SquareStack, Layers2, ArrowsUpFromLine, LayoutDashboard,
    ChartColumnStacked, RectangleEllipsis, ChartSpline, Car, Columns
} from 'lucide-vue-next';


const store = useCaisseStore();
const { isOpen } = storeToRefs(store);

const user_type = usePage().props.auth_user.data.r_user_role.nom_role;
const user_permissions = usePage().props.auth_user.user_permissions ?? [];

// console.log('usser_permissions', user_permissions);
</script>

<style>
.active-page {
    background-color: #CA7600;
    border-radius: 8px;
}
</style>