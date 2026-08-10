<?php
namespace Bijan\Model;

use MJ\WPORM\Blueprint;
use MJ\WPORM\Model;

class PriceHistory extends Model {
	protected $table = 'bijan_price_history';
	protected $fillable = ['id', 'product_id', 'history'];
	protected $guarded = ['id'];
	protected $timestamps = false;
	protected $casts = [
		'product_id'	=> 'int',
		'history'		=> 'array',
	];

	public function up( Blueprint $table ) {
		$table->id();
		$table->integer( 'product_id' );
		$table->longText( 'history' );
		$this->schema = $table->toSql();
	}
}