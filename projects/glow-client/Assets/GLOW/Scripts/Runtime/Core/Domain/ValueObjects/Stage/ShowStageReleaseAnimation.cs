namespace GLOW.Core.Domain.ValueObjects.Stage
{
    public record ShowStageReleaseAnimation(MasterDataId TargetMstStageId)
    {
        public bool ShouldShow => TargetMstStageId != MasterDataId.Empty;
        public static ShowStageReleaseAnimation Empty { get; } = new ShowStageReleaseAnimation(MasterDataId.Empty);
    };
}
