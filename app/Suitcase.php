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
    public $optContents;
    public $origin;
    public $swimming;
    public $climate;

    public function generateList() {

        $this->empty();
        $this->generateDocuments();
        $this->generateKospital();
        $this->minimalClothing();
        $this->accesories();
        $this->generateElectronics();
        if (isset($this->climate)) $this->climateSpecificClothing();


    }

    private function minimalClothing() {
        $calc = round(.75*$this->duration);
        $this->add("Spare underwear x$calc");
        $this->add("Spare socks x$calc pairs");
    }

    private function accesories() {
        $this->add("sunglasses");
        $this->add("mask and gloves");
        $this->add("pen");
        $this->addOptional("slippers");
    }

    private function generateDocuments() {
        if ($this->tripAbroad()) {
            $this->add("Passport RUS");
            $this->add("Passport GBR");
            $this->addOptional("International Driving License");
        }
        if ($this->involvesCountry("RUS")){
            $this->add("Passport N RUS");
            if ($this->tripAbroad()) $this->add("RUS/GBR simcard");
        }
        if ($this->involvesCountry("ITA")){
            $this->add("Residence Permit ITA");
        }
        if ($this->involvesCountry("USA")){
            $this->addOptional("American loyalty cards");
        }
        $this->add("Driving License");
    }

    private function climateSpecificClothing() {
        $c = $this->climate;
        if (is_int($c)) {
            if ($c>25) $c = "tropical";
            else if ($c>12) $c = "moderate";
            else if ($c>(-2)) $c = "chilly";
            else $c = "arctic";
        }
        switch ($c) {
            case "tropical":
                $this->add("sunglasses");
                if ($this->swimming) $this->add("swimwear");
                $this->addOptional("sun lotion");
                break;
            case "chilly":
                $this->add("warm coat");
                $this->addOptional("light sweater");
                $this->addOptional("light scarf");
                break;
            case "arctic":
                $this->add("down jacket");
                $this->add("scarf");
                $this->add("warm trousers");
                $this->add("sweater");
                $this->add("hat");
                break;
        }
    }

    private function generateElectronics() {
        $this->add("MacBook");
        $this->add("Headphones");
        $this->add("iPad");
        $this->add("USB-C -> USB-C cable");
        $this->addOptional("USB-C -> USB-C cable to spare");
        $this->add("USB(-C) -> lightning cable");
        $this->add("Universal cable");
        $this->add("USB and USB-C power adaptors");

        if ($this->tripAbroad()) $this->add("Mains adaptor");
    }

    private function generateKospital () {
        $this->empty();
        $a1Calculation = 12*$this->duration;
        $b2Calculation = 3*$this->duration;
        $spmCalculation = 3*$this->duration;
        $t1Calculation = 1+round(.33*$this->duration);
        $y10Calculation = 17*$this->duration;

        $this->add("Kospital A1 x$a1Calculation");
        $this->add("Kospital SPM x$spmCalculation");
        $this->add("Kospital T1 x$t1Calculation");
        $this->add("Kospital B2 x$b2Calculation");
        $this->add("Kospital S1 x$this->duration");
        $this->add("Kospital Y10 x$y10Calculation");
        $this->add("Shampoo anti dd");
        $this->add("Nasal Spray steroid");
        $this->add("Nasal Spray like Otrivine");
        $this->add("Bandage");
        $this->add("Small selection of plasters");

        $this->addOptional("Back scratcher");
        $this->addOptional("Please check Kospital for additional items");
    }

    private function empty() {
        $this->contents = [];
        $this->optContents = [];
    }

    private function add($item) {
        $this->contents[] = $item;
    }

    private function addOptional($item) {
        $this->optContents[] = $item;
    }

    public function departingCountry($country){
        if ($this->origin==$country) return true;
        else return false;
    }

    public function arrivingToCountry($country){
        if ($this->destination==$country) return true;
        else return false;
    }

    public function involvesCountry($country){
        if ($this->departingCountry($country) || $this->arrivingToCountry($country)) {
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
