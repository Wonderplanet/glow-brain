using System;
using System.Runtime.Serialization;
using Newtonsoft.Json;

namespace GLOW.Core.Data.Data.Announcement
{
    [Serializable]
    public class AnnouncementResultData
    {
        [DataMember(Name = "informations")][JsonProperty("informations")]
        public AnnouncementData[] AnnouncementData { get; set; }
    }
}