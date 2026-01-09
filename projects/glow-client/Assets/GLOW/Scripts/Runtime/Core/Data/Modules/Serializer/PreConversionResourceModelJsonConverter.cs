using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.ValueObjects;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;

namespace GLOW.Core.Data.Modules.Serializer
{
    public class PreConversionResourceModelJsonConverter : JsonConverter
    {
        public override bool CanConvert(Type objectType)
            => objectType == typeof(PreConversionResourceModel);

        public override object ReadJson(
            JsonReader reader,
            Type objectType,
            object existingValue,
            JsonSerializer serializer
        )
        {
            if (reader.TokenType == JsonToken.Null)
                return PreConversionResourceModel.Empty;

            var jo = JObject.Load(reader);

            if (!jo.HasValues)
                return PreConversionResourceModel.Empty;

            var resourceType = jo["ResourceType"]?.ToObject<ResourceType>(serializer) ?? ResourceType.Coin;
            var resourceId = jo["ResourceId"]?.ToObject<MasterDataId>(serializer) ?? MasterDataId.Empty;
            var resourceAmount = jo["ResourceAmount"]?.ToObject<ObscuredPlayerResourceAmount>(serializer) ?? ObscuredPlayerResourceAmount.Empty;

            var preConversion = new PreConversionResourceModel(resourceType, resourceId, resourceAmount);

            return preConversion;
        }

        public override void WriteJson(
            JsonWriter writer,
            object value,
            JsonSerializer serializer
        )
        {
            if (value is PreConversionResourceModel model)
            {
                if (model.IsEmpty())
                {
                    writer.WriteNull();
                    return;
                }

                writer.WriteStartObject();

                writer.WritePropertyName("ResourceType");
                serializer.Serialize(writer, model.ResourceType);

                writer.WritePropertyName("ResourceId");
                serializer.Serialize(writer, model.ResourceId);

                writer.WritePropertyName("ResourceAmount");
                serializer.Serialize(writer, model.ResourceAmount);

                writer.WriteEndObject();
            }
            else
            {
                writer.WriteNull();
            }
        }
    }
}
