<?php
/**
 +-------------------------------------------------------------------------+
 | Rubify  - An MiniDLNA Webapp                                            |
 | Version 1.0.0                                                           |
 |                                                                         |
 | This program is free software: you can redistribute it and/or modify    |
 | it under the terms of the GNU General Public License as published by    |
 | the Free Software Foundation.                                           |
 |                                                                         |
 | This file forms part of the Rubify software.                            |
 |                                                                         |
 | If you wish to use this file in another project or create a modified    |
 | version that will not be part of the Rubify Software, you               |
 | may remove the exception above and use this source code under the       |
 | original version of the license.                                        |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the            |
 | GNU General Public License for more details.                            |
 |                                                                         |
 | You should have received a copy of the GNU General Public License       |
 | along with this program.  If not, see http://www.gnu.org/licenses/.     |
 |                                                                         |
 +-------------------------------------------------------------------------+
 | Author: Jaime Rubio <jaime@rubiogafsi.com>                              |
 +-------------------------------------------------------------------------+
*/
namespace Rubify\Framework;

defined('_RBFYEXEC') or die;

use Rubify\Framework\Helpers;
use Rubify\Framework\Request;
use Rubify\Framework\History;
use Rubify\Framework\Language\Text;

class modelHistory extends Model
{

    public function display()
    {
        $history  = new History($this->user);
        $array = $history->load();
    
        //Save the result in a new array
        $result = [];        
        foreach($array as $item)
            $result[] = $item;

        //Sort by date
        usort($result, function($a, $b) { return strtotime($b->date) > strtotime($a->date); });   
        
        //This week
        $first = strtotime("first day of this week");
        $last  = strtotime("now");
        $array = [];

        foreach($result as $item){
            $date = strtotime($item->date);
            if($date >= $first && $date <= $last)
                $array[] = $item;
        }
        $this->data['thisweek'] = $array;

        //Last week
        $first = strtotime("last week");
        $last  = strtotime("this week");
        $array = [];

        foreach($result as $item){
            $date = strtotime($item->date);
            if($date >= $first && $date < $last)
                $array[] = $item;
        }
        $this->data['lastweek'] = $array;   
        
        //This Month
        $first = strtotime("first day of this month");
        $last  = strtotime("last week");      
        $array = [];

        foreach($result as $item){
            $date = strtotime($item->date);
            if($date > $first && $date < $last)
                $array[] = $item;
        }
        $this->data['thismonth'] = $array;      
        
        //Last Month
        $first = strtotime("first day of last month");
        $last  = strtotime("last day of last month");     
        $array = [];

        foreach($result as $item){
            $date = strtotime($item->date);
            if($date >= $first && $date < $last)
                $array[] = $item;
        }
        $this->data['lastmonth'] = $array;              

        $this->page->title  = Text::_('HISTORY');
        $this->page->data   = $this->data;    
        parent::display();         
    } 

    public function pop()
    {
        list($oid)  = Helpers::getId();        
        $history      = new History( $this->user );                
        $success     = false;

        //Cannot pop a missing track from the queue
        if($track = $history->get($oid)){
            $success    = $history->pop($oid);
            $result     = $track;
        }

        $array = ['success' => $success , 'result' => $result];    

        header('Content-Type: application/json; charset=utf-8');  
        echo json_encode($array);
        die();      
    }     
    
    public function push()
    {
        list($oid)  = Helpers::getId();    
        $time       = (int) Request::getVar('time',0,'GET');

        $history    = new History( $this->user );        
        $success    = $history->push($oid,$time);
        $result     = $history->get($oid);

        $array = ['success' => $success , 'result' => $result];

        header('Content-Type: application/json; charset=utf-8');  
        echo json_encode($array);
        die();
    }    

    public function empty()
    {
        $history  = new History($this->user);                  
        $array = ['success' => $history->empty() , 'result' => []];    

        header('Content-Type: application/json; charset=utf-8');  
        echo json_encode($array);
        die();                        
    }  


}