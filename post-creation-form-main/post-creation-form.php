<?php

/*
Plugin Name: formulaire de création de posts sur FE
Description:  Un shortcode qui affiche un formulaire avec 2 champs : titre et texte. Après avoir soumis le formulaire, le plugin crée un nouveau message non publié avec un titre du champ de titre et du texte du champ de texte, et envoie un e-mail à l'adresse e-mail de l'administrateur avec le titre et le texte du message.
Version: 1.0
Author: Belguith.med.amine
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class PostCreationForm
{
    public $post_creation_form_options;

    public function init_post_creation_form()
    {

        add_action('admin_menu', array(
            $this,
            'post_creation_form_add_plugin_page'
        ));
        add_action('admin_init', array(
            $this,
            'post_creation_form_page_init'
        ));
    }


    public function post_creation_form_add_plugin_page()
    {
        add_menu_page('post_creation_form', // page_title
            __('formulaire de création de posts sur FE ', 'post-creation-form'), // menu_title
            'manage_options', // capability
            'post_creation_form', // menu_slug
            array(
                $this,
                'post_creation_form_create_admin_page'
            ), // function
            'dashicons-admin-generic', // icon_url
            3 // position
        );
    }


    public function post_creation_form_create_admin_page()
    {
        $this->post_creation_form_options = get_option('post_creation_form_option_name');
        ?>

        <div class="wrap">
            <h2><?php echo  __('formulaire de création de posts sur FE ', 'post-creation-form');?></h2>
            <p><?php echo  __('Un shortcode qui affiche un  formulaire avec 2  champs : titre et texte.  Après avoir soumis le  formulaire, le plugin  crée un nouveau message non publié avec un titre  du champ de titre et du  texte du champ de texte, et envoie un e-mail à  l\'adresse e-mail de  l\'administrateur avec le titre et le texte du  message', 'post-creation-form');?></p>
            <?php
            settings_errors();
            ?>

            <form method="post" action="options.php">
                <?php
                settings_fields('post_creation_form_option_group');
                do_settings_sections('post_creation_form-admin');
                submit_button();
                ?>
            </form>

            <?php
            $attributes = array();
            if(isset($this->post_creation_form_options['required_post_content'])){
                $attributes['required_post_content'] = $this->post_creation_form_options['required_post_content'];
            }

            $dataAttributes = array_map(function($value, $key) {
                return $key.'="'.$value.'"';
            }, array_values($attributes), array_keys($attributes));

            $dataAttributes = implode(' ', $dataAttributes);
            ?>
            <p><b>Shortcode:</b> [post_creation_form <?php echo $dataAttributes ; ?>] </p>

        </div>
        <?php
    }

    public function post_creation_form_page_init()
    {
        register_setting('post_creation_form_option_group', // option_group
            'post_creation_form_option_name', // option_name
            array(
                $this,
                'post_creation_form_sanitize'
            ) // sanitize_callback
        );

        add_settings_section('post_creation_form_setting_section', // id
            'Settings', // title
            '', // callback
            'post_creation_form-admin' // page
        );


        add_settings_field('required_post_content', // id
            'Contenu obligatoire', // title
            array(
                $this,
                'required_post_content_callback'
            ), // callback
            'post_creation_form-admin', // page
            'post_creation_form_setting_section' // section
        );


        add_settings_field('post_status', // id
            'Publication avec modération', // title
            array(
                $this,
                'post_status_callback'
            ), // callback
            'post_creation_form-admin', // page
            'post_creation_form_setting_section' // section
        );
    }

    public function post_creation_form_sanitize($input)
    {
        $sanitary_values = array();
        if (isset($input['required_post_content'])) {
            $sanitary_values['required_post_content'] = sanitize_text_field($input['required_post_content']);
        } else {
            $sanitary_values['required_post_content'] = 0;
        }
        if (isset($input['post_status'])) {
            $sanitary_values['post_status'] = sanitize_text_field($input['post_status']);
        } else {
            $sanitary_values['post_status'] = 0;
        }

        return $sanitary_values;
    }


    public function required_post_content_callback()
    {
        printf('<input class="regular-text" type="checkbox" name="post_creation_form_option_name[required_post_content]"  %s id="required_post_content" value="1">', isset($this->post_creation_form_options['required_post_content']) && $this->post_creation_form_options['required_post_content'] == 1 ? 'checked' : '');
    }
    public function post_status_callback()
    {
        printf('<input class="regular-text" type="checkbox" name="post_creation_form_option_name[post_status]"  %s id="post_status" value="1">', isset($this->post_creation_form_options['post_status']) && $this->post_creation_form_options['post_status'] == 1 ? 'checked' : '');
    }

    public static  function post_creation_form_shortcode($atts) {
        $attributes = shortcode_atts( array(
            'required_post_content' => 0
        ), $atts );
        ob_start(); ?>
        <form method="post" action="" name="savePost">
            <label for="title"><?php echo  __('Titre :', 'post-creation-form') ;?></label>
            <input type="text" name="title" id="title" required>
            <br>
            <label for="content"><?php echo  __('Texte :', 'post-creation-form') ;?></label>
            <textarea name="content" id="content" <?php if(isset($attributes['required_post_content']) && $attributes['required_post_content'] == 1 ) {?> required <?php } ?>></textarea>
            <br>
            <input type="submit" value="<?php echo  __('Enregistrer', 'post-creation-form') ;?>">
            <div class="display"></div>
        </form>
        <?php
        return ob_get_clean();
    }

    public static function uninstall(){
        delete_option( 'post_creation_form_option_name');
    }
    public static function post_creation_form_enqueue_scripts(){

        wp_enqueue_script("jquery");
        wp_enqueue_script('post_creation_form_js',  plugin_dir_url( __FILE__ ). 'assets/js/script.js' , array( 'jquery' ));
        wp_enqueue_style('post_creation_form_css',  plugin_dir_url( __FILE__ ). 'assets/css/style.css' , false );
        $ajaxUrl = array(
            'ajaxurl' => admin_url( 'admin-ajax.php' )
        );
        wp_localize_script('post_creation_form_js', 'ajaxurl', $ajaxUrl);
    }
    public function post_creation_form_save_post() {
        $this->post_creation_form_options = get_option('post_creation_form_option_name');
        $b = 0 ;
        if($this->post_creation_form_options['required_post_content'] == 0){
            $b = 1;
        }else{
            if ( isset( $_POST['content'] ) && !empty($_POST['content'])) {
                $b = 1;
            }
        }
        if ( isset( $_POST['title'] ) && !empty($_POST['title']) && $b == 1 ) {
            $title = sanitize_text_field( $_POST['title'] );
            $content = sanitize_textarea_field( $_POST['content'] );
            if ( ! get_page_by_title( $title, OBJECT, 'post' ) ) {
                $post = array(
                    'post_title' => $title,
                    'post_content' => $content,
                    'post_status' =>  isset($this->post_creation_form_options['post_status']) && $this->post_creation_form_options['post_status'] == 1 ? 'draft' : 'publish',
                    'post_author' => 1,
                    'post_type' => 'post'
                );
                $post_id = wp_insert_post( $post );
                $admin_email = get_option( 'admin_email' );
                $subject = 'Nouveau message créé : ' . $title;
                $message = $content;
                wp_mail( $admin_email, $subject, $message );
                echo '<div class="message">'. __('Le message a été créé avec succès ! ', 'post-creation-form').'</div>';
            } else {
                echo '<div class="error">'. __('Un message avec le même titre existe déjà. Veuillez en choisir un autre.', 'post-creation-form').'</div>';
            }
        }else{
            echo '<div class="error">'. __('Veuillez remplir les champs obligatoire.', 'post-creation-form').'</div>';
        }
        exit;

    }

}


$PostCreationForm = new PostCreationForm();
if (is_admin()) {
    $PostCreationForm->init_post_creation_form();
    register_deactivation_hook(__FILE__, array('PostCreationForm', 'uninstall'));
}
add_shortcode( 'post_creation_form', array( 'PostCreationForm', 'post_creation_form_shortcode' ) );

add_action('wp_enqueue_scripts', array( 'PostCreationForm', 'post_creation_form_enqueue_scripts'), 100);
add_action( 'wp_ajax_save_post', array( $PostCreationForm, 'post_creation_form_save_post' )  );
add_action( 'wp_ajax_nopriv_save_post', array( $PostCreationForm, 'post_creation_form_save_post' )  );






