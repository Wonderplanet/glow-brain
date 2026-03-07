using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.QuestContentTop.Domain.ValueObject;
using Zenject;

namespace GLOW.Scenes.ArtworkPanelMission.Domain.Evaluator
{
    public class ArtworkPanelMissionExistEvaluator : IArtworkPanelMissionExistEvaluator
    {
        [Inject] IMstArtworkPanelMissionDataRepository MstArtworkPanelMissionDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        
        ArtworkPanelMissionExistFlag IArtworkPanelMissionExistEvaluator.IsExistValidArtworkPanelMission(MasterDataId mstEventId)
        {
            var artworkPanelMissionModels = MstArtworkPanelMissionDataRepository
                .GetMstArtworkPanelMissionModels(mstEventId);
            
            if (artworkPanelMissionModels.IsEmpty()) return ArtworkPanelMissionExistFlag.False;
            
            var isExistValidArtworkPanelMission = artworkPanelMissionModels
                .Any(m => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, m.StartDate.Value, m.EndDate.Value));

            return isExistValidArtworkPanelMission
                ? ArtworkPanelMissionExistFlag.True
                : ArtworkPanelMissionExistFlag.False;
        }
    }
}