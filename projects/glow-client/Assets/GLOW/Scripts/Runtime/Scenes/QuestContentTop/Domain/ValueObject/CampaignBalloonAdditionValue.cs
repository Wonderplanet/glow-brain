namespace GLOW.Scenes.QuestContentTop.Domain.ValueObject
{
    public record CampaignBalloonAdditionValue(float Value)
    {
        public static CampaignBalloonAdditionValue Empty { get; } = new(0f);
        public bool IsEmpty => ReferenceEquals(this, Empty);
    }
}
