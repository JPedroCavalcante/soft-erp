import { ref, computed } from 'vue';

export interface FormOptions {
  resetOnSuccess?: boolean;
}

export function useForm<T extends Record<string, unknown>>(
  initialData: T,
  options: FormOptions = {}
) {
  const formData = ref<T>({ ...initialData });
  const errors = ref<Record<string, string>>({});
  const isDirty = ref(false);
  const isSubmitting = ref(false);

  const hasErrors = computed(() => Object.keys(errors.value).length > 0);

  const resetForm = () => {
    formData.value = { ...initialData } as T;
    errors.value = {};
    isDirty.value = false;
  };

  const setErrors = (validationErrors: Record<string, string[]>) => {
    errors.value = {};
    Object.entries(validationErrors).forEach(([field, messages]) => {
      errors.value[field] = messages[0];
    });
  };

  const clearErrors = () => {
    errors.value = {};
  };

  const setFieldError = (field: string, message: string) => {
    errors.value[field] = message;
  };

  const clearFieldError = (field: string) => {
    delete errors.value[field];
  };

  const markAsDirty = () => {
    isDirty.value = true;
  };

  const handleSubmit = async (submitFn: () => Promise<void>) => {
    isSubmitting.value = true;
    clearErrors();

    try {
      await submitFn();
      if (options.resetOnSuccess) {
        resetForm();
      }
    } catch (err: unknown) {
      if (err && typeof err === 'object' && 'response' in err) {
        const response = (err as { response?: { data?: { errors?: Record<string, string[]> } } }).response;
        if (response?.data?.errors) {
          setErrors(response.data.errors);
        }
      }
      throw err;
    } finally {
      isSubmitting.value = false;
    }
  };

  return {
    formData,
    errors,
    isDirty,
    isSubmitting,
    hasErrors,
    resetForm,
    setErrors,
    clearErrors,
    setFieldError,
    clearFieldError,
    markAsDirty,
    handleSubmit,
  };
}
