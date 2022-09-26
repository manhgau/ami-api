<?php

namespace App\Models;

/*use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;*/

class QuestionType
{

    const MULTI_CHOICE_CHECKBOX                 = 'checkbox';
    const MULTI_CHOICE_RADIO                    = 'radio';
    const MULTI_CHOICE_DROPDOWN                 = 'dropdown';
    const YES_NO                                = 'yes_no';

    const DATETIME_DATE                         = 'date';
    const DATETIME_DATE_RANGE                   = 'date_range';

    const QUESTION_ENDED_SHORT_TEXT             = 'text_box';
    const QUESTION_ENDED_LONG_TEXT              = 'char_box';

    const RATING_STAR                           = 'star_rating';


    const MULTI_FACTOR_MATRIX                  = 'matrix';

    const MATRIX_VALUE_COLUMN                  = 'column';
    const MATRIX_VALUE_ROW                     = 'row';


    public static function getTypeQuestionList()
    {
        return [
            self::MULTI_CHOICE_CHECKBOX,
            self::MULTI_CHOICE_RADIO,
            self::MULTI_CHOICE_DROPDOWN,
            self::YES_NO,
            self::DATETIME_DATE,
            self::DATETIME_DATE_RANGE,
            self::QUESTION_ENDED_SHORT_TEXT,
            self::QUESTION_ENDED_LONG_TEXT,
            self::RATING_STAR,
            self::MULTI_FACTOR_MATRIX
        ];
    }

    public static function checkQuestionTypeValid($type)
    {
        $list = self::getTypeQuestionList();
        if (in_array($type, $list)) {
            return true;
        } else {
            return false;
        }
    }
}
