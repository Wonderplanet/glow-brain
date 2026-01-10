using System;
using System.Runtime.Serialization;
using Newtonsoft.Json;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Debugs.AdminDebug.Data.DataStores
{
    [Serializable]
    public class AdminDebugCommandData
    {
        [DataMember(Name = "command")][JsonProperty("command")]
        public string Command
        {
            get => _obscured_command;
            set => _obscured_command = value ?? string.Empty;
        }
        [DataMember(Name = "name")][JsonProperty("name")]
        public string Name
        {
            get => _obscured_name;
            set => _obscured_name = value ?? string.Empty;
        }
        [DataMember(Name = "description")][JsonProperty("description")]
        public string Description
        {
            get => _obscured_description;
            set => _obscured_description = value ?? string.Empty;
        }

        ObscuredString _obscured_command;
        ObscuredString _obscured_name;
        ObscuredString _obscured_description;
    }
}
