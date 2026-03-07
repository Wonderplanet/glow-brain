using GLOW.Core.Domain.Models;
using GLOW.Scenes.QuestContentTop.Domain.ValueObject;

namespace GLOW.Scenes.QuestContentTop.Domain.UseCaseModel
{
    public record LatestEventUseCaseModel(
        MstEventModel LatestMstEventModel,
        ArtworkPanelMissionExistFlag IsArtworkPanelMissionExist)
    {
        public static LatestEventUseCaseModel Empty { get; } = new(
            MstEventModel.Empty,
            ArtworkPanelMissionExistFlag.False);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}