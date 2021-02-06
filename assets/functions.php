<?php

function cleanRequestVar($name)
{
    if(!isset($_REQUEST[$name])) {
        return null;
    }
    
    return trim(htmlspecialchars(strip_tags($_REQUEST[$name]), ENT_QUOTES, 'UTF-8'));
}
