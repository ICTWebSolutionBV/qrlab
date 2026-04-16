export const sansSerif = [
    { label: 'Inter', value: 'Inter', stack: "'Inter', sans-serif" },
    { label: 'Roboto', value: 'Roboto', stack: "'Roboto', sans-serif" },
    { label: 'Open Sans', value: 'Open Sans', stack: "'Open Sans', sans-serif" },
    { label: 'Lato', value: 'Lato', stack: "'Lato', sans-serif" },
    { label: 'Montserrat', value: 'Montserrat', stack: "'Montserrat', sans-serif" },
    { label: 'Poppins', value: 'Poppins', stack: "'Poppins', sans-serif" },
    { label: 'Nunito', value: 'Nunito', stack: "'Nunito', sans-serif" },
    { label: 'Raleway', value: 'Raleway', stack: "'Raleway', sans-serif" },
]

export const monospace = [
    { label: 'Roboto Mono', value: 'Roboto Mono', stack: "'Roboto Mono', monospace" },
    { label: 'Source Code Pro', value: 'Source Code Pro', stack: "'Source Code Pro', monospace" },
    { label: 'Space Mono', value: 'Space Mono', stack: "'Space Mono', monospace" },
    { label: 'IBM Plex Mono', value: 'IBM Plex Mono', stack: "'IBM Plex Mono', monospace" },
    { label: 'Fira Code', value: 'Fira Code', stack: "'Fira Code', monospace" },
]

export const allFonts = [...sansSerif, ...monospace]

export function getFontStack(fontValue) {
    const found = allFonts.find(f => f.value === fontValue)
    return found ? found.stack : "'Inter', sans-serif"
}
