<template>
    <nav class="flex justify-end mt-4 gap-1" aria-label="Pagination">
        <Button variant="outline" size="sm" :disabled="meta.current_page === 1" @click="goTo(meta.current_page - 1)">
            «
        </Button>

        <template v-for="(page, i) in pages" :key="i">
            <Button v-if="page !== '...'" size="sm" variant="outline" :class="{
                'bg-primary text-primary-foreground hover:bg-primary/90': page === meta.current_page,
            }" @click="goTo(page)">
                {{ page }}
            </Button>
            <span v-else class="px-3 py-2 text-sm text-gray-400 dark:text-gray-600 select-none">
                ...
            </span>
        </template>

        <Button variant="outline" size="sm" :disabled="meta.current_page === meta.last_page"
            @click="goTo(meta.current_page + 1)">
            »
        </Button>
    </nav>
</template>

<script setup>
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
const props = defineProps({
    meta: Object,
})
const emit = defineEmits(['page-change'])

function goTo(page) {
    if (page !== props.meta.current_page) {
        emit('page-change', page)
    }
}

const pages = computed(() => {
    const total = props.meta.last_page
    const current = props.meta.current_page
    const out = []

    if (total <= 6) {
        return Array.from({ length: total }, (_, i) => i + 1)
    }

    out.push(1)

    if (current <= 3) {
        out.push(2, 3, 4, '...')
    } else if (current >= total - 2) {
        out.push('...', total - 3, total - 2, total - 1)
    } else {
        out.push('...', current - 1, current, current + 1, '...')
    }

    out.push(total)
    return out
})
</script>
