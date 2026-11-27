<?php

return [
    'required' => 'Dirreen :attribute gaafatama dha.',
    'required_if' => 'Dirreen :attribute gaafatama dha yoo :other :value ta’e.',
    'string' => 'Dirreen :attribute qooqa ta’uu qaba.',
    'numeric' => 'Dirreen :attribute lakkoofsa ta’uu qaba.',
    'integer' => 'Dirreen :attribute lakkoofsa guutuu ta’uu qaba.',
    'confirmed' => 'Mirkaanni dirree :attribute wal hin qabatu.',
    'unique' => ':attribute kun dursee fudhatameera.',
    'exists' => ':attribute filame sirrii miti.',
    'in' => ':attribute filame sirrii miti.',
    'max' => [
        'string' => 'Dirreen :attribute hanga :max hertoo caala hin qabatu.',
        'file' => 'Faayilii :attribute hanga :max kilobaytii caala hin qabatu.',
        'numeric' => 'Dirreen :attribute hanga :max caala hin qabatu.',
    ],
    'min' => [
        'string' => 'Dirreen :attribute hertoo :min gaafa qaba.',
        'file' => 'Faayilii :attribute kilobaytii :min gaafa qaba.',
    ],
    'file' => 'Dirreen :attribute faayilii ta’uu qaba.',
    'image' => 'Dirreen :attribute bifa (image) ta’uu qaba.',
    'mimes' => 'Faayilii :attribute gosa :values ta’uu qaba.',
    'uploaded' => 'Faayilii :attribute ka’umsuu hin dandeenye.',
    'regex' => 'Bifa dirree :attribute sirrii miti.',
    'prohibited' => 'Dirreen :attribute hin hayyammu.',
    'phone_format' => ':attribute lakkoofsa bilbila Itoophiyaa sirrii ta’uu qaba (09XXXXXXXX, 07XXXXXXXX ykn +251...).',
    'password' => [
        'letters' => 'Dirreen :attribute yabbina tokko yoo xiqqaate qabaachuu qaba.',
        'mixed' => 'Dirreen :attribute yabbina guddaa fi yabbina xiqqaa qabaachuu qaba.',
        'numbers' => 'Dirreen :attribute lakkoofsa tokko qabaachuu qaba.',
        'symbols' => 'Dirreen :attribute mallattoo tokko qabaachuu qaba.',
    ],
    'custom' => [
        'phone' => [
            'prohibited' => 'Lakkoofsi bilbila maalummaa keessaa hin jijjiiramu.',
        ],
        'status' => [
            'prohibited' => 'Haalli akkauntaa maalummaa keessaa hin jijjiiramu.',
        ],
        'role' => [
            'prohibited' => 'Maqaan hojii maalummaa keessaa hin jijjiiramu.',
        ],
        'phone_verified_at' => [
            'prohibited' => 'Mirkaansi bilbila maalummaa keessaa hin jijjiiramu.',
        ],
    ],
    'attributes' => [
        'name' => 'maqaa',
        'phone' => 'lakkoofsa bilbila',
        'password' => 'jecha darbii',
        'password_confirmation' => 'mirkaansoo jecha darbii',
        'code' => 'koodii mirkaansaa',
        'avatar' => 'fakkisaa',
        'role' => 'maqaa hojii',
        'status' => 'haala akkauntaa',
        'phone_verified_at' => 'mirkaansoo bilbila',
        'locale' => 'afaan',
    ],
];
