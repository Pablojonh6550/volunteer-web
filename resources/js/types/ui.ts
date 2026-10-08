import type { Component } from 'vue';
import type { RouteLocationRaw } from 'vue-router';

export type Appearance = 'light' | 'dark' | 'system';
export type ResolvedAppearance = 'light' | 'dark';

export interface NavItem {
    title: string;
    to: RouteLocationRaw;
    icon?: Component;
}

export interface SelectOption<T = string> {
    value: T;
    label: string;
}
