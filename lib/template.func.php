<?php


if(!defined('IN_APP')) {
    exit('Access Denied');
}

function parse_template($tplfile, $objfile) {
    $nest = 5;

    if(!@$fp = fopen($tplfile, 'r')) {
        exit("Current template file '$tplfile' not found or have no access!");
    }

    $template = fread($fp, filesize($tplfile));
    fclose($fp);

    $var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
    $const_regexp = "([A-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";

    $template = preg_replace("/([\n\r]+)\t+/s", "\\1", $template);
    $template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);

    $template = str_replace("{LF}", "<?=\"\\n\"?>", $template);

    $template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1?>", $template);
    $template = preg_replace("/$var_regexp/es", "addquote('<?=\\1?>')", $template);
    $template = preg_replace("/\<\?\=\<\?\=$var_regexp\?\>\?\>/es", "addquote('<?=\\1?>')", $template);


    //$template = preg_replace("/\{url\s+(.+?)\}/ies", "url('\\1')", $template);

    $template = "<? if(!defined('IN_APP')) exit('Access Denied'); ?>\n$template";
    $template = preg_replace("/[\n\r\t]*\{template\s+([a-z0-9_]+)\}[\n\r\t]*/is", "\n<? include template('\\1'); ?>\n", $template);
    $template = preg_replace("/[\n\r\t]*\{template\s+(.+?)\}[\n\r\t]*/is", "\n<? include template(\\1); ?>\n", $template);
    $template = preg_replace("/[\n\r\t]*\{eval\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('<? \\1 ?>','')", $template);
    $template = preg_replace("/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('\n<? echo \\1; ?>\n','')", $template);
    $template = preg_replace("/[\n\r\t]*\{elseif\s+(.+?)\}[\n\r\t]*/ies", "stripvtags('<? } elseif(\\1) { ?>','')", $template);
    $template = preg_replace("/[\n\r\t]*\{else\}[\n\r\t]*/is", "<? } else { ?>", $template);

    for($i = 0; $i < $nest; $i++) {
        $template = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r]*(.+?)[\n\r]*\{\/loop\}[\n\r\t]*/ies", "stripvtags('\n<? if(is_array(\\1)) { foreach(\\1 as \\2) { ?>','\n\\3\n<? } } ?>\n')", $template);
        $template = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*(.+?)[\n\r\t]*\{\/loop\}[\n\r\t]*/ies", "stripvtags('\n<? if(is_array(\\1)) { foreach(\\1 as \\2 => \\3) { ?>','\n\\4\n<? } } ?>\n')", $template);
        $template = preg_replace("/[\n\r\t]*\{if\s+(.+?)\}[\n\r]*(.+?)[\n\r]*\{\/if\}[\n\r\t]*/ies", "stripvtags('<? if(\\1) { ?>','\\2<? } ?>')", $template);
    }

    $template = preg_replace("/\{$const_regexp\}/s", "<?=\\1?>", $template);
    $template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);

    if(!@$fp = fopen($objfile, 'w')) {
        exit("Directory './data/view/' not found or have no access!");
    }

    //$template = preg_replace("/\"(http)?[\w\.\/:]+\?[^\"]+?&[^\"]+?\"/e", "transamp('\\0')", $template);
    //$template = preg_replace("/\<script[^\>]*?src=\"(.+?)\".*?\>\s*\<\/script\>/ise", "stripscriptamp('\\1')", $template);
    $template = str_replace('$ ', "$", $template); //增加模版中$的可用性,在模版中$后面用空格

    flock($fp, 2);
    fwrite($fp, $template);
    fclose($fp);
}

function transamp($str) {
    $str = str_replace('&', '&amp;', $str);
    $str = str_replace('&amp;amp;', '&amp;', $str);
    $str = str_replace('\"', '"', $str);
    return $str;
}

function addquote($var) {
    return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var));
}

function stripvtags($expr, $statement) {
    $expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
    $statement = str_replace("\\\"", "\"", $statement);
    return $expr.$statement;
}

function stripscriptamp($s) {
    $s = str_replace('&amp;', '&', $s);
    return "<script src=\"$s\" type=\"text/javascript\"></script>";
}

?>