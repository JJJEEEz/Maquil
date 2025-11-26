<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'Fecha' },
  allowedDates: { type: Function, default: null },
  locale: { type: String, default: 'es' },
  error: { type: Boolean, default: false },
  errorMessages: { type: [String, Array], default: null },
});

const emit = defineEmits(['update:modelValue', 'close']);

const open = ref(false);
// `temp` is the internal value used by the v-date-picker (YYYY-MM-DD)
const temp = ref(props.modelValue);
// `displayValue` is what we show in the text field (DD/MM/YYYY)
const displayValue = ref('');
const teleportTarget = ref('body');

watch(
  () => props.modelValue,
  (v) => {
    temp.value = v;
    // keep the displayed text in sync when external model changes
    if (v) displayValue.value = formatToDisplay(v);
    else displayValue.value = '';
  },
);

// Keep `temp` normalized as a YYYY-MM-DD string so the text field
// never shows the long Date.toString() representation.
watch(
  temp,
  (v) => {
    if (!v) {
      displayValue.value = '';
      return;
    }

    let normalized = v;
    if (v instanceof Date) {
      normalized = ymdFromDate(v);
    } else if (typeof v === 'string' && v.includes('T')) {
      normalized = v.slice(0, 10);
    }

    // If normalization changed the value, update temp to be YYYY-MM-DD
    if (normalized !== v) {
      temp.value = normalized;
    }

    // Update displayed text
    displayValue.value = formatToDisplay(normalized);
  },
  { immediate: false },
);

function pad(n) {
  return n < 10 ? `0${n}` : `${n}`;
}

function ymdFromDate(d) {
  const y = d.getFullYear();
  const m = pad(d.getMonth() + 1);
  const day = pad(d.getDate());
  return `${y}-${m}-${day}`;
}

function formatToDisplay(val) {
  if (!val) return '';
  // accept Date or YYYY-MM-DD or ISO
  if (val instanceof Date) {
    const d = val;
    return `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()}`;
  }
  if (typeof val === 'string') {
    // if ISO or contains T, take first 10
    let s = val;
    if (s.includes('T')) s = s.slice(0, 10);
    const parts = s.split('-'); // YYYY-MM-DD
    if (parts.length === 3) {
      return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    // If already dd/mm/yyyy, just return it
    if (s.includes('/')) return s;
  }
  return '';
}

function openPicker() {
  temp.value = props.modelValue;
  // If there's an open <dialog> (native modal) place the picker inside it so
  // it appears above the dialog's top layer. Otherwise default to body.
  const dialogEl = document.querySelector('dialog[open]');
  teleportTarget.value = dialogEl ? dialogEl : 'body';
  // ensure display is in sync when opening
  displayValue.value = temp.value ? formatToDisplay(temp.value) : '';
  open.value = true;
}

function closePicker() {
  open.value = false;
  emit('close');
  // Reset teleport target after closing
  teleportTarget.value = 'body';
}

function confirm() {
  // Ensure we emit a YYYY-MM-DD string while showing DD/MM/YYYY to users
  let out = temp.value;
  if (out instanceof Date) out = ymdFromDate(out);
  if (typeof out === 'string' && out.includes('T')) out = out.slice(0, 10);
  // update displayed text to dd/mm/yyyy
  displayValue.value = out ? formatToDisplay(out) : '';
  emit('update:modelValue', out);
  closePicker();
}

function onSelect(val) {
  temp.value = val;
}

// Default allowedDates: prevent selecting dates before today
const defaultAllowedDates = (val) => {
  if (!val) return true;
  // Accept either Date or string (YYYY-MM-DD)
  let dateStr = val;
  if (val instanceof Date) {
    dateStr = ymdFromDate(val);
  } else if (typeof val === 'string' && val.includes('T')) {
    dateStr = val.slice(0, 10);
  }
  const today = new Date().toISOString().slice(0, 10);
  return dateStr >= today;
};

// Ensure we pass an actual function to the picker (not a computed ref)
function allowedFn(val) {
  if (typeof props.allowedDates === 'function') {
    // Normalize value to YYYY-MM-DD string when possible because many
    // user-provided validators (like the one in `Form.vue`) expect that
    // format. We'll try the normalized string first, then fall back to
    // the raw value if needed.
    let dateStr = val;
    if (val instanceof Date) {
      dateStr = ymdFromDate(val);
    } else if (typeof val === 'string' && val.includes('T')) {
      dateStr = val.slice(0, 10);
    }

    try {
      const resNormalized = props.allowedDates(dateStr);
      if (typeof resNormalized === 'boolean') return resNormalized;
    } catch (e) {
      // ignore and try raw value below
    }

    try {
      const resRaw = props.allowedDates(val);
      if (typeof resRaw === 'boolean') return resRaw;
    } catch (e) {
      // ignore and fall back
    }

    return defaultAllowedDates(val);
  }

  return defaultAllowedDates(val);
}
</script>

<template>
  <div>
    <v-text-field
      v-model="displayValue"
      :label="label"
      readonly
      @click="openPicker"
      :error="error"
      :error-messages="errorMessages"
    />

    <teleport :to="teleportTarget">
      <div v-if="open" class="dp-overlay" @click.self="closePicker">
        <div class="dp-card">
          <v-card>
            <v-date-picker v-model="temp" :allowed-dates="allowedFn" :locale="locale" />
            <v-card-actions class="justify-end">
              <v-btn text @click="closePicker">Cerrar</v-btn>
              <v-btn text @click="confirm">OK</v-btn>
            </v-card-actions>
          </v-card>
        </div>
      </div>
    </teleport>
  </div>
</template>

<style>
.v-date-picker {
  max-width: 100%;
}

.dp-overlay {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0,0,0,0.35);
  z-index: 99999;
}

.dp-card {
  z-index: 100000;
  max-width: 90vw;
}
</style>
