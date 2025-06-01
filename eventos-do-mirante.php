<?php
/**
 * Plugin Name: Eventos do Mirante Plugin
 * Description: Cria o Custom Post Type de eventos com shortcode para listagem.
 * Version: 1.0
 * Author: RD Exclusive
 */

if (!defined('ABSPATH')) exit;

// 1. Registrar o Custom Post Type
function ep_register_eventos_cpt() {
    register_post_type('evento', [
        'label' => 'Eventos',
        'public' => true,
        'menu_icon' => 'dashicons-calendar-alt',
        'supports' => ['title', 'editor', 'thumbnail'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'ep_register_eventos_cpt');

// 2. Adicionar o metabox do link de compra
function ep_add_evento_metabox() {
    add_meta_box(
        'ep_evento_link',
        'Link para compra',
        'ep_evento_link_callback',
        'evento',
        'side'
    );
}
add_action('add_meta_boxes', 'ep_add_evento_metabox');

function ep_evento_link_callback($post) {
    $value = get_post_meta($post->ID, '_ep_evento_link', true);
    echo '<label for="ep_evento_link">URL do Evento:</label>';
    echo '<input type="url" name="ep_evento_link" id="ep_evento_link" value="' . esc_attr($value) . '" style="width:100%">';
}

function ep_save_evento_link($post_id) {
    if (array_key_exists('ep_evento_link', $_POST)) {
        update_post_meta($post_id, '_ep_evento_link', esc_url_raw($_POST['ep_evento_link']));
    }
}
add_action('save_post', 'ep_save_evento_link');

// 3. Shortcode para listar eventos
function ep_eventos_shortcode($atts) {
    ob_start();
    $query = new WP_Query([
        'post_type' => 'evento',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);

    if ($query->have_posts()) {
        ?>
        <style>
            .ep-eventos-grid{
                justify-content:space-between;
            }
            .ep-eventos-grid .button{
                background-color:#0074ad;
            }
            .ep-eventos-grid .button:hover{
                background-color:#006da3;
            }
            .ep-evento-item{
                width:22%;
                margin:12px 0;
            }
            
            @media(max-width:768px){
                .ep-evento-item{
                    width:100%;
                }
            }
        </style>
        <?php
        echo '<div class="ep-eventos-grid" style="display: flex; flex-wrap: wrap; gap: 20px;">';

        while ($query->have_posts()) {
            $query->the_post();
            $link = get_post_meta(get_the_ID(), '_ep_evento_link', true);
            ?>
            <div class="ep-evento-item" style="border: 1px solid #ccc; border-radius: 8px; overflow: hidden;">
                <?php if (has_post_thumbnail()) {
                    the_post_thumbnail('medium', ['style' => 'width:100%; height:auto;']);
                } ?>
                <div style="display: flex; flex-direction: row; padding: 10px; gap: 10px;">
                    <div style="flex: 1;">
                        <h4><?php the_excerpt(); ?></h4>
                    </div>
                    <div style="flex: 2; display: flex; align-items: center; justify-content: center;">
                        <?php if ($link): ?>
                            <a href="<?php echo esc_url($link); ?>" target='_blank' class="button" style="padding: 14px 20px; text-align:center; font-size:14px; width:100%; color: white; text-decoration: none; border-radius: 5px;">
                                COMPRAR
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
        }

        echo '</div>';
        wp_reset_postdata();
    } else {
        echo '<p>Nenhum evento encontrado.</p>';
    }

    return ob_get_clean();
}
add_shortcode('eventos_list', 'ep_eventos_shortcode');
