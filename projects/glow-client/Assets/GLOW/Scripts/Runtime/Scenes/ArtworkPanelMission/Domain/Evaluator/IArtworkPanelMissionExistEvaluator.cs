using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.QuestContentTop.Domain.ValueObject;

namespace GLOW.Scenes.ArtworkPanelMission.Domain.Evaluator
{
    public interface IArtworkPanelMissionExistEvaluator
    {
        ArtworkPanelMissionExistFlag IsExistValidArtworkPanelMission(MasterDataId mstEventId);
    }
}