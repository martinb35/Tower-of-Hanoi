<?php 

class TowerOfHanoi
{

    private $actualConfig;
 
    private $input;

    private $moves = array();

    public function play()
    {
        $this->input();
        $this->hanoi();
        $this->output();
    }

    private function hanoi()
    {
        $current = $this->actualConfig->current();
        $currentKey = $this->actualConfig->key();
        $target  = $this->input->getFinalConfig()->current();
        
        $continue = true;

        if($current != $target) {
            $parentKey = $this->actualConfig->getParent($current, $currentKey);
            if ($parentKey !== false) {
                
                $aux = $this->getAux($current, $target);
                $this->addMove($parentKey, $aux);

                $continue = false;
            } else {
                $this->addMove($currentKey, $target);    
            }
        }

        if ($continue) {
            $this->actualConfig->prev();
            $this->input->getFinalConfig()->prev();    
        }

        if ($this->actualConfig->valid()) {
            $this->hanoi();
        }
    }

    private function getAux($current, $target)
    {
        
        $sum = array_count_values($this->actualConfig->getConfig());
        $pegs = array_fill(1, $this->input->getKPegs(), 0);

        foreach ($pegs as $k => $peg) {
            if (array_key_exists($k, $sum))
                $pegs[$k] = $sum[$k];
        }

        asort($pegs);

        foreach ($pegs as $key => $val) {
            if($key != $current && $key != $target)
                return $key;
        }
        throw new Exception('Aux did not encountered');
    }

    private function addMove($key, $to)
    {
        $this->moves[] = new Move($this->actualConfig->get($key), $to);
        $this->actualConfig->set($key, $to);
    }

    private function input()
    {
        $this->input = new Input;
        $this->actualConfig = $this->input->getInitialConfig();
    }

    private function output()
    {
        fwrite(STDOUT, count($this->moves)."\n");
        foreach ($this->moves as $move) {
            fwrite(STDOUT, $move->from." ".$move->to."\n");
        }
    }
}

class Move
{
    public $from;
    public $to;

    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

}

class Input
{
    private $nDiscs;
    private $kPegs;

    private $initialConfig;
    private $finalConfig;

    public function __construct()
    {
        list($this->nDiscs, $this->kPegs) = $this->readInput(2);
        $this->initialConfig = $this->readInput($this->nDiscs);
        $this->finalConfig = $this->readInput($this->nDiscs);
    }

    private function readInput($lenght)
    {
        do {
            $invalid = false;

            $result = fgetcsv(STDIN,0,' ');

            if (is_array($result) && count($result) == $lenght) {
                return $result;
            } else {
                $invalid = true;
            }
        } while ($invalid);
    }

    public function getKPegs()
    {
        return $this->kPegs;
    }

    public function getInitialConfig()
    {
        return new Config($this->initialConfig);
    }

    public function getFinalConfig()
    {
        return  new Config($this->finalConfig);
    }

}

class Config implements Iterator
{
    private $config = array();

    public function __construct(Array $config)
    {
        $this->config = $config;
        $this->forward();
    }

    public function set($key, $val)
    {
        return $this->config[$key] = $val;
    }

    public function get($key)
    {
        return $this->config[$key];
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function rewind()
    {
        return reset($this->config);
    }

    public function forward()
    {
        return end($this->config);
    }

    public function current()
    {
        return current($this->config);
    }

    public function key()
    {
        return key($this->config);
    }

    public function prev()
    {
        return prev($this->config);
    }

    public function next()
    {
        return next($this->config);
    }

    public function valid()
    {
        return key($this->config) !== null;
    }

    public function getParent($value, $key)
    {
        $config = clone $this;
        $config->rewind();
        do {
            if ($config->current() == $value && $config->key() != $key)
                return $config->key();
            $config->next();
        } while ($config->valid() && $config->key() != $key);

        return false;
    }
}

$tower = new TowerOfHanoi;
$tower->play();