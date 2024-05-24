<?php

namespace App\Ai\Enum;


enum AiService: string
{
    case NULL = 'null';
    case GEMINI = 'gemini';
    case CHAT_GPT = 'gpt';

}
