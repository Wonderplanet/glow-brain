using System;
using Newtonsoft.Json;

namespace WPFramework.Debugs.Environment.Data.Data
{
    [Serializable]
    public record DebugEnvironmentTargetData(string Env)
    {
        [JsonProperty("env")]
        public string Env { get; } = Env;
    }
}
