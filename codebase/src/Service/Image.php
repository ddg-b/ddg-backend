<?php

namespace App\Service;

use Exception;

class Image
{
    function imagethumb($image_src, $dest_folder, $dest_filename, $max_size = 300): array
    {
        $file_details = getimagesize($image_src);
        if (!$file_details) {
            throw new \Exception($image_src, 8);
        }

        $width = $file_details[0];
        $height = $file_details[1];
        $ratio = $width / $height;
        $src_x = $src_y = 0;
        $src_w = $width;
        $src_h = $height;

        if ($ratio > 1) {
            // Landscape
            $new_width = $max_size;
            $new_height = round($max_size / $ratio);
        } else {
            // Portrait
            $new_height = $max_size;
            $new_width = round($max_size * $ratio);
        }

        $image_src = imagecreatefromgif($image_src);
        $new_image = imagecreatetruecolor($new_width, $new_height);


        // Transparent Gif
        if (imagecolortransparent($image_src) >= 0) {
            $transparent_index = imagecolortransparent($image_src);
            try {
                $transparent_color = imagecolorsforindex($image_src, $transparent_index);
                $transparent_index = imagecolorallocate($new_image, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                imagefill($new_image, 0, 0, $transparent_index);
                imagecolortransparent($new_image, $transparent_index);
            } catch (\ValueError ) {
            }
        }

        // Image resizing
        imagecopyresampled(
            $new_image, $image_src,
            0, 0, $src_x, $src_y,
            $new_width, $new_height, $src_w, $src_h
        );

        // Image saving
        $out = imagejpeg($new_image, $dest_folder.'/'.$dest_filename);
        if ($out === false) {
            throw new Exception($image_src);
        }

        // Free memory
        imagedestroy($new_image);

        return [
            'width' => $width,
            'height' => $height,
        ];
    }
}