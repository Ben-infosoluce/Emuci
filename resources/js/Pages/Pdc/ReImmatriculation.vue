<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-2">Welcome back!</h1>
        <p class="text-sm text-muted-foreground mb-6">Here's a list of your tasks for this month!</p>

        <div class="flex gap-2 mb-4">
            <Input v-model="search" placeholder="Filter tasks…" class="max-w-sm" />
            <Button variant="outline">+ Status</Button>
            <Button variant="outline">+ Priority</Button>
        </div>

        <Card>
            <CardContent class="p-0">
                <table class="w-full text-sm">
                    <thead class="border-b">
                        <tr class="text-left text-muted-foreground">
                            <th class="px-4 py-2"><input type="checkbox" /></th>
                            <th class="px-4 py-2">Task</th>
                            <th class="px-4 py-2">Title</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Priority</th>
                            <th class="px-4 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="task in paginatedTasks" :key="task.id" class="border-b">
                            <td class="px-4 py-2"><input type="checkbox" /></td>
                            <td class="px-4 py-2 font-medium">{{ task.id }}</td>
                            <td class="px-4 py-2">
                                <Badge variant="secondary" class="mr-2">{{ task.type }}</Badge>
                                {{ task.title }}
                            </td>
                            <td class="px-4 py-2">{{ task.status }}</td>
                            <td class="px-4 py-2">{{ task.priority }}</td>
                            <td class="px-4 py-2 text-right">⋮</td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>

            <CardFooter class="justify-between px-4 py-2">
                <div class="text-sm text-muted-foreground">
                    {{ selectedCount }} of {{ tasks.length }} row(s) selected.
                </div>
                <div class="flex items-center gap-2">
                    <Select v-model="rowsPerPage">
                        <SelectTrigger class="w-[80px]">
                            <SelectValue placeholder="10" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="5">5</SelectItem>
                            <SelectItem value="10">10</SelectItem>
                            <SelectItem value="20">20</SelectItem>
                        </SelectContent>
                    </Select>
                    <span class="text-sm">Page {{ currentPage }} of {{ totalPages }}</span>
                    <Button variant="ghost" size="sm" @click="prevPage">‹</Button>
                    <Button variant="ghost" size="sm" @click="nextPage">›</Button>
                </div>
            </CardFooter>
        </Card>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter } from '@/components/ui/card'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'

const search = ref('')
const rowsPerPage = ref(10)
const currentPage = ref(1)

const tasks = ref([
    { id: 'TASK-8782', title: "You can't compress the program...", type: 'Documentation', status: 'In Progress', priority: 'Medium' },
    { id: 'TASK-7878', title: 'Try to calculate the EXE feed...', type: 'Documentation', status: 'Backlog', priority: 'Medium' },
    { id: 'TASK-7839', title: 'We need to bypass the neural TCP card!', type: 'Bug', status: 'Todo', priority: 'High' },
    { id: 'TASK-5562', title: 'The SAS interface is down...', type: 'Feature', status: 'Backlog', priority: 'Medium' },
    { id: 'TASK-8686', title: 'I\'ll parse the wireless SSL protocol...', type: 'Feature', status: 'Canceled', priority: 'Medium' },
    { id: 'TASK-1280', title: 'Use the digital TLS panel...', type: 'Bug', status: 'Done', priority: 'High' },
    { id: 'TASK-7262', title: 'The UTF8 application is down...', type: 'Feature', status: 'Done', priority: 'High' },
    { id: 'TASK-1138', title: 'Generating the driver won\'t do anything...', type: 'Feature', status: 'In Progress', priority: 'Medium' },
    { id: 'TASK-7184', title: 'We need to program the back-end...', type: 'Feature', status: 'Todo', priority: 'Low' },
    { id: 'TASK-5160', title: 'Calculating the bus won\'t do anything...', type: 'Documentation', status: 'In Progress', priority: 'High' }
])



const filteredTasks = computed(() => {
    if (!search.value) return tasks.value
    return tasks.value.filter(task =>
        task.title.toLowerCase().includes(search.value.toLowerCase())
    )
})

const totalPages = computed(() => Math.ceil(filteredTasks.value.length / parseInt(rowsPerPage.value)))
const paginatedTasks = computed(() => {
    const start = (currentPage.value - 1) * parseInt(rowsPerPage.value)
    return filteredTasks.value.slice(start, start + parseInt(rowsPerPage.value))
})

function nextPage() {
    if (currentPage.value < totalPages.value) currentPage.value++
}

function prevPage() {
    if (currentPage.value > 1) currentPage.value--
}

const selectedCount = ref(0) // à gérer si tu veux des cases cochées


</script>

<script>

import Main from '/resources/js/Pages/Main.vue';
export default {
    layout: Main,
};</script>