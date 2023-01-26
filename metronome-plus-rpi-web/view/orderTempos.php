<?php
    //function to order tempos by their creation date
    function orderTempos($a, $b)
    {
        if ($a["Created"] == $b["Created"]) {
            return 0;
        }

        return ($a[0]["Created"] < $b[0]["Created"]) ? -1 : 1;
    }
?>