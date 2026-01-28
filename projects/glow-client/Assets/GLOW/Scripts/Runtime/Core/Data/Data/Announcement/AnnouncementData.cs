using System;
using System.Runtime.Serialization;
using GLOW.Core.Domain.AnnouncementWindow;
using Newtonsoft.Json;

namespace GLOW.Core.Data.Data.Announcement
{
    [Serializable]
    public class AnnouncementData
    {
        [DataMember(Name = "informationId")][JsonProperty("informationId")]
        public string InformationId { get; set; }
        [DataMember(Name = "osType")][JsonProperty("osType")]
        public string OsType { get; set; }
        [DataMember(Name = "lastUpdatedAt")][JsonProperty("lastUpdatedAt")]
        public DateTimeOffset LastUpdatedAt { get; set; }
        [DataMember(Name = "createdAt")][JsonProperty("createdAt")]
        public DateTimeOffset CreatedAt { get; set; }
        [DataMember(Name = "contentsUrl")][JsonProperty("contentsUrl")]
        public string ContentsUrl { get; set; }
        [DataMember(Name = "title")][JsonProperty("title")]
        public string Title { get; set; }
        [DataMember(Name = "bannerUrl")][JsonProperty("bannerUrl")]
        public string BannerUrl { get; set; }
        [DataMember(Name = "category")][JsonProperty("category")]
        public AnnouncementCategory AnnouncementCategory { get; set; }
        [DataMember(Name = "status")][JsonProperty("status")]
        public AnnouncementStatus Status { get; set; }
        [DataMember(Name = "startAt")][JsonProperty("startAt")]
        public DateTimeOffset StartAt { get; set; }
        [DataMember(Name = "endAt")][JsonProperty("endAt")]
        public DateTimeOffset EndAt { get; set; }
    }
}