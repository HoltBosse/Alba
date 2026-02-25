/**
 * Layout Widget — Admin Backend Script
 *
 * Manages the slot builder UI. Reads from and writes to a single hidden
 * input (#layout_config_input) in the standard widget edit form.
 *
 * The JSON shape it maintains:
 * {
 *   layout:  string,          // named variant key
 *   gap:     string,          // spacing token
 *   padding: string,          // spacing token
 *   slots:   SlotObject[]
 * }
 *
 * SlotObject shapes:
 *   { type: 'rich',   html: string }
 *   { type: 'widget', widget_id: number, widget_title: string }
 *   { type: 'image',  image_id: number, alt: string, caption: string }
 *   { type: 'layout', layout: string, gap: string, padding: string, slots: SlotObject[] }
 */

(function () {
    'use strict';

    const builder      = document.getElementById('layout_widget_builder');
    const configInput  = document.getElementById('layout_config_input');
    const slotList     = document.getElementById('layout_slot_list');
    const addSlotBtn   = document.getElementById('layout_add_slot');
    const gapSelect    = document.getElementById('layout_gap_select');
    const paddingSelect= document.getElementById('layout_padding_select');

    // Available widgets — injected by PHP into a hidden div
    const widgetRegistry = JSON.parse(
        document.getElementById('layout_widget_registry').dataset.widgets || '[]'
    );

    // ── State ────────────────────────────────────────────────────────────────

    let state = JSON.parse(builder.dataset.config || '{}');
    if (!state.layout)  state.layout  = 'full';
    if (!state.gap)     state.gap     = 'md';
    if (!state.padding) state.padding = 'none';
    if (!Array.isArray(state.slots)) state.slots = [];

    // ── Sync state → hidden input ─────────────────────────────────────────────

    function persist() {
        configInput.value = JSON.stringify(state);
    }

    // ── Variant picker ────────────────────────────────────────────────────────

    document.querySelectorAll('.layout_variant_btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.layout_variant_btn').forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            state.layout = btn.dataset.variant;
            persist();
        });
    });

    // ── Spacing selects ───────────────────────────────────────────────────────

    gapSelect.addEventListener('change', () => {
        state.gap = gapSelect.value;
        persist();
    });

    paddingSelect.addEventListener('change', () => {
        state.padding = paddingSelect.value;
        persist();
    });

    // ── Slot rendering ────────────────────────────────────────────────────────

    /**
     * Re-render the entire slot list from state.slots.
     * Simple and reliable — no diffing needed for this scale of UI.
     */
    function renderSlots(slots, container, depth) {
        container.innerHTML = '';
        slots.forEach((slot, index) => {
            container.appendChild(buildSlotElement(slot, index, slots, depth || 0));
        });
    }

    /**
     * Build a single slot DOM element with its controls.
     */
    function buildSlotElement(slot, index, slotsArray, depth) {
        const wrapper = document.createElement('div');
        wrapper.className = 'layout_slot_item';
        wrapper.dataset.index = index;
        wrapper.dataset.depth = depth;

        // ── Type selector ──────────────────────────────────────────────────
        const typeRow = document.createElement('div');
        typeRow.className = 'layout_slot_type_row';

        const typeLabel = document.createElement('span');
        typeLabel.className = 'layout_slot_label';
        typeLabel.textContent = `Slot ${index + 1}`;

        const typeSelect = document.createElement('select');
        typeSelect.className = 'select layout_slot_type_select';
        const slotTypes = { rich: 'Rich Text', widget: 'Widget', image: 'Image', layout: 'Nested Layout' };

        // At max depth, remove 'layout' as an option
        Object.entries(slotTypes).forEach(([val, label]) => {
            if (val === 'layout' && depth >= 2) return; // 0-indexed, so depth 2 = 3rd level
            const opt = document.createElement('option');
            opt.value = val;
            opt.textContent = label;
            if (val === (slot.type || 'rich')) opt.selected = true;
            typeSelect.appendChild(opt);
        });

        typeSelect.addEventListener('change', () => {
            slot.type = typeSelect.value;
            // Reset slot content when type changes
            const keys = ['html', 'widget_id', 'widget_title', 'image_id', 'alt', 'caption', 'layout', 'gap', 'padding', 'slots'];
            keys.forEach(k => delete slot[k]);
            // Set defaults for new type
            if (slot.type === 'layout') {
                slot.layout = 'full'; slot.gap = 'md'; slot.padding = 'none'; slot.slots = [];
            }
            persist();
            // Re-render this slot's content area only
            const contentArea = wrapper.querySelector('.layout_slot_content');
            contentArea.innerHTML = '';
            contentArea.appendChild(buildSlotContent(slot, slotsArray, depth));
        });

        // ── Order and remove controls ──────────────────────────────────────
        const controls = document.createElement('div');
        controls.className = 'layout_slot_controls';

        const upBtn = document.createElement('button');
        upBtn.type = 'button'; upBtn.textContent = '↑'; upBtn.title = 'Move up';
        upBtn.className = 'button is-small is-light';
        upBtn.disabled = index === 0;
        upBtn.addEventListener('click', () => {
            if (index === 0) return;
            [slotsArray[index - 1], slotsArray[index]] = [slotsArray[index], slotsArray[index - 1]];
            persist();
            renderSlots(slotsArray, container, depth);
        });

        const downBtn = document.createElement('button');
        downBtn.type = 'button'; downBtn.textContent = '↓'; downBtn.title = 'Move down';
        downBtn.className = 'button is-small is-light';
        downBtn.disabled = index === slotsArray.length - 1;
        downBtn.addEventListener('click', () => {
            if (index === slotsArray.length - 1) return;
            [slotsArray[index], slotsArray[index + 1]] = [slotsArray[index + 1], slotsArray[index]];
            persist();
            renderSlots(slotsArray, container, depth);
        });

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button'; removeBtn.textContent = '✕'; removeBtn.title = 'Remove slot';
        removeBtn.className = 'button is-small is-danger is-light';
        removeBtn.addEventListener('click', () => {
            if (!confirm('Remove this slot? Its content will be lost.')) return;
            slotsArray.splice(index, 1);
            persist();
            renderSlots(slotsArray, container, depth);
        });

        controls.append(upBtn, downBtn, removeBtn);
        typeRow.append(typeLabel, typeSelect, controls);

        // ── Slot content area (type-specific fields) ──────────────────────
        const contentArea = document.createElement('div');
        contentArea.className = 'layout_slot_content';
        contentArea.appendChild(buildSlotContent(slot, slotsArray, depth));

        wrapper.append(typeRow, contentArea);
        return wrapper;
    }

    /**
     * Builds the type-specific content controls for a slot.
     */
    function buildSlotContent(slot, slotsArray, depth) {
        const frag = document.createDocumentFragment();
        const type = slot.type || 'rich';

        if (type === 'rich') {
            frag.appendChild(buildRichControls(slot));
        } else if (type === 'widget') {
            frag.appendChild(buildWidgetControls(slot));
        } else if (type === 'image') {
            frag.appendChild(buildImageControls(slot));
        } else if (type === 'layout') {
            frag.appendChild(buildNestedLayoutControls(slot, depth));
        }

        return frag;
    }

    // ── Rich slot controls ─────────────────────────────────────────────────────

    function buildRichControls(slot) {
        const wrap = document.createElement('div');
        wrap.className = 'layout_slot_rich_wrap';

        const ta = document.createElement('textarea');
        ta.className = 'textarea layout_rich_textarea';
        ta.rows = 6;
        ta.placeholder = 'Enter HTML content, or wire up your Tiptap instance here...';
        ta.value = slot.html || '';

        // NOTE: In a full implementation, replace this textarea with a
        // Tiptap editor instance. The textarea is a functional placeholder
        // that stores raw HTML — suitable for initial development and for
        // cases where Tiptap is initialised externally and writes to this
        // element via its onUpdate callback.
        ta.addEventListener('input', () => {
            slot.html = ta.value;
            persist();
        });

        wrap.appendChild(ta);
        return wrap;
    }

    // ── Widget slot controls ──────────────────────────────────────────────────

    function buildWidgetControls(slot) {
        const wrap = document.createElement('div');
        wrap.className = 'layout_slot_widget_wrap';

        const label = document.createElement('label');
        label.className = 'label';
        label.textContent = 'Select Widget';

        const selectWrap = document.createElement('div');
        selectWrap.className = 'select';

        const sel = document.createElement('select');

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = '— Choose a widget —';
        sel.appendChild(placeholder);

        // Group by widget type for readability
        const grouped = {};
        widgetRegistry.forEach(w => {
            if (!grouped[w.type_title]) grouped[w.type_title] = [];
            grouped[w.type_title].push(w);
        });

        Object.entries(grouped).forEach(([groupName, widgets]) => {
            const og = document.createElement('optgroup');
            og.label = groupName;
            widgets.forEach(w => {
                const opt = document.createElement('option');
                opt.value = w.id;
                opt.textContent = w.title;
                if (parseInt(slot.widget_id) === w.id) opt.selected = true;
                og.appendChild(opt);
            });
            sel.appendChild(og);
        });

        sel.addEventListener('change', () => {
            const chosen = widgetRegistry.find(w => w.id === parseInt(sel.value));
            slot.widget_id    = chosen ? chosen.id    : null;
            slot.widget_title = chosen ? chosen.title : null;
            persist();
        });

        selectWrap.appendChild(sel);
        wrap.append(label, selectWrap);
        return wrap;
    }

    // ── Image slot controls ───────────────────────────────────────────────────
    // A minimal text-input based placeholder. In a full implementation this
    // would open the existing Alba media selector modal.

    function buildImageControls(slot) {
        const wrap = document.createElement('div');
        wrap.className = 'layout_slot_image_wrap';

        // Image ID field
        const idLabel = document.createElement('label');
        idLabel.className = 'label';
        idLabel.textContent = 'Image ID (from Media Library)';

        const idInput = document.createElement('input');
        idInput.type = 'number';
        idInput.className = 'input';
        idInput.placeholder = 'e.g. 42';
        idInput.value = slot.image_id || '';
        idInput.addEventListener('input', () => {
            slot.image_id = parseInt(idInput.value) || null;
            persist();
        });

        // Alt text field
        const altLabel = document.createElement('label');
        altLabel.className = 'label';
        altLabel.textContent = 'Alt Text';

        const altInput = document.createElement('input');
        altInput.type = 'text';
        altInput.className = 'input';
        altInput.placeholder = 'Describe the image for accessibility';
        altInput.value = slot.alt || '';
        altInput.addEventListener('input', () => {
            slot.alt = altInput.value;
            persist();
        });

        // Caption field
        const capLabel = document.createElement('label');
        capLabel.className = 'label';
        capLabel.textContent = 'Caption (optional)';

        const capInput = document.createElement('input');
        capInput.type = 'text';
        capInput.className = 'input';
        capInput.placeholder = 'Displayed below the image';
        capInput.value = slot.caption || '';
        capInput.addEventListener('input', () => {
            slot.caption = capInput.value;
            persist();
        });

        wrap.append(idLabel, idInput, altLabel, altInput, capLabel, capInput);
        return wrap;
    }

    // ── Nested layout slot controls ───────────────────────────────────────────

    function buildNestedLayoutControls(slot, depth) {
        const wrap = document.createElement('div');
        wrap.className = 'layout_slot_nested_wrap';
        wrap.dataset.depth = depth + 1;

        // Variant picker for the nested layout
        const variantLabel = document.createElement('p');
        variantLabel.className = 'label';
        variantLabel.textContent = 'Nested layout variant';

        const variantSelect = document.createElement('select');
        variantSelect.className = 'select layout_nested_variant_select';

        const variants = {
            full: 'Full Width', 'two-col': 'Two Columns (Equal)',
            'two-col-wide-left': 'Two Columns (Wide Left)',
            'two-col-wide-right': 'Two Columns (Wide Right)',
            'three-col': 'Three Columns', 'sidebar-left': 'Sidebar Left',
            'sidebar-right': 'Sidebar Right', 'two-row': 'Two Rows (Stacked)',
        };

        Object.entries(variants).forEach(([val, label]) => {
            const opt = document.createElement('option');
            opt.value = val; opt.textContent = label;
            if (val === (slot.layout || 'full')) opt.selected = true;
            variantSelect.appendChild(opt);
        });

        variantSelect.addEventListener('change', () => {
            slot.layout = variantSelect.value;
            persist();
        });

        // Gap select
        const gapLabel = document.createElement('label');
        gapLabel.className = 'label'; gapLabel.textContent = 'Gap';
        const nestedGapSel = buildSpacingSelect(slot.gap || 'sm', val => { slot.gap = val; persist(); });

        // Nested slots
        if (!Array.isArray(slot.slots)) slot.slots = [];
        const nestedSlotList = document.createElement('div');
        nestedSlotList.className = 'layout_slot_list layout_slot_list_nested';

        renderSlots(slot.slots, nestedSlotList, depth + 1);

        const addNestedBtn = document.createElement('button');
        addNestedBtn.type = 'button';
        addNestedBtn.className = 'button is-small is-light mt-2';
        addNestedBtn.textContent = '+ Add Slot';
        addNestedBtn.addEventListener('click', () => {
            slot.slots.push({ type: 'rich', html: '' });
            persist();
            renderSlots(slot.slots, nestedSlotList, depth + 1);
        });

        wrap.append(variantLabel, variantSelect, gapLabel, nestedGapSel, nestedSlotList, addNestedBtn);
        return wrap;
    }

    // ── Utility: spacing select ───────────────────────────────────────────────

    function buildSpacingSelect(currentValue, onChange) {
        const wrap = document.createElement('div');
        wrap.className = 'select';
        const sel = document.createElement('select');
        ['none', 'xs', 'sm', 'md', 'lg', 'xl'].forEach(token => {
            const opt = document.createElement('option');
            opt.value = token;
            opt.textContent = token.toUpperCase();
            if (token === currentValue) opt.selected = true;
            sel.appendChild(opt);
        });
        sel.addEventListener('change', () => onChange(sel.value));
        wrap.appendChild(sel);
        return wrap;
    }

    // ── Add slot button ───────────────────────────────────────────────────────

    addSlotBtn.addEventListener('click', () => {
        state.slots.push({ type: 'rich', html: '' });
        persist();
        renderSlots(state.slots, slotList, 0);
    });

    // ── Initial render ────────────────────────────────────────────────────────

    renderSlots(state.slots, slotList, 0);

    // Ensure hidden input is seeded even before any interaction
    persist();

})();