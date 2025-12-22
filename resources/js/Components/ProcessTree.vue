
<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  nodos: Array,
});

const emit = defineEmits(['edit']);

const zoom = ref(1);
const isExpanded = ref(true);

const zoomIn = () => {
  zoom.value = Math.min(zoom.value + 0.2, 3);
};

const zoomOut = () => {
  zoom.value = Math.max(zoom.value - 0.2, 0.5);
};

const toggleExpand = () => {
  isExpanded.value = !isExpanded.value;
};

// Calcular niveles basado en dependencias
const positionedNodes = computed(() => {
  if (!props.nodos || props.nodos.length === 0) return [];

  // Calcular el nivel de cada nodo basado en sus dependencias
  const nodeMap = {};
  const nodeLevels = {};

  props.nodos.forEach((nodo) => {
    nodeMap[nodo.id] = nodo;
    nodeLevels[nodo.id] = 0;
  });

  // Calcular profundidad: nivel máximo de dependencias
  const calculateLevel = (nodeId, visited = new Set()) => {
    if (visited.has(nodeId)) return 0;
    visited.add(nodeId);

    const nodo = nodeMap[nodeId];
    if (!nodo || !nodo.dependencias || nodo.dependencias.length === 0) {
      return 0;
    }

    const maxDependencyLevel = Math.max(
      ...nodo.dependencias.map((dep) => calculateLevel(dep.id, new Set(visited))),
      0
    );

    return maxDependencyLevel + 1;
  };

  props.nodos.forEach((nodo) => {
    nodeLevels[nodo.id] = calculateLevel(nodo.id);
  });

  // Agrupar nodos por nivel
  const nodesByLevel = {};
  Object.entries(nodeLevels).forEach(([nodeId, level]) => {
    if (!nodesByLevel[level]) {
      nodesByLevel[level] = [];
    }
    nodesByLevel[level].push(nodeMap[nodeId]);
  });

  // Posicionar nodos
  const positioned = [];
  const verticalSpacing = 200;
  const baseY = 80;
  const horizontalSpacing = 280;
  const containerWidth = 1000;

  Object.keys(nodesByLevel)
    .map(Number)
    .sort((a, b) => a - b)
    .forEach((level, levelIndex) => {
      const nodesInLevel = nodesByLevel[level];
      const levelY = baseY + levelIndex * verticalSpacing;

      // Distribuir horizontalmente
      const totalWidth = nodesInLevel.length * horizontalSpacing;
      const startX = Math.max(50, (containerWidth - totalWidth) / 2);

      nodesInLevel.forEach((nodo, indexInLevel) => {
        positioned.push({
          ...nodo,
          x: startX + indexInLevel * horizontalSpacing + 80,
          y: levelY,
          level: level,
          levelIndex: levelIndex,
        });
      });
    });

  return positioned;
});

// Generar conexiones basadas en dependencias
const connections = computed(() => {
  if (!props.nodos || props.nodos.length === 0) return [];

  const conns = [];
  const nodeMap = {};

  // Mapear nodos por ID
  positionedNodes.value.forEach((node) => {
    nodeMap[node.id] = node;
  });

  // Crear líneas basadas en dependencias
  positionedNodes.value.forEach((nodo) => {
    if (nodo.dependencias && nodo.dependencias.length > 0) {
      nodo.dependencias.forEach((dep, depIndex) => {
        const dependencyNode = nodeMap[dep.id];
        if (dependencyNode) {
          const x1 = dependencyNode.x;
          const y1 = dependencyNode.y + 25;
          const x2 = nodo.x;
          const y2 = nodo.y - 25;

          // Calcular la altura de la curva basada en distancia vertical
          const distanceY = y2 - y1;
          const curveDepth = Math.max(60, Math.abs(distanceY) / 3) + depIndex * 15;

          // Crear una curva suave (quadratic bezier)
          const midX = (x1 + x2) / 2;
          const midY = y1 + distanceY / 2 + curveDepth;

          const path = `M ${x1} ${y1} Q ${midX} ${midY}, ${x2} ${y2}`;

          conns.push({
            from: dep.id,
            to: nodo.id,
            path: path,
            stroke: '#3b82f6',
          });
        }
      });
    }
  });

  return conns;
});

// Dimensiones del SVG
const svgWidth = computed(() => {
  if (!positionedNodes.value.length) return 1000;
  const maxX = Math.max(...positionedNodes.value.map((n) => n.x));
  return Math.max(1000, maxX + 150);
});

const svgHeight = computed(() => {
  if (!positionedNodes.value.length) return 400;
  const maxLevel = Math.max(...positionedNodes.value.map((n) => n.levelIndex || 0));
  return Math.max(500, (maxLevel + 1) * 200);
});
</script>

<template>
  <div class="process-flow-diagram-container">
    <!-- Header con controles -->
    <div class="diagram-header">
      <div class="header-left">
        <button class="toggle-btn" @click="toggleExpand" :title="isExpanded ? 'Contraer' : 'Expandir'">
          <span class="toggle-icon">{{ isExpanded ? '▼' : '▶' }}</span>
          Diagrama de Flujo
        </button>
      </div>
      <div v-if="isExpanded" class="header-right">
        <button class="zoom-btn" @click="zoomOut" title="Reducir zoom" :disabled="zoom <= 0.5">−</button>
        <span class="zoom-value">{{ Math.round(zoom * 100) }}%</span>
        <button class="zoom-btn" @click="zoomIn" title="Aumentar zoom" :disabled="zoom >= 3">+</button>
      </div>
    </div>

    <!-- Diagrama -->
    <transition name="expand">
      <div v-if="isExpanded" class="process-flow-diagram">
        <svg v-if="nodos.length > 0" class="flow-svg" :viewBox="`0 0 ${svgWidth} ${svgHeight}`" preserveAspectRatio="xMidYMid meet" :style="{ transform: `scale(${zoom})`, transformOrigin: 'top center' }">
      <!-- Definiciones de gradientes -->
      <defs>
        <linearGradient id="lineGradient" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" style="stop-color: #3b82f6; stop-opacity: 1" />
          <stop offset="100%" style="stop-color: #8b5cf6; stop-opacity: 1" />
        </linearGradient>
        <linearGradient id="lineGradientAlt" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" style="stop-color: #f59e0b; stop-opacity: 1" />
          <stop offset="100%" style="stop-color: #3b82f6; stop-opacity: 1" />
        </linearGradient>
      </defs>

      <!-- Líneas de conexión (dibujadas primero para que queden atrás) -->
      <g class="connections">
        <path
          v-for="(connection, idx) in connections"
          :key="`line-${idx}`"
          :d="connection.path"
          class="connection-line"
          :style="{ stroke: connection.stroke }"
        />
        <!-- Marcadores de punta de flecha -->
        <defs>
          <marker id="arrowhead" markerWidth="10" markerHeight="10" refX="9" refY="3" orient="auto">
            <polygon points="0 0, 10 3, 0 6" fill="#3b82f6" />
          </marker>
        </defs>
      </g>

      <!-- Nodos -->
      <g class="nodes">
        <g v-for="node in positionedNodes" :key="`node-${node.id}`" class="node-group" @click="emit('edit', node)" style="cursor: pointer;">
          <!-- Sombra -->
          <rect :x="node.x - 70" :y="node.y - 25" width="140" height="50" rx="8" class="node-shadow" />

          <!-- Caja del nodo -->
          <rect :x="node.x - 70" :y="node.y - 25" width="140" height="50" rx="8" class="node-rect" :class="node.tipo" />

          <!-- Borde de selección -->
          <rect :x="node.x - 72" :y="node.y - 27" width="144" height="54" rx="8" class="node-border" />

          <!-- Texto -->
          <text :x="node.x" :y="node.y - 8" class="node-name" text-anchor="middle">{{ node.nombre }}</text>
          <text :x="node.x" :y="node.y + 8" class="node-info" text-anchor="middle">{{ node.tiempo_estimado_minutos }}min</text>
          <text :x="node.x" :y="node.y + 18" class="node-type" text-anchor="middle">{{ node.tipo }}</text>
        </g>
      </g>
    </svg>

    <!-- Mensaje si no hay procesos -->
    <div v-else class="empty-state">
      <p>No hay procesos para mostrar</p>
    </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
.process-flow-diagram-container {
  width: 100%;
}

.diagram-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-radius: 0.75rem 0.75rem 0 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  background: var(--v-theme-background, #F8FAFC);
}

.header-left {
  display: flex;
  align-items: center;
}

.toggle-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: none;
  border: none;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  color: var(--v-theme-on-surface);
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  transition: all 0.3s ease;
}

.toggle-btn:hover {
  background-color: rgba(0, 0, 0, 0.04);
  color: var(--v-theme-on-surface);
}

.toggle-icon {
  display: inline-block;
  transition: transform 0.3s ease;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.zoom-btn {
  width: 2.5rem;
  height: 2.5rem;
  border: 1px solid var(--v-theme-secondary, #6B7280);
  background: var(--v-theme-surface, #FFFFFF);
  border-radius: 0.375rem;
  cursor: pointer;
  font-size: 1.25rem;
  font-weight: bold;
  color: var(--v-theme-on-primary, #FFFFFF);
  transition: all 0.2s ease;
}

.zoom-btn:hover:not(:disabled) {
  background-color: rgba(0,0,0,0.04);
  border-color: var(--v-theme-secondary);
}

.zoom-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.zoom-value {
  min-width: 3rem;
  text-align: center;
  font-weight: 500;
  color: var(--v-theme-on-surface, #0B1220);
}

.process-flow-diagram {
  width: 100%;
  overflow: auto;
  background: var(--v-theme-background, #F8FAFC);
  border-radius: 0 0 0.75rem 0.75rem;
  padding: 1rem;
}

.flow-svg {
  width: 100%;
  height: auto;
  background: var(--v-theme-surface, #FFFFFF);
  border-radius: 0.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.empty-state {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 500px;
  color: var(--v-theme-secondary, #6B7280);
  font-size: 1rem;
}

/* Conexiones */
.connection-line {
  stroke: #3b82f6;
  stroke-width: 3;
  fill: none;
  stroke-linecap: round;
  stroke-linejoin: round;
  filter: drop-shadow(0 2px 4px rgba(59, 130, 246, 0.12));
  opacity: 0.95;
  transition: all 0.2s ease;
}

.connection-line:hover {
  stroke-width: 4;
  opacity: 1;
  filter: drop-shadow(0 3px 6px rgba(59,130,246,0.18));
}

/* Nodos */
.node-group {
  cursor: pointer;
  transition: filter 0.3s ease;
}

.node-group:hover {
  filter: brightness(1.1);
}

.node-shadow {
  fill: rgba(0, 0, 0, 0.12);
  filter: blur(4px);
}

.node-rect {
  fill: #ffffff;
  stroke: #3b82f6;
  stroke-width: 2.5;
  transition: all 0.3s ease;
}

.node-rect.operacion {
  fill: #eff6ff;
  stroke: #3b82f6;
}

.node-rect.inspeccion {
  fill: #fff7ed;
  stroke: #f59e0b;
}

.node-group:hover .node-rect {
  filter: drop-shadow(0 4px 12px rgba(59,130,246,0.28));
  stroke-width: 3;
}

.node-border {
  fill: none;
  stroke: transparent;
  stroke-width: 2;
  transition: all 0.3s ease;
  pointer-events: none;
}

.node-group:hover .node-border {
  stroke: #60a5fa;
  stroke-dasharray: 5, 5;
}

.node-name {
  font-size: 13px;
  font-weight: 700;
  fill: #0B1220;
  dominant-baseline: middle;
  pointer-events: none;
  user-select: none;
}

.node-info {
  font-size: 11px;
  font-weight: 500;
  fill: #6b7280;
  dominant-baseline: middle;
  pointer-events: none;
  user-select: none;
}

.node-type {
  font-size: 9px;
  fill: #6b7280;
  dominant-baseline: middle;
  text-transform: capitalize;
  pointer-events: none;
  user-select: none;
}

/* Dark mode handled by Vuetify theme variables */

/* Animación de expand/collapse */
.expand-enter-active,
.expand-leave-active {
  transition: all 0.3s ease;
}

.expand-enter-from {
  opacity: 0;
  max-height: 0;
}

.expand-leave-to {
  opacity: 0;
  max-height: 0;
}

@media (max-width: 768px) {
  .process-flow-diagram {
    min-height: 300px;
  }

  .node-name {
    font-size: 13px;
    font-weight: 700;
    fill: var(--v-theme-on-surface);
    dominant-baseline: middle;
    pointer-events: none;
    user-select: none;
  }
  .node-info {
    font-size: 11px;
    font-weight: 500;
    fill: var(--v-theme-secondary);
    dominant-baseline: middle;
    pointer-events: none;
    user-select: none;
  }
  .node-type {
    font-size: 9px;
    fill: var(--v-theme-secondary);
    dominant-baseline: middle;
    text-transform: capitalize;
    pointer-events: none;
    user-select: none;
  }}
</style>
