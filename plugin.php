<?php
/*
	Plugin Name: Widget Controls
	Description: Use Symfony Forms and Twig templating with WordPress widgets.
	Plugin URI:
	Author: Kaspars Dambis
	Author URI: http://kaspars.net
	Version: 0.1
*/

date_default_timezone_set( 'Europe/Riga' );

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationRequestHandler;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Translation\Translator;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\MinLength;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

add_action( 'widgets_init', function() {
	register_widget( 'WidgetFormsSample' );
} );

class WidgetFormsSample extends WP_Widget {

	private $controls = array();

	function __construct() {

		/**
		 * Input field definitions
		 * @var array
		 * @see http://symfony.com/doc/current/reference/forms/types.html
		 * @see http://symfony.com/doc/current/reference/constraints.html
		 */
		$this->controls = array(
				'title' => array(
					'type' => 'text',
					'options' => array(
						'label' => 'Internal Title',
						'required' => true,
						'attr' => array(
							'class' => 'widefat'
						),
						'constraints' => array(
							new NotBlank()
						)
					)
				),
				'display_title' => array(
					'type' => 'text',
					'options' => array(
						'label' => 'Display Title',
						'required' => false,
						'attr' => array(
							'class' => 'widefat'
						)
					)
				)
			);

		$formEngine = new TwigRendererEngine( array( 
				'form.html.twig' 
			) );

		$twig = new Twig_Environment( 
			new Twig_Loader_Filesystem( __DIR__ . '/views' ),
			array(
			    'cache' => __DIR__ . '/cache',
			)
		);

		$formEngine->setEnvironment( $twig );

		$twig->addExtension(
			new TranslationExtension( new Translator('en') )
		);

		$twig->addExtension(
			new FormExtension( new TwigRenderer( $formEngine ) )
		);

		$this->twig = $twig;

		parent::__construct( 
			'widget-controls-example', 
			__( 'Widget Controls Example' )
		);

	}

	function widget( $args, $instance ) {
		
		echo $this->twig->render( 'widget.html.twig', array(
				'instance' => print_r( $instance, true ),
			) );

	}

	function update( $new_instance, $old_instance ) {
		
		$form = $this->get_controls_form()->handleRequest();

		if ( $form->isValid() )
			return $new_instance;

		return $old_instance;

	}

	function form( $instance ) {

		$form = $this->get_controls_form( $instance )->handleRequest();

		echo $this->twig->render( 'controls.html.twig', array(
				'controls' => $form->createView(),
			) );

	}


	function get_controls_form( $settings = array() ) {

		$validator = Validation::createValidator();

		$form_factory = Forms::createFormFactoryBuilder()
			->addExtension( new ValidatorExtension( $validator ) )
			->getFormFactory();

		// Form for this instance of the widget
		$form = $form_factory->createNamedBuilder( 
				$this->number, 
				'form', 
				$settings, 
				array(
					'label' => ' ' // Hides label for the sub-form
				) 
			);

		foreach ( $this->controls as $field_name => $field_params )
			$form->add(
					$field_name,
					$field_params['type'],
					$field_params['options']
				);

		return $form_factory->createNamedBuilder( 'widget-' . $this->id_base, 'form' )
			->add( $form )
			->getForm();

	}
	
}

