import { cva } from 'class-variance-authority';

export { default as Badge } from './Badge.vue';

export const badgeVariants = cva(
  'inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
  {
    variants: {
      variant: {
        default:
          'border-transparent bg-primary text-primary-foreground shadow hover:bg-primary/80',
        secondary:
          'border-transparent bg-secondary text-secondary-foreground shadow hover:bg-secondary/80',
        destructive:
          'border-transparent bg-destructive text-destructive-foreground shadow hover:bg-destructive/80',
        outline:
          'border-transparent bg-muted text-foreground shadow hover:bg-muted/80',
        success:
          'border-transparent bg-green-100 text-green-800 shadow hover:bg-green-200',
        warning:
          'border-transparent bg-yellow-100 text-yellow-800 shadow hover:bg-yellow-200',
        info:
          'border-transparent bg-blue-100 text-blue-800 shadow hover:bg-blue-200',
        link: 'text-primary underline-offset-4 hover:underline',
        error: 'border-transparent bg-red-100 text-red-800 shadow hover:bg-red-200',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);
