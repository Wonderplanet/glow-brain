using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;
using Newtonsoft.Json;

namespace GLOW.Core.Data.Modules.Serializer
{
    public class ObscuredIntJsonConverter : JsonConverter
    {
        public override bool CanConvert(Type objectType)
        {
            return objectType == typeof(ObscuredInt);
        }

        public override object ReadJson(
            JsonReader reader,
            Type objectType,
            object existingValue,
            JsonSerializer serializer)
        {
            if (reader.TokenType == JsonToken.Null)
            {
                return (ObscuredInt)0;
            }

            return (ObscuredInt)Convert.ToInt32(reader.Value, reader.Culture);
        }

        public override void WriteJson(
            JsonWriter writer,
            object value,
            JsonSerializer serializer)
        {
            if (value == null)
            {
                writer.WriteNull();
                return;
            }

            var obscuredInt = (ObscuredInt)value;
            writer.WriteValue((int)obscuredInt);
        }
    }
}
