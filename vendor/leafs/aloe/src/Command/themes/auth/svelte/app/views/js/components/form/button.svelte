<script>
    import { cva } from "class-variance-authority";
    import { cn } from "@/utils";

    const { class: className, variant, size, as = "button", children, ...props } = $props();

    const buttonVariants = cva(
        "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg text-sm font-medium ring-offset-background transition-colors focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none",
        {
            variants: {
                variant: {
                    default:
                        "bg-primary text-primary-foreground hover:bg-primary/90",
                    destructive:
                        "bg-destructive text-destructive-foreground hover:bg-destructive/90",
                    outline:
                        "border border-input bg-background hover:bg-accent hover:text-accent-foreground",
                    secondary:
                        "bg-secondary text-secondary-foreground hover:bg-secondary/80",
                    ghost: "hover:bg-accent hover:text-accent-foreground",
                    link: "text-primary underline-offset-4 hover:underline",
                },
                size: {
                    default: "h-10 px-4 py-2",
                    sm: "h-9 rounded-lg px-3",
                    lg: "h-11 rounded-lg px-8",
                    icon: "h-10 w-10",
                },
            },
            defaultVariants: {
                variant: "default",
                size: "default",
            },
        },
    );
</script>

{#if as === "button"}
    <button
        class={cn(buttonVariants({ variant, size, className }))}
        {...props}
    >{@render children()}</button>
{:else}
    <a
        class={cn(buttonVariants({ variant, size, className }))}
        {...props}
    >{@render children()}</a>
{/if}
