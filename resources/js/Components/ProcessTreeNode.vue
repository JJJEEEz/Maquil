<template>
  <div class="tree-node">
    <!-- Línea vertical del árbol -->
    <div v-if="level > 0" class="tree-line" :style="{ left: `${level * 2 - 1}rem` }"></div>

    <!-- Nodo actual -->
    <div class="node-container" :style="{ marginLeft: `${level * 2}rem` }">
      <!-- Conector horizontal -->
      <div v-if="level > 0" class="tree-connector"></div>

      <!-- Caja del nodo -->
      <div class="node-box" :class="{ 'has-children': nodo.hijos && nodo.hijos.length > 0 }">
        <div class="node-header" @click="toggleExpanded">
          <!-- Icono de expandir/contraer -->
          <span v-if="nodo.hijos && nodo.hijos.length > 0" class="expand-icon" :class="{ expanded: isExpanded }">
            ▶
          </span>
          <span v-else class="expand-icon invisible">▶</span>

          <!-- Nombre del nodo -->
          <div class="node-title">{{ nodo.nombre }}</div>

          <!-- Badge de tipo -->
          <span class="node-badge" :class="nodo.tipo">
            {{ nodo.tipo }}
          </span>
        </div>

        <!-- Contenido del nodo -->
        <div class="node-content">
          <div class="info-row">
            <span class="label">Flujo:</span>
            <span class="value">{{ nodo.cantidad_entrada }} → {{ nodo.cantidad_salida }}</span>
          </div>
          <div class="info-row">
            <span class="label">Tiempo:</span>
            <span class="value">{{ nodo.tiempo_estimado_minutos }} min</span>
          </div>

          <!-- Dependencias -->
          <div v-if="nodo.dependencias && nodo.dependencias.length > 0" class="dependencies">
            <div class="label">Depende de:</div>
            <div class="deps-list">
              <span v-for="dep in nodo.dependencias" :key="dep.id" class="dep-badge">
                {{ dep.nombre }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Hijos (collapsible) -->
    <div v-if="nodo.hijos && nodo.hijos.length > 0 && isExpanded" class="children-container">
      <div v-for="(hijo, index) in nodo.hijos" :key="hijo.id" class="child-wrapper">
        <!-- Línea vertical que conecta con los hijos -->
        <div
          v-if="level >= 0"
          class="vertical-connector"
          :style="{
            left: `${level * 2 + 0.75}rem`,
            top: index === 0 ? '2rem' : '0',
            height: index === nodo.hijos.length - 1 ? '2rem' : 'calc(100% + 2rem)',
          }"
        ></div>
        <ProcessTreeNode :nodo="hijo" :level="level + 1" />
      </div>
    </div>

    <!-- Mensaje si está contraído -->
    <div v-if="nodo.hijos && nodo.hijos.length > 0 && !isExpanded" class="collapsed-info" :style="{ marginLeft: `${level * 2}rem` }">
      <span class="text-xs text-gray-500">
        {{ nodo.hijos.length }} proceso{{ nodo.hijos.length > 1 ? 's' : '' }} (expandir para ver)
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
  nodo: Object,
  level: {
    type: Number,
    default: 0,
  },
});

const isExpanded = ref(true);

const toggleExpanded = () => {
  isExpanded.value = !isExpanded.value;
};
</script>

<style scoped>
.tree-node {
  position: relative;
  font-family: system-ui, -apple-system, sans-serif;
}

.tree-line {
  position: absolute;
  width: 2px;
  background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
  top: 0;
  bottom: 0;
}

.node-container {
  position: relative;
  margin-bottom: 1rem;
}

.tree-connector {
  position: absolute;
  left: -0.5rem;
  top: 1.5rem;
  width: 0.5rem;
  height: 2px;
  background: linear-gradient(to right, #3b82f6, #8b5cf6);
}

.node-box {
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 0.75rem;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  min-width: 300px;
}

.node-box:hover {
  border-color: #3b82f6;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
  transform: translateY(-2px);
}

.node-box.has-children {
  border-left: 4px solid #3b82f6;
}

.node-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: linear-gradient(135deg, #f0f9ff 0%, #f3e8ff 100%);
  cursor: pointer;
  user-select: none;
  border-bottom: 1px solid #e5e7eb;
}

.node-header:hover {
  background: linear-gradient(135deg, #e0f2fe 0%, #e9d5ff 100%);
}

.expand-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 1.25rem;
  height: 1.25rem;
  color: #3b82f6;
  font-size: 0.75rem;
  font-weight: bold;
  transition: transform 0.2s ease;
  flex-shrink: 0;
}

.expand-icon.expanded {
  transform: rotate(90deg);
}

.expand-icon.invisible {
  visibility: hidden;
}

.node-title {
  flex: 1;
  font-weight: 600;
  color: #1f2937;
  font-size: 0.95rem;
}

.node-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
  white-space: nowrap;
}

.node-badge.operacion {
  background-color: #dbeafe;
  color: #1e40af;
}

.node-badge.inspeccion {
  background-color: #fed7aa;
  color: #b45309;
}

.node-content {
  padding: 0.75rem;
  font-size: 0.875rem;
  color: #4b5563;
}

.info-row {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 0.4rem;
  padding: 0.25rem 0;
}

.label {
  font-weight: 600;
  color: #6b7280;
  min-width: 5rem;
}

.value {
  color: #1f2937;
}

.dependencies {
  margin-top: 0.5rem;
  padding-top: 0.5rem;
  border-top: 1px solid #e5e7eb;
}

.deps-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
  margin-top: 0.4rem;
}

.dep-badge {
  display: inline-block;
  padding: 0.25rem 0.6rem;
  background-color: #f3e8ff;
  color: #7e22ce;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: 500;
  border: 1px solid #e9d5ff;
}

.children-container {
  margin-left: 1rem;
  margin-top: 1rem;
  position: relative;
}

.child-wrapper {
  position: relative;
  margin-bottom: 1rem;
}

.vertical-connector {
  position: absolute;
  width: 2px;
  background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
  border-radius: 2px;
}

.collapsed-info {
  padding: 0.5rem 0.75rem;
  margin-top: 0.5rem;
  background-color: #fafafa;
  border-radius: 0.375rem;
  border-left: 3px solid #9ca3af;
  font-style: italic;
}

/* Dark mode support */
:global(.dark) .node-box {
  background: #1f2937;
  border-color: #374151;
}

:global(.dark) .node-box:hover {
  border-color: #60a5fa;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

:global(.dark) .node-header {
  background: linear-gradient(135deg, #1e3a5f 0%, #2d1b4e 100%);
  border-bottom-color: #374151;
}

:global(.dark) .node-header:hover {
  background: linear-gradient(135deg, #2a4a7f 0%, #3d2b5e 100%);
}

:global(.dark) .node-title {
  color: #f3f4f6;
}

:global(.dark) .node-content {
  color: #d1d5db;
}

:global(.dark) .label {
  color: #9ca3af;
}

:global(.dark) .value {
  color: #e5e7eb;
}

:global(.dark) .dependencies {
  border-top-color: #374151;
}

:global(.dark) .dep-badge {
  background-color: #3d1f6e;
  color: #e9d5ff;
  border-color: #5b21b6;
}

:global(.dark) .collapsed-info {
  background-color: #111827;
  border-left-color: #4b5563;
  color: #9ca3af;
}
</style>
