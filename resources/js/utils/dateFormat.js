/**
 * Format date to YYYY-MM-DD (year, month, day only)
 * @param {string|Date} date - Date string or Date object
 * @returns {string} Formatted date string or '-' if date is null/undefined
 */
export function formatDate(date) {
    if (!date) return '-';

    const dateObj = typeof date === 'string' ? new Date(date) : date;
 
    if (isNaN(dateObj.getTime())) return '-';

    const year = dateObj.getFullYear();
    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
    const day = String(dateObj.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}
