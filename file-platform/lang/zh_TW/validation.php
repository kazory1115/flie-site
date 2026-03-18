<?php

return [
    'accepted' => ':attribute 必須接受。',
    'confirmed' => ':attribute 與確認欄位不一致。',
    'current_password' => '密碼不正確。',
    'email' => ':attribute 格式錯誤。',
    'file' => ':attribute 必須是檔案。',
    'integer' => ':attribute 必須是整數。',
    'max' => [
        'file' => ':attribute 不可大於 :max KB。',
        'string' => ':attribute 不可超過 :max 個字元。',
    ],
    'regex' => ':attribute 格式不正確。',
    'required' => ':attribute 為必填。',
    'string' => ':attribute 必須是字串。',
    'unique' => ':attribute 已經存在。',
    'attributes' => [
        'name' => '名稱',
        'email' => 'Email',
        'password' => '密碼',
        'password_confirmation' => '確認密碼',
        'current_password' => '目前密碼',
        'file' => '檔案',
        'folder_id' => '資料夾',
        'parent_id' => '上層資料夾',
    ],
];
