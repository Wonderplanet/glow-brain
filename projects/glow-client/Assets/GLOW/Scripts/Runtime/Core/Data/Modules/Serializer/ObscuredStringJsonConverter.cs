using System;
using Newtonsoft.Json;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Data.Modules.Serializer
{
    public class ObscuredStringJsonConverter : JsonConverter
    {
        public override bool CanConvert(Type objectType)
            => objectType == typeof(ObscuredString);

        public override object ReadJson(
            JsonReader reader,
            Type objectType,
            object existingValue,
            JsonSerializer serializer
        )
        {
            var value = reader.Value as string ?? string.Empty;
            return (ObscuredString)value;
        }

        public override void WriteJson(
            JsonWriter writer,
            object value,
            JsonSerializer serializer
        )
        {
            var obscured = value as ObscuredString;
            writer.WriteValue(obscured?.ToString() ?? string.Empty);
        }
    }
}
