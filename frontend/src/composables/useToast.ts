import { ref } from 'vue';

export type ToastType = 'success' | 'error' | 'warning' | 'info';

export interface Toast {
  id: number;
  message: string;
  type: ToastType;
  duration: number;
}

const toasts = ref<Toast[]>([]);
let toastId = 0;

export function useToast() {
  const showToast = (message: string, type: ToastType = 'info', duration = 3000) => {
    const id = ++toastId;
    const toast: Toast = { id, message, type, duration };

    toasts.value.push(toast);

    if (duration > 0) {
      setTimeout(() => {
        removeToast(id);
      }, duration);
    }

    return id;
  };

  const removeToast = (id: number) => {
    const index = toasts.value.findIndex(t => t.id === id);
    if (index !== -1) {
      toasts.value.splice(index, 1);
    }
  };

  const success = (message: string, duration = 3000) => {
    return showToast(message, 'success', duration);
  };

  const error = (message: string, duration = 4000) => {
    return showToast(message, 'error', duration);
  };

  const warning = (message: string, duration = 3500) => {
    return showToast(message, 'warning', duration);
  };

  const info = (message: string, duration = 3000) => {
    return showToast(message, 'info', duration);
  };

  const clear = () => {
    toasts.value = [];
  };

  return {
    toasts,
    showToast,
    removeToast,
    success,
    error,
    warning,
    info,
    clear,
  };
}
