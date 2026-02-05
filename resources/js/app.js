import "./bootstrap";
import "../css/app.css";

import { createApp, h } from "vue";
import { createInertiaApp, Head, Link } from "@inertiajs/vue3";
import { usePoll } from "@inertiajs/vue3";
import { Toaster, toast } from 'vue-sonner'
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import 'flowbite';
import { createPinia } from 'pinia';
// import Main from "./Layouts/Main.vue";

createInertiaApp({
    title: (title) => `EMUCI ${title}`,
    // resolve: (name) => {
    //     const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
    //     let page = pages[`./Pages/${name}.vue`];

    //     // page.default.layout = page.default.layout || Main;
    //     // return page;
    // },
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        return pages[`./Pages/${name}.vue`]
    },
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue)
            .component("Head", Head)
            .component("usePoll", usePoll)
            .component("Link", Link)
            .component("Toaster", Toaster)
            .mount(el);
    },
    progress: {
        color: "#fff",
        showSpinner: true,
    },
});
