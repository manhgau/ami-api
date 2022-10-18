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
    const NUMBER                                = 'number';

    const DATETIME_DATE                         = 'date';
    const DATETIME_DATE_RANGE                   = 'date_range';

    const QUESTION_ENDED_SHORT_TEXT             = 'text_box';
    const QUESTION_ENDED_LONG_TEXT              = 'char_box';

    const RATING_STAR                           = 'star_rating';
    const RANKING                               = 'ranking';


    const MULTI_FACTOR_MATRIX                  = 'matrix';

    const MATRIX_VALUE_COLUMN                  = 'column';
    const MATRIX_VALUE_ROW                     = 'row';

    const GROUP                                = 'group';


    public static function getTypeQuestionList()
    {
        return [
            self::MULTI_CHOICE_CHECKBOX,
            self::MULTI_CHOICE_RADIO,
            self::MULTI_CHOICE_DROPDOWN,
            self::YES_NO,
            self::NUMBER,
            self::DATETIME_DATE,
            self::DATETIME_DATE_RANGE,
            self::QUESTION_ENDED_SHORT_TEXT,
            self::QUESTION_ENDED_LONG_TEXT,
            self::RATING_STAR,
            self::MULTI_FACTOR_MATRIX
        ];
    }

    public static function getTypeQuestionBygroup()
    {
        return [
            'select_group' => [
                'name' => 'Nhóm lựa chọn',
                'data' => [
                    self::MULTI_CHOICE_CHECKBOX => 'Choice checkbox',
                    self::MULTI_CHOICE_RADIO => 'Choice radio',
                    self::MULTI_CHOICE_DROPDOWN => 'Dropdown',
                    self::YES_NO => 'Yes/No',
                    self::MULTI_FACTOR_MATRIX => 'Matrix'
                ]
            ],
            'text_group' => [
                'name' => 'Nhóm text',
                'data' => [
                    self::QUESTION_ENDED_SHORT_TEXT => 'Short text',
                    self::QUESTION_ENDED_LONG_TEXT => 'Long text',
                ]
            ],
            'other_group' => [
                'name' => 'Nhóm text',
                'data' => [
                    self::NUMBER => 'Number',
                    self::DATETIME_DATE => 'Date',
                    self::DATETIME_DATE_RANGE => 'Date range',
                    self::RATING_STAR => 'Rating (đánh giá)',
                    self::RANKING => 'Ranking (Xếp hạng thứ tự)',
                ]
            ],
            'structural_group' => [
                'name' => 'Nhóm cấu trúc bảng hỏi',
                'data' => [
                    self::GROUP => 'Tạo nhóm câu hỏi (question group)',
                ]
            ]
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
