<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "tertiary-container": "#006663",
                    "on-surface": "#191c1e",
                    "on-secondary-container": "#fffbff",
                    "primary-fixed-dim": "#b2c5ff",
                    "surface-variant": "#e1e2e4",
                    "on-secondary": "#ffffff",
                    "error-container": "#ffdad6",
                    "on-tertiary-container": "#00ebe5",
                    "on-secondary-fixed": "#40000c",
                    "on-tertiary": "#ffffff",
                    "on-primary-container": "#c4d2ff",
                    "on-error-container": "#93000a",
                    "secondary": "#ba0035",
                    "on-background": "#191c1e",
                    "primary": "#003d9b",
                    "surface-container-low": "#f3f4f6",
                    "primary-fixed": "#dae2ff",
                    "outline-variant": "#c3c6d6",
                    "tertiary-fixed-dim": "#00ddd8",
                    "on-primary-fixed-variant": "#0040a2",
                    "on-primary-fixed": "#001848",
                    "on-secondary-fixed-variant": "#920027",
                    "outline": "#737685",
                    "surface-container-high": "#e7e8ea",
                    "on-tertiary-fixed": "#00201f",
                    "secondary-container": "#e51146",
                    "primary-container": "#0052cc",
                    "surface-container-highest": "#e1e2e4",
                    "tertiary": "#004c4a",
                    "inverse-surface": "#2e3132",
                    "tertiary-fixed": "#35fbf5",
                    "background": "#f8f9fb",
                    "secondary-fixed-dim": "#ffb3b6",
                    "surface": "#f8f9fb",
                    "inverse-on-surface": "#f0f1f3",
                    "surface-dim": "#d9dadc",
                    "surface-container": "#edeef0",
                    "secondary-fixed": "#ffdada",
                    "surface-bright": "#f8f9fb",
                    "on-error": "#ffffff",
                    "on-surface-variant": "#434654",
                    "surface-tint": "#0c56d0",
                    "inverse-primary": "#b2c5ff",
                    "on-primary": "#ffffff",
                    "on-tertiary-fixed-variant": "#00504d",
                    "surface-container-lowest": "#ffffff",
                    "error": "#ba1a1a"
                },
                borderRadius: {
                    DEFAULT: "0.125rem",
                    lg: "0.25rem",
                    xl: "0.5rem",
                    full: "0.75rem"
                },
                spacing: {
                    unit: "4px",
                    "card-padding": "16px",
                    "margin-page": "24px",
                    "table-cell-padding": "12px 16px",
                    "gutter": "16px"
                },
                fontFamily: {
                    "headline-md": ["Inter"],
                    "body-md": ["Inter"],
                    "body-sm": ["Inter"],
                    "data-mono": ["Inter"],
                    "headline-lg": ["Inter"],
                    "label-caps": ["Inter"]
                },
                fontSize: {
                    "headline-md": ["18px", { lineHeight: "24px", letterSpacing: "-0.01em", fontWeight: "600" }],
                    "body-md": ["14px", { lineHeight: "20px", fontWeight: "400" }],
                    "body-sm": ["13px", { lineHeight: "18px", fontWeight: "400" }],
                    "data-mono": ["13px", { lineHeight: "18px", letterSpacing: "0.02em", fontWeight: "500" }],
                    "headline-lg": ["24px", { lineHeight: "32px", letterSpacing: "-0.02em", fontWeight: "600" }],
                    "label-caps": ["11px", { lineHeight: "16px", letterSpacing: "0.05em", fontWeight: "700" }]
                }
            }
        }
    }
</script>
<style>
    body { font-family: 'Inter', sans-serif; }
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        font-size: 20px;
        line-height: 1;
        user-select: none;
    }
    .icon-fill { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: #f3f4f6; }
    ::-webkit-scrollbar-thumb { background: #c3c6d6; border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #737685; }
</style>
