<?php
list($session_id, $ext) = explode('-', $_GET['session_id']);

if( session_id( $session_id ) == '' ) {
    session_start();
    if( isset( $_SESSION['extids'][$ext] ) ) {
    	unset( $_SESSION['extids'][$ext] );
    }
}

$chars = 'abcdefghijklmnopqrstuvwxyz1234567890';
$random_str = '';
$size = 6;

for( $i = 1; $i <= $size; $i++ ){
	$random_int = rand( 1, strlen( $chars ) );
	$random_str .= substr( $chars, $random_int - 1, 1 );
}
$_SESSION['captcha'] = $random_str;

$colors =  array ('0' => '145','1' => '204','2' => '177','3' => '184','4' => '199','5' => '255');
$height = 76;
$width = 382;
$font_size = 33;
$font = $_SERVER['DOCUMENT_ROOT'].'/system/modules/form_builder/view/museo_slab_500-webfont.ttf';
$image = imagecreatetruecolor( $width, $height );
$bg = imagecolorallocate($image, 255, 255, 255);
imagefill($image, 10, 10, $bg);
   
for ($x=0; $x < $width; $x++)
{
    for ($y=0; $y < $height; $y++)
    {
        $random = mt_rand(0 , 5);
        $temp_color = imagecolorallocate($image, $colors["$random"], $colors["$random"], $colors["$random"]);
        imagesetpixel( $image, $x, $y , $temp_color );
    }
}

$char_color = imagecolorallocatealpha($image, 0, 0, 0, 90);

$char = "";

for( $i = 0; $i < $size; $i++ ){
	$char = substr( $random_str, $i, 1 );
	$random_x = mt_rand( 50 * ( $i + 1 ), 50 * ( $i + 1 ) );
	$random_y = mt_rand( 45 , 60 );
	$random_angle = mt_rand(-20 , 20);
	imagettftext($image, $font_size, $random_angle, $random_x, $random_y, $char_color, $font, $char);
}

imagepng($image);
?>