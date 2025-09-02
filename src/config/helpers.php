<?php
function monthToRoman($month) {
    $map = [
        1 => "I", 2 => "II", 3 => "III", 4 => "IV", 5 => "V", 6 => "VI",
        7 => "VII", 8 => "VIII", 9 => "IX", 10 => "X", 11 => "XI", 12 => "XII"
    ];
    return $map[intval($month)] ?? "";
}

function generateCode($prefix, $id) {
    $month = date("n");
    $year  = date("Y");
    $roman = monthToRoman($month);
    return sprintf("%s-%s/%s/%d", $prefix, $roman, $year, $id);
}
?>
