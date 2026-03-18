import { usePage } from '@inertiajs/vue3';

const getValue = (object, path) => path.split('.').reduce((current, segment) => current?.[segment], object);

export const useTrans = () => {
    const page = usePage();

    const trans = (key, replacements = {}) => {
        const translations = page.props.locale?.translations ?? {};
        let value = getValue(translations, key) ?? key;

        if (typeof value !== 'string') {
            return key;
        }

        Object.entries(replacements).forEach(([replacementKey, replacementValue]) => {
            value = value.replaceAll(`:${replacementKey}`, replacementValue);
        });

        return value;
    };

    return {
        trans,
    };
};
