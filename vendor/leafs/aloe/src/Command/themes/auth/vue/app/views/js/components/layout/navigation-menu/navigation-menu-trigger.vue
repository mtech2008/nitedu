<script setup>
import { cn } from '@/utils';
import { computed } from 'vue';
import { ChevronDown } from 'lucide-vue-next';
import {
    NavigationMenuTrigger,
    useForwardProps,
} from 'radix-vue';
import { navigationMenuTriggerStyle } from '.';

const props = defineProps({
    class: {
        type: String,
        default: '',
    },
});

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;
    return delegated;
});

const forwardedProps = useForwardProps(delegatedProps);
</script>

<template>
    <NavigationMenuTrigger v-bind="forwardedProps" :class="cn(navigationMenuTriggerStyle(), 'group', props.class)">
        <slot />
        <ChevronDown class="relative top-px ml-1 h-3 w-3 transition duration-200 group-data-[state=open]:rotate-180"
            aria-hidden="true" />
    </NavigationMenuTrigger>
</template>
