<?php
require_once './common/config.php';
require_once './common/functions.php';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>TCAT :: Export Tweets sentiment</title>

        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <link rel="stylesheet" href="css/main.css" type="text/css" />

        <script type="text/javascript" language="javascript">
	
	
	
        </script>

    </head>

    <body>

        <h1>TCAT :: Export Tweets sentiment</h1>

        <?php
        validate_all_variables();
        /* @todo, use same export possibilities as mod.export_tweets.php */

        $header = "id,time,created_at,from_user_name,from_user_lang,text,source,location,lat,lng,from_user_tweetcount,from_user_followercount,from_user_friendcount,from_user_realname,to_user_name,in_reply_to_status_id,from_user_listed,from_user_utcoffset,from_user_timezone,from_user_description,from_user_url,from_user_verified,filter_level";
        if (isset($_GET['includeUrls']) && $_GET['includeUrls'] == 1)
            $header .= ",urls,urls_expanded,urls_followed,domains";
        $header .= ",sentistrength,negative,positive";
        $header .= "\n";

        $sql = "SELECT s.positive, s.negative, s.explanation, t.from_user_name as user, t.id as tid FROM " . $esc['mysql']['dataset'] . "_sentiment s, " . $esc['mysql']['dataset'] . "_tweets t ";
        $sql .= sqlSubset("t.id = s.tweet_id AND ");
        //print $sql . "<br>";
        $rec = mysql_query($sql);
        while ($res = mysql_fetch_assoc($rec)) {
            $sentiment[$res['tid']]['pos'] = $res['positive'];
            $sentiment[$res['tid']]['neg'] = $res['negative'];
            $sentiment[$res['tid']]['desc'] = $res['explanation'];
        }

        $sql = "SELECT * FROM " . $esc['mysql']['dataset'] . "_tweets t ";
        $sql .= sqlSubset();

        $sqlresults = mysql_query($sql);
        $out = $header;
        if ($sqlresults) {
            while ($data = mysql_fetch_assoc($sqlresults)) {
                if (preg_match("/_urls/", $sql))
                    $id = $data['tweet_id'];
                else
                    $id = $data['id'];
                $out.= $id.",".textToCSV($data['text']).",";
               /* $out.= $id . "," .
                        strtotime($data["created_at"]) . "," .
                        $data["created_at"] . "," .
                        $data["from_user_name"] . "," .
                        $data['from_user_lang'] . "," .
                        validate($data["text"], "tweet") . "," .
                        "\"" . strip_tags(html_entity_decode($data["source"])) . "\"," .
                        "\"" . preg_replace("/[\r\t\n,]/", " ", trim(strip_tags(html_entity_decode($data["location"])))) . "\"," .
                        $data['geo_lat'] . "," .
                        $data['geo_lng'] . "," .
                        (isset($data['from_user_followercount']) ? $data['from_user_followercount'] : "") . "," .
                        (isset($data['from_user_friendcount']) ? $data['from_user_friendcount'] : "") . "," .
                        (isset($data['from_user_realname']) ? $data['from_user_realname'] : "") . "," .
                        (isset($data['to_user_name']) ? $data['to_user_name'] : "") . "," .
                        (isset($data['in_reply_to_status_id']) ? $data['in_reply_to_status_id'] : "") . "," .
                        (isset($data['from_user_listed']) ? $data['from_user_listed'] : "") . "," .
                        (isset($data['from_user_utcoffset']) ? $data['from_user_utcoffset'] : "") . "," .
                        (isset($data['from_user_timezone']) ? $data['from_user_timezone'] : "") . "," .
                        "\"" . preg_replace("/[\r\t\n,]/", " ", trim(strip_tags(html_entity_decode($data['from_user_description'])))) . "\"," .
                        "\"" . preg_replace("/[\r\t\n,]/", " ", trim($data['from_user_url'])) . "\"," .
                        $data['from_user_verified'] . "," .
                        $data['filter_level'];
                if (isset($_GET['includeUrls']) && $_GET['includeUrls'] == 1) {
                    $urls = $expanded = $followed = $domain = "";
                    $sql2 = "SELECT url, url_expanded, url_followed, domain FROM " . $esc['mysql']['dataset'] . "_urls WHERE tweet_id = " . $id;
                    $rec2 = mysql_query($sql2);
                    if (mysql_num_rows($rec2) > 0) {
                        $res2 = mysql_fetch_assoc($rec2);
                        $urls = $res2['url'] . " ; ";
                        $expanded = $res2['url_expanded'] . " ; ";
                        $followed = $res2['url_followed'] . " ; ";
                        $domain = $res2['domain'] . " ; ";
                    }
                    if (!empty($urls)) {
                        $urls = substr($urls, 0, -3);
                        $expanded = substr($expanded, 0, -3);
                        $followed = substr($followed, 0, -3);
                        $domain = substr($domain, 0, -3);
                    }
                    $out .= "," . $urls . "," . $expanded . "," . $followed . "," . $domain;
                }*/
                if (isset($sentiment[$id]))
                    $out .= $id."," . textToCSV($sentiment[$id]['desc']) . "," . $sentiment[$id]['pos'] . "," . $sentiment[$id]['neg'];
                else
                    $out .= ",,,";
                $out .= "\n";
            }
        }

        $filename = get_filename_for_export("fullExport-sentiment");
        file_put_contents($filename, chr(239) . chr(187) . chr(191) . $out);

        echo '<fieldset class="if_parameters">';
        echo '<legend>Your File</legend>';
        echo '<p><a href="' . filename_to_url($filename) . '">' . $filename . '</a></p>';
        echo '</fieldset>';
        ?>

    </body>
</html>
