<?php

namespace SearchRegex;

class Action_Modify_Integer extends Action_Modify_Column {
	private $value = null;

	public function __construct( $option, Schema_Column $schema ) {
		parent::__construct( $option, $schema );

		if ( isset( $option['value'] ) ) {
			$this->value = intval( $option['value'], 10 );
		}

		$this->operation = 'set';
		if ( isset( $option['operation'] ) && in_array( $option['operation'], [ 'set', 'increment', 'decrement' ], true ) ) {
			$this->operation = $option['operation'];
		}
	}

	public function to_json() {
		return array_merge(
			parent::to_json(),
			[
				'operation' => $this->operation,
				'value' => $this->value,
			]
		);
	}

	public function perform( $row_id, $row_value, Search_Source $source, Match_Column $column, array $raw ) {
		// Go through contexts and find the matching action that modifies it
		if ( count( $column->get_contexts() ) === 1 ) {
			$context = $column->get_contexts()[0];
			$value = $context->get_value();

			if ( $this->operation === 'increment' ) {
				$replacement = intval( $value, 10 ) + $this->value;
				$context = new Match_Context_Replace( $value );
				$context->set_replacement( "{$replacement}", $source->convert_result_value( $this->schema, $replacement ) );
			} elseif ( $this->operation === 'decrement' ) {
				$replacement = max( 0, intval( $value, 10 ) - $this->value );
				$context = new Match_Context_Replace( $value );
				$context->set_replacement( "{$replacement}", $source->convert_result_value( $this->schema, $replacement ) );
			} elseif ( intval( $this->value, 10 ) !== intval( $value, 10 ) ) {
				$context = new Match_Context_Replace( $value );
				$context->set_replacement( "{$this->value}", $source->convert_result_value( $this->schema, $this->value ) );
			}

			$column->set_contexts( [ $context ] );
		}

		return $column;
	}
}
