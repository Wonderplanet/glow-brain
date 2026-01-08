using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.Localization;
using Newtonsoft.Json;

namespace GLOW.Core.Domain.Modules.Serializers
{
    public class JsonLanguageEnumConverter : JsonConverter
    {
        public override void WriteJson(JsonWriter writer, object value, JsonSerializer serializer)
        {
            if (value == null)
            {
                writer.WriteNull();
                return;
            }

            var enumValue = (Language)value;
            var customValue = LanguageConverter.ToLocaleCode(enumValue);
            writer.WriteValue(customValue);
        }

        public override object ReadJson(JsonReader reader, Type objectType, object existingValue, JsonSerializer serializer)
        {
            if (reader.TokenType == JsonToken.Null)
            {
                return Nullable.GetUnderlyingType(objectType) != null ? null : Activator.CreateInstance(objectType);
            }

            // NOTE: enumにはハイフネーションは利用できないため変換処理を行う
            var enumString = reader.Value?.ToString();
            return enumString switch
            {
                null      => Nullable.GetUnderlyingType(objectType) != null ? null : Activator.CreateInstance(objectType),
                _         => LanguageConverter.ToLanguage(enumString),
            };
        }

        public override bool CanConvert(Type objectType)
        {
            // NOTE: Language 型のみを対象としたコンバート処理になるように制限を行う
            return objectType == typeof(Language);
        }
    }
}
