<?php

namespace App\Traits;

use SM\Factory\FactoryInterface;

trait Statable
{

    /**
     * @var StateMachine $stateMachine
     */
    protected $stateMachine;

    /**
     *
     */
    public function stateMachine()
    {
        if (!$this->stateMachine) {
            $this->stateMachine = app(FactoryInterface::class)->get($this, self::SM_CONFIG);
        }
        return $this->stateMachine;
    }


    /**
     * return the SM state
     * @return \\
     */
    public function stateIs()
    {
        return $this->stateMachine()->getState();
    }

    /**
     * return the Camel Case state
     * @return \\
     */
    public function niceStateIs()
    {
        return str_snake_title($this->stateMachine()->getState());
    }

    /**
     * apply the transition
     *
     * @var String $transition
     * @return Object $transition
     */
    public function transition($transition)
    {
        return $this->stateMachine()->apply($transition);
    }

    /**
     * determine if the transition can be applied
     *
     * @var String $transition
     * @return bool
     */
    public function transitionAllowed($transition)
    {
        return $this->stateMachine()->can($transition);
    }

    /**
     * state history relation
     */
    public function history()
    {
        return $this->hasMany(self::HISTORY_MODEL['name']);
    }

    /**
     * Save the model after a state change
     *
     */
    public function addHistoryLine(array $transitionData) {
        $this->save();
        return $this->history()->create($transitionData);
    }
}
