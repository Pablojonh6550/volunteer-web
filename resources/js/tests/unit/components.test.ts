import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vite-plus/test';
import { h } from 'vue';
import FormField from '@/components/common/FormField.vue';
import StatusBadge from '@/components/common/StatusBadge.vue';
import RequestCard from '@/components/requests/RequestCard.vue';
import type { HelpRequest } from '@/types';

describe('StatusBadge', () => {
    it('exibe o rótulo em português (cor nunca é o único indicador)', () => {
        expect(
            mount(StatusBadge, {
                props: { kind: 'status', value: 'in_progress' },
            }).text(),
        ).toBe('Em andamento');
        expect(
            mount(StatusBadge, {
                props: { kind: 'participation', value: 'approved' },
            }).text(),
        ).toBe('Aprovado');
    });

    it('anuncia "Prioridade" para leitores de tela', () => {
        const wrapper = mount(StatusBadge, {
            props: { kind: 'priority', value: 'urgent' },
        });
        expect(wrapper.find('.sr-only').text()).toContain('Prioridade');
        expect(wrapper.text()).toContain('Urgente');
    });
});

describe('FormField', () => {
    it('associa label, erro e aria-* ao controle', () => {
        const wrapper = mount(FormField, {
            props: {
                id: 'email',
                label: 'E-mail',
                error: 'Obrigatório',
                hint: 'Seu e-mail',
                required: true,
            },
            slots: {
                default: (field: Record<string, unknown>) => h('input', field),
            },
        });

        const input = wrapper.get('input');
        expect(wrapper.get('label').attributes('for')).toBe('email');
        expect(input.attributes('id')).toBe('email');
        expect(input.attributes('aria-invalid')).toBe('true');
        expect(input.attributes('aria-describedby')).toBe(
            'email-hint email-error',
        );
        expect(wrapper.get('#email-error').attributes('role')).toBe('alert');
        expect(wrapper.text()).toContain('(obrigatório)');
    });

    it('não marca aria-invalid sem erro', () => {
        const wrapper = mount(FormField, {
            props: { id: 'name', label: 'Nome' },
            slots: {
                default: (field: Record<string, unknown>) => h('input', field),
            },
        });

        expect(wrapper.get('input').attributes('aria-invalid')).toBeUndefined();
        expect(wrapper.find('[role="alert"]').exists()).toBe(false);
    });
});

describe('RequestCard', () => {
    const request: HelpRequest = {
        id: 7,
        organization_id: 1,
        category_id: null,
        type: 'donation',
        title: 'Cestas básicas',
        description: 'Precisamos de alimentos não perecíveis.',
        quantity: 10,
        priority: 'high',
        status: 'open',
        start_date: null,
        end_date: null,
        latitude: null,
        longitude: null,
        distance_km: 1.5,
        interested_count: 3,
        organization: {
            id: 1,
            name: 'Instituto Teste',
        } as HelpRequest['organization'],
        created_at: null,
        updated_at: null,
    };

    it('mostra título, ONG, distância e interessados', () => {
        const wrapper = mount(RequestCard, {
            props: { request, to: '/voluntario/solicitacoes/7' },
        });

        expect(wrapper.get('h2').text()).toBe('Cestas básicas');
        expect(wrapper.text()).toContain('Instituto Teste');
        expect(wrapper.text()).toContain('1,5 km');
        expect(wrapper.text()).toContain('3 interessado(s)');
        expect(wrapper.text()).toContain('Doação');
    });

    it('só exibe o status quando solicitado', () => {
        expect(
            mount(RequestCard, { props: { request, to: '/' } }).text(),
        ).not.toContain('Aberta');
        expect(
            mount(RequestCard, {
                props: { request, to: '/', showStatus: true },
            }).text(),
        ).toContain('Aberta');
    });
});
