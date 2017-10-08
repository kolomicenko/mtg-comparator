<?php

namespace MTG_Comparator\Fetch_and_parse;

abstract class Enum
{
    public static $LANGUAGES = array('English','Japanese','Portuguese','Chinese','Italian','Spanish','French','German','Russian','Korean');
    public static $QUALITIES = array('MINT','LIGHTLY','HEAVILY','DAMAGED');
    public static $DIRECTIONS = array('SELL','BUY');
    public static $CURRENCIES = array('KC','USD');

    public static $CARDS_FOUND_MESSAGE = 'found';
    public static $CARDS_NOT_FOUND_MESSAGE = 'not_found';
    public static $CONFIRM_QUEUE_NAME_SUFFIX = '_confirm';
    public static $WORKER_COUNT = 10;
}

