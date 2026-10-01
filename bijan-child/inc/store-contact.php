<?php
/** Shared support details; the phone remains editable in Bijan footer settings. */
defined( 'ABSPATH' ) || exit;
function clz_store_contact_details() {
	$options = get_option( 'bijan', array() );
	$phone = $options['footer_contact_info'][0] ?? '09981687867';
	$phone = is_string( $phone ) ? preg_replace( '/\D+/', '', strtr( $phone, array( '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9' ) ) ) : '';
	if ( preg_match( '/^989\d{9}$/D', $phone ) ) { $phone = '0' . substr( $phone, 2 ); }
	if ( ! preg_match( '/^09\d{9}$/D', $phone ) ) { $phone = '09981687867'; }
	return array( 'phone' => $phone, 'tel' => '+98' . substr( $phone, 1 ), 'hours' => 'هر روز، ساعت ۸ تا ۲۲', 'chat' => 'https://www.goftino.com/c/h6q2ir', 'telegram' => 'https://t.me/real_call_margin', 'bale' => 'https://ble.ir/real_call_margin' );
}
function clz_contact_call_shortcode() {
	$contact = clz_store_contact_details();
	$label = substr( $contact['phone'], 0, 4 ) . ' ' . substr( $contact['phone'], 4, 3 ) . ' ' . substr( $contact['phone'], 7 );
	$label = strtr( $label, array( '0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹' ) );
	ob_start(); ?>
	<section class="clz-contact-channel" aria-labelledby="clz-contact-call"><h2 id="clz-contact-call">تماس و پیامک</h2><p>برای تماس و ارسال پیامک، از شماره زیر استفاده کنید.</p><p><a class="clz-info-phone" href="<?php echo esc_url( 'tel:' . $contact['tel'] ); ?>" dir="ltr"><?php echo esc_html( $label ); ?></a></p><p class="clz-contact-response">زمان معمول پاسخ در ساعات کاری: تا ۱ ساعت</p><div class="clz-info-actions"><a class="clz-info-button clz-info-button--outline" href="<?php echo esc_url( 'tel:' . $contact['tel'] ); ?>">تماس بگیرید</a><a class="clz-info-button clz-info-button--outline" href="<?php echo esc_url( 'sms:' . $contact['tel'] ); ?>">پیامک ارسال کنید</a></div></section>
	<?php return ob_get_clean();
}
add_shortcode( 'clz_contact_call', 'clz_contact_call_shortcode' );
