<?php

require __DIR__ . '/vendor/autoload.php';

use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use PhpParser\NodeTraverser;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

$prettyPrinter = new PrettyPrinter\Standard;

// Somewhere to store results
$parse_results = [];
$errors = [];
class TranslateFunctionVisitor extends NodeVisitorAbstract
{
    public function leaveNode(Node $node) {
        if ($node instanceof Node\Scalar\String_) {
            $node->value = 'foo';
        }
        if ( is_translate_function( $node ) )
         {
			$this->check_translate_call( $node );
        }
    }

    // Override this call in a subclass to implement different rules
    public function check_translate_call( $node ) {
    }
}

/* The visitor (a rule) */
class NoVariablesCheck extends TranslateFunctionVisitor {
	static $error_string = 'Translate arguments must be plain strings';
	function check_translate_call( $node ) {
		global $errors, $prettyPrinter;
		foreach ( $node->args as $arg ) {
			if( ! is_literal( $arg ) ) {
				$errors[] = [
					'line' => $node->getLine(),
					// 'node' => $node->attributes,
					'code' => $prettyPrinter->prettyPrint( [$node] ),
					'error' => self::$error_string
				];
			}
		}
	}

}


// Node Util functions
function name_of( $node ){
	return $node->name->parts[0];
}

function is_literal( $node ) {
	if( 'PhpParser\Node\Arg' === get_class( $node ) ) {
		$node = $node->value;
	} else {
		echo( '$node = ' . var_export( $node, true ) . PHP_EOL ); //DEBUG
	}
	return is_subclass_of( $node, 'PhpParser\Node\Scalar' );
}

function is_translate_function( $node ) {
	static $translate_function_names = array(
		'__',
		'_x',
		'_n',
	);
	return $node instanceof Node\Expr\FuncCall &&
		in_array( $node->name->parts[0], $translate_function_names );
}

function check_file( $filename ) {
	$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP5);
	$traverser = new NodeTraverser;
	$traverser->addVisitor(new NoVariablesCheck);

	try {
		$code = file_get_contents( $filename );
		$stmts = $parser->parse($code);
		// $stmts is an array of statement nodes
		 $stmts = $traverser->traverse($stmts);
	} catch (Error $e) {
	echo 'Parse Error: ', $e->getMessage();
	}
}

// TDOD: some arg handling
check_file( $argv[1] );

echo "filename: " . $argv[1] . "\n";
echo( '$errors = ' . var_export( $errors, true ) . PHP_EOL ); //DEBUG
