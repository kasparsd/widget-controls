<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Form\Forms;
use Symfony\Component\Translation\Translator;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;

date_default_timezone_set( 'Europe/Riga' );

$fields = array(
	'title' => array(
		'type' => 'text',
		'options' => array(
			'label' => 'Internal Title',
			'required' => true
		)
	),
	'display_title' => array(
		'type' => 'text',
		'options' => array(
			'label' => 'Display Title',
			'required' => false
		)
	)
);

$widget_options = array(
	'title' => 'Some Title',
	'display_title' => 'Another Title'
);

$twig = new Twig_Environment( new Twig_Loader_Filesystem( array(
    	__DIR__ . '/views',
    	__DIR__ . '/vendor/symfony/twig-bridge/Symfony/Bridge/Twig/Resources/views/Form'
	) ) );

$formEngine = new TwigRendererEngine( array( 
		'form_div_layout.html.twig' 
	) );

$formEngine->setEnvironment( $twig );

$twig->addExtension(
	new TranslationExtension( new Translator('en') )
);

$twig->addExtension(
    new FormExtension( new TwigRenderer( $formEngine ) )
);

$formFactory = Forms::createFormFactory();

$builder = $formFactory->createBuilder( 'form', $widget_options );

foreach ( $fields as $field_name => $field_params )
	$builder->add(
			$field_name,
			$field_params['type'],
			$field_params['options']
		);

echo $twig->render('form.html.twig', array(
    'form' => $builder->getForm()->createView(),
));

