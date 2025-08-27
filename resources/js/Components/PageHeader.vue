<template>
    <div class="mb-4">
        <div v-if="crumbs && crumbs.length" class="mb-1">
            <n-breadcrumb>
                <n-breadcrumb-item v-for="(c, i) in crumbs" :key="i">
                    <Link v-if="c.href" :href="c.href">{{ c.label }}</Link>
                    <span v-else>{{ c.label }}</span>
                </n-breadcrumb-item>
            </n-breadcrumb>
        </div>
        <div class="flex items-center justify-between">
            <div>
                <h1 :class="titleClasses">
                    {{ title }}
                </h1>
                <p
                    v-if="subtitle"
                    class="text-sm text-gray-500 dark:text-gray-400 mt-0.5"
                >
                    {{ subtitle }}
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <slot name="actions" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { NBreadcrumb, NBreadcrumbItem } from "naive-ui";
import { Link } from "@inertiajs/vue3";
import { computed } from 'vue'

const props = defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: "" },
    crumbs: { type: Array, default: () => [] },
    titleColor: { type: String, default: 'black' }, // 'black' | 'white'
    titleClass: { type: String, default: '' },
});

const titleClasses = computed(() => {
    const base = 'text-xl font-bold'
    const color = props.titleColor === 'white' ? 'text-white' : 'text-black'
    return `${base} ${color} ${props.titleClass}`.trim()
})
</script>
