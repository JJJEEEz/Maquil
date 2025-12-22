<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);
const dialog = ref();
const showSlot = ref(props.show);

watch(
    () => props.show,
    () => {
        if (props.show) {
            document.body.style.overflow = 'hidden';
            showSlot.value = true;

            dialog.value?.showModal();
        } else {
            document.body.style.overflow = '';

            setTimeout(() => {
                dialog.value?.close();
                showSlot.value = false;
            }, 200);
        }
    },
);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape') {
        e.preventDefault();

        if (props.show) {
            close();
        }
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);

    document.body.style.overflow = '';
});

const maxWidthClass = computed(() => {
    return {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});
</script>

<template>
    <dialog
        class="z-50 m-0 min-h-full min-w-full overflow-y-auto bg-transparent backdrop:bg-transparent"
        ref="dialog"
    >
        <div
            class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
            scroll-region
        >
            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-show="show"
                    class="fixed inset-0 transform transition-all"
                    @click="close"
                >
                    <div
                        class="absolute inset-0 bg-gray-500 opacity-75"
                    />
                </div>
            </Transition>

            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
                <div
                    v-show="show"
                    class="mb-6 transform overflow-hidden rounded-lg shadow-xl transition-all sm:mx-auto sm:w-full themed-modal"
                    :class="maxWidthClass"
                >
                    <!-- Scrollable content area: restrict max height and allow internal scrolling -->
                    <div class="modal-inner">
                        <div class="modal-body">
                          <slot v-if="showSlot" />
                        </div>
                        <div class="modal-actions-wrapper">
                          <slot name="footer" />
                        </div>
                    </div>
                </div>
            </Transition>
        </div>
    </dialog>
</template>

<style scoped>
.themed-modal {
    background-color: #ffffff !important;
    color: #0B1220;
}

.v-theme--dark .themed-modal {
    background-color: #16151bff !important;
    color: #F8FAFC;
}

.modal-inner {
    position: relative;
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 80px);
    overflow: auto;
    background-color: #ffffff;
}

.v-theme--dark .modal-inner {
    background-color: #16151bff;
}

.modal-body {
    flex: 1 1 auto;
    overflow: auto;
    background-color: #ffffff;
}

.v-theme--dark .modal-body {
    background-color: #000000;
}

.modal-actions-wrapper {
    flex: 0 0 auto;
    background-color: #ffffff;
    border-top: 1px solid rgba(0,0,0,0.06);
    padding-top: 12px;
    padding-bottom: 12px;
}

.v-theme--dark .modal-actions-wrapper {
    background-color: #000000;
    border-top-color: rgba(255,255,255,0.08);
}
</style>
