using System.Text;
using Newtonsoft.Json;

namespace WPFramework.Modules.Environment
{
    public sealed class JsonEnvironmentDataParser : IEnvironmentDataParser
    {
        T IEnvironmentDataParser.Parse<T>(byte[] bytes)
        {
            return JsonConvert.DeserializeObject<T>(Encoding.UTF8.GetString(bytes));
        }

        T IEnvironmentDataParser.Parse<T>(string text)
        {
            return JsonConvert.DeserializeObject<T>(text);
        }
    }
}
