<template>
  <div class="login-page">
    <div class="login-background">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
    </div>

    <div class="login-container">
      <div class="login-card">
        <div class="login-header">
          <div class="logo-container">
            <Icon name="office" :size="48" color="#6366f1" />
          </div>
          <h1>Bem-vindo ao Soft ERP</h1>
          <p>Faça login para continuar</p>
        </div>

        <form @submit.prevent="handleLogin" class="login-form">
          <div class="input-group">
            <label for="email">E-mail</label>
            <input
              id="email"
              v-model="credentials.email"
              type="email"
              placeholder="seu@email.com"
              required
              autocomplete="email"
              :disabled="authStore.loading"
            />
          </div>

          <div class="input-group">
            <label for="password">Senha</label>
            <input
              id="password"
              v-model="credentials.password"
              type="password"
              placeholder="••••••••"
              required
              autocomplete="current-password"
              :disabled="authStore.loading"
            />
          </div>

          <div v-if="authStore.error" class="alert-error">
            {{ authStore.error }}
          </div>

          <button type="submit" :disabled="authStore.loading" class="btn-submit">
            <span v-if="!authStore.loading">Entrar</span>
            <span v-else class="loading-text">
              <span class="spinner"></span>
              Entrando...
            </span>
          </button>
        </form>

        <div class="login-footer">
          <div class="demo-info">
            <p><strong>Credenciais para teste:</strong></p>
            <p class="credentials">admin@softerp.com • password</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import Icon from '@/core/components/Icon.vue';
import type { LoginCredentials } from '../types';

const router = useRouter();
const authStore = useAuthStore();

const credentials = ref<LoginCredentials>({
  email: '',
  password: '',
});

const handleLogin = async () => {
  const success = await authStore.login(credentials.value);
  if (success) {
    router.push('/products');
  }
};
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  position: relative;
  overflow: hidden;
}

.login-background {
  position: fixed;
  inset: 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  z-index: 0;
}

.gradient-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.6;
  animation: float 20s ease-in-out infinite;
}

.orb-1 {
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(99, 102, 241, 0.8) 0%, transparent 70%);
  top: -250px;
  left: -250px;
  animation-delay: 0s;
}

.orb-2 {
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(217, 70, 239, 0.6) 0%, transparent 70%);
  bottom: -200px;
  right: -200px;
  animation-delay: -7s;
}

.orb-3 {
  width: 350px;
  height: 350px;
  background: radial-gradient(circle, rgba(139, 92, 246, 0.5) 0%, transparent 70%);
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  animation-delay: -14s;
}

@keyframes float {
  0%, 100% {
    transform: translate(0, 0);
  }
  25% {
    transform: translate(30px, -30px);
  }
  50% {
    transform: translate(-20px, 20px);
  }
  75% {
    transform: translate(20px, 10px);
  }
}

.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  position: relative;
  z-index: 1;
}

.login-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(255, 255, 255, 0.1);
  padding: 48px;
  width: 100%;
  max-width: 480px;
  animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.login-header {
  text-align: center;
  margin-bottom: 40px;
}

.logo-container {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%);
  border-radius: 20px;
  margin-bottom: 24px;
  box-shadow: 0 8px 16px rgba(99, 102, 241, 0.15);
}

.login-header h1 {
  font-size: 28px;
  font-weight: 700;
  color: var(--gray-900);
  margin: 0 0 8px 0;
  letter-spacing: -0.02em;
}

.login-header p {
  font-size: 15px;
  color: var(--gray-600);
  margin: 0;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.input-group label {
  font-size: 14px;
  font-weight: 600;
  color: var(--gray-700);
}

.input-group input {
  padding: 14px 16px;
  border: 2px solid var(--gray-200);
  border-radius: var(--radius);
  font-size: 15px;
  font-family: inherit;
  transition: all var(--transition-base);
  background: white;
}

.input-group input:hover:not(:disabled) {
  border-color: var(--gray-300);
}

.input-group input:focus {
  outline: none;
  border-color: var(--primary-600);
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
}

.input-group input:disabled {
  background-color: var(--gray-50);
  cursor: not-allowed;
}

.alert-error {
  padding: 14px 16px;
  background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
  border: 1px solid var(--danger-200);
  border-left: 4px solid var(--danger-600);
  border-radius: var(--radius);
  color: var(--danger-700);
  font-size: 14px;
  font-weight: 500;
  animation: shake 0.4s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-8px); }
  75% { transform: translateX(8px); }
}

.btn-submit {
  padding: 16px;
  background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-700) 100%);
  color: white;
  border: none;
  border-radius: var(--radius);
  font-size: 16px;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: all var(--transition-base);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  position: relative;
  overflow: hidden;
}

.btn-submit::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, transparent 100%);
  opacity: 0;
  transition: opacity var(--transition-base);
}

.btn-submit:hover:not(:disabled)::before {
  opacity: 1;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
}

.btn-submit:active:not(:disabled) {
  transform: translateY(0);
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.loading-text {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.login-footer {
  margin-top: 32px;
  padding-top: 24px;
  border-top: 1px solid var(--gray-200);
}

.demo-info {
  text-align: center;
}

.demo-info p {
  margin: 0;
  font-size: 13px;
  color: var(--gray-600);
  line-height: 1.6;
}

.demo-info strong {
  color: var(--gray-900);
  font-weight: 600;
}

.credentials {
  margin-top: 8px;
  padding: 10px 16px;
  background: var(--gray-100);
  border-radius: var(--radius-sm);
  font-family: ui-monospace, 'Cascadia Code', 'Source Code Pro', monospace;
  font-size: 12px;
  color: var(--primary-700);
  font-weight: 500;
}

@media (max-width: 480px) {
  .login-card {
    padding: 32px 24px;
  }

  .login-header h1 {
    font-size: 24px;
  }

  .logo-container {
    width: 64px;
    height: 64px;
  }
}
</style>
