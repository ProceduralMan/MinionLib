<?php
/* ejemplo de https://github.com/FriendsOfPHP/PHP-CS-Fixer#config-file

Faltan las reglas correspondientes a phpunit

$finder = PhpCsFixer\Finder::create()
    ->exclude('somedir')
    ->notPath('src/Symfony/Component/Translation/Tests/fixtures/resources.php')
    ->in(__DIR__)
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder)
;
*/
$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
;

$config = new PhpCsFixer\Config();

$config
    ->setRiskyAllowed(TRUE)
    ->setRules(array(
        'align_multiline_comment'         => array('comment_type' => 'phpdocs_only'),
        'array_indentation'               => TRUE,
        'array_syntax'                    => array('syntax' =>'long'),
        'binary_operator_spaces'          => array('default'    => 'no_space',
            'operators'                                         => array('=' => 'single_space', '==' => 'single_space',
                '==='                                                        => 'single_space', '.=' => 'single_space',
                '!='                                                         => 'single_space', '!==' => 'single_space', '=>' => 'align')),
        'blank_line_after_opening_tag'    => TRUE,
        'blank_line_before_statement'     => array('statements' => array('exit', 'return')),
        'braces'                          => array(  'allow_single_line_closure' => FALSE,
            'position_after_anonymous_constructs'                                => 'next',
            'position_after_control_structures'                                  => 'next',
            'position_after_functions_and_oop_constructs'                        => 'next'),
        'combine_consecutive_issets'                     => TRUE,
        'combine_consecutive_unsets'                     => TRUE,
        'concat_space'                                   => array('spacing' => 'none'),
        'constant_case'                                  => array('case' => 'upper'),
        'dir_constant'                                   => TRUE,
        'elseif'                                         => TRUE,
        'encoding'                                       => TRUE,
        'ereg_to_preg'                                   => TRUE,
        'fopen_flag_order'                               => TRUE,
        'fopen_flags'                                    => TRUE,
        'full_opening_tag'                               => TRUE,
        'implode_call'                                   => TRUE,
        'include'                                        => TRUE,
        'indentation_type'                               => TRUE,
        'line_ending'                                    => TRUE,
        'linebreak_after_opening_tag'                    => TRUE,
        'list_syntax'                                    => array('syntax' =>'long'),
        'logical_operators'                              => TRUE,
        'multiline_comment_opening_closing'              => TRUE,
        'no_alternative_syntax'                          => TRUE,
        'no_blank_lines_after_phpdoc'                    => TRUE,
        'no_closing_tag'                                 => TRUE,
        'no_empty_comment'                               => TRUE,
        'no_empty_phpdoc'                                => TRUE,
        'no_empty_statement'                             => TRUE,
        'no_multiline_whitespace_around_double_arrow'    => TRUE,
        'echo_tag_syntax'                                => array('format' =>'long'),
        'no_spaces_after_function_name'                  => TRUE,
        'no_spaces_around_offset'                        => array('positions' => array('inside', 'outside')),
        'no_superfluous_elseif'                          => TRUE,
        'no_trailing_whitespace'                         => TRUE,
        'no_unneeded_control_parentheses'                => TRUE,
        'no_unneeded_curly_braces'                       => TRUE,
        'no_unused_imports'                              => TRUE,
        //ESTA JODE LOS ELSES CUANDO EL IF TIENE UN EXIT 'no_useless_else'                                => TRUE,
        'no_useless_return'                              => TRUE,
        'no_whitespace_before_comma_in_array'            => TRUE,
        'no_whitespace_in_blank_line'                    => TRUE,
        'non_printable_character'                        => array('use_escape_sequences_in_strings' => TRUE),
        'normalize_index_brace'                          => TRUE,
        'phpdoc_add_missing_param_annotation'            => TRUE,
        //esta escojona las cabeceras cuando son complicadas 'phpdoc_align'                                   => array('align' => 'vertical'),
        'phpdoc_order'                                   => TRUE,
        'semicolon_after_instruction'                    => TRUE,
        'single_blank_line_at_eof'                       => TRUE,
        'single_line_comment_style'                      => TRUE,
        // Esta jode las cadenas 'single_quote'                                   => array('strings_containing_single_quote_chars' => FALSE),
        'space_after_semicolon'                          => TRUE,
        'standardize_not_equals'                         => TRUE,
        'switch_case_semicolon_to_colon'                 => TRUE,
        'switch_case_space'                              => TRUE
    ))
    ->setFinder($finder)
;

return $config;
