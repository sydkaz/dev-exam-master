<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 27/10/19
 * Time: 11:19 PM
 */

namespace CatchOfTheDay\DevExamBundle\lib;

class Paginator
{
    private $totalPages;
    private $page;
    private $rpp;

    public function __construct($page, $totalcount, $rpp,$path)
    {
        $this->rpp=$rpp;
        $this->page=$page;
        $this->path=$path;
        $this->totalPages=$this->setTotalPages($totalcount, $rpp);
    }


    private function setTotalPages($totalcount, $rpp)
    {
        $this->totalPages=ceil($totalcount / $rpp);
        return $this->totalPages;
    }

    public function getTotalPages()
    {
        return $this->totalPages;
    }

    public function getPagesList()
    {   $pages = array();

        if($this->page > 1)
        array_push($pages,'<li class="page-item  "><a class="page-link" href="'."$this->path/".($this->page-1)."/$this->rpp".'"   @click.prevent="paginate($event,\''.($this->page-1).'\')" >previous</a></li>');

        for($i = 1; $i <= $this->totalPages;$i++){
            $active  = ($i == $this->page)? 'active':'';

            array_push($pages,'<li class="page-item '.$active.'"><a class="page-link" href="'."$this->path/$i/$this->rpp".'"  @click.prevent="paginate($event,\''.$i.'\')" >'.$i.'</a></li>');

        }
        if($this->page < $this->totalPages)
            array_push($pages,'<li class="page-item"><a class="page-link" href="'."$this->path/".($this->page+1)."/$this->rpp".'"   @click.prevent="paginate($event,\''.($this->page+1).'\')" >next</a></li>');
        return $pages;
    }

}

?>