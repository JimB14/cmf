<?php

/*
 * Resource: Adam Khoury: https://youtu.be/nlCfOcETQUo
 * Uses GD Library functions
 */

function image_resize($target, $newcopy, $w, $h, $ext){

    // Get first two elements in the getimagesize array of target image (width and height), and store in variables ($w_orig & $h_orig)
    list($w_orig, $h_orig) = getimagesize($target);


  /* - - - to preserve the original aspect ratio & not distort image - - - */

    // calculate original width:height ratio & store in variable
    $scale_ratio = $w_orig / $h_orig;

    /* $w & $h values passed as parameters in image_resize(); they are NOT the original ($w_orig & $h_orig) dimensions
     * if scale ratio (w:h) of target dimensions is greater than the original,
     * target width must be changed proportionately using orignal scale ratio
     */
    if(($w / $h) > $scale_ratio)
    {
        $w = $h * $scale_ratio;
    }
    /* if scale ratio (w:h) of target dimensions is not greater than the original,
     * target height must be changed proportionately using orignal scale ratio
     */
    else
    {
        $h = $w / $scale_ratio;
    }

  /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

    // Initialize new variable
    $img = '';

    // Create new image based on image type and store in $img variable
    $ext = strtolower($ext);

    if($ext == 'gif')
    {
        $img = imagecreatefromgif($target);
    }
    elseif($ext == 'png')
    {
        $img = imagecreatefrompng($target);
    }
    else
    {
        $img = imagecreatefromjpeg($target);
    }

    // Make black rectangle of specified width and height
    $tci = imagecreatetruecolor($w, $h);

    // imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) 10 parameters!
    imagecopyresampled($tci, $img, 0, 0, 0, 0, $w, $h, $w_orig, $h_orig);

    // Output image to browser or file based on extension. Creates JPEG file from the given image (http://php.net/manual/en/function.imagejpeg.php)
    if($ext == 'gif')
    {
        imagegif($tci, $newcopy);
    }
    elseif($ext == 'jpg')
    {
        imagejpeg($tci, $newcopy, 80);
    }
    else
    {
        imagepng($tci, $newcopy, 80);
    }
}
