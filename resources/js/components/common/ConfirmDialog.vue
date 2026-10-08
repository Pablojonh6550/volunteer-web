<script setup lang="ts">
/**
 * Diálogo de confirmação para ações destrutivas ou irreversíveis.
 * Baseado no Dialog do design system (reka-ui): foco preso no diálogo,
 * fecha com Esc e devolve o foco ao gatilho.
 */
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

withDefaults(
    defineProps<{
        title: string;
        description?: string;
        confirmLabel?: string;
        destructive?: boolean;
        loading?: boolean;
    }>(),
    { confirmLabel: 'Confirmar', destructive: false, loading: false },
);

const open = defineModel<boolean>('open', { default: false });
const emit = defineEmits<{ confirm: [] }>();
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription v-if="description">{{
                    description
                }}</DialogDescription>
            </DialogHeader>
            <slot />
            <DialogFooter>
                <Button
                    variant="outline"
                    :disabled="loading"
                    @click="open = false"
                    >Voltar</Button
                >
                <Button
                    :variant="destructive ? 'destructive' : 'default'"
                    :disabled="loading"
                    @click="emit('confirm')"
                >
                    {{ confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
