/**
 * Resolve image path to a valid URL
 * Handles various path formats and normalizes them to work with Laravel storage
 * @param {string|null|undefined} path - Image path from database
 * @returns {string|null} Resolved image URL or null if path is invalid
 */
export function resolveImage(path) {
    if (!path) return null;

    let p = String(path).trim();
    
    // If empty after trimming, return null
    if (!p) return null;

    // Normalize slashes (Windows uses backslashes)
    p = p.replace(/\\/g, '/');

    // Remove duplicate slashes (except after protocol)
    p = p.replace(/([^:]\/)\/+/g, '$1');

    // Absolute HTTP(S) URL - return as is
    if (p.startsWith('http://') || p.startsWith('https://')) {
        return p;
    }

    // Debug logging - check browser console to see image paths
    if (process.env.NODE_ENV === 'development') {
        console.log('resolveImage - Original:', path, 'Normalized:', p);
    }

    // If the path already starts with '/storage/', return as is
    if (p.startsWith('/storage/')) {
        return p;
    }

    // If the path contains '/storage/', trim everything before it
    const storageIndex = p.indexOf('/storage/');
    if (storageIndex !== -1) {
        p = p.slice(storageIndex);
        return p;
    }

    // If path contains 'storage/' without leading slash, add it
    if (p.includes('storage/')) {
        const storageIdx = p.indexOf('storage/');
        p = '/' + p.slice(storageIdx);
        return p;
    }

    // If path starts with 'storage/' (no leading slash), add it
    if (p.startsWith('storage/')) {
        p = '/' + p;
        return p;
    }

    // Strip leading 'public/' if present
    if (p.startsWith('public/')) {
        p = p.slice('public/'.length);
    }

    // If path looks like it's in storage directories (employees/, items/, etc.)
    // but doesn't have /storage/ prefix, add it
    if (p.match(/^(employees|items|projects|logo)\//)) {
        p = '/storage/' + p;
        return p;
    }

    // Ensure it starts with '/'
    if (!p.startsWith('/')) {
        p = '/' + p;
    }

    return p;
}

