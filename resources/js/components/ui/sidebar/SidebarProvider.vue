<script>
import { cn } from '@/lib/utils'
import { useEventListener, useMediaQuery, useVModel } from '@vueuse/core'
import { TooltipProvider } from 'radix-vue'
import { computed, ref } from 'vue'
import { provideSidebarContext, SIDEBAR_COOKIE_MAX_AGE, SIDEBAR_COOKIE_NAME, SIDEBAR_KEYBOARD_SHORTCUT, SIDEBAR_WIDTH, SIDEBAR_WIDTH_ICON } from './utils'

export default {
  name: 'SidebarProvider',
  props: {
    defaultOpen: {
      type: Boolean,
      default: true
    },
    open: {
      type: Boolean,
      default: undefined
    },
    class: {
      type: [String, Object, Array],
      default: ''
    }
  },
  emits: ['update:open'],
  setup(props, { emit, attrs }) {
    const isMobile = useMediaQuery('(max-width: 768px)')
    const openMobile = ref(false)

    const open = useVModel(props, 'open', emit, {
      defaultValue: props.defaultOpen ?? false,
      passive: (props.open === undefined)
    })

    function setOpen(value) {
      open.value = value

      // This sets the cookie to keep the sidebar state.
      document.cookie = `${SIDEBAR_COOKIE_NAME}=${open.value}; path=/; max-age=${SIDEBAR_COOKIE_MAX_AGE}`
    }

    function setOpenMobile(value) {
      openMobile.value = value
    }

    // Helper to toggle the sidebar.
    function toggleSidebar() {
      return isMobile.value ? setOpenMobile(!openMobile.value) : setOpen(!open.value)
    }

    useEventListener('keydown', (event) => {
      if (event.key === SIDEBAR_KEYBOARD_SHORTCUT && (event.metaKey || event.ctrlKey)) {
        event.preventDefault()
        toggleSidebar()
      }
    })

    // We add a state so that we can do data-state="expanded" or "collapsed".
    // This makes it easier to style the sidebar with Tailwind classes.
    const state = computed(() => open.value ? 'expanded' : 'collapsed')

    provideSidebarContext({
      state,
      open,
      setOpen,
      isMobile,
      openMobile,
      setOpenMobile,
      toggleSidebar,
    })

    const combinedClass = computed(() => {
      return cn('group/sidebar-wrapper flex min-h-svh w-full has-[[data-variant=inset]]:bg-sidebar', props.class)
    })

    return {
      isMobile,
      openMobile,
      open,
      state,
      SIDEBAR_WIDTH,
      SIDEBAR_WIDTH_ICON,
      combinedClass,
      attrs
    }
  }
}
</script>

<template>
  <TooltipProvider :delay-duration="0">
    <div :style="{
      '--sidebar-width': SIDEBAR_WIDTH,
      '--sidebar-width-icon': SIDEBAR_WIDTH_ICON,
    }" :class="combinedClass" v-bind="attrs">
      <slot />
    </div>
  </TooltipProvider>
</template>