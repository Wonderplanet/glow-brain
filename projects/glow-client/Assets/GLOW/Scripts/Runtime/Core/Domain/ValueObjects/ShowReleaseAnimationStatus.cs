namespace GLOW.Core.Domain.ValueObjects
{
    public record ShowReleaseAnimationStatus(MasterDataId NewReleaseMstQuestId ,MasterDataId NewReleaseMstStageId)
    {
        public static ShowReleaseAnimationStatus Empty { get; } = new ShowReleaseAnimationStatus(MasterDataId.Empty, MasterDataId.Empty);
    }
}
