function astra_child_customize_register($wp_customize){
  $wp_customize->add_section('home_section', array('title'=>'Home Settings','priority'=>30));
  $wp_customize->add_setting('home_hero_image');
  $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'home_hero_image_control', array(
    'label' => 'Hero image',
    'section' => 'home_section',
    'settings' => 'home_hero_image'
  )));
}
add_action('customize_register','astra_child_customize_register');
