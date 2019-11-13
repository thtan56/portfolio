<?php

class Mybet {
    private $dbserver = "127.0.0.1";
    private $dbuser = "root";
    private $dbpass = "cancer56";
    private $dbdatabase = "test";
    private $msg = "";

    public function getMsg() { return $this->msg; }
    public function getMybets()
    {
        $mybet = array();
        try {
            $mysqli = new mysqli($this->dbserver, $this->dbuser, $this->dbpass, $this->dbdatabase);
            if ($mysqli->connect_errno) {
                $this->msg = $mysqli->error;
                return $mybet;
            }
            $query = "select id, game, date, match, score1, score2, username, bet_amount, bet_score1, bet_score2, bet_type, bet_odd_type, bet_odd, remarks, status from mybet";
            if (!($stmt = $mysqli->prepare($query))) {
                $mysqli->close();
                $this->msg = $mysqli->error;
                return $mybet;
            }
            if (!$stmt->execute()) {
                $mysqli->close();
                $this->msg = $stmt->error;
                return $mybet;
            } else {
                $stmt->bind_result($id,$game, $date, $match, $score1, $score2, $mybetname,
                    $bet_amount, $bet_score1, $bet_score2, $bet_type, $bet_odd_type, $bet_odd, $remarks, $status);
                while ($stmt->fetch()) {
                    array_push($mybet, array("id"=>$id,"game"=>$game, "date"=>$date,"match"=>$match,
                        "score1"=>$score1,"score2"=>$score2,"username"=>$mybetname, "bet_amount"=>$bet_amount,
                        "bet_score1"=>$bet_score1,"bet_score2"=>$bet_score2,"bet_type"=>$bet_type,
                        "bet_odd_type"=>$bet_odd_type,"bet_odd"=>$bet_odd,"remarks"=>$remarks,"status"=>$status));
                }
            }

            $stmt->close();
            $mysqli->close();

        } catch (Exception $e) {

            $this->msg = $e->getMessage();
        }

        return $mybet;
    }
    public function insertMybet($game, $date, $match, $score1, $score2, $mybetname,
                    $bet_amount, $bet_score1, $bet_score2, $bet_type, $bet_odd_type, $bet_odd, $remarks, $status) {
        $mybet = -1;
        try {
            $mysqli = new mysqli($this->dbserver, $this->dbuser, $this->dbpass, $this->dbdatabase);
            if ($mysqli->connect_errno) { $this->msg = $mysqli->error; return $mybet; }
            $query = "insert into mybet(game, date, match, score1, score2, username,
                    bet_amount, bet_score1, bet_score2, bet_type, bet_odd_type, bet_odd, remarks, status,
            created) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,now())";
            if (!($stmt = $mysqli->prepare($query))) {
                $mysqli->close();
                $this->msg = $mysqli->error;
                return $mybet;
            }
            $stmt->bind_param('sssddsdddsssss', $game, $date, $match, $score1, $score2, $mybetname,
                    $bet_amount, $bet_score1, $bet_score2, $bet_type, $bet_odd_type, $bet_odd, $remarks, $status);
            if (!$stmt->execute()) {
                $mysqli->close();
                $this->msg = $stmt->error;
                return $mybet;
            }
            $mybet = 1;
            $this->msg = "";
            $stmt->close();
            $mysqli->close();
        } catch (Exception $e) { $this->msg = $e->getMessage(); }
        return $mybet;
    }
    public function updateMybet($id,$game, $date, $match, $score1, $score2, $mybetname,
                    $bet_amount, $bet_score1, $bet_score2, $bet_type, $bet_odd_type, $bet_odd, $remarks, $status) {
        $mybet = -1;
        try {
            $mysqli = new mysqli($this->dbserver, $this->dbuser, $this->dbpass, $this->dbdatabase);
            if ($mysqli->connect_errno) { $this->msg = $mysqli->error; return $mybet; }
            $query = "update mybet set game=?, date=?, match=?, score1=?, score2=?, mybetname=?,
                 bet_amount=?, bet_score1=?, bet_score2=?, bet_type=?, bet_odd_type=?, bet_odd=?, remarks=?, status=? where id=?";
            if (!($stmt = $mysqli->prepare($query))) {
                $mysqli->close();
                $this->msg = $mysqli->error;
                return $mybet;
            }
            $stmt->bind_param('sssddsdddsssssd', $game, $date, $match, $score1, $score2, $mybetname,
                    $bet_amount, $bet_score1, $bet_score2, $bet_type, $bet_odd_type, $bet_odd, $remarks, $status, $id);
            if (!$stmt->execute()) {
                $mysqli->close();
                $this->msg = $stmt->error;
                return $mybet;
            }
            $mybet = 1;
            $this->msg = "";
            $stmt->close();
            $mysqli->close();
        } catch (Exception $e) { $this->msg = $e->getMessage(); }
        return $mybet;
    }
    public function deleteMybet($id)
    {
        $mybet = -1;
        try {
            $mysqli = new mysqli($this->dbserver, $this->dbuser, $this->dbpass, $this->dbdatabase);
            if ($mysqli->connect_errno) {
                $this->msg = $mysqli->error;
                return $mybet;
            }
            $query = "delete from mybet where id=?";
            if (!($stmt = $mysqli->prepare($query))) {
                $mysqli->close();
                $this->msg = $mysqli->error;
                return $mybet;
            }
            $stmt->bind_param('d', $id);
            if (!$stmt->execute()) {
                $mysqli->close();
                $this->msg = $stmt->error;
                return $mybet;
            }
            $mybet = 1;
            $this->msg = "";
            $stmt->close();
            $mysqli->close();

        } catch (Exception $e) { $this->msg = $e->getMessage(); }

        return $mybet;
    }
}