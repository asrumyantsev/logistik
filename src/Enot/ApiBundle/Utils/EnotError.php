<?php
/**
 * ...
 */

namespace Enot\ApiBundle\Utils;


final class EnotError
{
    const
        WRONG_CONNECTOR_ID = 1,
        WRONG_BALANCE = 2,
        WRONG_TOKEN = 3,
        WRONG_AUTH_STATUS = 4,
        WRONG_SESSION_ID = 5,
        WRONG_PARAMETERS = 6,
        WRONG_RATE_DATES = 7,
        WRONG_PHONE = 8,
        WRONG_PAYMENT_BY_SESSION_ID = 9,
        WRONG_CHARGE_BOX_IDENTITY = 10,
        WRONG_STATION_ID = 11,
        WRONG_CODE = 12,
        WRONG_PHONE_OR_CODE = 13,

        SESSION_NOT_FOUND = 14,
        SESSION_ALREADY_STOPPED = 15,
        SESSION_NOT_STARTED_OR_ALREADY_STOPPED = 16,
        STATION_AND_CONNECTION_NOT_DEFINE = 17,
        WRONG_STATION_CONNECTION = 18,
        ERR_CONNECTION_SMS = 19,
        PAYMENT_ALREADY_CREDITED = 20,
        MOE_CONNECTION_NOT_FOUND = 21,
        SECRET_NOT_VALID = 22,
        UNKNOWN_STATUS = 23,
        ERR_LOG = 24,
        ERR_AUTH_SUPPLIER = 25,
        SUPPLIER_NOT_FOUND = 26,
        WRONG_CONNECTION_TYPE = 27,
        WRONG_STICKER_ID = 28,
        WRONG_VEHICLE_ID = 29,
        WRONG_TRIP_ID = 30,
        TRIP_NOT_STARTED_OR_ALREADY_STOPPED = 31,
        EVENT_NOT_FOUND = 32,
        TRIP_ALREADY_STARTED = 33,
        ALARM_ERROR = 34,
        ALARM_SYSTEM_NOT_DEFINED = 35,
        WRONG_IMAGE = 36,
        EVENT_ALREADY_STARTED = 37,
        OCPP_COMMAND_NOT_FOUND = 38,
        WRONG_PARKING_ID = 39,
        OUT_OF_PARKING = 40,
        CARD_NOT_FOUND = 41,
        WRONG_SUM = 42,
        VENDOR_ERROR = 43,

        DRIVER_NOT_FOUND = 44,
        VEHICLE_NOT_FOUND = 45,
        TRAILER_NOT_FOUND = 46,
        CONTAINER_NOT_FOUND = 47,
        TRANSPORTATION_NOT_FOUND = 48,
        TRANSPORTATION_ASSIGNED = 49,
        ENTITY_ALREADY_EXIST = 50,
        TRANSPORTATION_ALREADY_FINISHED = 51,
        TRANSPORTATION_ALREADY_ASSIGNED = 52,
        TRANSPORTATION_POINT_WAS_PASSED = 53,
        PAST_TRANSPORTATION_POINT_WAS_NOT_PASSED = 54,
        DRIVER_OFFLINE = 55;
}