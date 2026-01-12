using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.PassShop.Domain.Factory;
using Zenject;

namespace GLOW.Scenes.BeginnerMission.Domain.UseCase
{
    public class UpdateBeginnerMissionAndPassStatusUseCase
    {
        [Inject] IBeginnerMissionFinishedEvaluator BeginnerMissionFinishedEvaluator { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IHeldPassEffectDisplayModelFactory HeldPassEffectDisplayModelFactory { get; }

        public HomeBeginnerMissionIconAndPassStatusModel UpdateBeginnerMissionStatusAndGetPassStatus()
        {
            var beginnerMissionFinished = BeginnerMissionFinishedEvaluator.CheckBeginnerMissionAllCompleted();
            SaveBadgeStatus(beginnerMissionFinished);

            var heldPassEffectDisplayModels = HeldPassEffectDisplayModelFactory.GetHeldPassEffectDisplayModels(
                new HashSet<ShopPassEffectType>());

            return new HomeBeginnerMissionIconAndPassStatusModel(
                beginnerMissionFinished,
                heldPassEffectDisplayModels);
        }

        void SaveBadgeStatus(BeginnerMissionFinishedFlag beginnerMissionFinished)
        {
            var fetchModel = GameRepository.GetGameFetch();
            var updatedFetchModel = fetchModel with
            {
                MissionStatusModel = new MissionStatusModel(new MissionAllCompleted(beginnerMissionFinished))
            };
            GameManagement.SaveGameFetch(updatedFetchModel);
        }
    }
}
