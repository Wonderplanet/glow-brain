using System.Text;
using Newtonsoft.Json;
using SFStorage;

namespace WPFramework.Modules.Serializers
{
    public sealed class DefaultSFStorageJsonDotNetSerializer : ISerializer
    {
        byte[] ISerializer.Serialize(object obj)
        {
            var jsonSerializerSettings = new JsonSerializerSettings
            {
                DateTimeZoneHandling = DateTimeZoneHandling.Utc,
            };
            var jsonString = JsonConvert.SerializeObject(obj, jsonSerializerSettings);
            return Encoding.UTF8.GetBytes(jsonString);
        }

        T ISerializer.Deserialize<T>(byte[] data)
        {
            var jsonString = Encoding.UTF8.GetString(data);
            var jsonSerializerSettings = new JsonSerializerSettings
            {
                DateTimeZoneHandling = DateTimeZoneHandling.Utc,
            };
            return JsonConvert.DeserializeObject<T>(jsonString, jsonSerializerSettings);
        }
    }
}
