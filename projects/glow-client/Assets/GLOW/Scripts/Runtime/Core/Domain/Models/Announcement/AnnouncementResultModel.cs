using System.Collections.Generic;

namespace GLOW.Core.Domain.Models.Announcement
{
    public record AnnouncementResultModel(IReadOnlyList<AnnouncementModel> AnnouncementModels)
    {
        public static AnnouncementResultModel Empty { get; } = new(new List<AnnouncementModel>());
    }
}
