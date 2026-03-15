using System;
using System.Runtime.Serialization;
using Newtonsoft.Json;

namespace GLOW.Core.Data.Data.Announcement
{
    public class AnnouncementLastUpdatedData
    {
        [DataMember(Name = "information_ios")][JsonProperty("information_ios")]
        public DateTimeOffset? InformationIos { get; set; }
        [DataMember(Name = "operation_ios")][JsonProperty("operation_ios")]
        public DateTimeOffset? OperationIos { get; set; }
        [DataMember(Name = "information_android")][JsonProperty("information_android")]
        public DateTimeOffset? InformationAndroid { get; set; }
        [DataMember(Name = "operation_android")][JsonProperty("operation_android")]
        public DateTimeOffset? OperationAndroid { get; set; }
    }
}