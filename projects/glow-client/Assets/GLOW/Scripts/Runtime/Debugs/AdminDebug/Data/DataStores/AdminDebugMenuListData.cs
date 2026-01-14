using System;
using System.Runtime.Serialization;
using Newtonsoft.Json;

namespace GLOW.Debugs.AdminDebug.Data.DataStores
{
    [Serializable]
    public class AdminDebugMenuListData
    {
        [DataMember(Name = "debugCommands")][JsonProperty("debugCommands")]
        public AdminDebugCommandData[] DebugCommandData { get; set; }
    }
}
