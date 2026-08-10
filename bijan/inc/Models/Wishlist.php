<?php
namespace Bijan\Model;

use MJ\WPORM\Blueprint;
use MJ\WPORM\Model;

class Wishlist extends Model {
	protected $table = 'bijan_wishlist';
	protected $fillable = ['id', 'product_id', 'user_id', 'created_at'];
	protected $guarded = ['id'];
	protected $timestamps = false;
	protected $casts = [
		'product_id'	=> 'int',
		'user_id'		=> 'int',
		'created_at'	=> 'datetime'
	];

	public function up( Blueprint $table ) {
		$table->id();
		$table->integer( 'product_id' );
		$table->integer( 'user_id' );
		$table->timestamp( 'created_at' );
		$this->schema = $table->toSql();
	}
}