using System;

namespace GLOW.Scenes.QuestContentTop.Domain.ValueObject
{
    public record CampaignBalloonLimitTime(TimeSpan Value)
    {
        public static CampaignBalloonLimitTime Empty { get; } = new(TimeSpan.Zero);
        public bool IsEmpty => ReferenceEquals(this, Empty);
    }
}
