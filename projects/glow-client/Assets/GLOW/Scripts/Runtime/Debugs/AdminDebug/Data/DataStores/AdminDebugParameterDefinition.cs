using System;
using System.Runtime.Serialization;
using Newtonsoft.Json;

namespace GLOW.Debugs.AdminDebug.Data.DataStores
{
    [Serializable]
    public class AdminDebugParameterDefinition
    {
        [DataMember(Name = "type")][JsonProperty("type")]
        public string Type { get; set; }

        [DataMember(Name = "min")][JsonProperty("min")]
        public int? Min { get; set; }

        [DataMember(Name = "max")][JsonProperty("max")]
        public int? Max { get; set; }

        [DataMember(Name = "description")][JsonProperty("description")]
        public string Description { get; set; }
    }
}
