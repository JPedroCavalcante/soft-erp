<template>
  <component
    :is="iconComponent"
    :size="size"
    :color="color"
    :stroke-width="strokeWidth"
  />
</template>

<script setup lang="ts">
import { computed } from 'vue';
import {
  Package,
  ShoppingCart,
  DollarSign,
  LogOut,
  Building,
  Plus,
  X,
  Menu,
  Edit,
  Trash2,
  AlertTriangle,
  ChevronLeft,
  ChevronRight,
} from 'lucide-vue-next';

type IconName =
  | 'package'
  | 'shopping-cart'
  | 'currency-dollar'
  | 'logout'
  | 'office'
  | 'plus'
  | 'x'
  | 'menu'
  | 'edit'
  | 'trash'
  | 'exclamation-triangle'
  | 'chevron-left'
  | 'chevron-right';

interface Props {
  name: IconName;
  size?: number;
  color?: string;
  strokeWidth?: number;
}

const props = withDefaults(defineProps<Props>(), {
  size: 24,
  color: 'currentColor',
  strokeWidth: 2,
});

const iconMap = {
  package: Package,
  'shopping-cart': ShoppingCart,
  'currency-dollar': DollarSign,
  logout: LogOut,
  office: Building,
  plus: Plus,
  x: X,
  menu: Menu,
  edit: Edit,
  trash: Trash2,
  'exclamation-triangle': AlertTriangle,
  'chevron-left': ChevronLeft,
  'chevron-right': ChevronRight,
};

const iconComponent = computed(() => {
  return iconMap[props.name as IconName] || Package;
});
</script>

<style scoped>
svg {
  display: inline-block;
  vertical-align: middle;
  flex-shrink: 0;
}
</style>
