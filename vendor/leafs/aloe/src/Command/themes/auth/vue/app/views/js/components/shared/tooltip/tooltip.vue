<script setup>
import { TooltipRoot, useForwardPropsEmits } from 'radix-vue';

const props = defineProps();
const emits = defineEmits(['update:open']);

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
    <TooltipRoot v-bind="forwarded">
        <slot />
    </TooltipRoot>
</template>
