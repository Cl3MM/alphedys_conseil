<?php
/**
 * Generic Captcha Plugin for Joomla! 1.5
 * @author bigodines
 * <b>info</b>: http://www.bigodines.com / or (in portuguese) http://www.joomla.com.br
 * 
 * 
 * I'm using the same plugin structure as 'OSTCaptcha' by CoolAcid so you can easily replace one to the other.
 * If you can't find the features you want here, maybe you can try his: 
 * http://forum.joomla.org/index.php/topic,218637.0.html
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//include_once "GIFEncoder.class.php";
//include_once "Functions.php";

Define ( 'MUL', 0.017453292519943295 );
Define ( 'ANIM_FRAMES', 8 );
Define ( 'ANIM_DELAYS', 10 );

define('r','r');
define('g','g');
define('b','b');

/*
:::::::::::::::::::::::::::::::::::::::::::::::::
::                                             ::
::                                             ::
::                 Class Flag                  ::
::                                             ::
::                                             ::
:::::::::::::::::::::::::::::::::::::::::::::::::
*/

Class Flag {
	var $kx, $ky, $kz;
	var $ro, $ow, $oh;
	var $px, $of, $im;
	/*
	:::::::::::::::::::::::::::::::::::::::::::::
	::                                         ::
	::                 F L A G                 ::
	::                                         ::
	:::::::::::::::::::::::::::::::::::::::::::::
	*/
	function Flag ( $w, $h, $o ) {
		$this->ro = new Rotator ( );
		$this->kx = 0.029999999999999999;
		$this->ky = 0.01;
		$this->kz = 8;
		$this->ow = $w;
		$this->oh = $h;
		$this->px = $o;
		$this->im = Array ( r=>255, g=>255, b=>255 );
	}
	/*
	:::::::::::::::::::::::::::::::::::::::::::::
	::                                         ::
	::                F V A L U E              ::
	::                                         ::
	:::::::::::::::::::::::::::::::::::::::::::::
	*/
	function FValue ( $i, $c ) {
		$ow = $this->ow;
		$oh	= $this->oh;

		$this->ro->SetRotation ( 0, 15, 0 );

		for ( $j = 0; $j < $this->ow; $j++ ) {
			for ( $k = 0; $k < $this->oh; $k++ ) {
				$C00 = Flag::ZValue ( $j, $k, $i );
				$C01 = $this->ro->RotatePoints ( Array ( $j, $k, $C00 ) );
				$C02 = $k * $this->ow + $j;
                $this->of [ $C02 ] = new FramePoint (
                							$C01 [ 0 ],
                							$C01 [ 1 ] - ( $this->oh / 2 ),
                							$C01 [ 2 ],
                							$this->px [ $C02 ]
                );
                $C03 = $this->of [ $C02 ]->c [ r ];
                $C04 = $this->of [ $C02 ]->c [ g ];
                $C05 = $this->of [ $C02 ]->c [ b ];
                $C06 = ( $C00 + 50 ) / 100;
                $C06 = ( $C06 + $C06 ) - 1.0;
                if ( $C06 > 0.0 ) {
					$C03 = ( $C03 + ( 255 - $C03 ) * $C06 );
					$C04 = ( $C04 + ( 255 - $C04 ) * $C06 );
					$C05 = ( $C05 + ( 255 - $C05 ) * $C06 );
                }
                else {
					if ( $C06 < 0.0 ) {
						$C03 = ( $C03 - $C03 * $C06 * -1 );
						$C04 = ( $C04 - $C04 * $C06 * -1 );
						$C05 = ( $C05 - $C05 * $C06 * -1 );
					}
				}
				$this->of [ $C02 ]->c [ r ]	= $C03;
				$this->of [ $C02 ]->c [ g ]	= $C04;
				$this->of [ $C02 ]->c [ b ]	= $C05;
			}
		}

		$w = $ow + 4;
		$h = $oh + 4;

		for ( $i = 0; $i < ($w * $h); $i++ ) {
        	$this->im [ $i ] [ r ] = $c [ r ];
        	$this->im [ $i ] [ g ] = $c [ g ];
        	$this->im [ $i ] [ b ] = $c [ b ];
		}

		for ( $i = 0; $i < count ( $this->of ); $i++ ) {
			$C07 = $this->of [ $i ]->PointX ( );
			$C08 = $this->of [ $i ]->PointY ( ) + round ( $this->oh / 2 );

			if ( $C07 < $this->ow - 1 && $C08 < $this->oh - 1 && $C07 > 0 && $C08 > 0 ) {
				$this->im [ $C08 * $this->ow + $C07 ] [ r ] = $this->of [ $i ]->c [ r ];
				$this->im [ $C08 * $this->ow + $C07 ] [ g ] = $this->of [ $i ]->c [ g ];
				$this->im [ $C08 * $this->ow + $C07 ] [ b ] = $this->of [ $i ]->c [ b ];
				$this->im [ $C08 * $this->ow + $C07 + 1 ] [ r ] = $this->of [ $i ]->c [ r ];
				$this->im [ $C08 * $this->ow + $C07 + 1 ] [ g ] = $this->of [ $i ]->c [ g ];
				$this->im [ $C08 * $this->ow + $C07 + 1 ] [ b ] = $this->of [ $i ]->c [ b ];
				$this->im [ ( $C08 + 1 ) * $this->ow + $C07 ] [ r ] = $this->of [ $i ]->c [ r ];
				$this->im [ ( $C08 + 1 ) * $this->ow + $C07 ] [ g ] = $this->of [ $i ]->c [ g ];
				$this->im [ ( $C08 + 1 ) * $this->ow + $C07 ] [ b ] = $this->of [ $i ]->c [ b ];

				if (
						$this->im [ ( $C08 * $this->ow + $C07 ) - 1 ] [ r ] == $c [ r ] &&
						$this->im [ ( $C08 * $this->ow + $C07 ) - 1 ] [ g ] == $c [ g ] &&
						$this->im [ ( $C08 * $this->ow + $C07 ) - 1 ] [ b ] == $c [ b ]
				) {
					$this->im [ ( $C08 * $this->ow + $C07 ) - 1 ] [ r ] = $this->of [ $i ]->c [ r ];
					$this->im [ ( $C08 * $this->ow + $C07 ) - 1 ] [ g ] = $this->of [ $i ]->c [ g ];
					$this->im [ ( $C08 * $this->ow + $C07 ) - 1 ] [ b ] = $this->of [ $i ]->c [ b ];
				}
				if (
						$this->im [ ( $C08 - 1 ) * $this->ow + $C07 ] [ r ] == $c [ r ] &&
						$this->im [ ( $C08 - 1 ) * $this->ow + $C07 ] [ g ] == $c [ g ] &&
						$this->im [ ( $C08 - 1 ) * $this->ow + $C07 ] [ b ] == $c [ b ]
				) {
					$this->im [ ( $C08 - 1 ) * $this->ow + $C07 ] [ r ] = $this->of [ $i ]->c [ r ];
					$this->im [ ( $C08 - 1 ) * $this->ow + $C07 ] [ g ] = $this->of [ $i ]->c [ g ];
					$this->im [ ( $C08 - 1 ) * $this->ow + $C07 ] [ b ] = $this->of [ $i ]->c [ b ];
				}
			}
		}
	}
	/*
	:::::::::::::::::::::::::::::::::::::::::::::
	::                                         ::
	::               Z V A L U E               ::
	::                                         ::
	:::::::::::::::::::::::::::::::::::::::::::::
	*/
	function ZValue ( $i, $j, $k ) {
		$this->kx = 0.029999999999999999 + ( 11 * $i ) / ( $this->ow * $this->ow );
		$this->ky = 0.01 + ( 4.4000000000000004 * $i ) / ( $this->ow * $this->ow );
		if ( $i < ( $this->ow / 6 ) ) {
			$this->kz = 1.0 + $i * ( 8 / ( $this->ow / 6 ) );
		}
		else {
			$this->kz = 8;
		}

		$f = ( ( $k * 0.017453292519943295 - $this->kx * $i ) + $this->ky * $j );
		$l = intval ( $this->kz * sin ( $f ) + 0.5 );

		return ( $l );
	}
	/*
	:::::::::::::::::::::::::::::::::::::::::::::
	::                                         ::
	::          A N I M A T E D O U T          ::
	::                                         ::
	:::::::::::::::::::::::::::::::::::::::::::::
	*/
	function AnimatedOut ( ) {
		$bg = Array ( r=>134, g=>170, b=>191 );
		$im = imageCreateTrueColor ( $this->ow, $this->oh );

		for ( $i = 0; $i < ANIM_FRAMES; $i++ ) {
			Flag::FValue ( ( 360 / ANIM_FRAMES ) * $i, $bg );
			for ( $x = 0; $x < $this->ow; $x++ ) {
				for ( $y = 0; $y < $this->oh; $y++ ) {
                    $p = $y * $this->ow + $x;
                    imageSetPixel (
                    				$im, $x, $y,
									( $this->im [ $p ] [ r ] << 16 ) |
									( $this->im [ $p ] [ g ] <<  8 ) |
									( $this->im [ $p ] [ b ] <<  0 )
                    );
				}
			}
			ob_start ( );
			imageGif ( $im );
			$frames [ ] = ob_get_contents ( );
			$framed [ ] = 10;
			ob_end_clean ( );
		}
		imageDestroy ( $im );
		$gif = new GIFEncoder	(
									$frames,
									$framed,
									0,
									2,
									-1, -1, -1,
									"bin"
		);
		return ( $gif->GetAnimation ( ) );
	}
}

/*
:::::::::::::::::::::::::::::::::::::::::::::::::
::                                             ::
::                                             ::
::         Class Rotator Extends Flag          ::
::                                             ::
::                                             ::
:::::::::::::::::::::::::::::::::::::::::::::::::
*/

Class Rotator extends Flag {
	var $ct;
	var $st;
	var $cp;
	var $sp;
	var $cg;
	var $sg;
	/*
	:::::::::::::::::::::::::::::::::::::::::::::
	::                                         ::
	::               R O T A T O R             ::
	::                                         ::
	:::::::::::::::::::::::::::::::::::::::::::::
	*/
	function Rotator ( ) {
		$this->ct = 1.0;
		$this->cp = 1.0;
		$this->cg = 1.0;
	}
	/*
	:::::::::::::::::::::::::::::::::::::::::::::
	::                                         ::
	::          S E T R O T A T I O N          ::
	::                                         ::
	:::::::::::::::::::::::::::::::::::::::::::::
	*/
	function SetRotation ( $i, $j, $k ) {
		$this->ct = cos ( $i * MUL );
		$this->st = sin ( $i * MUL );
		$this->cp = cos ( $j * MUL );
		$this->sp = sin ( $j * MUL );
		$this->cg = cos ( $k * MUL );
		$this->sg = sin ( $k * MUL );
	}
	/*
	:::::::::::::::::::::::::::::::::::::::::::::
	::                                         ::
	::         R O T A T E P O I N T S         ::
	::                                         ::
	:::::::::::::::::::::::::::::::::::::::::::::
	*/
	function RotatePoints ( $p ) {
		$p [ 0 ] = round (
					$p [ 0 ] *
					( $this->cp * $this->cg - $this->st * $this->sp * $this->sg ) +
					$p [ 1 ] * ( $this->cp * $this->sg + $this->st * $this->sp * $this->cg ) +
					$p [ 2 ] * - ( $this->ct * $this->sp )
		);
		$p [ 1 ] = round (
					$p [ 0 ] * // $ai
					- ( $this->ct * $this->sg ) +
					$p [ 1 ] * ( $this->ct * $this->cg ) +
					$p [ 2 ] * $this->st
		);
		$p [ 2 ] = round (
					$p [ 0 ] *
					( $this->sp * $this->cg + $this->st * $this->cp * $this->sg ) +
					$p [ 1 ] * ( $this->sp * $this->sg - $this->st * $this->cp * $this->cg ) +
					$p [ 2 ] * ( $this->ct * $this->cp )
		);

		return ( $p );
	}
}

/*
:::::::::::::::::::::::::::::::::::::::::::::::::
::                                             ::
::                                             ::
::       Class FramePoint Extends Flag         ::
::                                             ::
::                                             ::
:::::::::::::::::::::::::::::::::::::::::::::::::
*/

Class FramePoint extends Flag {
	var $x;
	var $y;
	var $z;
	var $c;
	var $d;
	/*
	:::::::::::::::::::::::::::::::::::::::::::::
	::                                         ::
	::           F R A M E P O I N T           ::
	::                                         ::
	:::::::::::::::::::::::::::::::::::::::::::::
	*/
	function FramePoint ( $i, $j, $k, $l ) {
		$this->d = 300;
		$this->x = $i;
		$this->y = $j;
		$this->z = $k;
		$this->c = $l;
	}
	/*
	:::::::::::::::::::::::::::::::::::::::::::::
	::                                         ::
	::               P O I N T X               ::
	::                                         ::
	:::::::::::::::::::::::::::::::::::::::::::::
	*/
	function PointX ( ) {
		if ( $this->z + $this->d == 0 ) {
			return $this->x * $this->d;
		}
		else {
			return intval ( ( $this->x * $this->d ) / ( $this->z + $this->d ) + 0.5 );
		}
	}
	/*
	:::::::::::::::::::::::::::::::::::::::::::::
	::                                         ::
	::               P O I N T Y               ::
	::                                         ::
	:::::::::::::::::::::::::::::::::::::::::::::
	*/
	function PointY ( ) {
		if ( $this->z + $this->d == 0 ) {
			return $this->y * $this->d;
		}
		else {
			return intval ( ( $this->y * $this->d ) / ( $this->z + $this->d ) + 0.5 );
		}
	}
}

/* :::::::::::::::::::::::::::::::::::::::::::::: */
function GetPixels ( $im ) {
	$sx = imageSx ( $im );
	$sy = imageSy ( $im );

	for ( $i = 0; $i < $sx; $i++ ) {
		for ( $j = 0; $j < $sy; $j++ ) {
			$px = imageColorsForIndex ( $im, imageColorAt ( $im, $i, $j ) );
			$co [ $j * $sx + $i ] = Array (
						'r'=>$px [ 'red'   ],
						'g'=>$px [ 'green' ],
						'b'=>$px [ 'blue'  ]
			);
		}
	}

	return ( $co );
}

Set_Time_Limit ( 0 );			
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
header ( 'Content-type:image/gif' );		
	

if ( $dh = opendir (JPATH_PLUGINS.DS.'system'.DS.'Captcha04'.DS."fonts".DS ) ) {
	while ( false !== ( $dat = readdir ( $dh ) ) ) {
		if ( $dat != "." && $dat != ".." ) {
			$fonts [ ] = JPATH_PLUGINS.DS.'system'.DS.'Captcha04'.DS."fonts".DS."$dat";
		}
	}
	closedir ( $dh );
}

if ( $uid) {
	$UID = explode ( ";", $uid );
	$FNT = $fonts [ rand ( 0, ( count ( $fonts ) ) - 1 ) ];
	$STR = md5_decrypt ( $UID [ 1 ] );

	$dm = array ( );
	$dm = imageTTFBbox ( 22, 0, $FNT, $STR );
	$im = imageCreateTrueColor ( $dm [ 4 ] + 15, abs ( $dm [ 5 ] ) + 15 );
	$bg = ImageColorAllocate( $im,  106,  150,  176 );
	$fg = ImageColorAllocate( $im,  255,  255,  255 );
	imageFill ( $im, 0, 0, $bg );
	imagettftext ( $im, 22, 0, 7, abs ( $dm [ 5 ] ) + 6, $fg, $FNT, $STR );
	$sx = imageSx ( $im );
	$sy = imageSy ( $im );
	$an = new Flag ( $sx, $sy, GetPixels ( $im ) );
	echo $an->AnimatedOut ( );
}
?>
