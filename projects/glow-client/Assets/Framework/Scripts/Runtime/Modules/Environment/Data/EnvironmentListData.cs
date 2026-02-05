using System;
using System.Runtime.Serialization;
using Newtonsoft.Json;

namespace WPFramework.Modules.Environment
{
    [Serializable]
    public partial record EnvironmentListData(EnvironmentData[] Environments)
    {
        [DataMember(Name = "environments")][JsonProperty("environments")]
        public EnvironmentData[] Environments { get; set; } = Environments;
    }
}
