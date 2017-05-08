<?php

//abstract class Log {
//}
    
class LOG{
	static protected $colors = [
	    "clear"   => "0",
	    "bold"    => "1",
	    "black"   => "0;30",
	    "dgray"   => "1;30",
	    "blue"    => "0;34",
	    "lblue"   => "1;34",
	    "green"   => "0;32",
	    "lgreen"  => "1;32",
	    "cyan"    => "0;36",
	    "lcyan"   => "1;36",
	    "red"     => "0;31",
	    "lred"    => "1;31",
	    "purple"  => "0;35",
	    "lpurple" => "1;35",
	    "brown"   => "0;33",
	    "yellow"  => "1;33",
	    "lgray"   => "0;37",
	    "white"   => "1;37"
	];
	static protected $config = [
	    "colors" => [
	        "debug" => [
	            "header"  => "",
	            "message" => "dgray"
	        ],
	        "info" => [
	            "header"  => "",
	            "message" => "lgray",
	        ],
	        "notice" => [
	            "header"  => "",
	            "message" => "yellow",
	        ],
	        "success" => [
	            "header"  => "",
	            "message" => "lgreen",
	        ],
	        "warning" => [
	            "header"  => "",
	            "message" => "lblue",
	        ],
	        "error" => [
	            "header"  => "",
	            "message" => "lred",
	        ],
	    ],
	    "path"   => "log/",
	    "name"   => '',
	    "header" => [
	        "format" => "[%H:%i:%s] ",
	        "color" =>  "green"
	    ],
	    "spacer" => "+-"
	];
	static protected $path = null;
	static protected $files = [];
	static public function error($msg, $section = null) {
	    static::message(__FUNCTION__, $msg, $section);
	}
	static public function warning($msg, $section = null) {
	    static::message(__FUNCTION__, $msg, $section);
	}
	static public function notice($msg, $section = null) {
	    static::message(__FUNCTION__, $msg, $section);
	}
	static public function info($msg, $section = null) {
	    static::message(__FUNCTION__, $msg, $section);
	}
	static public function success($msg, $section = null) {
	    static::message(__FUNCTION__, $msg, $section);
	}
	static public function debug($msg, $section = null) {
	    static::message(__FUNCTION__, $msg, $section);
	}
	static protected function open($path, $section) {
	    static::close($section);
	    umask(0);
	    if (!is_dir($path) && !@mkdir($path, 0777, true)) {
	        //Между двумя предыдущими проверками проходит время,
	        //директория могла быть создана параллельным процессом
	        if (!is_dir($path)) {
	            throw new \Exception("Could not create directory '$path' for log");
	        }
	    }
	    static::$files[ $section ] = [
	        "handle" =>  fopen($path . "$section.log", "a"),
	        "path" => $path
	    ];
	}
	static protected function close($section) {
	    if (isset(static::$files[ $section ]) && is_resource(static::$files[ $section ][ "handle" ])) {
	        fclose(static::$files[ $section ][ "handle" ]);
	    }
	}
	static protected function message($level, $msg, $section) {
	    //$section = $section ? $section : static::$config[ "name" ];
	    $section = $section ? $section : date("Ymd");
	    $path = static::getFilePath($section);
	    if (!isset(static::$files[ $section ]) || static::$files[ $section ][ "path" ] !== $path) {
	        static::open($path, $section);
	    }
	    fwrite(
	        static::$files[ $section ][ "handle" ],
	        static::formatString(
	            "<" . static::$config["header"]["color"] . ">" .
	            static::$config[ "header" ][ "format" ] .
	            "</" . static::$config["header"]["color"] . ">" .
	            "<" . static::$config["colors"][ $level ][ "message" ] . ">" .
	            static::$config["spacer"] .
	            $msg . "\n" .
	            "</" . static::$config["colors"][ $level ][ "message" ] . ">",
	            $level
	        )
	    );
	}
	static protected function getFilePath($section) {
	    return static::applyDateTime(static::$config["path"]);
	}
	static protected function applyDateTime($string) {
	    return preg_replace_callback("#%([a-z])#i", function($match) {
	        return date($match[1]);
	    }, $string);
	}
	static public function formatString($string, $level) {
	    $string = static::applyDateTime($string);
	    $string = str_replace([
	        "^PID",
	        "^MEM",
	        "^MCT",
	        "^LVL"
	    ], [
	        str_pad(getmypid(), 6, " ", STR_PAD_LEFT),
	        memory_get_usage(true),
	        substr((float)microtime(), 2, 4),
	        ucfirst($level{0})
	    ], $string);
	    $colorStack = [
	        "clear"
	    ];
	    $string = preg_replace_callback("#</?(" . implode("|", array_keys(static::$colors)) . ")>#", function($match) use(&$colorStack) {
	        if ($match[0]{1} == "/") {
	            array_shift($colorStack);
	        } else {
	            array_unshift($colorStack, $match[1]);
	        }
	        return static::colorToCode($colorStack[ 0 ]);
	    }, $string);
	    return $string . static::colorToCode("clear");
	}
	static public function config(array $config) {
	    static::$config = array_replace_recursive(static::$config, $config);
	}
	static function colorToCode($color) {
	    return "\033[" . static::$colors[ $color ] . "m";
	}
}   



