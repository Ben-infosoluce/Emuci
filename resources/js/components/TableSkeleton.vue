<!-- components/TableSkeleton.vue -->
<template>
    <TableRow>
        <TableCell>
            <Skeleton class="h-4 w-8" />
        </TableCell>
        <TableCell>
            <Skeleton class="h-4 w-24" />
        </TableCell>
        <TableCell>
            <Skeleton class="h-4 w-20" />
        </TableCell>
        <TableCell>
            <Skeleton class="h-4 w-28" />
        </TableCell>
        <TableCell>
            <Skeleton class="h-4 w-24" />
        </TableCell>
        <TableCell>
            <Skeleton class="h-4 w-24" />
        </TableCell>
        <TableCell>
            <Skeleton class="h-4 w-20" />
        </TableCell>
        <TableCell class="text-right">
            <Skeleton class="h-4 w-10 ml-auto" />
        </TableCell>
    </TableRow>
</template>

<script setup>
import { Skeleton } from '@/components/ui/skeleton'
defineProps({
    rows: {
        type: Number,
        default: 10,
    },
})
</script>
