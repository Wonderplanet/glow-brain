using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using GLOW.Scenes.ArtworkPanelMission.Domain.Evaluator;
using GLOW.Scenes.QuestContentTop.Domain.UseCaseModel;
using Zenject;

namespace GLOW.Scenes.QuestContentTop.Domain
{
    public class GetLatestEventInfoUseCase
    {
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IArtworkPanelMissionExistEvaluator ArtworkPanelMissionExistEvaluator { get; }

        public LatestEventUseCaseModel GetLatestEventInfo()
        {
            var latestMstEventModel = MstEventDataRepository.GetEvents()
                .Where(m => CalculateTimeCalculator.IsValidTime(TimeProvider.Now, m.StartAt, m.EndAt))
                .MaxBy(m =>m.StartAt) ?? MstEventModel.Empty;

            var isArtworkPanelMissionExist = ArtworkPanelMissionExistEvaluator.IsExistValidArtworkPanelMission(
                latestMstEventModel.Id);

            return new LatestEventUseCaseModel(latestMstEventModel, isArtworkPanelMissionExist);
        }
    }
}
