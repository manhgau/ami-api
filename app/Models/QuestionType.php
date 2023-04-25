<?php

namespace App\Models;

/*use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;*/

class QuestionType
{

    const MULTI_CHOICE                          = 'choice';
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
            self::MULTI_CHOICE,
            self::MULTI_CHOICE_DROPDOWN,
            self::YES_NO,
            self::NUMBER,
            self::DATETIME_DATE,
            self::QUESTION_ENDED_SHORT_TEXT,
            self::QUESTION_ENDED_LONG_TEXT,
            self::RATING_STAR,
            self::MULTI_FACTOR_MATRIX,
            self::GROUP,
            self::RANKING,
        ];
    }

    public static function getTypeQuestionBygroup()
    {
        return [
            [
                'key' => 'select_group',
                'name' => 'Câu hỏi lựa chọn',
                'data' => [
                    ['question_type' => self::YES_NO, 'name' => 'Có/ Không (Yes/ No)'],
                    ['question_type' => self::MULTI_CHOICE, 'name' => 'Lựa chọn (Choices)'],
                    ['question_type' => self::MULTI_FACTOR_MATRIX, 'name' => 'Lưới tùy chọn (Matrix)'],
                    ['question_type' => self::MULTI_CHOICE_DROPDOWN, 'name' => 'Bảng tùy chọn (Dropdown)'],
                ]
            ],
            [
                'key' => 'text_group',
                'name' => 'Câu hỏi văn bản',
                'data' => [
                    ['question_type' => self::QUESTION_ENDED_SHORT_TEXT, 'name' => 'Văn bản ngắn (Short text)'],
                    ['question_type' => self::QUESTION_ENDED_LONG_TEXT, 'name' => 'Văn bản dài (Long text)'],
                ]
            ],
            [
                'name' => 'Câu hỏi khác',
                'key' => 'other_group',
                'data' => [
                    ['question_type' => self::NUMBER, 'name' => 'Số (Number)'],
                    ['question_type' => self::RATING_STAR, 'name' => 'Thang đánh giá (Rating)'],
                    ['question_type' => self::DATETIME_DATE, 'name' => 'Ngày/ giờ (Date/ time)'],
                    ['question_type' => self::RANKING, 'name' => 'Mức độ (Score)'],
                ]
            ],
            [
                'name' => 'Cấu trúc',
                'key' => 'structural_group',
                'data' => [
                    ['question_type' => self::GROUP, 'name' => 'Phân nhóm (Group)'],
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
