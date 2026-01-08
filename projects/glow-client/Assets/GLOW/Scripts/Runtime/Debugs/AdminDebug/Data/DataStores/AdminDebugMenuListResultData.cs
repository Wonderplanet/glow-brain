using System;
using System.Runtime.Serialization;
using Newtonsoft.Json;

namespace GLOW.Debugs.AdminDebug.Data.DataStores
{
    [Serializable]
    public class AdminDebugMenuListResultData
    {
        [DataMember(Name = "commands")][JsonProperty("commands")]
        public AdminDebugCommandData[] DebugCommand { get; set; }
    }
}
