<?php
/**
 * Plugin Name: Nuvex Capital Challenge Card
 * Description: Fully admin-configurable trading challenge card. Use shortcode [challenge_card].
 * Version:     1.0.0
 * Author:      Trading Tech
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Nuvex_Challenge_Card {

    const OPTION_KEY = 'nuvex_challenge_settings';
    const NONCE      = 'nuvex_save_nonce';

    public function __construct() {
        add_action( 'admin_menu',                   [ $this, 'register_menu'       ] );
        add_action( 'admin_enqueue_scripts',         [ $this, 'enqueue_media'       ] );
        add_action( 'admin_post_nuvex_save',         [ $this, 'handle_save'         ] );
        add_action( 'admin_post_nuvex_save_tab',     [ $this, 'handle_save_tab'     ] );
        add_action( 'wp_head',                       [ $this, 'frontend_styles'     ] );
        add_shortcode( 'challenge_card',             [ $this, 'shortcode'           ] );
    }

    /* ═══════════════════════════════════════════════════════════════
       ENQUEUE MEDIA UPLOADER
    ═══════════════════════════════════════════════════════════════ */

    public function enqueue_media( $hook ): void {
        if ( strpos( $hook, 'nuvex-capital' ) === false ) return;
        wp_enqueue_media();
    }

    /* ═══════════════════════════════════════════════════════════════
       DEFAULTS
    ═══════════════════════════════════════════════════════════════ */

    private function defaults(): array {
        return [
            'general' => [
                'accent_color' => '#7c3aed',
                'btn_text'     => 'Buy Challenge',
                'currency'     => '€',
                'payments'     => [
                    [ 'label' => 'Visa',       'icon_type' => 'emoji', 'icon_value' => '💳', 'display_mode' => 'both' ],
                    [ 'label' => 'Mastercard', 'icon_type' => 'emoji', 'icon_value' => '💳', 'display_mode' => 'both' ],
                    [ 'label' => 'Crypto',     'icon_type' => 'emoji', 'icon_value' => '🪙', 'display_mode' => 'both' ],
                ],
            ],
            '1step' => [
                'label'      => '1 STEP',
                'active'     => '1',
                'col_phase1' => 'Phase 1',
                'col_funded' => 'Funded',
                'has_phase2' => '0',   // 1-step: only Phase 1 + Funded
                'sizes'      => [
                    [ 'label' => '$7.5k',   'price' => '199',  'discount' => '0',   'link' => '#', 'active' => '1', 'featured' => '0', 'badge' => ''           ],
                    [ 'label' => '$10k',    'price' => '199',  'discount' => '89',  'link' => '#', 'active' => '1', 'featured' => '0', 'badge' => ''           ],
                    [ 'label' => '$25k',    'price' => '299',  'discount' => '138', 'link' => '#', 'active' => '1', 'featured' => '0', 'badge' => ''           ],
                    [ 'label' => '$50k',    'price' => '499',  'discount' => '329', 'link' => '#', 'active' => '1', 'featured' => '1', 'badge' => 'Best Value' ],
                    [ 'label' => '$100k',   'price' => '949',  'discount' => '497', 'link' => '#', 'active' => '1', 'featured' => '0', 'badge' => ''           ],
                    [ 'label' => '$200k',   'price' => '1449', 'discount' => '939', 'link' => '#', 'active' => '1', 'featured' => '0', 'badge' => ''           ],
                ],
                'metrics' => [
                    [ 'icon_type' => 'emoji', 'icon_value' => '⚡', 'label' => 'Target',            'phase1' => '10%',           'funded' => '/'            ],
                    [ 'icon_type' => 'emoji', 'icon_value' => '📉', 'label' => 'Daily Drawdown',    'phase1' => '5%',            'funded' => '5%'           ],
                    [ 'icon_type' => 'emoji', 'icon_value' => '📊', 'label' => 'Maximum Drawdown',  'phase1' => '10%',           'funded' => '10%'          ],
                    [ 'icon_type' => 'emoji', 'icon_value' => '⏱',  'label' => 'Trading Period',    'phase1' => 'No limit',      'funded' => 'No limit'     ],
                    [ 'icon_type' => 'emoji', 'icon_value' => '📅', 'label' => 'Min. Trading Days', 'phase1' => '4',             'funded' => '4'            ],
                    [ 'icon_type' => 'emoji', 'icon_value' => '🔄', 'label' => 'Refundable',        'phase1' => 'Yes',           'funded' => '/'            ],
                ],
            ],
            '2step' => [
                'label'      => '2 STEP',
                'active'     => '1',
                'col_phase1' => 'Phase 1',
                'col_phase2' => 'Phase 2',
                'col_funded' => 'Funded',
                'has_phase2' => '1',   // 2-step: Phase 1 + Phase 2 + Funded
                'sizes'      => [
                    [ 'label' => '5K',   'price' => '39',  'discount' => '29',  'link' => '#', 'active' => '1', 'featured' => '0', 'badge' => ''           ],
                    [ 'label' => '10K',  'price' => '59',  'discount' => '0',   'link' => '#', 'active' => '1', 'featured' => '0', 'badge' => ''           ],
                    [ 'label' => '50K',  'price' => '149', 'discount' => '0',   'link' => '#', 'active' => '1', 'featured' => '0', 'badge' => ''           ],
                    [ 'label' => '100K', 'price' => '229', 'discount' => '199', 'link' => '#', 'active' => '1', 'featured' => '1', 'badge' => 'Best Value' ],
                    [ 'label' => '150K', 'price' => '299', 'discount' => '0',   'link' => '#', 'active' => '1', 'featured' => '0', 'badge' => ''           ],
                    [ 'label' => '200K', 'price' => '399', 'discount' => '0',   'link' => '#', 'active' => '1', 'featured' => '0', 'badge' => ''           ],
                ],
                'metrics' => [
                    [ 'icon_type' => 'emoji', 'icon_value' => '⚡', 'label' => 'Target',            'phase1' => '8%',            'phase2' => '5%',            'funded' => '-'            ],
                    [ 'icon_type' => 'emoji', 'icon_value' => '📉', 'label' => 'Daily Drawdown',    'phase1' => '5%',            'phase2' => '5%',            'funded' => '5%'           ],
                    [ 'icon_type' => 'emoji', 'icon_value' => '📊', 'label' => 'Maximum Drawdown',  'phase1' => '10%',           'phase2' => '10%',           'funded' => '10%'          ],
                    [ 'icon_type' => 'emoji', 'icon_value' => '⏱',  'label' => 'Trading Period',    'phase1' => 'No time limit', 'phase2' => 'No time limit', 'funded' => 'No time limit'],
                    [ 'icon_type' => 'emoji', 'icon_value' => '📅', 'label' => 'Min. Trading Days', 'phase1' => '3',             'phase2' => '3',             'funded' => '3'            ],
                    [ 'icon_type' => 'emoji', 'icon_value' => '🔄', 'label' => 'Refundable Fee',    'phase1' => 'Yes',           'phase2' => '-',             'funded' => '-'            ],
                ],
            ],
        ];
    }

    private function get_settings(): array {
        $saved = get_option( self::OPTION_KEY, [] );
        return ! empty( $saved ) ? $saved : $this->defaults();
    }

    /* ═══════════════════════════════════════════════════════════════
       SAVE HANDLER — full save (all tabs)
    ═══════════════════════════════════════════════════════════════ */

    public function handle_save(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( self::NONCE );

        $raw      = $_POST['nuvex'] ?? [];
        $existing = $this->get_settings();
        $data     = $this->sanitize_all( $raw, $existing );

        update_option( self::OPTION_KEY, $data );
        wp_redirect( add_query_arg( [ 'page' => 'nuvex-capital', 'saved' => 'all' ], admin_url( 'admin.php' ) ) );
        exit;
    }

    /* ═══════════════════════════════════════════════════════════════
       SAVE HANDLER — per-tab save (AJAX-like via admin-post)
    ═══════════════════════════════════════════════════════════════ */

    public function handle_save_tab(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( self::NONCE );

        $tab      = sanitize_key( $_POST['nuvex_tab'] ?? '' );
        $raw      = $_POST['nuvex'] ?? [];
        $existing = $this->get_settings();

        if ( $tab === 'general' ) {
            $existing['general'] = $this->sanitize_general( $raw['general'] ?? [] );
        } elseif ( in_array( $tab, [ '1step', '2step' ], true ) ) {
            $existing[ $tab ] = $this->sanitize_step( $tab, $raw[ $tab ] ?? [] );
        }

        update_option( self::OPTION_KEY, $existing );
        wp_redirect( add_query_arg( [ 'page' => 'nuvex-capital', 'saved' => $tab, 'tab' => $tab ], admin_url( 'admin.php' ) ) );
        exit;
    }

    /* ─── sanitize helpers ─── */

    private function sanitize_all( array $raw, array $existing ): array {
        $data              = $existing;
        $data['general']   = $this->sanitize_general( $raw['general'] ?? [] );
        $data['1step']     = $this->sanitize_step( '1step', $raw['1step'] ?? [] );
        $data['2step']     = $this->sanitize_step( '2step', $raw['2step'] ?? [] );
        return $data;
    }

    private function sanitize_general( array $gen ): array {
        $out = [
            'accent_color' => sanitize_hex_color( $gen['accent_color'] ?? '#7c3aed' ) ?: '#7c3aed',
            'btn_text'     => sanitize_text_field( $gen['btn_text'] ?? 'Buy Challenge' ),
            'currency'     => in_array( $gen['currency'] ?? '€', [ '€', '$' ], true ) ? $gen['currency'] : '€',
            'payments'     => [],
        ];
        foreach ( $gen['payments'] ?? [] as $p ) {
            $label = sanitize_text_field( $p['label'] ?? '' );
            $icon_value = $this->sanitize_icon_value( $p['icon_type'] ?? 'emoji', $p['icon_value'] ?? '' );
            $display_mode = in_array( $p['display_mode'] ?? 'both', [ 'both', 'icon_only', 'text_only' ], true ) ? $p['display_mode'] : 'both';
            
            // Allow saving if label is not empty OR icon_value is not empty (for icon_only mode)
            if ( $label !== '' || ( $display_mode === 'icon_only' && $icon_value !== '' ) ) {
                $out['payments'][] = [
                    'label'        => $label,
                    'icon_type'    => in_array( $p['icon_type'] ?? 'emoji', [ 'emoji', 'url', 'upload' ], true ) ? $p['icon_type'] : 'emoji',
                    'icon_value'   => $icon_value,
                    'display_mode' => $display_mode,
                ];
            }
        }
        return $out;
    }

    private function sanitize_step( string $key, array $s ): array {
        $has_p2 = ( $key === '2step' ) ? '1' : '0';
        $out = [
            'label'      => sanitize_text_field( $s['label']      ?? '' ),
            'active'     => isset( $s['active'] )    ? '1' : '0',
            'has_phase2' => $has_p2,
            'col_phase1' => sanitize_text_field( $s['col_phase1'] ?? 'Phase 1' ),
            'col_funded' => sanitize_text_field( $s['col_funded'] ?? 'Funded'  ),
            'sizes'      => [],
            'metrics'    => [],
        ];
        if ( $has_p2 === '1' ) {
            $out['col_phase2'] = sanitize_text_field( $s['col_phase2'] ?? 'Phase 2' );
        }
        foreach ( $s['sizes'] ?? [] as $sz ) {
            $link = $sz['link'] ?? '#';
            // Allow # as placeholder
            if ( $link !== '#' ) {
                $link = esc_url_raw( $link );
            }
            $out['sizes'][] = [
                'active'   => isset( $sz['active']   ) ? '1' : '0',
                'label'    => sanitize_text_field( $sz['label']    ?? '' ),
                'price'    => sanitize_text_field( $sz['price']    ?? '0' ),
                'discount' => sanitize_text_field( $sz['discount'] ?? '0' ),
                'link'     => $link,
                'featured' => isset( $sz['featured'] ) ? '1' : '0',
                'badge'    => sanitize_text_field( $sz['badge']    ?? '' ),
            ];
        }
        foreach ( $s['metrics'] ?? [] as $m ) {
            $row = [
                'icon_type'  => in_array( $m['icon_type'] ?? 'emoji', [ 'emoji', 'url', 'upload' ], true ) ? $m['icon_type'] : 'emoji',
                'icon_value' => $this->sanitize_icon_value( $m['icon_type'] ?? 'emoji', $m['icon_value'] ?? '' ),
                'label'      => sanitize_text_field( $m['label']  ?? '' ),
                'phase1'     => sanitize_text_field( $m['phase1'] ?? '' ),
                'funded'     => sanitize_text_field( $m['funded'] ?? '' ),
            ];
            if ( $has_p2 === '1' ) {
                $row['phase2'] = sanitize_text_field( $m['phase2'] ?? '' );
            }
            $out['metrics'][] = $row;
        }
        return $out;
    }

    private function sanitize_icon_value( string $type, string $value ): string {
        if ( $type === 'url' || $type === 'upload' ) {
            return esc_url_raw( $value );
        }
        return sanitize_text_field( $value );
    }

    /* ═══════════════════════════════════════════════════════════════
       ADMIN MENU
    ═══════════════════════════════════════════════════════════════ */

    public function register_menu(): void {
        add_menu_page(
            'Nuvex Capital',
            'Nuvex Capital',
            'manage_options',
            'nuvex-capital',
            [ $this, 'admin_page' ],
            'dashicons-chart-bar',
            25
        );
    }

    /* ═══════════════════════════════════════════════════════════════
       ADMIN PAGE
    ═══════════════════════════════════════════════════════════════ */

    public function admin_page(): void {
        $s          = $this->get_settings();
        $gen        = $s['general'];
        $saved_tab  = sanitize_key( $_GET['saved'] ?? '' );
        $active_tab = sanitize_key( $_GET['tab']   ?? 'general' );
        ?>
<div class="wrap ncc-admin">

    <h1 style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
        <span style="font-size:26px">📊</span>
        Nuvex Capital — Challenge Settings
    </h1>
    <p style="color:#666;margin-top:0">Use shortcode <code>[challenge_card]</code> to embed the card anywhere.</p>

    <?php if ( $saved_tab === 'all' ) : ?>
    <div class="notice notice-success is-dismissible" id="ncc-save-notice">
        <p><strong>✅ All settings saved successfully.</strong></p>
    </div>
    <?php elseif ( in_array( $saved_tab, [ 'general', '1step', '2step' ], true ) ) :
        $tab_labels = [ 'general' => 'General', '1step' => '1 STEP', '2step' => '2 STEP' ];
    ?>
    <div class="notice notice-success is-dismissible" id="ncc-save-notice">
        <p><strong>✅ <?php echo esc_html( $tab_labels[ $saved_tab ] ?? $saved_tab ); ?> settings saved
                successfully.</strong></p>
    </div>
    <?php endif; ?>

    <!-- Tab navigation -->
    <nav class="nav-tab-wrapper" style="margin-top:18px" id="ncc-tabs-nav">
        <a href="#ncc-tab-general"
            class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?> ncc-tab-link"
            data-tab="general">⚙️ General</a>
        <a href="#ncc-tab-1step"
            class="nav-tab <?php echo $active_tab === '1step'   ? 'nav-tab-active' : ''; ?> ncc-tab-link"
            data-tab="1step">1 STEP</a>
        <a href="#ncc-tab-2step"
            class="nav-tab <?php echo $active_tab === '2step'   ? 'nav-tab-active' : ''; ?> ncc-tab-link"
            data-tab="2step">2 STEP</a>
    </nav>

    <!-- ── GENERAL TAB ─────────────────────────────── -->
    <div id="ncc-tab-general" class="ncc-tab <?php echo $active_tab === 'general' ? 'active' : ''; ?>">
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( self::NONCE ); ?>
            <input type="hidden" name="action" value="nuvex_save_tab">
            <input type="hidden" name="nuvex_tab" value="general">

            <h2>General Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label>Accent Color</label></th>
                    <td style="display:flex;align-items:center;gap:10px;padding-top:14px">
                        <input type="color" id="ncc-color-picker" name="nuvex[general][accent_color]"
                            value="<?php echo esc_attr( $gen['accent_color'] ); ?>"
                            style="height:38px;width:60px;cursor:pointer;border:1px solid #ddd;border-radius:6px;padding:2px">
                        <span id="ncc-color-hex"
                            style="font-family:monospace;font-size:13px;color:#555"><?php echo esc_html( $gen['accent_color'] ); ?></span>
                        <p class="description" style="margin:0">Applied to buttons, active states, and accents on the
                            card.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ncc-btn-text">Buy Button Text</label></th>
                    <td>
                        <input type="text" id="ncc-btn-text" name="nuvex[general][btn_text]"
                            value="<?php echo esc_attr( $gen['btn_text'] ); ?>" style="width:220px">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ncc-currency">Currency Symbol</label></th>
                    <td>
                        <select id="ncc-currency" name="nuvex[general][currency]" style="width:100px">
                            <option value="€" <?php selected( $gen['currency'] ?? '€', '€' ); ?>>€ (Euro)</option>
                            <option value="$" <?php selected( $gen['currency'] ?? '€', '$' ); ?>>$ (Dollar)</option>
                        </select>
                        <p class="description">Currency symbol displayed with prices</p>
                    </td>
                </tr>
            </table>

            <h3>Payment Method Labels</h3>
            <p class="description">Each payment method can show an icon, label, or both. Upload an image/SVG or enter a
                URL. Icon sizing depends on display mode: "Icon Only" displays larger icons (45×30px for images, 32px
                for emoji), while "Icon + Text" uses balanced sizing (32×20px for images, 20px for emoji).</p>
            <table class="wp-list-table widefat striped" style="max-width:800px;margin-top:8px">
                <thead>
                    <tr>
                        <th style="width:220px">Icon (emoji / URL / upload)</th>
                        <th style="width:120px">Display</th>
                        <th>Label</th>
                        <th style="width:80px">Remove</th>
                    </tr>
                </thead>
                <tbody id="ncc-payments-body">
                    <?php foreach ( $gen['payments'] as $i => $p ) :
                        $this->render_icon_row( 'general', 'payments', $i, $p );
                    endforeach; ?>
                </tbody>
            </table>
            <button type="button" class="button" id="ncc-add-payment" style="margin-top:8px">+ Add Payment
                Method</button>

            <div
                style="margin-top:22px;padding-top:16px;border-top:1px solid #ddd;display:flex;align-items:center;gap:14px">
                <?php submit_button( 'Save General Settings', 'primary', 'submit', false ); ?>
                <span class="ncc-inline-saving" style="display:none;color:#2271b1;font-size:13px">⏳ Saving…</span>
            </div>
        </form>
    </div>

    <!-- ── STEP TABS ────────────────────────────────── -->
    <?php foreach ( [ '1step', '2step' ] as $step_key ) :
        $step    = $s[ $step_key ];
        $has_p2  = ( $step_key === '2step' );
        $tab_cls = ( $active_tab === $step_key ) ? 'active' : '';
    ?>
    <div id="ncc-tab-<?php echo $step_key; ?>" class="ncc-tab <?php echo $tab_cls; ?>">
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( self::NONCE ); ?>
            <input type="hidden" name="action" value="nuvex_save_tab">
            <input type="hidden" name="nuvex_tab" value="<?php echo esc_attr( $step_key ); ?>">

            <h2><?php echo esc_html( $step['label'] ); ?> Settings</h2>

            <table class="form-table">
                <tr>
                    <th scope="row">Step Label</th>
                    <td>
                        <input type="text" name="nuvex[<?php echo $step_key; ?>][label]"
                            value="<?php echo esc_attr( $step['label'] ); ?>" style="width:160px">
                    </td>
                </tr>
                <tr>
                    <th scope="row">Visibility</th>
                    <td>
                        <label style="display:flex;align-items:center;gap:6px">
                            <input type="checkbox" name="nuvex[<?php echo $step_key; ?>][active]" value="1"
                                <?php checked( $step['active'], '1' ); ?>>
                            Show this step on the card
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Column Headers</th>
                    <td style="display:flex;gap:16px;flex-wrap:wrap;padding-top:14px">
                        <label>Phase 1 Header<br>
                            <input type="text" name="nuvex[<?php echo $step_key; ?>][col_phase1]"
                                value="<?php echo esc_attr( $step['col_phase1'] ); ?>" style="width:130px">
                        </label>
                        <?php if ( $has_p2 ) : ?>
                        <label>Phase 2 Header<br>
                            <input type="text" name="nuvex[<?php echo $step_key; ?>][col_phase2]"
                                value="<?php echo esc_attr( $step['col_phase2'] ?? 'Phase 2' ); ?>" style="width:130px">
                        </label>
                        <?php endif; ?>
                        <label>Funded Column Header<br>
                            <input type="text" name="nuvex[<?php echo $step_key; ?>][col_funded]"
                                value="<?php echo esc_attr( $step['col_funded'] ); ?>" style="width:130px">
                        </label>
                    </td>
                </tr>
            </table>

            <!-- Account Sizes -->
            <h3>Account Sizes</h3>
            <p class="description">Each size becomes a button on the card. Unchecked rows are hidden but not deleted.
                Checkout Link can be a full URL (https://...) or the placeholder # for internal linking.
            </p>
            <div style="overflow-x:auto;margin-top:8px">
                <table class="wp-list-table widefat striped ncc-size-table">
                    <thead>
                        <tr>
                            <th style="width:60px">Active</th>
                            <th style="width:80px">Size Label</th>
                            <th style="width:100px">Price</th>
                            <th style="width:130px">Discount Price<br><small style="font-weight:400">0 = no discount
                                    shown</small></th>
                            <th>Checkout Link (https://... or #)</th>
                            <th style="width:70px">Featured</th>
                            <th style="width:110px">Badge Text</th>
                            <th style="width:70px"></th>
                        </tr>
                    </thead>
                    <tbody id="ncc-size-rows-<?php echo $step_key; ?>">
                        <?php foreach ( $step['sizes'] as $i => $sz ) :
                            $this->render_size_row( $step_key, $i, $sz );
                        endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button type="button" class="button ncc-add-size" data-step="<?php echo $step_key; ?>"
                style="margin-top:8px">
                + Add Account Size
            </button>

            <!-- Metrics -->
            <h3 style="margin-top:28px">Challenge Metrics Table</h3>
            <p class="description">
                Rows shown in the comparison table on the card. Icons can be an emoji, a URL to an image/SVG, or a media
                library upload — all rendered at a fixed <strong>24×24 px</strong> size. Phase 1, Phase 2, and Funded
                columns have distinct background colors for clarity.
            </p>
            <div style="overflow-x:auto;margin-top:8px">
                <table class="wp-list-table widefat striped">
                    <thead>
                        <tr>
                            <th style="width:240px">Icon (emoji / URL / upload)</th>
                            <th>Metric Label</th>
                            <th style="width:130px"><?php echo esc_html( $step['col_phase1'] ); ?></th>
                            <?php if ( $has_p2 ) : ?>
                            <th style="width:130px"><?php echo esc_html( $step['col_phase2'] ?? 'Phase 2' ); ?></th>
                            <?php endif; ?>
                            <th style="width:130px"><?php echo esc_html( $step['col_funded'] ); ?></th>
                            <th style="width:70px"></th>
                        </tr>
                    </thead>
                    <tbody id="ncc-metric-rows-<?php echo $step_key; ?>">
                        <?php foreach ( $step['metrics'] as $i => $m ) :
                            $this->render_metric_row( $step_key, $i, $m, $has_p2 );
                        endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button type="button" class="button ncc-add-metric" data-step="<?php echo $step_key; ?>"
                data-has-p2="<?php echo $has_p2 ? '1' : '0'; ?>" style="margin-top:8px">
                + Add Metric Row
            </button>

            <div
                style="margin-top:28px;padding-top:18px;border-top:1px solid #ddd;display:flex;align-items:center;gap:14px">
                <?php submit_button( 'Save ' . esc_html( $step['label'] ) . ' Settings', 'primary large', 'submit', false ); ?>
                <span class="ncc-inline-saving" style="display:none;color:#2271b1;font-size:13px">⏳ Saving…</span>
            </div>

        </form>
    </div>
    <?php endforeach; ?>

</div><!-- .ncc-admin -->

<style>
.ncc-admin .ncc-tab {
    display: none;
    padding: 20px 0 10px;
}

.ncc-admin .ncc-tab.active {
    display: block;
}

.ncc-admin .ncc-size-table td,
.ncc-admin .ncc-size-table th {
    vertical-align: middle;
}

.ncc-admin table input[type="text"],
.ncc-admin table input[type="url"],
.ncc-admin table input[type="number"] {
    width: 100%;
    box-sizing: border-box;
}

.ncc-admin table input[type="checkbox"] {
    width: auto;
}

/* Icon picker cell */
.ncc-icon-cell {
    min-width: 220px;
}

.ncc-icon-picker {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 8px;
    background: #fafafa;
}

.ncc-icon-type-btns {
    display: flex;
    gap: 4px;
    margin-bottom: 6px;
}

.ncc-icon-type-btn {
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 4px;
    border: 1px solid #ccc;
    background: #fff;
    cursor: pointer;
    color: #555;
    transition: all .15s;
}

.ncc-icon-type-btn.active {
    background: #2271b1;
    color: #fff;
    border-color: #2271b1;
}

.ncc-icon-value-wrap input {
    width: 100% !important;
    box-sizing: border-box;
}

.ncc-icon-preview {
    margin-top: 5px;
    display: flex;
    align-items: center;
    gap: 6px;
    min-height: 28px;
}

.ncc-icon-preview img {
    width: 24px !important;
    height: 24px !important;
    object-fit: contain;
    border: 1px solid #eee;
    border-radius: 3px;
}

.ncc-pay-icon-preview img {
    width: 32px !important;
    height: 20px !important;
    object-fit: contain;
    border: 1px solid #eee;
    border-radius: 3px;
}

.ncc-upload-btn {
    font-size: 11px !important;
    padding: 2px 7px !important;
}
</style>

<script>
(function() {
    /* ── Tab switching ── */
    document.querySelectorAll('.ncc-tab-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.ncc-tab-link').forEach(function(l) {
                l.classList.remove('nav-tab-active');
            });
            document.querySelectorAll('.ncc-tab').forEach(function(t) {
                t.classList.remove('active');
            });
            link.classList.add('nav-tab-active');
            var target = document.querySelector(link.getAttribute('href'));
            if (target) target.classList.add('active');
            // update URL so refresh keeps tab
            var url = new URL(window.location.href);
            url.searchParams.set('tab', link.dataset.tab);
            window.history.replaceState({}, '', url);
        });
    });

    /* ── Color picker sync ── */
    var picker = document.getElementById('ncc-color-picker');
    var hexLbl = document.getElementById('ncc-color-hex');
    if (picker && hexLbl) {
        picker.addEventListener('input', function() {
            hexLbl.textContent = picker.value;
        });
    }

    /* ── Remove row ── */
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('ncc-remove-row')) {
            e.target.closest('tr').remove();
        }
    });

    /* ── Icon picker logic (delegated) ── */
    document.addEventListener('click', function(e) {
        // type button toggle
        if (e.target.classList.contains('ncc-icon-type-btn')) {
            var btn = e.target;
            var cell = btn.closest('.ncc-icon-picker');
            var type = btn.dataset.type;
            cell.querySelectorAll('.ncc-icon-type-btn').forEach(function(b) {
                b.classList.remove('active');
            });
            btn.classList.add('active');
            cell.querySelector('.ncc-icon-type-input').value = type;
            // show/hide panels
            cell.querySelectorAll('.ncc-icon-panel').forEach(function(p) {
                p.style.display = 'none';
            });
            var panel = cell.querySelector('.ncc-icon-panel[data-panel="' + type + '"]');
            if (panel) panel.style.display = 'block';
            updatePreview(cell);
        }
        // media upload button
        if (e.target.classList.contains('ncc-upload-btn')) {
            var uploadBtn = e.target;
            var cell2 = uploadBtn.closest('.ncc-icon-picker');
            var urlInput = cell2.querySelector('.ncc-upload-url-input');
            var frame = wp.media({
                title: 'Select Icon Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false,
                library: {
                    type: ['image']
                }
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                urlInput.value = attachment.url;
                updatePreview(cell2);
            });
            frame.open();
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('ncc-icon-val-input') || e.target.classList.contains(
                'ncc-upload-url-input') || e.target.classList.contains('ncc-url-input')) {
            var cell = e.target.closest('.ncc-icon-picker');
            if (cell) updatePreview(cell);
        }
    });

    function updatePreview(cell) {
        var type = (cell.querySelector('.ncc-icon-type-input') || {}).value || 'emoji';
        var preview = cell.querySelector('.ncc-icon-preview, .ncc-pay-icon-preview');
        if (!preview) return;
        preview.innerHTML = '';

        if (type === 'emoji') {
            var val = (cell.querySelector('.ncc-icon-val-input') || {}).value || '';
            preview.innerHTML = '<span style="font-size:20px">' + val + '</span>';
        } else {
            var urlIn = cell.querySelector('.ncc-upload-url-input') || cell.querySelector('.ncc-url-input');
            var src = urlIn ? urlIn.value.trim() : '';
            if (src) {
                var img = document.createElement('img');
                img.src = src;
                img.alt = '';
                preview.appendChild(img);
            } else {
                preview.innerHTML = '<span style="color:#aaa;font-size:11px">No image set</span>';
            }
        }
    }

    /* ── Add payment method ── */
    var addPay = document.getElementById('ncc-add-payment');
    if (addPay) {
        addPay.addEventListener('click', function() {
            var tbody = document.getElementById('ncc-payments-body');
            var idx = tbody.querySelectorAll('tr').length;
            var tr = document.createElement('tr');
            tr.innerHTML = buildPaymentRow('general', idx);
            tbody.appendChild(tr);
        });
    }

    /* ── Add size row ── */
    document.querySelectorAll('.ncc-add-size').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var step = btn.dataset.step;
            var tbody = document.getElementById('ncc-size-rows-' + step);
            var idx = tbody.querySelectorAll('tr').length;
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td style="text-align:center"><input type="checkbox" name="nuvex[' + step +
                '][sizes][' + idx + '][active]" value="1" checked></td>' +
                '<td><input type="text" name="nuvex[' + step + '][sizes][' + idx +
                '][label]" placeholder="e.g. 250K"></td>' +
                '<td><input type="number" name="nuvex[' + step + '][sizes][' + idx +
                '][price]" value="0" min="0"></td>' +
                '<td><input type="number" name="nuvex[' + step + '][sizes][' + idx +
                '][discount]" value="0" min="0"></td>' +
                '<td><input type="text" name="nuvex[' + step + '][sizes][' + idx +
                '][link]" placeholder="https://... or #"></td>' +
                '<td style="text-align:center"><input type="checkbox" name="nuvex[' + step +
                '][sizes][' + idx + '][featured]" value="1"></td>' +
                '<td><input type="text" name="nuvex[' + step + '][sizes][' + idx +
                '][badge]" placeholder="e.g. Popular"></td>' +
                '<td><button type="button" class="button button-small ncc-remove-row">Remove</button></td>';
            tbody.appendChild(tr);
        });
    });

    /* ── Add metric row ── */
    document.querySelectorAll('.ncc-add-metric').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var step = btn.dataset.step;
            var hasP2 = btn.dataset.hasP2 === '1';
            var tbody = document.getElementById('ncc-metric-rows-' + step);
            var idx = tbody.querySelectorAll('tr').length;
            var tr = document.createElement('tr');
            tr.innerHTML = buildMetricRow(step, idx, hasP2);
            tbody.appendChild(tr);
        });
    });

    /* ── Row builder helpers ── */
    function iconPickerHtml(nameBase, type, value, isPayment) {
        type = type || 'emoji';
        value = value || '';
        var previewClass = isPayment ? 'ncc-pay-icon-preview' : 'ncc-icon-preview';
        var emojiActive = type === 'emoji' ? ' active' : '';
        var urlActive = type === 'url' ? ' active' : '';
        var upActive = type === 'upload' ? ' active' : '';
        var emojiDisplay = type === 'emoji' ? 'block' : 'none';
        var urlDisplay = (type === 'url' || type === 'upload') ? 'block' : 'none';
        var previewHtml = '';
        if (type === 'emoji') {
            previewHtml = '<span style="font-size:20px">' + value + '</span>';
        } else if (value) {
            var imgStyle = isPayment ? 'width:32px!important;height:20px!important' :
                'width:24px!important;height:24px!important';
            previewHtml = '<img src="' + value + '" style="' + imgStyle +
                ';object-fit:contain;border:1px solid #eee;border-radius:3px">';
        }
        return '<div class="ncc-icon-picker">' +
            '<div class="ncc-icon-type-btns">' +
            '<button type="button" class="ncc-icon-type-btn' + emojiActive + '" data-type="emoji">Emoji</button>' +
            '<button type="button" class="ncc-icon-type-btn' + urlActive + '"   data-type="url">URL</button>' +
            '<button type="button" class="ncc-icon-type-btn' + upActive +
            '"    data-type="upload">Upload</button>' +
            '</div>' +
            '<input type="hidden" class="ncc-icon-type-input" name="' + nameBase + '[icon_type]" value="' + type +
            '">' +
            '<div class="ncc-icon-panel" data-panel="emoji" style="display:' + emojiDisplay + '">' +
            '<input type="text" class="ncc-icon-val-input" name="' + nameBase + '[icon_value]" value="' + (type ===
                'emoji' ? value : '') + '" placeholder="Paste emoji">' +
            '</div>' +
            '<div class="ncc-icon-panel" data-panel="url" style="display:' + (type === 'url' ? 'block' : 'none') +
            '">' +
            '<input type="url" class="ncc-url-input ncc-upload-url-input" name="' + nameBase +
            '[icon_value]_url" value="' + (type === 'url' ? value : '') + '" placeholder="https://...">' +
            '</div>' +
            '<div class="ncc-icon-panel" data-panel="upload" style="display:' + (type === 'upload' ? 'block' :
                'none') + '">' +
            '<input type="url" class="ncc-upload-url-input" name="' + nameBase + '[icon_value]_upload" value="' + (
                type === 'upload' ? value : '') + '" placeholder="Click Upload to choose">' +
            '<button type="button" class="button ncc-upload-btn" style="margin-top:4px">📁 Upload</button>' +
            '</div>' +
            '<div class="' + previewClass +
            '" style="margin-top:5px;display:flex;align-items:center;gap:6px;min-height:28px">' + previewHtml +
            '</div>' +
            '</div>';
    }

    function buildPaymentRow(scope, idx) {
        var nameBase = 'nuvex[' + scope + '][payments][' + idx + ']';
        return '<td class="ncc-icon-cell">' + iconPickerHtml(nameBase, 'emoji', '💳', true) + '</td>' +
            '<td>' +
            '<select name="' + nameBase + '[display_mode]" style="width:100%">' +
            '<option value="both">Icon + Text</option>' +
            '<option value="icon_only">Icon Only</option>' +
            '<option value="text_only">Text Only</option>' +
            '</select>' +
            '</td>' +
            '<td><input type="text" name="' + nameBase +
            '[label]" placeholder="e.g. PayPal" style="width:100%"></td>' +
            '<td><button type="button" class="button button-small ncc-remove-row">Remove</button></td>';
    }

    function buildMetricRow(step, idx, hasP2) {
        var nameBase = 'nuvex[' + step + '][metrics][' + idx + ']';
        var p2col = hasP2 ? '<td><input type="text" name="' + nameBase + '[phase2]" placeholder="—"></td>' : '';
        return '<td class="ncc-icon-cell">' + iconPickerHtml(nameBase, 'emoji', '', false) + '</td>' +
            '<td><input type="text" name="' + nameBase + '[label]"  placeholder="Metric name"></td>' +
            '<td><input type="text" name="' + nameBase + '[phase1]" placeholder="—"></td>' +
            p2col +
            '<td><input type="text" name="' + nameBase + '[funded]" placeholder="—"></td>' +
            '<td><button type="button" class="button button-small ncc-remove-row">Remove</button></td>';
    }

    /* ── Saving spinner on submit ── */
    document.querySelectorAll('.ncc-tab form').forEach(function(form) {
        form.addEventListener('submit', function() {
            var spinner = form.querySelector('.ncc-inline-saving');
            if (spinner) spinner.style.display = 'inline';
        });
    });

    /* ── Fix upload inputs: merge panel inputs into single icon_value before submit ── */
    document.querySelectorAll('.ncc-tab form').forEach(function(form) {
        form.addEventListener('submit', function() {
            form.querySelectorAll('.ncc-icon-picker').forEach(function(picker) {
                var typeInput = picker.querySelector('.ncc-icon-type-input');
                var type = typeInput ? typeInput.value : 'emoji';
                var nameBase = typeInput ? typeInput.name.replace('[icon_type]', '') : '';
                var valInput;
                if (type === 'emoji') {
                    valInput = picker.querySelector('.ncc-icon-val-input');
                } else if (type === 'url') {
                    valInput = picker.querySelector('.ncc-url-input');
                } else {
                    valInput = picker.querySelector('.ncc-upload-url-input');
                }
                // Create/update a hidden merged icon_value field
                var hidden = picker.querySelector('.ncc-merged-icon-value');
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.className = 'ncc-merged-icon-value';
                    hidden.name = nameBase + '[icon_value]';
                    picker.appendChild(hidden);
                }
                hidden.value = valInput ? valInput.value : '';
            });
        });
    });

})();
</script>
<?php
    }

    /* ── Admin helper: icon picker (PHP-rendered) ── */
    private function render_icon_picker( string $name_base, string $type, string $value, bool $is_payment = false ): void {
        $type        = in_array( $type, [ 'emoji', 'url', 'upload' ], true ) ? $type : 'emoji';
        $preview_cls = $is_payment ? 'ncc-pay-icon-preview' : 'ncc-icon-preview';
        $img_style   = $is_payment ? 'width:32px!important;height:20px!important' : 'width:24px!important;height:24px!important';
        ?>
<div class="ncc-icon-picker">
    <div class="ncc-icon-type-btns">
        <button type="button" class="ncc-icon-type-btn<?php echo $type==='emoji'  ? ' active' : ''; ?>"
            data-type="emoji">Emoji</button>
        <button type="button" class="ncc-icon-type-btn<?php echo $type==='url'    ? ' active' : ''; ?>"
            data-type="url">URL</button>
        <button type="button" class="ncc-icon-type-btn<?php echo $type==='upload' ? ' active' : ''; ?>"
            data-type="upload">Upload</button>
    </div>
    <input type="hidden" class="ncc-icon-type-input" name="<?php echo esc_attr( $name_base ); ?>[icon_type]"
        value="<?php echo esc_attr( $type ); ?>">

    <div class="ncc-icon-panel" data-panel="emoji" style="display:<?php echo $type==='emoji' ? 'block' : 'none'; ?>">
        <input type="text" class="ncc-icon-val-input" name="<?php echo esc_attr( $name_base ); ?>[icon_value_emoji]"
            value="<?php echo esc_attr( $type==='emoji' ? $value : '' ); ?>" placeholder="Paste emoji">
    </div>
    <div class="ncc-icon-panel" data-panel="url" style="display:<?php echo $type==='url' ? 'block' : 'none'; ?>">
        <input type="url" class="ncc-url-input ncc-upload-url-input"
            name="<?php echo esc_attr( $name_base ); ?>[icon_value_url]"
            value="<?php echo esc_attr( $type==='url' ? $value : '' ); ?>" placeholder="https://...">
    </div>
    <div class="ncc-icon-panel" data-panel="upload" style="display:<?php echo $type==='upload' ? 'block' : 'none'; ?>">
        <input type="url" class="ncc-upload-url-input" name="<?php echo esc_attr( $name_base ); ?>[icon_value_upload]"
            value="<?php echo esc_attr( $type==='upload' ? $value : '' ); ?>" placeholder="Click Upload to choose">
        <button type="button" class="button ncc-upload-btn" style="margin-top:4px">📁 Upload</button>
    </div>
    <!-- merged hidden value, populated on submit -->
    <input type="hidden" class="ncc-merged-icon-value" name="<?php echo esc_attr( $name_base ); ?>[icon_value]"
        value="<?php echo esc_attr( $value ); ?>">

    <div class="<?php echo esc_attr( $preview_cls ); ?>"
        style="margin-top:5px;display:flex;align-items:center;gap:6px;min-height:28px">
        <?php if ( $type === 'emoji' ) : ?>
        <span style="font-size:20px"><?php echo esc_html( $value ); ?></span>
        <?php elseif ( $value ) : ?>
        <img src="<?php echo esc_url( $value ); ?>" alt=""
            style="<?php echo esc_attr( $img_style ); ?>;object-fit:contain;border:1px solid #eee;border-radius:3px">
        <?php endif; ?>
    </div>
</div>
<?php
    }

    /* ── Admin helper: payment icon row ── */
    private function render_icon_row( string $scope, string $group, int $idx, array $item ): void {
        $name_base    = "nuvex[{$scope}][{$group}][{$idx}]";
        $icon_type    = $item['icon_type']    ?? 'emoji';
        $icon_value   = $item['icon_value']   ?? '';
        $label        = $item['label']        ?? '';
        $display_mode = $item['display_mode'] ?? 'both';
        ?>
<tr>
    <td class="ncc-icon-cell"><?php $this->render_icon_picker( $name_base, $icon_type, $icon_value, true ); ?></td>
    <td>
        <select name="<?php echo esc_attr( $name_base ); ?>[display_mode]" style="width:100%">
            <option value="both" <?php selected( $display_mode, 'both' ); ?>>Icon + Text</option>
            <option value="icon_only" <?php selected( $display_mode, 'icon_only' ); ?>>Icon Only</option>
            <option value="text_only" <?php selected( $display_mode, 'text_only' ); ?>>Text Only</option>
        </select>
    </td>
    <td><input type="text" name="<?php echo esc_attr( $name_base ); ?>[label]" value="<?php echo esc_attr( $label ); ?>"
            style="width:100%"></td>
    <td><button type="button" class="button button-small ncc-remove-row">Remove</button></td>
</tr>
<?php
    }

    /* ── Admin helper: size table row ── */
    private function render_size_row( string $step, int $idx, array $sz ): void { ?>
<tr>
    <td style="text-align:center">
        <input type="checkbox" name="nuvex[<?php echo $step; ?>][sizes][<?php echo $idx; ?>][active]" value="1"
            <?php checked( $sz['active'] ?? '1', '1' ); ?>>
    </td>
    <td><input type="text" name="nuvex[<?php echo $step; ?>][sizes][<?php echo $idx; ?>][label]"
            value="<?php echo esc_attr( $sz['label'] ); ?>"></td>
    <td><input type="number" name="nuvex[<?php echo $step; ?>][sizes][<?php echo $idx; ?>][price]"
            value="<?php echo esc_attr( $sz['price'] ); ?>" min="0"></td>
    <td><input type="number" name="nuvex[<?php echo $step; ?>][sizes][<?php echo $idx; ?>][discount]"
            value="<?php echo esc_attr( $sz['discount'] ); ?>" min="0"></td>
    <td><input type="text" name="nuvex[<?php echo $step; ?>][sizes][<?php echo $idx; ?>][link]"
            value="<?php echo esc_attr( $sz['link'] ); ?>" placeholder="https://... or #"></td>
    <td style="text-align:center">
        <input type="checkbox" name="nuvex[<?php echo $step; ?>][sizes][<?php echo $idx; ?>][featured]" value="1"
            <?php checked( $sz['featured'] ?? '0', '1' ); ?>>
    </td>
    <td><input type="text" name="nuvex[<?php echo $step; ?>][sizes][<?php echo $idx; ?>][badge]"
            value="<?php echo esc_attr( $sz['badge'] ); ?>"></td>
    <td><button type="button" class="button button-small ncc-remove-row">Remove</button></td>
</tr>
<?php }

    /* ── Admin helper: metric table row ── */
    private function render_metric_row( string $step, int $idx, array $m, bool $has_p2 ): void {
        $name_base  = "nuvex[{$step}][metrics][{$idx}]";
        $icon_type  = $m['icon_type']  ?? 'emoji';
        $icon_value = $m['icon_value'] ?? '';
        ?>
<tr>
    <td class="ncc-icon-cell"><?php $this->render_icon_picker( $name_base, $icon_type, $icon_value, false ); ?></td>
    <td><input type="text" name="<?php echo esc_attr( $name_base ); ?>[label]"
            value="<?php echo esc_attr( $m['label'] ?? '' ); ?>"></td>
    <td><input type="text" name="<?php echo esc_attr( $name_base ); ?>[phase1]"
            value="<?php echo esc_attr( $m['phase1'] ?? '' ); ?>"></td>
    <?php if ( $has_p2 ) : ?>
    <td><input type="text" name="<?php echo esc_attr( $name_base ); ?>[phase2]"
            value="<?php echo esc_attr( $m['phase2'] ?? '' ); ?>"></td>
    <?php endif; ?>
    <td><input type="text" name="<?php echo esc_attr( $name_base ); ?>[funded]"
            value="<?php echo esc_attr( $m['funded'] ?? '' ); ?>"></td>
    <td><button type="button" class="button button-small ncc-remove-row">Remove</button></td>
</tr>
<?php }

    /* ═══════════════════════════════════════════════════════════════
       FRONTEND STYLES
    ═══════════════════════════════════════════════════════════════ */

    public function frontend_styles(): void {
        $s     = $this->get_settings();
        $color = esc_attr( $s['general']['accent_color'] ?? '#7c3aed' );
        ?>
<style id="ncc-frontend-styles">
:root {
    --ncc: <?php echo $color;
    ?>;
    --ncc-rgb: <?php echo implode(',', $this->hex_to_rgb($color));
    ?>;
}

.ncc-card {
    max-width: 780px;
    width: 100%;
    margin: 40px auto;
    background: #f7f7f7;
    border-radius: 32px;
    border: 1px solid #DEE0E3;
    padding: 28px 28px 22px;
    font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    box-sizing: border-box;
    color: #111;
}

.ncc-step-switch {
    display: flex;
    gap: 8px;
    justify-content: center;
    margin-bottom: 20px;
}

.ncc-step {
    flex: 1;
    max-width: 180px;
    padding: 10px 0;
    font-size: 16px;
    font-weight: 700;
    letter-spacing: .6px;
    cursor: pointer;
    color: #0d0d12;
    transition: all .18s ease;
    text-align: center;
    border-radius: 8px;
    border: 1px solid #DEE0E3;
    background: #F6F8FA;
}

.ncc-step:hover:not(.active) {
    border-color: var(--ncc);
    color: var(--ncc);
    background: rgba(var(--ncc-rgb), .05);
}

.ncc-step.active {
    background: var(--ncc);
    color: #fff;
    border-color: var(--ncc);
    box-shadow: 0 4px 14px rgba(var(--ncc-rgb), .35);
}

.ncc-sizes {
    display: flex;
    gap: 8px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.ncc-size {
    padding: 8px 30px;
    border-radius: 8px;
    border: 1.5px solid #DEE0E3;
    background: #F6F8FA;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    position: relative;
    color: #0d0d12;
    transition: all .16s ease;
    text-align: center;
}

.ncc-size:hover:not(.active) {
    border-color: var(--ncc);
    color: var(--ncc);
    background: rgba(var(--ncc-rgb), .05);
}

.ncc-size.active {
    background: var(--ncc);
    color: #fff;
    border-color: var(--ncc);
    box-shadow: 0 3px 10px rgba(var(--ncc-rgb), .3);
}

.ncc-size.featured {
    padding-top: 8px;
}

.ncc-badge {
    position: absolute;
    top: -9px;
    left: 50%;
    transform: translateX(-50%) rotate(5deg);
    border-radius: 8px;
    background: #00DF80;
    color: #3E3E3E;
    font-size: 8px;
    font-weight: 700;
    padding: 2px 8px;
    white-space: nowrap;
    letter-spacing: .3px;
}

.ncc-table-wrap {
    border: 2px solid #DEE0E3;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 18px;
    backdrop-filter: blur(7.5px);
}

.ncc-table-wrap-inner {
    border-radius: 6px;
    overflow: auto;
    padding: 4px;
    backdrop-filter: blur(7.5px);
    max-height: 100%;
    -webkit-overflow-scrolling: touch;
}

.ncc-metrics-table {
    border: 1px solid transparent;
    border-radius: 8px;
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    table-layout: auto;
    margin-block-end: 0;
}

.ncc-metrics-table caption+thead tr:first-child td,
.ncc-metrics-table caption+thead tr:first-child th,
.ncc-metrics-table colgroup+thead tr:first-child td,
.ncc-metrics-table colgroup+thead tr:first-child th,
.ncc-metrics-table thead:first-child tr:first-child td,
.ncc-metrics-table thead:first-child tr:first-child th,
.ncc-metrics-table td,
.ncc-metrics-table th {
    border: 0;
}

.ncc-metrics-table th:not(:first-child),
.ncc-metrics-table td:not(:first-child) {
    border-left: 1px solid rgba(192, 127, 220, 0.10);
}

.ncc-metrics-table tr:not(:last-child) td,
.ncc-metrics-table tr:not(:last-child) th {
    border-bottom: 1px solid rgba(192, 127, 220, 0.10);
}

.ncc-metric-colgroup col {
    width: auto;
}

.ncc-thead {
    background: #f8f8fa;
}

.ncc-thead th {
    font-size: 11.5px;
    font-weight: 700;
    color: #888;
    text-transform: uppercase;
    letter-spacing: .5px;
    text-align: center;
    padding: 12px 16px;
    border-bottom: 1px solid rgba(192, 127, 220, 0.10);
}

.ncc-thead th:first-child {
    text-align: left;
    background: #E8E8E8 !important;
}

.ncc-row {
    transition: background .1s;
}

.ncc-row:hover {
    background: #fbfbfd;
}

.ncc-row td {
    padding: 11px 16px;
    text-align: center;
    color: #444;
    font-size: 13px;
    vertical-align: middle;
}

.ncc-row td:first-child {
    text-align: left;
    color: #818898;
    font-weight: 500;
}

.ncc-col-label,
.ncc-row td.ncc-col-label {
    background: #E8E8E8 !important;
}

.ncc-col-phase1,
.ncc-row td.ncc-col-phase1 {
    background: #f5f5f5 !important;
}

.ncc-col-phase2,
.ncc-row td.ncc-col-phase2 {
    background: #f5f5f5 !important;
}

.ncc-col-funded,
.ncc-row td.ncc-col-funded {
    background: #ffffff !important;
    color: #0f0f0f !important;
}

.ncc-col-funded {
    color: var(--ncc) !important;
}

/* Rounded edges for first and last rows */
.ncc-metrics-table {
    /* overflow: hidden; - removed to allow scrolling */
}

.ncc-thead th:first-child {
    border-top-left-radius: 6px;
}

.ncc-row:first-child td:last-child {
    border-top-right-radius: 6px;
}

.ncc-row:last-child td:first-child {
    border-bottom-left-radius: 6px;
}

.ncc-row:last-child td:last-child {
    border-bottom-right-radius: 6px;
}

.ncc-row:last-child td {
    border-bottom: none;
}

/* Override alternating row colors from other plugins */
.ncc-metrics-table tbody tr:nth-child(odd) td,
.ncc-metrics-table tbody tr:nth-child(odd) th {
    background-color: transparent !important;
}

.ncc-metrics-table tbody tr:nth-child(even) td,
.ncc-metrics-table tbody tr:nth-child(even) th {
    background-color: transparent !important;
}

.ncc-col-funded {
    color: #6F25C0 !important;
}

/* icon sizing — fixed 24×24 for metrics, 32×20 for payments */
.ncc-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 6px;
    vertical-align: middle;
}

.ncc-pay>span.ncc-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 0;
    vertical-align: middle;
}

.ncc-icon img {
    width: 24px !important;
    height: 24px !important;
    object-fit: contain;
    vertical-align: middle;
}

.ncc-icon-emoji {
    font-size: 16px;
    line-height: 1;
}

.ncc-pay-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.ncc-pay-icon img {
    max-width: 70px !important;
    width: auto !important;
    height: auto;
    object-fit: contain;
}

.ncc-pricing {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    border: 1.5px solid #DEE0E3;
    border-radius: 8px;
    padding: 16px 20px;
    margin-bottom: 16px;
    background: white;
}

.ncc-for small {
    font-size: 14px;
    color: rgba(18, 18, 18, 0.50);
    display: block;
    margin-bottom: 3px;
}

.ncc-account-size strong {
    font-size: 30px;
    font-weight: 800;
    color: #121212;
    letter-spacing: -.5px;
}

.ncc-account-size span {
    font-size: 14px;
    color: rgba(18, 18, 18, 0.50);
    margin-left: 4px;
}

.ncc-price-box {
    text-align: center;
}

.ncc-price-box small {
    font-size: 14px;
    color: rgba(18, 18, 18, 0.50);
    display: block;
    margin-bottom: 3px;
}

.ncc-old {
    text-decoration: line-through;
    color: #c5c5c5;
    font-size: 20px;
    margin-right: 5px;
}

.ncc-new {
    font-size: 30px;
    font-weight: 800;
    color: #121212;
    letter-spacing: -.5px;
}

.ncc-buy-btn {
    display: inline-block;
    background: radial-gradient(73.52% 406.85% at 50.68% 140.2%, rgba(255, 255, 255, 0.20) 8.16%, rgba(255, 255, 255, 0.00) 100%), #6F25C0;
    color: #fff !important;
    border: none;
    padding: 13px 40px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    white-space: nowrap;
    letter-spacing: .2px;
    box-shadow: 0 4px 16px rgba(var(--ncc-rgb), .35);
    transition: opacity .16s, transform .12s;
}

.ncc-buy-btn:hover {
    opacity: .88;
    transform: translateY(-1px);
}

.ncc-buy-btn:active {
    transform: translateY(0);
    opacity: 1;
}

.ncc-payments {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.ncc-pay {
    flex: 1;
    min-width: 70px;
    border: 1.5px solid #DEE0E3;
    border-radius: 8px;
    text-align: center;
    padding: 12px 8px;
    font-size: 12px;
    font-weight: 700;
    color: var(--ncc);
    background: white;
    letter-spacing: .2px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 60px;
}

/* Icon only - larger box and icon */
.ncc-pay[data-display="icon_only"] {
    min-height: 75px;
    padding: 16px 12px;
    gap: 0;
}

/* Icon sizing in payment boxes */
.ncc-pay .ncc-pay-icon img,
.ncc-pay .ncc-icon img {
    width: 40px !important;
    height: 28px !important;
    object-fit: contain;
}

.ncc-pay .ncc-icon-emoji {
    font-size: 28px;
    line-height: 1;
}

.ncc-pay-label {
    font-size: 12px;
    font-weight: 700;
}

/* Icon only - larger */
.ncc-pay[data-display="icon_only"] .ncc-pay-icon img,
.ncc-pay[data-display="icon_only"] .ncc-icon img {
    width: 45px !important;
    height: 30px !important;
    object-fit: contain;
}

.ncc-pay[data-display="icon_only"] .ncc-icon-emoji {
    font-size: 32px;
    line-height: 1;
}

/* Text only - larger text */
.ncc-pay[data-display="text_only"] .ncc-pay-label {
    font-size: 14px;
    font-weight: 700;
}

/* Both - balanced */
.ncc-pay[data-display="both"] .ncc-pay-icon img,
.ncc-pay[data-display="both"] .ncc-icon img {
    width: 32px !important;
    height: 20px !important;
    object-fit: contain;
}

.ncc-pay[data-display="both"] .ncc-icon-emoji {
    font-size: 20px;
    line-height: 1;
}

.ncc-pay[data-display="both"] .ncc-pay-label {
    font-size: 11px;
    font-weight: 700;
}

@media (max-width:680px) {
    .ncc-card {
        padding: 20px 16px 18px;
        border-radius: 14px;
    }

    .ncc-step {
        padding: 9px 10px;
        font-size: 12px;
    }

    .ncc-new {
        font-size: 28px;
    }

    .ncc-account-size strong {
        font-size: 28px;
    }

    .ncc-buy-btn {
        padding: 12px 18px;
        font-size: 13px;
    }

    .ncc-metrics-table {
        font-size: 12px;
    }

    .ncc-thead th,
    .ncc-row td {
        padding: 10px 14px;
    }
}

@media (max-width:520px) {
    .ncc-card {
        padding: 16px 12px 14px;
        margin: 20px auto;
        border-radius: 12px;
    }

    .ncc-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 8px;
    }

    .ncc-metrics-table {
        min-width: 500px;
        font-size: 12px;
    }

    .ncc-thead th,
    .ncc-row td {
        padding: 8px 12px;
        font-size: 12px;
    }

    .ncc-pricing {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 16px;
    }

    .ncc-buy-btn {
        width: 100%;
        text-align: center;
        box-sizing: border-box;
    }

    .ncc-price-box {
        text-align: left;
    }

    .ncc-sizes {
        gap: 6px;
    }

    .ncc-size {
        padding: 7px 12px;
        font-size: 12px;
    }
}

@media (max-width:360px) {
    .ncc-step-switch {
        flex-direction: column;
        align-items: center;
    }

    .ncc-step {
        max-width: 100%;
        width: 100%;
    }
}
</style>
<?php
    }

    private function hex_to_rgb( string $hex ): array {
        $hex = ltrim( $hex, '#' );
        if ( strlen( $hex ) === 3 ) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        return [ hexdec(substr($hex,0,2)), hexdec(substr($hex,2,2)), hexdec(substr($hex,4,2)) ];
    }

    /* ── Render icon HTML for frontend ── */
    private function render_frontend_icon( array $item, bool $is_payment = false ): string {
        $type  = $item['icon_type']  ?? 'emoji';
        $value = $item['icon_value'] ?? '';
        if ( $type === 'emoji' && $value !== '' ) {
            return '<span class="ncc-icon"><span class="ncc-icon-emoji">' . esc_html( $value ) . '</span></span>';
        } elseif ( in_array( $type, [ 'url', 'upload' ], true ) && $value !== '' ) {
            $cls  = $is_payment ? 'ncc-pay-icon' : 'ncc-icon';
            $size = $is_payment ? 'max-width:60px!important;width:auto!important;height:auto!important' : 'width:24px!important;height:24px!important';
            return '<span class="' . $cls . '"><img src="' . esc_url( $value ) . '" alt="" style="' . $size . ';object-fit:contain" loading="lazy"></span>';
        }
        return '';
    }

    /* ═══════════════════════════════════════════════════════════════
       SHORTCODE
    ═══════════════════════════════════════════════════════════════ */

    public function shortcode(): string {
        $s   = $this->get_settings();
        $gen = $s['general'];

        $active_step_keys = array_values(
            array_filter( [ '1step', '2step' ], function ( $k ) use ( $s ) {
                return ! empty( $s[ $k ]['active'] );
            } )
        );

        if ( empty( $active_step_keys ) ) {
            return '<p style="color:#999;text-align:center">No active challenge steps configured.</p>';
        }

        // Pre-build JSON with only what frontend needs
        // Strip admin-only fields, keep icon data
        $uid  = 'ncc-' . uniqid();
        $json = wp_json_encode( $s );

        ob_start(); ?>
<div class="ncc-card" id="<?php echo esc_attr( $uid ); ?>">

    <!-- Step switch -->
    <div class="ncc-step-switch">
        <?php foreach ( $active_step_keys as $i => $step_key ) : ?>
        <button class="ncc-step <?php echo $i === 0 ? 'active' : ''; ?>" type="button"
            data-step="<?php echo esc_attr( $step_key ); ?>">
            <?php echo esc_html( $s[ $step_key ]['label'] ); ?>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- Sizes (filled by JS) -->
    <div class="ncc-sizes" role="group" aria-label="Account size"></div>

    <!-- Metrics table (filled by JS) -->
    <div class="ncc-table-wrap">
        <div class="ncc-table-wrap-inner">
            <table class="ncc-metrics-table">
                <colgroup class="ncc-metric-colgroup"></colgroup>
                <thead class="ncc-thead"></thead>
                <tbody class="ncc-tbody"></tbody>
            </table>
        </div>
    </div>

    <!-- Pricing (filled by JS) -->
    <div class="ncc-pricing">
        <div class="ncc-for">
            <small>For</small>
            <div class="ncc-account-size"></div>
        </div>
        <div class="ncc-price-box">
            <small>Price</small>
            <div class="ncc-prices"></div>
        </div>
        <a href="#" class="ncc-buy-btn"><?php echo esc_html( $gen['btn_text'] ); ?></a>
    </div>

    <!-- Payments -->
    <?php if ( ! empty( $gen['payments'] ) ) : ?>
    <div class="ncc-payments">
        <?php foreach ( $gen['payments'] as $idx => $pay ) :
            $display_mode = isset( $pay['display_mode'] ) ? $pay['display_mode'] : 'both';
            $show_icon = in_array( $display_mode, [ 'both', 'icon_only' ], true );
            $show_text = in_array( $display_mode, [ 'both', 'text_only' ], true );
        ?>
        <div class="ncc-pay" data-display="<?php echo esc_attr( $display_mode ); ?>">
            <?php if ( $show_icon ) echo $this->render_frontend_icon( $pay, true ); ?>
            <?php if ( $show_text ) : ?><span
                class="ncc-pay-label"><?php echo esc_html( $pay['label'] ); ?></span><?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
(function() {
    var settings = <?php echo $json; ?>;
    var card = document.getElementById(<?php echo wp_json_encode( $uid ); ?>);
    var currentStep = <?php echo wp_json_encode( $active_step_keys[0] ); ?>;
    var currentSzIdx = 0;
    var currency = (settings.general && settings.general.currency) ? settings.general.currency : '€';

    function activeSizes(stepKey) {
        return (settings[stepKey].sizes || []).filter(function(s) {
            return s.active === '1';
        });
    }

    function el(tag, cls, html) {
        var n = document.createElement(tag);
        if (cls) n.className = cls;
        if (html !== void 0) n.innerHTML = html;
        return n;
    }

    function esc(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g,
            '&quot;');
    }

    function iconHtml(item, isPayment) {
        var type = item.icon_type || 'emoji';
        var value = item.icon_value || '';
        if (!value) return '';
        if (type === 'emoji') {
            return '<span class="ncc-icon"><span class="ncc-icon-emoji">' + esc(value) + '</span></span>';
        }
        var cls = isPayment ? 'ncc-pay-icon' : 'ncc-icon';
        var style = isPayment ? 'width:32px!important;height:20px!important' :
            'width:24px!important;height:24px!important';
        return '<span class="' + cls + '"><img src="' + esc(value) + '" alt="" style="' + style +
            ';object-fit:contain" loading="lazy"></span>';
    }

    function render() {
        var step = settings[currentStep];
        var sizes = activeSizes(currentStep);
        if (!sizes.length) return;
        if (currentSzIdx >= sizes.length) currentSzIdx = 0;
        var sz = sizes[currentSzIdx];
        var hasP2 = step.has_phase2 === '1';

        /* sizes */
        var sizesEl = card.querySelector('.ncc-sizes');
        sizesEl.innerHTML = '';
        sizes.forEach(function(s, i) {
            var cls = 'ncc-size' + (i === currentSzIdx ? ' active' : '') + (s.featured === '1' ?
                ' featured' : '');
            var btn = el('button', cls);
            btn.type = 'button';
            if (s.featured === '1' && s.badge) {
                btn.innerHTML = esc(s.label) + '<span class="ncc-badge">' + esc(s.badge) + '</span>';
            } else {
                btn.textContent = s.label;
            }
            btn.addEventListener('click', function() {
                currentSzIdx = i;
                render();
            });
            sizesEl.appendChild(btn);
        });

        /* table head — dynamic columns based on has_phase2 */
        var colgroup = card.querySelector('.ncc-metric-colgroup');
        var cols = hasP2 ? 4 : 3;
        var colHtml = '<col class="ncc-col-label">' +
            '<col class="ncc-col-phase1">' +
            (hasP2 ? '<col class="ncc-col-phase2">' : '') +
            '<col class="ncc-col-funded">';
        colgroup.innerHTML = colHtml;

        var thead = card.querySelector('.ncc-thead');
        thead.innerHTML =
            '<tr>' +
            '<th></th>' +
            '<th class="ncc-col-phase1">' + esc(step.col_phase1 || 'Phase 1') + '</th>' +
            (hasP2 ? '<th class="ncc-col-phase2">' + esc(step.col_phase2 || 'Phase 2') + '</th>' : '') +
            '<th class="ncc-col-funded">' + esc(step.col_funded || 'Funded') + '</th>' +
            '</tr>';

        /* table body */
        var tbody = card.querySelector('.ncc-tbody');
        tbody.innerHTML = '';
        (step.metrics || []).forEach(function(m) {
            var row = el('tr', 'ncc-row');
            row.innerHTML =
                '<td>' + iconHtml(m, false) + esc(m.label) + '</td>' +
                '<td class="ncc-col-phase1">' + esc(m.phase1) + '</td>' +
                (hasP2 ? '<td class="ncc-col-phase2">' + esc(m.phase2 || '') + '</td>' : '') +
                '<td class="ncc-col-funded">' + esc(m.funded) + '</td>';
            tbody.appendChild(row);
        });

        /* pricing */
        card.querySelector('.ncc-account-size').innerHTML =
            '<strong>' + currency + esc(sz.label) + '</strong><span>Account</span>';
        var disc = parseFloat(sz.discount) || 0;
        card.querySelector('.ncc-prices').innerHTML = disc > 0 ?
            '<span class="ncc-old">' + currency + esc(sz.price) + '</span><span class="ncc-new">' + currency + esc(
                sz.discount) +
            '</span>' :
            '<span class="ncc-new">' + currency + esc(sz.price) + '</span>';
        card.querySelector('.ncc-buy-btn').href = sz.link || '#';
    }

    /* step clicks */
    card.querySelectorAll('.ncc-step').forEach(function(btn) {
        btn.addEventListener('click', function() {
            card.querySelectorAll('.ncc-step').forEach(function(b) {
                b.classList.remove('active');
            });
            btn.classList.add('active');
            currentStep = btn.dataset.step;
            currentSzIdx = 0;
            render();
        });
    });

    render();
})();
</script>
<?php
        return ob_get_clean();
    }
}

new Nuvex_Challenge_Card();