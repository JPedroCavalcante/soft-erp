<template>
  <div class="app-layout">
    <aside class="sidebar">
      <div class="sidebar-header">
        <h1 class="logo">
          <Icon name="office" :size="28" />
          <span>Soft ERP</span>
        </h1>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/products" class="nav-item">
          <Icon name="package" :size="20" />
          <span class="nav-text">Produtos</span>
        </router-link>

        <router-link to="/purchases" class="nav-item">
          <Icon name="shopping-cart" :size="20" />
          <span class="nav-text">Compras</span>
        </router-link>

        <router-link to="/sales" class="nav-item">
          <Icon name="currency-dollar" :size="20" />
          <span class="nav-text">Vendas</span>
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <div class="user-info">
          <div class="user-avatar">{{ userInitials }}</div>
          <div class="user-details">
            <div class="user-name">{{ authStore.user?.name }}</div>
            <div class="user-email">{{ authStore.user?.email }}</div>
          </div>
        </div>
        <button @click="handleLogout" class="btn-logout">
          <Icon name="logout" :size="18" />
          <span>Sair</span>
        </button>
      </div>
    </aside>

    <div class="main-wrapper">
      <header class="topbar">
        <div class="topbar-content">
          <h2 class="page-title">{{ pageTitle }}</h2>
        </div>
      </header>

      <main class="main-content">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import Icon from '@/core/components/Icon.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const pageTitle = computed(() => {
  const titles: Record<string, string> = {
    products: 'Produtos',
    purchases: 'Compras',
    sales: 'Vendas',
  };
  return titles[route.name as string] || 'Dashboard';
});

const userInitials = computed(() => {
  const name = authStore.user?.name || '';
  return name
    .split(' ')
    .map((n: string) => n[0])
    .slice(0, 2)
    .join('')
    .toUpperCase();
});

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>

<style scoped>
.app-layout {
  min-height: 100vh;
  display: flex;
  background-color: #f7fafc;
}

.sidebar {
  width: 280px;
  background: linear-gradient(180deg, #1a202c 0%, #2d3748 100%);
  color: white;
  display: flex;
  flex-direction: column;
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
  position: fixed;
  height: 100vh;
  left: 0;
  top: 0;
  z-index: 1000;
}

.sidebar-header {
  padding: 24px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.sidebar-nav {
  flex: 1;
  padding: 24px 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  overflow-y: auto;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  border-radius: 12px;
  transition: all 0.2s;
  font-weight: 500;
}

.nav-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
  transform: translateX(4px);
}

.nav-item.router-link-active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.nav-text {
  font-size: 15px;
}

.sidebar-footer {
  padding: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background-color: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  margin-bottom: 12px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
  flex-shrink: 0;
}

.user-details {
  flex: 1;
  min-width: 0;
}

.user-name {
  font-size: 14px;
  font-weight: 600;
  color: white;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-email {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.6);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.btn-logout {
  width: 100%;
  padding: 12px;
  background-color: rgba(255, 255, 255, 0.1);
  border: none;
  border-radius: 8px;
  color: white;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-logout:hover {
  background-color: rgba(239, 68, 68, 0.2);
  transform: translateY(-2px);
}

.main-wrapper {
  margin-left: 280px;
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.topbar {
  background-color: white;
  border-bottom: 1px solid #e2e8f0;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.topbar-content {
  padding: 20px 32px;
}

.page-title {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  color: #1a202c;
}

.main-content {
  flex: 1;
  padding: 32px;
  max-width: 1400px;
  width: 100%;
}

@media (max-width: 1024px) {
  .sidebar {
    width: 240px;
  }

  .main-wrapper {
    margin-left: 240px;
  }
}

@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
    transition: transform 0.3s;
  }

  .main-wrapper {
    margin-left: 0;
  }

  .main-content {
    padding: 20px;
  }

  .topbar-content {
    padding: 16px 20px;
  }

  .page-title {
    font-size: 20px;
  }
}
</style>
