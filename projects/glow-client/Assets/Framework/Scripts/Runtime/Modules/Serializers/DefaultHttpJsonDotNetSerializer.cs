using System;
using System.Text;
using Newtonsoft.Json;
using UnityHTTPLibrary;

namespace WPFramework.Modules.Serializers
{
    public sealed class DefaultHttpJsonDotNetSerializer : ISerializer
    {
        byte[] ISerializer.Serialize(object obj)
        {
            throw new NotImplementedException();
        }

        T ISerializer.Deserialize<T>(byte[] data)
        {
            if (typeof(T) == typeof(string))
            {
                object val = Encoding.UTF8.GetString(data);
                return (T)val;
            }

            var dataAsString = Encoding.UTF8.GetString(data);

            // NOTE: 日付データは全てUTCとして取り扱うため明示的に設定する
            //       明示的に指定されていない場合DateTimeのKindはLocalとなる
            var jsonSerializerSettings = new JsonSerializerSettings
            {
                DateTimeZoneHandling = DateTimeZoneHandling.Utc,
            };
            return JsonConvert.DeserializeObject<T>(dataAsString, jsonSerializerSettings);
        }
    }
}
