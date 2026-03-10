<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="modelValue" class="modal-overlay" @click="handleOverlayClick">
        <Transition name="modal-slide">
          <div
            v-if="modelValue"
            class="modal-container"
            :class="sizeClass"
            @click.stop
            role="dialog"
            aria-modal="true"
          >
            <div class="modal-header">
              <h2 class="modal-title">{{ title }}</h2>
              <button
                v-if="closable"
                class="modal-close"
                @click="close"
                aria-label="Fechar"
              >
                <Icon name="x" :size="24" />
              </button>
            </div>

            <div class="modal-body">
              <slot></slot>
            </div>

            <div v-if="$slots.footer" class="modal-footer">
              <slot name="footer"></slot>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, watch, onUnmounted } from 'vue';
import Icon from './Icon.vue';

interface Props {
  modelValue: boolean;
  title: string;
  size?: 'sm' | 'md' | 'lg' | 'xl';
  closable?: boolean;
  closeOnOverlay?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  closable: true,
  closeOnOverlay: true,
});

const emit = defineEmits<{
  'update:modelValue': [value: boolean];
  close: [];
}>();

const sizeClass = computed(() => `modal-${props.size}`);

const close = () => {
  emit('update:modelValue', false);
  emit('close');
};

const handleOverlayClick = () => {
  if (props.closeOnOverlay) {
    close();
  }
};

watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
});

onUnmounted(() => {
  document.body.style.overflow = '';
});
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: flex-end;
  justify-content: center;
  padding: 0;
  z-index: 9999;
  overflow-y: auto;
}

@media (min-width: 768px) {
  .modal-overlay {
    align-items: center;
    padding: 16px;
  }
}

.modal-container {
  background: white;
  border-radius: var(--radius-lg) var(--radius-lg) 0 0;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  width: 100%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  position: relative;
}

@media (min-width: 768px) {
  .modal-container {
    border-radius: var(--radius-lg);
    max-height: calc(100vh - 32px);
  }
}

.modal-sm {
  max-width: 400px;
}

.modal-md {
  max-width: 600px;
}

.modal-lg {
  max-width: 800px;
}

.modal-xl {
  max-width: 1200px;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px;
  border-bottom: 1px solid var(--gray-200);
  flex-shrink: 0;
}

@media (min-width: 768px) {
  .modal-header {
    padding: 24px;
  }
}

.modal-title {
  font-size: 18px;
  font-weight: 700;
  color: var(--gray-900);
  margin: 0;
}

@media (min-width: 768px) {
  .modal-title {
    font-size: 20px;
  }
}

.modal-close {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border: none;
  background: var(--gray-100);
  border-radius: var(--radius);
  color: var(--gray-600);
  cursor: pointer;
  transition: all var(--transition-base);
  flex-shrink: 0;
}

.modal-close:hover {
  background: var(--gray-200);
  color: var(--gray-900);
}

.modal-body {
  padding: 20px;
  overflow-y: auto;
  flex: 1;
}

@media (min-width: 768px) {
  .modal-body {
    padding: 24px;
  }
}

.modal-footer {
  padding: 16px 20px;
  border-top: 1px solid var(--gray-200);
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  flex-shrink: 0;
  background: var(--gray-50);
  border-radius: 0 0 var(--radius-lg) var(--radius-lg);
}

@media (min-width: 768px) {
  .modal-footer {
    padding: 20px 24px;
  }
}

/* Transitions */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-slide-enter-active,
.modal-slide-leave-active {
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-slide-enter-from,
.modal-slide-leave-to {
  transform: translateY(100%);
}

@media (min-width: 768px) {
  .modal-slide-enter-from,
  .modal-slide-leave-to {
    transform: translateY(20px) scale(0.95);
  }
}
</style>
