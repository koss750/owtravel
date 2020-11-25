<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Suitcase extends Model
{
    public $id;
    public $destination;
    public $duration;
    public $methodOfTransport;
    public $contents;

    public function generateList() {

        if ($this->tripAbroad()) {
            $this->add("Passport RUS");
            $this->add("Passport GBR");
        }
        if ($this->involvesRussia()){
            $this->add("Passport N RUS");
            $this->add("RUS/GBR simcard");
        }
        if ($this->duration>2) {
        }

    }

    private function add($item) {
        $this->contents[] = $item;
    }

    public function departingRussia(){
        if ($this->origin=="RUS") return true;
        else return false;
    }

    public function arrivingToRussia(){
        if ($this->destination=="RUS") return true;
        else return false;
    }

    public function involvesRussia(){
        if ($this->departingRussia() || $this->arrivingToRussia()) {
            return true;
        }
        else return false;
    }

    public function tripAbroad(){
        if ($this->destination!=$this->origin) {
            return true;
        }
        else return false;
    }

}
