<?php
/**
 * PHP-CS-Fixer configuration for PrestaShop modules.
 *
 * Tuned to satisfy the PrestaShop Addons validator with zero Standards
 * and Licenses errors.
 *
 * Usage:
 *   php php-cs-fixer.phar fix --config=.php-cs-fixer.php --allow-risky=yes
 *
 * --allow-risky=yes is required: several rules needed by the validator
 * are marked risky by the fixer.
 *
 * See https://github.com/multiplicit-com/prestashop-module-validator-guide
 */

// ---------------------------------------------------------------------------
// UPDATE THIS BLOCK for your module
// ---------------------------------------------------------------------------
$header = <<<'EOF'
Your Module Display Name

@author    Your Name / Company
@copyright 2024 Your Name / Company
@license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
EOF;
// For paid/commercial modules replace the @license line with your own text.
// ---------------------------------------------------------------------------

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('vendor')
    ->exclude('node_modules')
    ->notName('php-cs-fixer.phar');

return (new PhpCsFixer\Config())
    ->setRules([
        // Base ruleset
        '@PSR12'                           => true,

        // Override PSR-12's blank line after <?php — the validator requires
        // <?php to be immediately followed by the licence comment, no blank line.
        'blank_line_after_opening_tag'     => false,

        // Licence header on every file.
        // 'separate' => 'none' means no blank lines around the header block,
        // which is what the validator requires.
        'header_comment'                   => [
            'header'       => $header,
            'comment_type' => 'PHPDoc',
            'location'     => 'after_open',
            'separate'     => 'none',
        ],

        // Syntax
        'array_syntax'                     => ['syntax' => 'short'],
        'single_quote'                     => true,
        'trailing_comma_in_multiline'      => true,
        'no_alternative_syntax'            => true,     // if(): / endif; → if() {}
        'echo_tag_syntax'                  => [
            'format'                        => 'long',  // <?= → <?php echo
            'shorten_simple_statements_only' => false,
        ],
        'semicolon_after_instruction'      => true,
        'standardize_increment'            => true,     // += 1 → ++

        // Imports
        'no_unused_imports'                => true,
        'ordered_imports'                  => ['sort_algorithm' => 'alpha'],

        // Spacing
        'concat_space'                     => ['spacing' => 'one'],
        'binary_operator_spaces'           => ['default' => 'single_space'],
        'cast_spaces'                      => ['space' => 'single'],
        'type_declaration_spaces'          => true,
        'single_space_around_construct'    => true,
        'operator_linebreak'               => ['position' => 'beginning'],

        // Blank lines
        'blank_line_after_namespace'       => true,
        'blank_line_before_statement'      => ['statements' => ['return', 'throw', 'try']],
        'no_extra_blank_lines'             => [
            'tokens' => ['extra', 'throw', 'use', 'use_trait', 'curly_brace_block'],
        ],

        // PHPDoc
        'no_blank_lines_after_phpdoc'      => true,
        'no_superfluous_phpdoc_tags'       => false,
        'phpdoc_separation'                => true,
        'phpdoc_align'                     => ['align' => 'left'],

        // Control structures / braces
        'no_unneeded_control_parentheses'  => [
            'statements' => [
                'break', 'clone', 'continue', 'echo_print',
                'negative_instanceof', 'others', 'return',
                'switch_case', 'yield', 'yield_from',
            ],
        ],
        'braces_position'                  => [
            'control_structures_opening_brace'          => 'same_line',
            'functions_opening_brace'                   => 'next_line_unless_newline_at_signature_end',
            'anonymous_functions_opening_brace'         => 'same_line',
            'classes_opening_brace'                     => 'next_line_unless_newline_at_signature_end',
            'anonymous_classes_opening_brace'           => 'next_line_unless_newline_at_signature_end',
            'allow_single_line_empty_anonymous_classes' => false,
            'allow_single_line_anonymous_functions'     => false,
        ],
        'statement_indentation'            => true,
    ])
    ->setFinder($finder)
    ->setUsingCache(true);
