const isObject = (obj: unknown): boolean =>
	obj !== null && typeof obj === 'object' && !Array.isArray(obj) && !(obj instanceof Date);

const toCamelCase = (str: string): string =>
	str.replace(/([-_][a-z])/g, (group) => group.toUpperCase().replace('-', '').replace('_', ''));

const toSnakeCase = (str: string): string => str.replace(/[A-Z]/g, (letter) => `_${letter.toLowerCase()}`);

const toKebabCase = (str: string): string =>
	str
		.replace(/[A-Z]/g, (letter) => `-${letter.toLowerCase()}`)
		.replace(/[\s_]+/g, '-')
		.replace(/^-+|-+$/g, '')
		.toLowerCase();

export const camelToSnakeObj = (obj: unknown): unknown => {
	if (!isObject(obj)) return obj;

	const result: Record<string, unknown> = {};

	for (const key in obj as Record<string, unknown>) {
		if (Object.prototype.hasOwnProperty.call(obj, key)) {
			const snakeKey = toSnakeCase(key);
			const value = (obj as Record<string, unknown>)[key];

			if (isObject(value)) {
				result[snakeKey] = camelToSnakeObj(value);
			} else if (Array.isArray(value)) {
				result[snakeKey] = value.map((item) => (isObject(item) ? camelToSnakeObj(item) : item));
			} else {
				result[snakeKey] = value;
			}
		}
	}

	return result;
};

export const snakeToCamelObj = <T>(obj: unknown): T => {
	if (obj === null || typeof obj !== 'object' || obj instanceof Date) {
		return obj as T;
	}

	if (Array.isArray(obj)) {
		return obj.map(snakeToCamelObj) as unknown as T;
	}

	const result: Record<string, unknown> = {};

	for (const key in obj as Record<string, unknown>) {
		if (Object.prototype.hasOwnProperty.call(obj, key)) {
			const camelKey = toCamelCase(key);
			const value = (obj as Record<string, unknown>)[key];

			if (isObject(value)) {
				result[camelKey] = snakeToCamelObj(value);
			} else if (Array.isArray(value)) {
				result[camelKey] = value.map((item) => (isObject(item) ? snakeToCamelObj(item) : item));
			} else {
				result[camelKey] = value;
			}
		}
	}

	return result as T;
};

export { toKebabCase };
