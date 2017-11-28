<?php
namespace RateGetter\Domain;

interface RateResponse
{
    public function getData() : array;

    public function getParsedResultSet() : array;

    public function getTimeIndexedField(string $field) : array;
}