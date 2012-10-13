<?php

namespace warhol\client;
use \warhol;

class rollup extends asset {

    public function init($name) {
        // files
        $files = array();

        foreach ($this->manifest()->get('files')->all() as $file) {
            if (array_key_exists($name, $file['rollups'])) {
                $files[$file['id']] = $file['rollups'][$name];
            }
        }

        // no files
        if (count($files) == 0 ) { return; }

        // krsort
        krsort($files);

        // files
        $this->files = $files;

    }

    public function getContent() {
        $content = array();

        foreach ($this->files as $fid => $x) {
            $f = new file($this->base, $fid);
            $content[] = $f->getContent();
        }

        return implode("\n\n", $content);

    }

}