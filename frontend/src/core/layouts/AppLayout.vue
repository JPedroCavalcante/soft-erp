<template>
  <div class="app-layout">
    <Transition name="fade">
      <div
        v-if="isMobileMenuOpen"
        class="mobile-overlay"
        @click="isMobileMenuOpen = false"
      ></div>
    </Transition>

    <aside class="sidebar" :class="{
      'sidebar-collapsed': isCollapsed && !isMobile,
      'sidebar-mobile-open': isMobileMenuOpen
    }">
      <div class="sidebar-header">
        <h1 class="logo">
          <Icon name="office" :size="28" />
          <Transition name="fade-slide">
            <span v-if="!isCollapsed || isMobile">Soft ERP</span>
          </Transition>
        </h1>

        <button
          v-if="!isMobile"
          class="btn-toggle"
          @click="isCollapsed = !isCollapsed"
          :title="isCollapsed ? 'Expandir menu' : 'Retrair menu'"
        >
          <Icon :name="isCollapsed ? 'chevron-right' : 'chevron-left'" :size="20" />
        </button>

        <button
          v-if="isMobile"
          class="btn-close-mobile"
          @click="isMobileMenuOpen = false"
        >
          <Icon name="x" :size="24" />
        </button>
      </div>

      <nav class="sidebar-nav">
        <router-link
          to="/products"
          class="nav-item"
          @click="isMobileMenuOpen = false"
          :title="isCollapsed ? 'Produtos' : ''"
        >
          <Icon name="package" :size="20" />
          <Transition name="fade-slide">
            <span v-if="!isCollapsed || isMobile" class="nav-text">Produtos</span>
          </Transition>
        </router-link>

        <router-link
          to="/purchases"
          class="nav-item"
          @click="isMobileMenuOpen = false"
          :title="isCollapsed ? 'Compras' : ''"
        >
          <Icon name="shopping-cart" :size="20" />
          <Transition name="fade-slide">
            <span v-if="!isCollapsed || isMobile" class="nav-text">Compras</span>
          </Transition>
        </router-link>

        <router-link
          to="/sales"
          class="nav-item"
          @click="isMobileMenuOpen = false"
          :title="isCollapsed ? 'Vendas' : ''"
        >
          <Icon name="currency-dollar" :size="20" />
          <Transition name="fade-slide">
            <span v-if="!isCollapsed || isMobile" class="nav-text">Vendas</span>
          </Transition>
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <div class="user-info">
          <div class="user-avatar">{{ userInitials }}</div>
          <Transition name="fade-slide">
            <div v-if="!isCollapsed || isMobile" class="user-details">
              <div class="user-name">{{ authStore.user?.name }}</div>
              <div class="user-email">{{ authStore.user?.email }}</div>
            </div>
          </Transition>
        </div>
        <button
          @click="handleLogout"
          class="btn-logout"
          :title="isCollapsed ? 'Sair' : ''"
        >
          <Icon name="logout" :size="18" />
          <Transition name="fade-slide">
            <span v-if="!isCollapsed || isMobile">Sair</span>
          </Transition>
        </button>
      </div>
    </aside>

    <div class="main-wrapper" :class="{ 'main-collapsed': isCollapsed && !isMobile }">
      <header class="topbar">
        <div class="topbar-content">
          <button
            v-if="isMobile"
            class="btn-menu-mobile"
            @click="isMobileMenuOpen = true"
          >
            <Icon name="menu" :size="24" />
          </button>

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
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import Icon from '@/core/components/Icon.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const isCollapsed = ref(false);
const isMobileMenuOpen = ref(false);
const isMobile = ref(false);

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

const checkMobile = () => {
  isMobile.value = window.innerWidth < 768;
  if (isMobile.value) {
    isCollapsed.value = false;
  }
};

onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile);
});
</script>

<style scoped>
.app-layout {
  min-height: 100vh;
  display: flex;
  background-color: #f7fafc;
}

.mobile-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
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
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar-collapsed {
  width: 80px;
}

.sidebar-header {
  padding: 24px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
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
  flex: 1;
}

.sidebar-collapsed .logo {
  flex-direction: column;
  gap: 4px;
}

.btn-toggle {
  position: absolute;
  right: -12px;
  top: 50%;
  transform: translateY(-50%);
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: 2px solid #1a202c;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  z-index: 10;
}

.btn-toggle:hover {
  transform: translateY(-50%) scale(1.1);
  box-shadow: 0 0 12px rgba(102, 126, 234, 0.5);
}

.btn-close-mobile {
  width: 36px;
  height: 36px;
  border-radius: var(--radius);
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-close-mobile:hover {
  background: rgba(255, 255, 255, 0.2);
}

.sidebar-nav {
  flex: 1;
  padding: 24px 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  overflow-y: auto;
}

.sidebar-collapsed .sidebar-nav {
  padding: 24px 8px;
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
  white-space: nowrap;
}

.sidebar-collapsed .nav-item {
  justify-content: center;
  padding: 14px 8px;
}

.nav-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
  transform: translateX(4px);
}

.sidebar-collapsed .nav-item:hover {
  transform: translateX(0) scale(1.05);
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

.sidebar-collapsed .sidebar-footer {
  padding: 16px 8px;
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

.sidebar-collapsed .user-info {
  justify-content: center;
  padding: 12px 8px;
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
  transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.main-collapsed {
  margin-left: 80px;
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
  padding: 16px 20px;
  display: flex;
  align-items: center;
  gap: 16px;
}

@media (min-width: 768px) {
  .topbar-content {
    padding: 20px 32px;
  }
}

.btn-menu-mobile {
  width: 40px;
  height: 40px;
  border-radius: var(--radius);
  background: var(--gray-100);
  border: none;
  color: var(--gray-700);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}

.btn-menu-mobile:hover {
  background: var(--gray-200);
  color: var(--gray-900);
}

.page-title {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  color: #1a202c;
}

@media (min-width: 768px) {
  .page-title {
    font-size: 24px;
  }
}

.main-content {
  flex: 1;
  padding: 20px;
  max-width: 1400px;
  width: 100%;
}

@media (min-width: 768px) {
  .main-content {
    padding: 32px;
  }
}

/* Mobile Styles */
@media (max-width: 767px) {
  .sidebar {
    transform: translateX(-100%);
  }

  .sidebar-mobile-open {
    transform: translateX(0);
  }

  .main-wrapper {
    margin-left: 0;
  }

  .btn-toggle {
    display: none;
  }
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.2s;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(-10px);
}
</style>
