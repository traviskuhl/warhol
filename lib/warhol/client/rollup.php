<?php

namespace warhol\client;
use \warhol;

class rollup extends asset {

    private $files = array();

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
        foreach ($files as $fid => $x) {        
            $this->files[$fid] = $this->file('fid', $fid);
        }

    }

    public function getFiles() {
        return $this->files;
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