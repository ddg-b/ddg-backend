<?php

namespace App\Service;

// from https://stackoverflow.com/questions/4847752/how-to-get-video-duration-dimension-and-size-in-php

use Exception;

class FFMpeg {

    private const FFMPEG_PATH = 'ffmpeg'; //or: /usr/bin/ffmpeg - depends on your installation
    private array $video_attributes = [];

    public function load($video): void
    {
        if (file_exists($video)) {

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $video); // check mime type
            finfo_close($finfo);

            if (preg_match('/video\/*/', $mime_type)) {
                $this->video_attributes = $this->get_video_attributes($video);
            } else {
                throw new Exception('File is not a video.');
            }
        } else {
            throw new Exception('File does not exist.');
        }
    }

    public function get_infos(): array
    {
        return $this->video_attributes;
    }

    private function get_video_attributes($video): array
    {
        $command = self::FFMPEG_PATH . ' -i ' . $video . ' -vstats 2>&1';
        $output = shell_exec($command);

        $regex_sizes = "/Video: ([^,]*), ([^,]*), ([0-9]{1,4})x([0-9]{1,4})/";
        if (preg_match($regex_sizes, $output, $regs)) {
            $codec = $regs [1] ? $regs [1] : null;
            $width = $regs [3] ? $regs [3] : null;
            $height = $regs [4] ? $regs [4] : null;
        }

        $regex_duration = "/Duration: ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2}).([0-9]{1,2})/";
        if (preg_match($regex_duration, $output, $regs)) {
            $hours = $regs [1] ? $regs [1] : null;
            $mins = $regs [2] ? $regs [2] : null;
            $secs = $regs [3] ? $regs [3] : null;
            $ms = $regs [4] ? $regs [4] : null;
        }

        return [
            'codec' => $codec,
            'width' => $width,
            'height' => $height,
            'hours' => $hours,
            'mins' => $mins,
            'secs' => $secs,
            'ms' => $ms
        ];
    }
}
