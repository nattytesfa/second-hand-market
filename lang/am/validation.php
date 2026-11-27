<?php

return [
    'required' => 'የሚስጥ :attribute መስክ አስፈላጊ ነው።',
    'required_if' => 'የሚስጥ :attribute መስክ :other :value ሲሆን አስፈላጊ ነው።',
    'string' => 'የ:attribute መስክ ጽሑፍ መሆን አለበት።',
    'numeric' => 'የ:attribute መስክ ቁጥር መሆን አለበት።',
    'integer' => 'የ:attribute መስክ ጸጥተኛ ቁጥር መሆን አለበት።',
    'confirmed' => 'የ:attribute መስክ ማረጋገጫ አይዛመድም።',
    'unique' => 'ይህ :attribute ቀድሞውኑ ተወስዷል።',
    'exists' => 'የተመረጠው :attribute ትክክል አይደለም።',
    'in' => 'የተመረጠው :attribute ትክክል አይደለም።',
    'max' => [
        'string' => 'የ:attribute መስክ ከ:max ቁምፊዎች በላይ መሆን የለበትም።',
        'file' => 'የ:attribute ፋይል ከ:max ኪሎባይት በላይ መሆን የለበትም።',
        'numeric' => 'የ:attribute መስክ ከ:max በላይ መሆን የለበትም።',
    ],
    'min' => [
        'string' => 'የ:attribute መስክ ቢያንስ :min ቁምፊዎች ያስፈልጋሉ።',
        'file' => 'የ:attribute ፋይል ቢያንስ :min ኪሎባይት መሆን አለበት።',
    ],
    'file' => 'የ:attribute መስክ ፋይል መሆን አለበት።',
    'image' => 'የ:attribute መስክ ምስል መሆን አለበት።',
    'mimes' => 'የ:attribute ፋይል ከሚከተሉት ዓይነቶች መሆን አለበት፡ :values።',
    'uploaded' => 'የ:attribute ፋይል መስቀል አልተቻለም።',
    'regex' => 'የ:attribute መስክ ቅርጸት ትክክል አይደለም።',
    'prohibited' => 'የ:attribute መስክ አይፈቀድም።',
    'phone_format' => 'የ:attribute ትክክለኛ የኢትዮጵያ ስልክ ቁጥር መሆን አለበት (09XXXXXXXX፣ 07XXXXXXXX ወይም +251...)።',
    'password' => [
        'letters' => 'የ:attribute መስክ ቢያንስ አንድ ፊደል መያዝ አለበት።',
        'mixed' => 'የ:attribute መስክ ቢያንስ አንድ ትልቅና አንድ ትንሽ ፊደል መያዝ አለበት።',
        'numbers' => 'የ:attribute መስክ ቢያንስ አንድ ቁጥር መያዝ አለበት።',
        'symbols' => 'የ:attribute መስክ ቢያንስ አንድ ምልክት መያዝ አለበት።',
    ],
    'custom' => [
        'phone' => [
            'prohibited' => 'የስልክ ቁጥር ከመገለጫ ሊቀየር አይችልም።',
        ],
        'status' => [
            'prohibited' => 'የመለያ ሁኔታ ከመገለጫ ሊቀየር አይችልም።',
        ],
        'role' => [
            'prohibited' => 'ሚና ከመገለጫ ሊቀየር አይችልም።',
        ],
        'phone_verified_at' => [
            'prohibited' => 'የስልክ ማረጋገጫ ከመገለጫ ሊቀየር አይችልም።',
        ],
    ],
    'attributes' => [
        'name' => 'ስም',
        'phone' => 'የስልክ ቁጥር',
        'password' => 'የይለፍ ቃል',
        'password_confirmation' => 'የይለፍ ቃል ማረጋገጥ',
        'code' => 'የማረጋገጫ ኮድ',
        'avatar' => 'አቫታር',
        'role' => 'ሚና',
        'status' => 'የመለያ ሁኔታ',
        'phone_verified_at' => 'የስልክ ማረጋገጫ',
        'locale' => 'ቋንቋ',
    ],
];
