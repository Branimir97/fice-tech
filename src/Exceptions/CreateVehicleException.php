<?php


namespace App\Exceptions;


class CreateVehicleException
{
    private $exception;

    /**
     * @return mixed
     */
    public function getException()
    {
        return $this->exception;
    }

    public function setException()
    {
        $this->exception = [
            'status' => 'error',
            'code' => 400,
            'message'=> 'Error constructing new vehicle object!',
        ];
    }
}