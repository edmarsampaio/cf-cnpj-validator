<?php
/**
 * Plugin Name:       Validar CNPJ
 * Plugin URI:        https://github.com/edmarsampaio/cnpj-field-validator/edit/master/custom-cf-validator.php
 * Description:       Validate Brazilian CNPJ document format in Caldera Forms
 * Version:           1.0
 * Requires at least: 1.0
 * Requires PHP:      7.0
 * Author:            Edmar Sampaio
 * Author URI:        https://edmarsampaio.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cnpj-cf-validator
 * Domain Path:       /languages
 */
add_filter('caldera_forms_get_form_processors', 'cnpj-cf-validator.php');

/**
 * Add a custom processor for field validation
 *
 * @uses 'cnpj-cf-validator.php'
 *
 * @param array $processors Processor configs
 *
 * @return array
 */
function cnpj_cf_validator_processor($processors){
    $processors['cnpj-cf-validator'] = array(
        'name' => __('CNPJ Validator', 'cnpj' ),
        'description' => '',
        'pre_processor' => 'cnpj_validator',
        'template' => dirname(__FILE__) . '/config.php'

    );

    return $processors;
}

/**
 * Run field validation
 *
 * @param array $config Processor config
 * @param array $form Form config
 *
 * @return array|void Error array if needed, else void.
 */
function cnpj_validator( array $config, array $form ){

    //Processor data object
    $data = new Caldera_Forms_Processor_Get_Data( $config, $form, cnpj_cf_validator_fields() );

    //Value of field to be validated
    $value = $data->get_value( 'cnpj' );

    //if not valid, return an error
    if( false == cnpj_cf_validator_is_valid( $value ) ){

        //get ID of field to put error on
        $fields = $data->get_fields();
        $field_id = $fields[ 'field-to-validate' ][ 'config_field' ];

        //Get label of field to use in error message above form
        $field = $form[ 'fields' ][ $field_id ];
        $label = $field[ 'label' ];

        //this is error data to send back
        return array(
            'type' => 'error',
            //this message will be shown above form
            'note' => sprintf( 'Por favor, corrija o %s', $label ),
            //Add error messages for any form field
            'fields' => array(
                //This error message will be shown below the field that we are validating
                $field_id => __( 'Esse CNPJ é inválido', 'text-domain' )
            )
        );
    }

    //If everything is good, don't return anything!

}


/**
 * Check if value is valid
 *
 * UPDATE THIS! Use your array of values, or query the database here.
 *
 * @return bool
 */
function cnpj_cf_validator_is_valid( $value ){
    
    $cnpj = preg_replace('/[^0-9]/', '', (string) $value);
	
	// Valida tamanho
	if (strlen($cnpj) != 14)
		return false;

	// Verifica se todos os digitos são iguais
	if (preg_match('/(\d)\1{13}/', $cnpj))
		return false;	

	// Valida primeiro dígito verificador
	for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++)
	{
		$soma += $cnpj[$i] * $j;
		$j = ($j == 2) ? 9 : $j - 1;
	}

	$resto = $soma % 11;

	if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto))
		return false;

	// Valida segundo dígito verificador
	for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++)
	{
		$soma += $cnpj[$i] * $j;
		$j = ($j == 2) ? 9 : $j - 1;
	}

	$resto = $soma % 11;

	return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    
}

/**
 * Processor fields
 *
 * @return array
 */
function my_custom_cf_validator_fields(){
    return array(
        array(
            'id' => 'field-to-validate',
            'type' => 'text',
            'required' => true,
            'magic' => true,
            'label' => __( 'Magic tag for field to validate.', 'cnpj' )
        ),
    );
}
