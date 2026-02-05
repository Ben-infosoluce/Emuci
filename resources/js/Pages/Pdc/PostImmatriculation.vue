<template>
    <div class="p-4 max-w-5xl mx-auto space-y-4">
        <Input v-model="search" @input="updateSearch" placeholder="Rechercher par titre" class="w-full" />

        <Card>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>ID</TableHead>
                            <TableHead>Titre</TableHead>
                            <TableHead>Contenu</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow v-for="post in posts" :key="post.id">
                            <TableCell>{{ post.id }}</TableCell>
                            <TableCell>{{ post.title }}</TableCell>
                            <TableCell>{{ post.body }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>

        <div class="flex justify-between items-center mt-4">
            <Button @click="prevPage" :disabled="page <= 1" variant="outline">Précédent</Button>
            <span class="text-sm text-muted-foreground">Page {{ page }}</span>
            <Button @click="nextPage" :disabled="page >= maxPage" variant="outline">Suivant</Button>
        </div>
    </div>
</template>

<script setup>
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import DateRangePicker from '@/components/ui/DateRangePicker.vue'
import UserNav from '@/components/ui/UserNav.vue'


import { ref, onMounted } from 'vue'

// shadcn-vue components
import { Input } from '@/components/ui/input'
import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableHead,
    TableCell,
} from '@/components/ui/table'

const posts = ref([])
const page = ref(1)
const search = ref('')
const loading = ref(false)
const maxPage = 10

onMounted(() => {
    const params = new URLSearchParams(window.location.search)
    page.value = Number(params.get('page')) || 1
    search.value = params.get('search') || ''
    fetchPosts()
})

function updateURL() {
    const params = new URLSearchParams()
    if (search.value) params.set('search', search.value)
    if (page.value) params.set('page', page.value)
    // history.pushState(null, '', `?${params.toString()}`)
}

async function fetchPosts() {
    loading.value = true
    const start = (page.value - 1) * 10
    const res = await fetch(`https://jsonplaceholder.typicode.com/posts?_start=${start}&_limit=10`)
    let data = await res.json()

    // Filtrer localement
    if (search.value) {
        data = data.filter(p => p.title.toLowerCase().includes(search.value.toLowerCase()))
    }

    posts.value = data
    loading.value = false
    updateURL()
}

function nextPage() {
    if (page.value < maxPage) {
        page.value++
        fetchPosts()
    }
}
function prevPage() {
    if (page.value > 1) {
        page.value--
        fetchPosts()
    }
}
function updateSearch() {
    page.value = 1
    fetchPosts()
}

// Écoute du bouton "précédent navigateur"
window.addEventListener('popstate', () => {
    const params = new URLSearchParams(window.location.search)
    page.value = Number(params.get('page')) || 1
    search.value = params.get('search') || ''
    fetchPosts()
})
</script>

<script>

import Main from '/resources/js/Pages/Main.vue';
export default {
    layout: Main,
};</script>


<style>
.input {
    width: 100%;
    padding: 0.5rem;
    margin-bottom: 1rem;
}

.post-item {
    margin-bottom: 1rem;
}

.pagination {
    display: flex;
    justify-content: space-between;
    margin-top: 1rem;
}
</style>