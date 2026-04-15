/**
 * Galgospedia — Genealogy Tree Renderer
 * Uses D3.js v7 to render an interactive, unlimited-depth pedigree tree.
 * "children" in JSON = parents of the dog (tree grows left → ancestors on right)
 */

(function () {
    'use strict';

    const COLORS = {
        male:     { border: '#CC0000', bg: '#fff1f2' },
        female:   { border: '#F5A000', bg: '#fffbeb' },
        unknown:  { border: '#9ca3af', bg: '#f9fafb' },
        stallion: { border: '#3b82f6', bg: '#eff6ff' },
        broodmare:{ border: '#ec4899', bg: '#fdf2f8' },
    };

    const NODE_W  = 220;  // wider to fit full names
    const NODE_H  = 120;  // taller to fit more info
    const H_GAP   = 220;
    const V_GAP   = 20;

    let svg, g, zoom;
    let currentGen = 5;

    // ── Init ──────────────────────────────────────────────────

    function init() {
        const genSelect = document.getElementById('gen-select');
        if (genSelect) {
            genSelect.addEventListener('change', () => {
                currentGen = parseInt(genSelect.value, 10);
                loadTree();
            });
        }
        loadTree();
    }

    // ── Load data from API ────────────────────────────────────

    function loadTree() {
        document.getElementById('tree-loading').classList.remove('hidden');
        document.getElementById('tree-error').classList.add('hidden');
        document.getElementById('tree-svg').classList.add('hidden');

        fetch(`/api/arbol/${DOG_SLUG}?gen=${currentGen}`)
            .then(r => r.ok ? r.json() : Promise.reject(r.status))
            .then(data => {
                if (!data || data.error) throw new Error(data?.error || 'No data');
                document.getElementById('tree-loading').classList.add('hidden');
                document.getElementById('tree-svg').classList.remove('hidden');
                renderTree(data);
            })
            .catch(() => {
                document.getElementById('tree-loading').classList.add('hidden');
                document.getElementById('tree-error').classList.remove('hidden');
            });
    }

    // ── Render D3 tree ────────────────────────────────────────

    function renderTree(data) {
        const svgEl   = document.getElementById('tree-svg');
        const width   = svgEl.parentElement.clientWidth || 900;
        const height  = Math.max(500, window.innerHeight - 300);

        svgEl.setAttribute('viewBox', `0 0 ${width} ${height}`);
        svgEl.setAttribute('height', height);

        // Clear previous
        d3.select(svgEl).selectAll('*').remove();

        svg  = d3.select(svgEl);
        zoom = d3.zoom()
                 .scaleExtent([0.1, 3])
                 .on('zoom', e => g.attr('transform', e.transform));
        svg.call(zoom);

        g = svg.append('g');

        // Build D3 hierarchy
        const root = d3.hierarchy(data, d => d.children?.length ? d.children : null);

        // Use tree layout — horizontal (swap x/y)
        const treeLayout = d3.tree()
            .nodeSize([NODE_H + V_GAP, NODE_W + H_GAP]);

        treeLayout(root);

        // Center the root node
        const initialX = width / 2 - root.y;
        const initialY = height / 2 - root.x;
        svg.call(zoom.transform, d3.zoomIdentity.translate(initialX, initialY));

        // ── Links ──────────────────────────────────────────
        g.selectAll('.link')
         .data(root.links())
         .join('path')
         .attr('class', 'link')
         .attr('fill', 'none')
         .attr('stroke', d => {
             const side = d.target.data.side;
             return side === 'paternal' ? '#93c5fd' : side === 'maternal' ? '#f9a8d4' : '#d1d5db';
         })
         .attr('stroke-width', 1.5)
         .attr('d', d3.linkHorizontal()
             .x(d => d.y)
             .y(d => d.x)
         );

        // ── Nodes ─────────────────────────────────────────
        const node = g.selectAll('.node')
            .data(root.descendants())
            .join('g')
            .attr('class', 'node')
            .attr('transform', d => `translate(${d.y},${d.x})`)
            .style('cursor', 'pointer')
            .on('click', (event, d) => {
                window.location.href = `/galgos/${d.data.slug}`;
            });

        // Node background rect
        node.append('rect')
            .attr('x', -(NODE_W / 2))
            .attr('y', -(NODE_H / 2))
            .attr('width', NODE_W)
            .attr('height', NODE_H)
            .attr('rx', 10)
            .attr('fill', d => nodeColor(d.data).bg)
            .attr('stroke', d => nodeColor(d.data).border)
            .attr('stroke-width', d => d.data.depth === 0 ? 3 : 1.5)
            .on('mouseover', function () {
                d3.select(this).attr('stroke-width', 3).attr('filter', 'drop-shadow(0 4px 6px rgba(0,0,0,0.15))');
            })
            .on('mouseout', function (event, d) {
                d3.select(this).attr('stroke-width', d.data.depth === 0 ? 3 : 1.5).attr('filter', null);
            });

        // Photo area constants
        const PHOTO_X = -(NODE_W / 2) + 4;
        const PHOTO_Y = -(NODE_H / 2) + 4;
        const PHOTO_W = 72;
        const PHOTO_H = NODE_H - 8;
        const TEXT_X  = -(NODE_W / 2) + 82;  // right of photo + gap
        const TEXT_MAX_W = (NODE_W / 2) - 82 - 6; // available width in px

        const defs = svg.append('defs');

        // Photo area background
        node.append('rect')
            .attr('x', PHOTO_X)
            .attr('y', PHOTO_Y)
            .attr('width', PHOTO_W)
            .attr('height', PHOTO_H)
            .attr('rx', 6)
            .attr('fill', d => nodeColor(d.data).bg);

        node.each(function (d) {
            const clipId = `clip-${d.data.id}`;
            defs.append('clipPath')
                .attr('id', clipId)
                .append('rect')
                .attr('x', PHOTO_X)
                .attr('y', PHOTO_Y)
                .attr('width', PHOTO_W)
                .attr('height', PHOTO_H)
                .attr('rx', 6);

            const imgSrc = d.data.photo ? '/' + d.data.photo : '/logo/logo512-512.png';
            const opacity = d.data.photo ? 1 : 0.18;

            d3.select(this).append('image')
                .attr('href', imgSrc)
                .attr('x', PHOTO_X)
                .attr('y', PHOTO_Y)
                .attr('width', PHOTO_W)
                .attr('height', PHOTO_H)
                .attr('clip-path', `url(#${clipId})`)
                .attr('preserveAspectRatio', 'xMidYMid meet')
                .attr('opacity', opacity);
        });

        // ── Text content ───────────────────────────────────
        node.each(function (d) {
            const el       = d3.select(this);
            const nameParts = wrapName(d.data.name, 18);
            const hasTwo    = nameParts.length > 1;

            // Name — line 1
            el.append('text')
                .attr('x', TEXT_X)
                .attr('y', hasTwo ? -30 : -22)
                .attr('text-anchor', 'start')
                .attr('font-size', '11px')
                .attr('font-weight', '700')
                .attr('fill', '#1f2937')
                .text(nameParts[0]);

            // Name — line 2 (if long)
            if (hasTwo) {
                el.append('text')
                    .attr('x', TEXT_X)
                    .attr('y', -16)
                    .attr('text-anchor', 'start')
                    .attr('font-size', '11px')
                    .attr('font-weight', '700')
                    .attr('fill', '#1f2937')
                    .text(nameParts[1]);
            }

            const baseY = hasTwo ? -2 : -6;

            // Birth year
            if (d.data.birth) {
                el.append('text')
                    .attr('x', TEXT_X)
                    .attr('y', baseY)
                    .attr('text-anchor', 'start')
                    .attr('font-size', '9px')
                    .attr('fill', '#9ca3af')
                    .text(d.data.birth.substring(0, 4));
            }

            // Club · País
            const location = [d.data.club, d.data.country].filter(Boolean).join(' · ');
            if (location) {
                el.append('text')
                    .attr('x', TEXT_X)
                    .attr('y', baseY + 14)
                    .attr('text-anchor', 'start')
                    .attr('font-size', '9px')
                    .attr('fill', '#6b7280')
                    .text(truncate(location, 22));
            }

            // Campeón / Títulos
            if (d.data.champion) {
                const champEl = el.append('text')
                    .attr('x', TEXT_X)
                    .attr('y', baseY + 28)
                    .attr('text-anchor', 'start')
                    .attr('font-size', '9px')
                    .attr('font-weight', '600')
                    .attr('fill', '#b45309')
                    .text('🏆 ' + truncate(d.data.champion, 28));
                champEl.append('title').text(d.data.champion);
            }
        });

        // Stallion/broodmare badge dot
        node.filter(d => d.data.isStallion || d.data.isBreeder)
            .append('circle')
            .attr('cx', (NODE_W / 2) - 10)
            .attr('cy', -(NODE_H / 2) + 10)
            .attr('r', 7)
            .attr('fill', d => d.data.isStallion ? '#3b82f6' : '#ec4899');
    }

    // ── Helpers ───────────────────────────────────────────────

    function nodeColor(data) {
        if (data.isStallion) return COLORS.stallion;
        if (data.isBreeder)  return COLORS.broodmare;
        return COLORS[data.gender] || COLORS.unknown;
    }

    /** Wrap name into max 2 lines of maxChars each */
    function wrapName(str, maxChars) {
        if (!str) return [''];
        if (str.length <= maxChars) return [str];
        // Try to break at a word boundary
        const idx = str.lastIndexOf(' ', maxChars);
        if (idx > 2) {
            const line2 = str.substring(idx + 1);
            return [str.substring(0, idx), truncate(line2, maxChars)];
        }
        return [str.substring(0, maxChars), truncate(str.substring(maxChars), maxChars)];
    }

    function truncate(str, max) {
        return str && str.length > max ? str.substring(0, max) + '…' : (str || '');
    }

    // ── Export SVG ────────────────────────────────────────────

    window.exportSVG = function () {
        const svgEl = document.getElementById('tree-svg');
        const blob  = new Blob(
            ['<?xml version="1.0" encoding="UTF-8"?>\n', svgEl.outerHTML],
            { type: 'image/svg+xml;charset=utf-8' }
        );
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href     = url;
        a.download = `arbol-${DOG_SLUG}.svg`;
        a.click();
        URL.revokeObjectURL(url);
    };

    // Expose for inline onclick (retry button)
    window.loadTree = loadTree;

    // ── Boot ─────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', init);

    // Re-render on window resize
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (document.getElementById('tree-svg') && !document.getElementById('tree-svg').classList.contains('hidden')) {
                loadTree();
            }
        }, 400);
    });

})();
