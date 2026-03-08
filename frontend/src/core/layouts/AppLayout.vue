<template>
  <div class="app-layout">
    <nav class="navbar">
      <div class="navbar-container">
        <div class="navbar-brand">
          <h1>Soft ERP</h1>
        </div>
        <ul class="navbar-menu">
          <li>
            <router-link to="/products" class="nav-link">
              Produtos
            </router-link>
          </li>
          <li>
            <router-link to="/purchases" class="nav-link">
              Compras
            </router-link>
          </li>
          <li>
            <router-link to="/sales" class="nav-link">
              Vendas
            </router-link>
          </li>
        </ul>
      </div>
    </nav>

    <main class="main-content">
      <slot />
    </main>
  </div>
</template>

<script setup lang="ts">
</script>

<style scoped>
.app-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.navbar {
  background-color: #2c3e50;
  color: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 100;
}

.navbar-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 64px;
}

.navbar-brand h1 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
  color: white;
}

.navbar-menu {
  list-style: none;
  display: flex;
  gap: 2rem;
  margin: 0;
  padding: 0;
}

.nav-link {
  color: rgba(255, 255, 255, 0.85);
  text-decoration: none;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  transition: all 0.2s ease;
  display: block;
}

.nav-link:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
}

.nav-link.router-link-active {
  background-color: #3498db;
  color: white;
}

.main-content {
  flex: 1;
  max-width: 1400px;
  width: 100%;
  margin: 0 auto;
  padding: 2rem 1.5rem;
}

/* Responsividade */
@media (max-width: 768px) {
  .navbar-container {
    flex-direction: column;
    height: auto;
    padding: 1rem;
    gap: 1rem;
  }

  .navbar-menu {
    width: 100%;
    flex-direction: column;
    gap: 0.5rem;
  }

  .nav-link {
    text-align: center;
  }

  .main-content {
    padding: 1rem;
  }
}
</style>
