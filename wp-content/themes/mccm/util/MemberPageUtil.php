<?php

class MemberPageUtil {
    private $specialMemberCount;
    private $memberCount;
    private $memberList;
    private $markup = '';
    
    /**
     * $memberContent this util is able to parse:
     * --- Content format 1 ---
     * <p>Muster Hans</p>
     * <p>Schuster Franz Ehrenmitglied</p>
     * <p>Gopp Eduard</p>
     * --- Content format 2 ----
     * <p>Muster Hans<br>
     * Schuster Franz Ehrenmitglied<br>
     * Gopp Eduard</p>
     * 
     * @param string $membersContent The member page content maintained with 
     * help of WP backend. It mostly consists of member names. 
     * In addition it can contain the marker "Ehrenmitglied".
     *  
     * The marker position is not important.
     * The marker will be removed before render the member name in UI. However, 
     * in UI special highlighting will be applied for special members.
     */
    public function __construct($membersContent) {
        $this->specialMemberCount = 0;
        $this->memberCount = 0;
        $this->memberList = array();
        $this->parseMemberList($membersContent);
        $this->calculateMemberCounts(); 
    }
    
    /**
     * TODO: describe
     * 
     * @param type $memberContent The member page content maintained with help
     * of WP backend.
     */
    private function parseMemberList($membersContent) {
        $members = $this->extractPlainMembersFromMarkup($membersContent);
        
        foreach($members as $m) {
            $replaceCount = 0;
            $m = preg_replace('/Ehrenmitglied/', '', $m, -1, $replaceCount);
            array_push($this->memberList, array(
                'name' => $m,
                'isSpecialMember' => ($replaceCount > 0 ? true : false)
            ));
        }
        $this->markup = json_encode($this->memberList);
    }
    
    /**
     * Calculates the member counts based on the member list meta parsed.
     * 
     * @see self::memberList
     * 
     */
    private function calculateMemberCounts() {
        foreach ($this->memberList as $memberMeta) {
            if($memberMeta['isSpecialMember']) {
                $this->specialMemberCount++;
            } else {
                $this->memberCount++;
            }
        }
    }
    
    /**
     * Extract the plain member names and return in array.
     * 
     * @param type $membersContent  The member page content maintained with help
     * of WP backend.
     * 
     * @return array
     */
    private function extractPlainMembersFromMarkup($membersContent) {
        $membersContent = trim($membersContent);
        
        //remove from the beginning
        $membersContent = preg_replace('/^<p>/', '', $membersContent);
        
        //remove from the end
        $membersContent = preg_replace('/<\/p>$/', '', $membersContent);
       
        //replace </p><p> tags with split marker
        //e.g. "foobar 1</p>{newline|carriage return}<p> foobar 2" will be replaced by
        //"foobar 1|foobar 2"
        $membersContent = preg_replace('/<\/p>(\r|\n)*<p>/', '|', $membersContent);
        
        //replace <br\> tags with split marker
        //e.g. "foobar 1<br /> foobar 2" will be replaced by "foobar 1|foobar 2"
        $membersContent = preg_replace('/<br.*\/>\s*/', '|', $membersContent);
       
        return explode('|', $membersContent);
    }
    
    public function getMemberSummaryMarkup() {
        $total = $this->specialMemberCount + $this->memberCount;
        return '<table class="no-border-table member-overview-table">
                <tbody>
                <tr>
                <td class="mccm-coloring" style="font-weight: bold;">Ehrenmitglieder:</td>
                <td class="member-overview-second-td mccm-coloring">'.$this->specialMemberCount.'</td>
                </tr>
                <tr>
                <td>Mitglieder:</td>
                <td class="member-overview-second-td">'.$this->memberCount.'</td>
                </tr>
                <tr>
                <td>Mitglieder gesamt:</td>
                <td class="member-overview-second-td">'.$total.'</td>
                </tr>
                </tbody>
                </table>
                <hr style="margin-bottom: 1em;" />';
    }
    
    /**
     * The markup generated will consist out of 4 columns in total.
     */
    public function getMemberListMarkup() {
        $markup = '';
        $fullColumnMemberCount = ceil(count($this->memberList) / 4);
        $popCount = 0;
//        $markup .= "Max column member count $fullColumnMemberCount<br/>";
        $markup .= "<div class='member-col'>";
        while($member = array_shift($this->memberList)) {
            $popCount++;
            $markup .= $member['isSpecialMember'] == true ? '<span class="mccm-coloring">' : '';
            $markup .= $member['name'];
            $markup .= $member['isSpecialMember'] == true ? '</span>' : '';
            $markup .= '<br/>';

            $calc = $popCount % $fullColumnMemberCount;
            if($calc == 0 && $popCount > 1) {
//                $markup .= " <!-- $popCount % $fullColumnMemberCount is $calc  -->";
                $markup .= "</div>";
                
                //TODO: don't open new container if no more members
                $markup .= "<div class='member-col'>";
            } else {
//                $markup .= "<!-- $popCount % $fullColumnMemberCount is $calc -->";
            }
        }
        $markup .= "</div>";
        
        return $markup;
    }
}

