<script setup>
import { cn } from '@/utils';
import { Check } from 'lucide-vue-next';
import {
    DropdownMenuCheckboxItem,
    DropdownMenuItemIndicator,
    useForwardPropsEmits,
} from 'radix-vue';
import { computed } from 'vue';

const props = defineProps({
    class: {
        type: String,
        default: '',
    },
    checked: {
        type: Boolean,
        default: false,
    },
});

const emits = defineEmits(['update:checked']);

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;

    return delegated;
});

const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <DropdownMenuCheckboxItem
        v-bind="forwarded"
        :class="
            cn(
                'relative flex cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none transition-colors focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
                props.class,
            )
        "
    >
        <span class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
            <DropdownMenuItemIndicator>
                <Check class="h-4 w-4" />
            </DropdownMenuItemIndicator>
        </span>
        <slot />
    </DropdownMenuCheckboxItem>
</template>
