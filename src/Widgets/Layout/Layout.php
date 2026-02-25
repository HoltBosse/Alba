<?php
namespace HoltBosse\Alba\Widgets\Layout;

use HoltBosse\Alba\Core\{Widget, CMS};
use HoltBosse\DB\DB;
use HoltBosse\Form\Input;
use Respect\Validation\Validator as v;
use \stdClass;

class Layout extends Widget
{
    // ─── Layout variant definitions ───────────────────────────────────────────
    // These drive both the admin UI picker and the data-layout attribute on
    // the frontend wrapper. CSS in your template stylesheet handles the rest.
    public const VARIANTS = [
        'full'               => 'Full Width',
        'two-col'            => 'Two Columns (Equal)',
        'two-col-wide-left'  => 'Two Columns (Wide Left)',
        'two-col-wide-right' => 'Two Columns (Wide Right)',
        'three-col'          => 'Three Columns',
        'sidebar-left'       => 'Sidebar Left',
        'sidebar-right'      => 'Sidebar Right',
        'two-row'            => 'Two Rows (Stacked)',
    ];

    // ─── Spacing tokens ───────────────────────────────────────────────────────
    // Map to CSS custom properties / data-attributes in template CSS.
    public const SPACING = ['none', 'xs', 'sm', 'md', 'lg', 'xl'];

    // ─── Slot types ───────────────────────────────────────────────────────────
    public const SLOT_TYPES = [
        'rich'   => 'Rich Text',
        'widget' => 'Widget',
        'image'  => 'Image',
        'layout' => 'Nested Layout',
    ];

    // Max nesting depth enforced in the admin UI only (not in render).
    public const MAX_ADMIN_DEPTH = 3;


    // =========================================================================
    // ADMIN INTERFACE
    // =========================================================================

    public function hasCustomBackend(): bool
    {
        return true;
    }

    /**
     * Rendered inside the standard widget edit form by the edit view.
     * Outputs the layout picker and the slot manager, plus the hidden
     * input that carries the full JSON config on submit.
     */
    public function render_custom_backend(): void
    {
        // Decode existing config (if editing an existing widget)
        $config = $this->getLayoutConfig();

        $currentVariant = $config->layout  ?? 'full';
        $currentGap     = $config->gap     ?? 'md';
        $currentPadding = $config->padding ?? 'none';
        $currentSlots   = $config->slots   ?? [];

        // Build a JSON-safe representation to seed the JS state
        $configJson = htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8');

        ?>
        <style>
            <?php echo file_get_contents(__DIR__ . '/backend_style.css'); ?>
        </style>

        <div id="layout_widget_builder" data-config="<?php echo $configJson; ?>">

            <!-- ── Layout variant picker ───────────────────────────────── -->
            <section class="layout_section">
                <h3 class="title is-6">Layout Variant</h3>
                <div class="layout_variant_grid">
                    <?php foreach (self::VARIANTS as $key => $label): ?>
                        <button type="button"
                                class="layout_variant_btn <?php echo $key === $currentVariant ? 'is-active' : ''; ?>"
                                data-variant="<?php echo $key; ?>">
                            <span class="variant_icon variant_icon_<?php echo str_replace('-', '_', $key); ?>"></span>
                            <span class="variant_label"><?php echo $label; ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- ── Spacing tokens ──────────────────────────────────────── -->
            <section class="layout_section">
                <h3 class="title is-6">Spacing</h3>
                <div class="layout_spacing_row">
                    <label class="label">Gap between slots</label>
                    <div class="select">
                        <select id="layout_gap_select">
                            <?php foreach (self::SPACING as $token): ?>
                                <option value="<?php echo $token; ?>"
                                    <?php echo $token === $currentGap ? 'selected' : ''; ?>>
                                    <?php echo strtoupper($token); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <label class="label" style="margin-left:2rem;">Container padding</label>
                    <div class="select">
                        <select id="layout_padding_select">
                            <?php foreach (self::SPACING as $token): ?>
                                <option value="<?php echo $token; ?>"
                                    <?php echo $token === $currentPadding ? 'selected' : ''; ?>>
                                    <?php echo strtoupper($token); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </section>

            <!-- ── Slot manager ────────────────────────────────────────── -->
            <section class="layout_section">
                <h3 class="title is-6">Slots</h3>
                <div id="layout_slot_list" class="layout_slot_list">
                    <!-- Slots are rendered by JS from the config JSON -->
                </div>
                <button type="button" id="layout_add_slot" class="button is-small is-light mt-2">
                    + Add Slot
                </button>
            </section>

            <!-- ── Available widgets for the widget-type slot picker ───── -->
            <div id="layout_widget_registry" style="display:none;"
                 data-widgets="<?php echo htmlspecialchars($this->getAvailableWidgetsJson(), ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <!-- ── Hidden input — carries JSON config to custom_save() ─── -->
            <input type="hidden"
                   name="layout_config"
                   id="layout_config_input"
                   value="<?php echo $configJson; ?>">
        </div>

        <script>
            <?php echo file_get_contents(__DIR__ . '/backend_script.js'); ?>
        </script>
        <?php
    }

    /**
     * Called by the base Widget::save() after it has processed the standard
     * form fields. We pull our JSON blob from POST and append it to
     * $this->options in the same {name, value} shape the rest of the CMS uses.
     */
    public function custom_save(): void
    {
        $layoutConfigJson = Input::getvar('layout_config', v::stringVal(), '{}');

        // Basic sanity-check: ensure it's decodable JSON before storing
        $decoded = json_decode($layoutConfigJson);
        if (json_last_error() !== JSON_ERROR_NONE || !is_object($decoded)) {
            $layoutConfigJson = '{}';
        }

        $obj        = new stdClass();
        $obj->name  = 'layout_config';
        $obj->value = $layoutConfigJson;

        // Append to the options array that base Widget::save() has already
        // populated from the standard widget_options_form fields (empty here,
        // since widget_config.json declares no fields).
        $this->options[] = $obj;
    }


    // =========================================================================
    // FRONTEND RENDERING
    // =========================================================================

    /**
     * Main render entry point — called by internal_render() in the base class.
     */
    public function render(): void
    {
        $config = $this->getLayoutConfig();
        $this->renderLayoutBlock($config);
    }

    /**
     * Recursive layout renderer. Accepts either the top-level config object
     * or an inline nested layout slot object — both share the same shape.
     */
    private function renderLayoutBlock(object $config, int $depth = 0): void
    {
        $variant = htmlspecialchars($config->layout  ?? 'full',   ENT_QUOTES, 'UTF-8');
        $gap     = htmlspecialchars($config->gap     ?? 'md',     ENT_QUOTES, 'UTF-8');
        $padding = htmlspecialchars($config->padding ?? 'none',   ENT_QUOTES, 'UTF-8');
        $slots   = $config->slots ?? [];

        echo "<div class=\"layout-block\""
           . " data-layout=\"{$variant}\""
           . " data-gap=\"{$gap}\""
           . " data-padding=\"{$padding}\""
           . " data-depth=\"{$depth}\">";

        echo "<div class=\"layout-slots\">";

        foreach ($slots as $slot) {
            echo "<div class=\"layout-slot\">";
            $this->renderSlot($slot, $depth);
            echo "</div>";
        }

        echo "</div>"; // .layout-slots
        echo "</div>"; // .layout-block
    }

    /**
     * Dispatches a single slot to the appropriate renderer based on its type.
     */
    private function renderSlot(object $slot, int $depth): void
    {
        $type = $slot->type ?? 'rich';

        match ($type) {
            'rich'   => $this->renderRichSlot($slot),
            'widget' => $this->renderWidgetSlot($slot),
            'image'  => $this->renderImageSlot($slot),
            'layout' => $this->renderNestedLayoutSlot($slot, $depth),
            default  => null,
        };
    }

    /**
     * Rich text slot — the HTML was sanitised at save time by Tiptap/server.
     * Output directly; no further processing needed.
     */
    private function renderRichSlot(object $slot): void
    {
        $html = $slot->html ?? '';
        echo "<div class=\"layout-slot-rich\">{$html}</div>";
    }

    /**
     * Widget slot — resolves the widget by ID and runs its full render lifecycle,
     * including any on_widget_render hooks registered in the system.
     */
    private function renderWidgetSlot(object $slot): void
    {
        $widgetId = (int) ($slot->widget_id ?? 0);
        if ($widgetId <= 0) {
            return;
        }

        $widgetRow = DB::fetch('SELECT * FROM widgets WHERE id=? AND state>0', $widgetId);
        if (!$widgetRow) {
            // Widget not found or unpublished — render nothing on the frontend.
            return;
        }

        $typeInfo       = Widget::get_widget_type($widgetRow->type);
        $widgetClass    = Widget::getWidgetClass($typeInfo->location);

        if (!$widgetClass || !class_exists($widgetClass)) {
            return;
        }

        /** @var Widget $instance */
        $instance = new $widgetClass();
        $instance->load($widgetId);

        echo "<div class=\"layout-slot-widget\">";
        $instance->internal_render();
        echo "</div>";
    }

    /**
     * Image slot — resolves the media record and outputs a semantic figure.
     * Relies on the existing media table structure used elsewhere in Alba.
     */
    private function renderImageSlot(object $slot): void
    {
        $imageId = (int) ($slot->image_id ?? 0);
        if ($imageId <= 0) {
            return;
        }

        $media = DB::fetch('SELECT * FROM media WHERE id=? AND state>=0', $imageId);
        if (!$media) {
            return;
        }

        $alt     = htmlspecialchars($slot->alt ?? $media->alt ?? '', ENT_QUOTES, 'UTF-8');
        $caption = htmlspecialchars($slot->caption ?? $media->title ?? '', ENT_QUOTES, 'UTF-8');
        $src     = htmlspecialchars($_ENV['uripath'] . '/image/' . $imageId . '/web', ENT_QUOTES, 'UTF-8');

        echo "<figure class=\"layout-slot-image\">";
        echo "<img src=\"{$src}\" alt=\"{$alt}\">";
        if ($caption) {
            echo "<figcaption>{$caption}</figcaption>";
        }
        echo "</figure>";
    }

    /**
     * Nested layout slot — recurse into renderLayoutBlock() with the
     * inline slot config. No database lookup required; the full nested
     * config is stored inline in the parent's JSON blob.
     */
    private function renderNestedLayoutSlot(object $slot, int $depth): void
    {
        // The slot IS the nested layout config — it has the same shape:
        // { type, layout, gap, padding, slots[] }
        $this->renderLayoutBlock($slot, $depth + 1);
    }


    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Retrieves and decodes the layout config from $this->options.
     * Returns a safe default object if nothing is stored yet.
     */
    private function getLayoutConfig(): object
    {
        $json = null;

        // $this->options is an array of {name, value} stdClass objects
        // as decoded by Widget::load() from the widgets.options JSON column.
        if (is_array($this->options)) {
            foreach ($this->options as $option) {
                if (isset($option->name) && $option->name === 'layout_config') {
                    $json = $option->value;
                    break;
                }
            }
        }

        if ($json) {
            $decoded = json_decode($json);
            if (json_last_error() === JSON_ERROR_NONE && is_object($decoded)) {
                return $decoded;
            }
        }

        // Return a safe default for a brand-new widget
        return (object)[
            'layout'  => 'full',
            'gap'     => 'md',
            'padding' => 'none',
            'slots'   => [],
        ];
    }

    /**
     * Returns a JSON string of all published widgets suitable for seeding
     * the admin widget-picker UI. Includes id, title, and widget type name.
     */
    private function getAvailableWidgetsJson(): string
    {
        $widgets = DB::fetchAll(
            'SELECT w.id, w.title, wt.title AS type_title
             FROM widgets w
             JOIN widget_types wt ON wt.id = w.type
             WHERE w.state >= 0
             AND w.id != ?
             ORDER BY wt.title ASC, w.title ASC',
            [$this->id ?? 0]
        );

        return json_encode($widgets ?? []);
    }

    /**
     * Used by loadFromSlot() to initialise a nested Layout instance from an
     * inline slot config object rather than from the database. This allows
     * renderNestedLayoutSlot() to call renderLayoutBlock() without a DB lookup.
     *
     * Not needed for the top-level widget — only for recursion.
     */
    public function loadFromSlot(object $slotConfig): void
    {
        // Wrap the slot config in the options array format the rest of the
        // class expects, so getLayoutConfig() can find it normally.
        $wrapper        = new stdClass();
        $wrapper->name  = 'layout_config';
        $wrapper->value = json_encode($slotConfig);

        $this->options = [$wrapper];
    }
}