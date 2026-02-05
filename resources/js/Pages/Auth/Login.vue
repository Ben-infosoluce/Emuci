<template>
    <div class="flex min-h-screen justify-center items-center bg-cover bg-center bg-no-repeat"
        style="background-image: url('/assets/images/login_bg.svg');">
        <!-- <Toaster richColors position="top-right" /> -->
        <Toaster position="top-right" />
        <Card class="m-12">
            <div class="w-full lg:grid lg:min-h-[600px] lg:grid-cols-2 xl:min-h-[800px] ">
                <div
                    class="hidden  lg:flex lg:items-center lg:justify-center bg-cover bg-center bg-no-repeat p-12 bg-white   m-1">
                    <Link :href="route('home')">
                    <img src="../../../../public/assets/images/digi_connect.svg" alt="Image" width="264" height="191"
                        class="mx-auto rounded">
                    </Link>
                </div>

                <div class="flex items-center justify-center py-12 m-24">
                    <form class="w-2/3 space-y-3 w-full max-w-md" @submit="onSubmit">

                        <div class="mx-auto grid w-[350px] gap-6 m-12">
                            <div class="grid gap-2 text-center">
                                <h1 class="text-3xl font-bold text-[#CA7600]">
                                    Identifiez-vous
                                </h1>
                            </div>
                            <div class="grid gap-4">
                                <div class="grid gap-2">
                                    <!-- Email -->
                                    <FormField v-slot="{ componentField }" name="email">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Email</FormLabel>
                                            <FormControl>
                                                <Input type="email" placeholder="exemple@email.com"
                                                    v-bind="componentField" autocomplete="email" v-model="form.email" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </div>
                                <div class="grid gap-2">
                                    <!-- Mot de passe -->
                                    <FormField v-slot="{ componentField }" name="password">
                                        <FormItem v-auto-animate>
                                            <FormLabel>Mot de passe</FormLabel>
                                            <FormControl>
                                                <Input type="password" placeholder="********" v-bind="componentField"
                                                    autocomplete="new-password" v-model="form.password" />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    </FormField>
                                </div>
                                <Button type="submit" class="w-full my-4 bg-[#CA7600] ">
                                    Se connecter
                                </Button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </Card>
    </div>
</template>


<script setup>
import { Button } from '@/components/ui/button'
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
import { Toaster, toast } from 'vue-sonner'
import { toTypedSchema } from '@vee-validate/zod'
import { vAutoAnimate } from '@formkit/auto-animate/vue'
import { useForm } from 'vee-validate'
import { router } from '@inertiajs/vue3'
import * as z from 'zod'

// Schéma de validation

const formSchema = toTypedSchema(z.object({

    email: z
        .string({
            required_error: "L'email est obligatoire.",
        })
        .email({
            message: 'Invalid email address.',
        }),

    password: z
        .string({
            required_error: 'Le mot de passe est obligatoire.',
        })
        .min(8, {
            message: 'Le mot de passe doit contenir au moins 8 caractères.',
        }),
}))


const form = useForm({
    email: '',
    password: '',
})
const { handleSubmit } = useForm({
    validationSchema: formSchema,
})

const onSubmit = handleSubmit((values) => {
    // console.log(values)
    router.post('user/login', values, {
        onSuccess: () => {
            console.log('success')
            toast.success('Connexion réussie')
        },
        onError: () => {
            console.log('error')
            toast.error('Identifiant incorecte')
        },
    })
})

</script>
