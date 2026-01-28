using System;
using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.Modules.Localization
{
    public static class LanguageConverter
    {
        // <summary>
        // [language]-[region] という形式のカルチャ名です。
        // この名前は、ISO 639 の (2 つの小文字から成る) 言語に関連付けられたカルチャコードと、ISO 3166 の (2 つの大文字から成る) 国または地域に関連付けられたサブカルチャコードを組み合わせたものです。
        // 例えば、Language English は 'en'、Regional English(UK) は 'en-GB'、Regional English(US) は 'en-US' です。
        // 非標準の識別子を表す場合は任意の文字列の値が使用可能です。
        // </summary>

        public static string ToLocaleCode(Language language)
        {
            // NOTE: 対応言語が増えた際に追加する
            return language switch
            {
                Language.ja      => "ja",
                // Language.En      => "en",
                // Language.Zh_Hant => "zh-Hant",
                _                => "ja",
            };
        }

        public static Language ToLanguage(string localeCode)
        {
            // NOTE: enumでパースするためにはハイフネーションは利用できないため変換を行う
            var target = localeCode.Replace("-", "_");
            return Enum.TryParse(target, true, out Language language) ? language : Language.ja;
        }

        public static string ToUrlParameterCode(Language language)
        {
            // 日本はjpn、その他の国は全てenを返す
            return language == Language.ja ? "jpn" : "en";
        }
    }
}
