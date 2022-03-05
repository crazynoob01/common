<?php

declare(strict_types=1);

use PhpCsFixer\Config;

return (new Config())->setRiskyAllowed(true)
    ->setRules(
        [
            '@PSR1'                                       => true,
            '@PSR2'                                       => true,
            '@Symfony'                                    => true,
            '@DoctrineAnnotation'                         => true,
            'align_multiline_comment'                     => ['comment_type' => 'phpdocs_only'],
            'array_indentation'                           => true,
            'array_syntax'                                => ['syntax' => 'short'],
            'binary_operator_spaces'                      => [
                'operators' => [
                    '=>' => 'align_single_space',
                    '='  => 'align_single_space',
                ],
            ],
            'blank_line_before_statement'                 => [
                'statements' => [
                    'break',
                    'continue',
                    'declare',
                    'return',
                    'throw',
                    'try',
                ],
            ],
            'braces'                                      => [
                'allow_single_line_closure'                   => false,
                'position_after_anonymous_constructs'         => 'same',
                'position_after_control_structures'           => 'same',
                'position_after_functions_and_oop_constructs' => 'same',
            ],
            'class_attributes_separation'                 => [
                'elements' => [
                    'const'    => 'one',
                    'method'   => 'one',
                    'property' => 'one',
                ],
            ],
            'combine_consecutive_issets'                  => true,
            'combine_consecutive_unsets'                  => true,
            'compact_nullable_typehint'                   => true,
            'concat_space'                                => ['spacing' => 'one'],
            'explicit_indirect_variable'                  => true,
            'explicit_string_variable'                    => true,
            'line_ending'                                 => false,
            'general_phpdoc_annotation_remove'            => ['annotations' => ['package']],
            'list_syntax'                                 => ['syntax' => 'short'],
            'increment_style'                             => ['style' => 'post'],
            'no_alternative_syntax'                       => true,
            'no_blank_lines_after_class_opening'          => true,
            'no_blank_lines_after_phpdoc'                 => true,
            'no_empty_phpdoc'                             => true,
            'blank_line_after_namespace'                  => true,
            'phpdoc_trim'                                 => true,
            'no_empty_comment'                            => true,
            'no_empty_statement'                          => true,
            'no_closing_tag'                              => true,
            'method_chaining_indentation'                 => true,
            'no_useless_return'                           => true,
            'no_useless_else'                             => true,
            'not_operator_with_space'                     => true,
            'phpdoc_order'                                => true,
            'phpdoc_types_order'                          => ['null_adjustment' => 'always_last'],
            'phpdoc_add_missing_param_annotation'         => ['only_untyped' => true],
            'ternary_to_null_coalescing'                  => true,
            'strict_param'                                => true,
            // risky elements
            'void_return'                                 => true,
            'comment_to_phpdoc'                           => true,
            'no_alias_functions'                          => true,
            'is_null'                                     => true,
            'strict_comparison'                           => true,
            // symfony overrides
            'no_multiline_whitespace_around_double_arrow' => true,
            'yoda_style'                                  => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
            'phpdoc_annotation_without_dot'               => false,
            'fully_qualified_strict_types'                => true,
            'php_unit_fqcn_annotation'                    => true,
            'declare_strict_types'                        => true,
            'single_blank_line_before_namespace'          => true,
            'single_trait_insert_per_statement'           => false,
            'ordered_class_elements'                      => true,
            'no_superfluous_phpdoc_tags'                  => [
                'allow_mixed'       => true,
                'remove_inheritdoc' => true,
            ],
            'phpdoc_to_comment'                           => false,
            'types_spaces'                                => ['space' => 'single'],
        ]
    );
