<script setup>
import { cn } from '@/utils';

const props = defineProps({
    class: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <li :class="cn('inline-flex items-center gap-1.5', props.class)">
        <slot />
    </li>
</template>
