<link href="https://fonts.googleapis.com/css2?family={{ urlencode($brand['font_heading']) }}:wght@400;600;700&family={{ urlencode($brand['font_body']) }}:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    :root {
        --studio-color-primary: {{ $brand['color_primary'] }};
        --studio-color-secondary: {{ $brand['color_secondary'] }};
        --studio-color-accent: {{ $brand['color_accent'] }};
        --studio-color-text-dark: {{ $brand['color_text_dark'] }};
        --studio-color-text-light: {{ $brand['color_text_light'] }};
        --studio-color-background: {{ $brand['color_background'] }};
        --studio-font-heading: '{{ $brand['font_heading'] }}';
        --studio-font-body: '{{ $brand['font_body'] }}';
    }
</style>