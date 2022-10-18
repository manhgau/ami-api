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
            self::MULTI_FACTOR_MATRIX,
            self::GROUP
        ];
    }

    public static function getTypeQuestionBygroup()
    {
        return [
            [
                'key' => 'select_group',
                'name' => 'Nhóm lựa chọn',
                'data' => [
                    ['question_type' => self::MULTI_CHOICE_CHECKBOX, 'name' => 'Choice checkbox'],
                    ['question_type' => self::MULTI_CHOICE_RADIO, 'name' => 'Choice radio'],
                    ['question_type' => self::MULTI_CHOICE_DROPDOWN, 'name' => 'Dropdown'],
                    ['question_type' => self::YES_NO, 'name' => 'Yes/No'],
                    ['question_type' => self::MULTI_FACTOR_MATRIX, 'name' => 'Matrix'],
                ]
            ],
            [
                'key' => 'text_group',
                'name' => 'Nhóm text',
                'data' => [
                    ['question_type' => self::QUESTION_ENDED_SHORT_TEXT, 'name' => 'Short text'],
                    ['question_type' => self::QUESTION_ENDED_LONG_TEXT, 'name' => 'Long text'],
                ]
            ],
            [
                'name' => 'Nhóm khác',
                'key' => 'other_group',
                'data' => [
                    ['question_type' => self::NUMBER, 'name' => 'Number'],
                    ['question_type' => self::DATETIME_DATE, 'name' => 'Date'],
                    ['question_type' => self::DATETIME_DATE_RANGE, 'name' => 'Date range'],
                    ['question_type' => self::RATING_STAR, 'name' => 'Rating (đánh giá)'],
                    ['question_type' => self::RANKING, 'name' => 'Ranking (Xếp hạng thứ tự)'],
                ]
            ],
            [
                'name' => 'Nhóm cấu trúc bảng hỏi',
                'key' => 'structural_group',
                'data' => [
                    ['question_type' => self::GROUP, 'name' => 'Tạo nhóm câu hỏi (question group)'],
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
